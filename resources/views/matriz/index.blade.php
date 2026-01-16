@extends('layouts.app')

@section('title', 'Matriz Extendida - SENA')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <main>
                <div class="search-container">
                    <div class="search-box" id="searchBox">
                        <i class="bi bi-search search-icon" aria-hidden="true"></i>
                        <input 
                            type="text" 
                            class="search-input" 
                            id="searchInput"
                            placeholder="Buscar programa por nombre, código, técnico, tecnólogo..."
                            autocomplete="off"
                        >
                    </div>
                    <div class="suggestions-dropdown" id="suggestionsDropdown"></div>
                </div>

                <div class="search-stats" id="searchStats" style="display: none;">
                    Mostrando <strong id="resultCount">0</strong> programas
                </div>

                @if(count($programas) > 0)
                    <div class="programs-list" id="programsList">
                        @foreach($programas as $programa)
                            <a href="{{ route('matriz.show', $programa->id_prog) }}" 
                               class="program-card" 
                               data-nombre="{{ strtolower($programa->nombre) }}"
                               data-codigo="{{ $programa->id_prog }}"
                               data-nivel="{{ strtolower($programa->nivel) }}"
                               data-version="{{ $programa->version }}">
                                <div class="nivel-line"><span class="nivel-badge">{{ $programa->nivel }}</span></div>
                                <h3>{{ $programa->nombre }}</h3>
                                <div class="info">
                                    <div class="info-item">
                                        <span>Código</span>
                                        <strong>{{ $programa->id_prog }}</strong>
                                    </div>
                                    <div class="info-item">
                                        <span>Versión</span>
                                        <strong>{{ $programa->version }}</strong>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <h3>No hay programas registrados</h3>
                        <p>Carga un programa desde Excel para comenzar</p>
                    </div>
                @endif

                <div style="margin: 20px 0 10px 0; text-align: left;">
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left" style="margin-right:8px;" aria-hidden="true"></i>
                        Volver al Inicio
                    </a>
                </div>
            </main>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const suggestionsDropdown = document.getElementById('suggestionsDropdown');
        const searchBox = document.getElementById('searchBox');
        const programsList = document.getElementById('programsList');
        const searchStats = document.getElementById('searchStats');
        const resultCount = document.getElementById('resultCount');
        
        const programas = {!! json_encode($programas->map(function($p) {
            return [
                'id' => $p->id_prog,
                'nombre' => $p->nombre,
                'nivel' => $p->nivel,
                'version' => $p->version,
            ];
        })->values()) !!};
        
        // Normalizar texto (remover tildes y caracteres especiales)
        function normalizar(texto) {
            return texto.toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s]/g, '');
        }
        
        // Búsqueda en tiempo real (insensible a mayúsculas/acentos + atajos para técnico/tecnólogo)
        function isMatchPrograma(p, query, queryNorm) {
            const nombreNorm = normalizar(p.nombre);
            const nivelNorm = normalizar(p.nivel);
            const codigoStr = p.id.toString();
            const qDigits = query.replace(/\D+/g, '');

            const isTec = /tecnic/.test(queryNorm);      // tecnico, técnico, tecnic...
            const isTecno = /tecnolo/.test(queryNorm);   // tecnologo, tecnólogo, tecnolo...

            if (isTec) return nivelNorm.includes('tecnico');
            if (isTecno) return nivelNorm.includes('tecnologo');

            return (
                nombreNorm.includes(queryNorm) ||
                nivelNorm.includes(queryNorm) ||
                (qDigits.length > 0 && codigoStr.includes(qDigits))
            );
        }

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            const queryNorm = normalizar(query);

            if (query.length === 0) {
                const allCards = programsList.querySelectorAll('.program-card');
                allCards.forEach(card => card.style.display = 'block');
                suggestionsDropdown.innerHTML = '';
                suggestionsDropdown.classList.remove('active');
                searchStats.style.display = 'none';
                return;
            }

            // Filtrar programas con la misma lógica para tarjetas y sugerencias
            const matches = programas.filter(p => isMatchPrograma(p, query, queryNorm));

            // Actualizar tarjetas visibles
            const allCards = programsList.querySelectorAll('.program-card');
            let visibleCount = 0;
            allCards.forEach(card => {
                const p = {
                    id: card.dataset.codigo,
                    nombre: card.dataset.nombre,
                    nivel: card.dataset.nivel
                };
                const cardMatch = isMatchPrograma(p, query, queryNorm);
                card.style.display = cardMatch ? 'block' : 'none';
                if (cardMatch) visibleCount++;
            });

            // Actualizar estadísticas
            resultCount.textContent = visibleCount;
            searchStats.style.display = 'block';

            // Mostrar sugerencias
            if (matches.length > 0) {
                const maxSuggestions = Math.min(matches.length, 5);
                suggestionsDropdown.innerHTML = matches.slice(0, maxSuggestions).map(p => {
                    const nivelKey = normalizar(p.nivel);
                    const nivelColor = nivelKey.includes('tecnologo') ? '#39A900' : '#ffc107';
                    const url = '{{ url("matriz") }}/' + p.id;
                    return `
                        <a href="${url}" class="suggestion-item">
                            <div class="suggestion-name">${p.nombre}</div>
                            <div class="suggestion-meta">
                                <span>${p.id}</span>
                                <span style="background: ${nivelColor}; color: white;">${p.nivel}</span>
                            </div>
                        </a>
                    `;
                }).join('');
                suggestionsDropdown.classList.add('active');
            } else {
                suggestionsDropdown.innerHTML = '<div class="no-suggestions">No se encontraron programas</div>';
                suggestionsDropdown.classList.add('active');
            }
        });
        
        // Cerrar sugerencias al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!searchBox.contains(e.target)) {
                suggestionsDropdown.classList.remove('active');
            }
        });
        
        // Reabrir sugerencias al hacer focus
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length > 0) {
                this.dispatchEvent(new Event('input'));
            }
        });
    </script>
@endsection

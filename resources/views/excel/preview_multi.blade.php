@extends('layouts.app')

@section('title', 'Previsualización múltiple - SENA')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <main>
                <div class="program-info">
                    <h2><i class="bi bi-journal-text" aria-hidden="true"></i> Previsualización de archivos</h2>
                    <div class="info-grid">
                        <div class="info-item"><strong>Archivos</strong><span>{{ count($previews) }}</span></div>
                    </div>
                </div>

                @php($validos = collect($previews)->where('ok', true)->values())
                @php($invalidos = collect($previews)->where('ok', false)->values())
                @php($duplicados = collect($previews)->filter(function($p){ return !empty($p['duplicate']); }))

                @if($invalidos->count() > 0)
                <div class="alert alert-error">
                    <span>Algunos archivos no se pueden cargar. Revisa el log en el ícono de notificaciones del encabezado.</span>
                </div>
                @endif

                @if($duplicados->count() > 0)
                <div class="alert alert-warning">
                    <span>Se detectaron códigos de programa repetidos en esta carga. Solo se permite uno por código.</span>
                </div>
                @endif

                <div class="preview-section">
                    <h3>Resumen por archivo</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                        <th style="width: 5%; text-align:center;"><input type="checkbox" id="selectAll" title="Seleccionar todos"></th>
                                        <th>Archivo</th>
                                        <th>Programa</th>
                                        <th>Competencias</th>
                                        <th>Resultados</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previews as $p)
                                    <tr>
                                            <td style="text-align:center;">
                                                @if($p['ok'])
                                                    <input type="checkbox" class="select-file" data-filename="{{ $p['fileName'] }}" aria-label="Seleccionar archivo">
                                                @else
                                                    <input type="checkbox" disabled aria-label="No seleccionable">
                                                @endif
                                            </td>
                                        <td><strong>{{ $p['originalName'] ?? $p['fileName'] }}</strong></td>
                                        <td>
                                            @if($p['ok'])
                                                <div><strong>Código:</strong> {{ $p['codigo'] }}</div>
                                                <div><strong>Nombre:</strong> {{ $p['nombre'] }}</div>
                                                <div><strong>Nivel/Versión:</strong> {{ $p['nivel'] }} / {{ $p['version'] }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($p['ok'])
                                                {{ count($p['competencias']) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($p['ok'])
                                                {{ collect($p['competencias'])->reduce(function($carry,$c){return $carry + count($c['resultados']);},0) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($p['duplicate']))
                                                <span class="pill pill-warning" title="Código repetido">Repetido</span>
                                            @elseif($p['ok'])
                                                <span class="pill pill-success">Válido</span>
                                            @else
                                                <span class="pill pill-danger" title="{{ $p['error'] ?? 'Error' }}">Inválido</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($p['ok'])
                                                <form action="{{ route('excel.preview.file') }}" method="POST" style="display:inline-block; margin-right:6px;">
                                                    @csrf
                                                    <input type="hidden" name="file_name" value="{{ $p['fileName'] }}">
                                                    <button type="submit" class="btn btn-secondary"><i class="bi bi-eye" aria-hidden="true" style="margin-right:6px;"></i>Previsualizar</button>
                                                </form>
                                                <form action="{{ route('excel.process') }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    <input type="hidden" name="file_name" value="{{ $p['fileName'] }}">
                                                    <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle" aria-hidden="true" style="margin-right:6px;"></i>Cargar</button>
                                                </form>
                                            @else
                                                @if(!empty($p['duplicate']))
                                                    <span class="pill pill-warning" style="margin-right:6px;">Código repetido</span>
                                                    <form action="{{ route('excel.preview.file') }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        <input type="hidden" name="file_name" value="{{ $p['fileName'] }}">
                                                        <button type="submit" class="btn btn-secondary"><i class="bi bi-eye" aria-hidden="true" style="margin-right:6px;"></i>Previsualizar</button>
                                                    </form>
                                                @else
                                                <span style="opacity:.7;">Sin acciones</span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($validos->count() > 0)
                <form action="{{ route('excel.process.multi') }}" method="POST">
                    @csrf
                    @foreach($validos as $v)
                        <input type="hidden" name="file_names[]" value="{{ $v['fileName'] }}">
                    @endforeach
                    <div class="buttons">
                        <a href="{{ route('excel.upload') }}" class="btn btn-secondary"><i class="bi bi-arrow-left" aria-hidden="true" style="margin-right:8px;"></i>Volver</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-upload" aria-hidden="true" style="margin-right:8px;"></i>Cargar todos los válidos</button>
                    </div>
                </form>
                @else
                <div class="buttons">
                    <a href="{{ route('excel.upload') }}" class="btn btn-secondary"><i class="bi bi-arrow-left" aria-hidden="true" style="margin-right:8px;"></i>Volver</a>
                </div>
                @endif
            </main>
                        <button type="button" class="floating-scroll-bottom" id="scrollBottomBtnMulti" title="Bajar al final">
                                <i class="bi bi-arrow-down" aria-hidden="true"></i>
                                                    <button type="submit" class="btn btn-success"><i class="bi bi-eye" aria-hidden="true" style="margin-right:6px;"></i>Previsualizar</button>
        </div>
    (function(){
        const btn = document.getElementById('scrollBottomBtnMulti');
        const icon = btn ? btn.querySelector('i') : null;
        const selectAll = document.getElementById('selectAll');
        const checkboxes = Array.from(document.querySelectorAll('.select-file'));
        const form = document.getElementById('processMultiForm');
        const inputsHolder = document.getElementById('selectedInputs');
        const btnSelected = document.getElementById('btnProcessSelected');
        const btnAll = document.getElementById('btnProcessAll');
        const validFileNames = @json($validos->map(function($v){ return $v['fileName']; }));
        function update(){
            const y = window.scrollY || document.documentElement.scrollTop;
            const h = document.documentElement.scrollHeight;
            const vh = window.innerHeight;
            const atBottom = (y + vh) >= (h - 10);
            if (btn && icon){
                if (atBottom){ btn.dataset.dir = 'up'; icon.className = 'bi bi-arrow-up'; btn.title = 'Subir al inicio'; }
                else { btn.dataset.dir = 'down'; icon.className = 'bi bi-arrow-down'; btn.title = 'Bajar al final'; }
            }
        }
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('load', update);
        btn?.addEventListener('click', function(){
            const dir = btn.dataset.dir || 'down';
            if (dir === 'up') window.scrollTo({ top: 0, behavior: 'smooth' });
            else window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' });
        });

        // Seleccionar todos
        selectAll?.addEventListener('change', function(){
            checkboxes.forEach(cb => { if (!cb.disabled) cb.checked = selectAll.checked; });
        });
        // Procesar seleccionados o todos
        function submitWith(names){
            inputsHolder.innerHTML = '';
            names.forEach(n => {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = 'file_names[]'; i.value = n;
                inputsHolder.appendChild(i);
            });
            form.submit();
        }
        btnSelected?.addEventListener('click', function(e){
            e.preventDefault();
            const selected = checkboxes.filter(cb => cb.checked && !cb.disabled).map(cb => cb.dataset.filename);
            if (selected.length === 0){
                if (window.showToast) window.showToast('Selecciona al menos un archivo válido', 'warning');
                return;
            }
            submitWith(selected);
        });
        btnAll?.addEventListener('click', function(e){
            e.preventDefault();
            submitWith(validFileNames);
        });
    })();
</script>
@endsection

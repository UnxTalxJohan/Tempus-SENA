<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'App CIDE - SENA')</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <!-- Pantalla de Carga Global -->
    <div class="global-loader" id="globalLoader">
        <div class="loader-content">
            <img src="{{ asset('images/logo-sena.svg') }}" alt="SENA" class="loader-logo">
            <div class="spinner"></div>
        </div>
    </div>

    <header class="global-header">
        <div class="header-container">
            <div class="logo-section">
                <img src="{{ asset('images/logo-sena.svg') }}" alt="Logo SENA">
                <span class="logo-text">CIDE                                            </span>
            </div>

            <div class="search-section">
                <div class="header-search-box">
                    <i class="bi bi-search header-search-icon"></i>
                    <input 
                        type="text" 
                        class="header-search-input" 
                        id="globalSearch"
                        placeholder="Buscar páginas: matriz, cargar, inicio..."
                        autocomplete="off"
                    >
                </div>
                <div class="global-suggestions" id="globalSuggestions" style="display: none;"></div>
            </div>

            <nav class="nav-section">
                @auth
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        Inicio
                    </a>
                    <a href="{{ route('matriz.index') }}" class="nav-link {{ request()->routeIs('matriz.*') ? 'active' : '' }}">
                        Matriz
                    </a>
                    <a href="{{ route('excel.upload') }}" class="nav-link {{ request()->routeIs('excel.*') ? 'active' : '' }}">
                        Cargar Excel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}">
                        Iniciar sesión
                    </a>
                @endauth
            </nav>

            <div class="user-section" style="display:flex; align-items:center; gap:12px;">
                @auth
                    <span class="nav-link" style="opacity:0.8;">Hola, {{ auth()->user()->name }}</span>
                    <form id="logoutForm" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link" style="border:none; background:none; cursor:pointer;">Cerrar sesión</button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <!-- Toasts globales -->
    <div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>

    @yield('content')

    <script>
        // === PANTALLA DE CARGA GLOBAL ===
        const globalLoader = document.getElementById('globalLoader');
        
        // Ocultar loader al cargar la página
        window.addEventListener('load', function() {
            setTimeout(() => {
                globalLoader.classList.add('hidden');
            }, 250); // cuarto de segundo
        });
        
        // Mostrar loader al hacer clic en links internos
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && !link.target && link.href.startsWith(window.location.origin)) {
                // Solo para links internos sin target="_blank"
                if (!link.href.includes('#') && link.href !== window.location.href) {
                    globalLoader.classList.remove('hidden');
                }
            }
        });
        
        // Mostrar loader en submit de formularios
        document.addEventListener('submit', function(e) {
            globalLoader.classList.remove('hidden');
        });

        // === BÚSQUEDA GLOBAL DEL HEADER ===
        const globalSearch = document.getElementById('globalSearch');
        const globalSuggestions = document.getElementById('globalSuggestions');
        
        const pages = [
            { name: 'Inicio', url: '{{ route("dashboard") }}', keywords: ['inicio', 'home', 'principal'] },
            { name: 'Matriz Extendida', url: '{{ route("matriz.index") }}', keywords: ['matriz', 'competencias', 'resultados', 'ver'] },
            { name: 'Cargar Excel', url: '{{ route("excel.upload") }}', keywords: ['cargar', 'excel', 'importar', 'subir', 'archivo'] }
        ];
        
        if (globalSearch) {
            globalSearch.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                
                if (query.length === 0) {
                    globalSuggestions.style.display = 'none';
                    globalSuggestions.innerHTML = '';
                    return;
                }
                
                // Filtrar solo si hay al menos 1 caracter
                const matches = pages.filter(page => {
                    const nameMatch = page.name.toLowerCase().includes(query);
                    const keywordMatch = page.keywords.some(keyword => keyword.toLowerCase().includes(query));
                    return nameMatch || keywordMatch;
                });
                
                if (matches.length > 0) {
                    const docIcon = `<i class='bi bi-file-earmark icon' aria-hidden='true'></i>`;
                    globalSuggestions.innerHTML = matches.map(page => 
                        `<a href="${page.url}" class="global-suggestion-item">
                            ${docIcon}
                            <strong>${page.name}</strong>
                        </a>`
                    ).join('');
                    globalSuggestions.style.display = 'block';
                } else {
                    globalSuggestions.innerHTML = '<div class="no-suggestions">No se encontraron páginas</div>';
                    globalSuggestions.style.display = 'block';
                }
            });
            
            globalSearch.addEventListener('blur', function() {
                setTimeout(() => {
                    globalSuggestions.style.display = 'none';
                }, 200);
            });
            
            globalSearch.addEventListener('focus', function() {
                if (this.value.trim().length > 0) {
                    this.dispatchEvent(new Event('input'));
                }
            });
        }

        // === TOASTS GLOBALES ===
        // Uso: showToast('mensaje', 'success'|'error'|'warning'|'info', { offsetTop: number })
        window.showToast = function(message, type = 'info', options = {}) {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const top = (options && typeof options.offsetTop === 'number') ? options.offsetTop : 70; // debajo del header
            container.style.top = top + 'px';
            const toast = document.createElement('div');
            toast.className = `toast-item ${type}`;
            const icons = {
                success: 'bi-check-circle',
                error: 'bi-x-circle',
                warning: 'bi-exclamation-triangle',
                info: 'bi-info-circle'
            };
            const icon = icons[type] || icons.info;
            toast.innerHTML = `<i class="bi ${icon}" aria-hidden="true"></i><span>${message}</span><button class="toast-close" aria-label="Cerrar">×</button>`;
            container.appendChild(toast);
            const remove = () => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 200);
            };
            toast.querySelector('.toast-close').addEventListener('click', remove);
            setTimeout(remove, 3500);
        };
    </script>
</body>
</html>

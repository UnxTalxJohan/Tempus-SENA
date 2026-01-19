<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tempus-SENA')</title>
        <!-- -----------------------------------
            Favicon: usar el logo SENA (SVG) en la pestaña
            Se agrega `rel="icon"` apuntando a images/logo-sena.svg
            y un fallback al favicon.ico para navegadores antiguos
            ----------------------------------- -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-sena.svg') }}">
        <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <!-- -----------------------------------
            Safari pinned tab: color verde institucional
            (usa el mismo SVG como máscara)
            ----------------------------------- -->
        <link rel="mask-icon" href="{{ asset('images/logo-sena.svg') }}" color="#00A859">
        <!-- -----------------------------------
            Accent de navegador en móviles: tema verde
            ----------------------------------- -->
        <meta name="theme-color" content="#00A859">
        <!-- -----------------------------------
            Versionado CSS para evitar caché del navegador
            (si cambian estilos, agrega un sufijo distinto)
            ----------------------------------- -->
        <link href="{{ asset('css/style.css') }}?v=login-art-9" rel="stylesheet">
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

    @if(request()->routeIs('login'))
    <!-- Fondo carrusel diagonal global solo en login -->
    <div class="bg-carousel" id="bgCarousel" aria-hidden="true"></div>
    @endif

    <header class="global-header">
        <div class="header-container">
            <div class="logo-section">
                <img src="{{ asset('images/logo-sena.svg') }}" alt="Logo SENA">
                <span class="logo-text">CIDE</span>
            </div>

            @php($appAuthHeader = session('app_auth'))
            @if($appAuthHeader && ($appAuthHeader['rol_id'] ?? 0) == 1)
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
            @endif

            <nav class="nav-section">
                @php($appAuth = session('app_auth'))
                @if($appAuth && ($appAuth['rol_id'] ?? 0) == 1)
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Inicio</a>
                    <a href="{{ route('matriz.index') }}" class="nav-link {{ request()->routeIs('matriz.*') ? 'active' : '' }}">Matriz</a>
                    <a href="{{ route('excel.upload') }}" class="nav-link {{ request()->routeIs('excel.*') ? 'active' : '' }}">Cargar Excel</a>
                @else
                    @if(!request()->routeIs('login'))
                        <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}">Iniciar sesión</a>
                    @endif
                @endif
            </nav>

            <div class="user-section" style="display:flex; align-items:center; gap:12px;">
                @php($appAuth = session('app_auth'))
                @if($appAuth && ($appAuth['rol_id'] ?? 0) == 1)
                    <div class="user-menu" id="userMenu">
                        <button class="user-avatar" id="userMenuBtn" aria-haspopup="true" aria-expanded="false" title="Cuenta">
                            <i class="bi bi-person-circle" aria-hidden="true" style="font-size:22px;"></i>
                        </button>
                        <div class="user-dropdown" id="userDropdown" style="display:none;">
                            <div class="user-info" style="padding:6px 10px 8px 10px;">
                                <div class="user-name" style="font-weight:600;">{{ $appAuth['nombre'] ?? 'admin' }}</div>
                                @if(!empty($appAuth['email']))
                                <div class="user-mail" style="opacity:.75; font-size:12px;">{{ $appAuth['email'] }}</div>
                                @endif
                            </div>
                            <div style="height:1px; background:#eee; margin:6px 0;"></div>
                            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width:100%; text-align:left; background:none; border:none; padding:8px 10px; border-radius:8px; cursor:pointer;">
                                    <i class="bi bi-box-arrow-right" aria-hidden="true" style="margin-right:8px;"></i>
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </header>

    <!-- Toasts globales -->
    <div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>

    @yield('content')

    <!-- -----------------------------------
         Footer global: línea gris + barra verde
         Aparece en todas las vistas (como el header)
         ----------------------------------- -->
    <footer class="global-footer" role="contentinfo">
        <div class="footer-sep" aria-hidden="true"></div>
        <div class="footer-bar">
            <div class="footer-container">
                <div class="footer-left">
                    <img src="{{ asset('images/logo-sena.svg') }}" alt="" aria-hidden="true">
                    <span class="footer-brand">Tempus-SENA</span>
                </div>
                <div class="footer-right">
                    <span>© {{ date('Y') }} SENA</span>
                </div>
            </div>
        </div>
    </footer>

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

        // === MENÚ DE USUARIO ===
        (function(){
            const btn = document.getElementById('userMenuBtn');
            const dd = document.getElementById('userDropdown');
            if (!btn || !dd) return;
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const open = dd.style.display === 'block';
                dd.style.display = open ? 'none' : 'block';
                btn.setAttribute('aria-expanded', open ? 'false' : 'true');
            });
            document.addEventListener('click', (e) => {
                if (!dd.contains(e.target) && !btn.contains(e.target)) {
                    dd.style.display = 'none';
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        })();
    </script>
</body>
</html>

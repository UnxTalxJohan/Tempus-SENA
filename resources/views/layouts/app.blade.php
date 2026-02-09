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
        <!-- Fuentes: Inter (moderna) y Cormorant (legacy, fallback opcional) -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500&display=swap" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}?v=login-art-66" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
@php($bgStyle = (request()->routeIs('dashboard') ? (request('bg') ?: (session('bg_style') ?? 'bubbles')) : (session('bg_style') ?? 'bubbles')))
@if(request()->routeIs('dashboard') && request('bg'))
    @php(session(['bg_style' => request('bg')]))
@endif
@php($bodyClass = request()->routeIs('login')
    ? 'login-page'
    : (session('app_auth')
        ? ((request()->routeIs('dashboard') ? 'dashboard-page ' : '') . 'auth-page auth-bg-' . $bgStyle)
        : ''))
<body class="{{ $bodyClass }}">
    @if(session('app_auth') && $bgStyle === 'bubbles' && !request()->routeIs('login'))
        <!-- Capa SVG para burbujas animadas en el fondo -->
        <svg id="bgBubblesSvg" class="bg-bubbles-svg" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"></svg>
    @endif
    <!-- Pantalla de Carga Global -->
    <div class="global-loader" id="globalLoader">
        <div class="loader-content">
            <img src="{{ asset('images/logo-sena.svg') }}" alt="SENA" class="loader-logo">
            <div class="spinner"></div>
        </div>
    </div>

    @if(request()->routeIs('login'))
    <!-- Fondo carrusel completo solo en login -->
    <div class="bg-carousel" id="bgCarousel" aria-hidden="true">
        <div class="bgc-layer bgc-left" id="bgcLeft"></div>
    </div>
    @endif

    <header class="global-header {{ session('app_auth') ? 'inverted' : '' }}">
        <div class="header-container">
            <a href="{{ session('app_auth') ? route('dashboard') : route('login') }}" class="logo-section" style="text-decoration:none;">
                <img src="{{ asset('images/logo-sena.svg') }}" alt="Logo SENA">
                <span class="logo-text">CIDE</span>
            </a>

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
                    <!-- Notificaciones reales (BD) -->
                    <div class="notif-menu" id="notifMenu">
                        <button type="button" class="notif-bell" id="notifBtn" aria-haspopup="true" aria-expanded="false" title="Notificaciones" style="z-index:1200;">
                            <i class="bi bi-bell" aria-hidden="true" style="font-size:22px;"></i>
                            <span class="notif-dot" id="notifDot" style="display:none;" aria-hidden="true"></span>
                        </button>
                        <style>
                            .notif-dropdown { background:#fff; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,.12); padding:8px; }
                            .notif-bell{ background:transparent; border:none; box-shadow:none; padding:6px; border-radius:50%; position:relative; outline:none; }
                            .notif-bell:focus, .notif-bell:focus-visible{ outline:none; box-shadow:none; }
                            .notif-dot{ position:absolute; top:2px; right:2px; width:8px; height:8px; background:#2ecc71; border-radius:50%; box-shadow:0 0 0 2px #fff; }
                            .notif-header{ font-weight:700; padding:8px 12px; border-bottom:1px solid #f1f1f1; display:flex; align-items:center; justify-content:space-between; gap:8px; }
                            .notif-markall-btn{ background:#00A859; color:#fff; border:none; padding:6px 10px; border-radius:8px; font-size:12px; cursor:pointer; }
                            .notif-list{ list-style:none; margin:0; padding:8px; max-height:320px; overflow:auto; }
                            .notif-bell.ring{ animation: bell-ring 1s ease-in-out; transform-origin: top center; }
                            @keyframes bell-ring {
                                0% { transform: rotate(0deg); }
                                10% { transform: rotate(14deg); }
                                20% { transform: rotate(-14deg); }
                                30% { transform: rotate(12deg); }
                                40% { transform: rotate(-12deg); }
                                50% { transform: rotate(8deg); }
                                60% { transform: rotate(-8deg); }
                                70% { transform: rotate(4deg); }
                                80% { transform: rotate(-4deg); }
                                90% { transform: rotate(2deg); }
                                100% { transform: rotate(0deg); }
                            }
                            /* Compact style used inside the bell dropdown */
                            .notif-item{ display:flex; gap:10px; padding:8px; border-radius:8px; align-items:flex-start; background: #fff; }
                            .notif-item:not(:last-child){ margin-bottom:6px; }
                            .notif-unread{ border-left:4px solid #e53e3e; background: linear-gradient(90deg,#fff7f7,#fff); }
                            .notif-success{ border-left:4px solid #00A859; background: linear-gradient(90deg,#f3fff7,#fff); }
                            .notif-error{ border-left:4px solid #e53e3e; background: linear-gradient(90deg,#fff3f3,#fff); }
                            .notif-warn{ border-left:4px solid #e53e3e; background: linear-gradient(90deg,#fff3f3,#fff); }
                            .notif-icon{ color:#e53e3e; font-size:18px; margin-top:2px; }
                            .notif-icon.success{ color:#00A859; }
                            .notif-title{ font-weight:700; font-size:13px; color:#111; max-width:160px; }
                            .notif-desc{ color:#333; font-size:12px; opacity:0.95; margin-top:4px; max-width:160px; white-space:normal; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
                            .notif-meta{ color:#666; font-size:11px; margin-left:auto; white-space:nowrap; text-align:right; }
                            .notif-actions{ display:flex; gap:6px; margin-left:8px; align-items:center; }
                            .notif-view-btn{ background:#00A859; color:#fff; border:none; padding:6px 8px; border-radius:8px; cursor:pointer; font-size:12px; }
                            .notif-delete-btn{ background:#e53e3e; color:#fff; border:none; padding:6px 8px; border-radius:8px; cursor:pointer; font-size:12px; }
                            .notif-status-badge{ font-size:11px; padding:4px 8px; border-radius:999px; background:#00A859; color:#fff; font-weight:700; }
                            .notif-status-new{ background:#00A859; color:#fff; }
                            .notif-status-read{ background:#9aa7a0; color:#fff; }
                            .notif-excerpt{ margin-top:4px; }
                            /* Larger modal styles remain unchanged above */
                            /* Panel lateral */
                            #notifDetailPanel{ position:fixed; right:18px; top:80px; width:360px; max-width:92%; background:#fff; border-radius:10px; box-shadow:0 18px 50px rgba(0,0,0,.18); padding:12px; z-index:1300; display:none; }
                            #notifDetailPanel .panel-title{ font-weight:700; margin-bottom:8px; }
                            #notifDetailPanel .panel-body{ color:#333; white-space:pre-wrap; max-height:48vh; overflow:auto; }
                            #notifDetailPanel .panel-footer{ margin-top:10px; display:flex; gap:8px; justify-content:flex-end; }
                        </style>
                        <div class="notif-dropdown" id="notifDropdown" style="display:none; min-width:320px;">
                            <div class="notif-header">
                                <span>Notificaciones</span>
                                <button id="notifMarkAll" type="button" class="notif-markall-btn">Marcar todas</button>
                            </div>
                            <div id="notifListContainer">
                                <div class="notif-empty">Cargando...</div>
                            </div>
                        </div>
                        <style>
                            /* Modal centered with blur backdrop */
                            #notifDetailOverlay{ position:fixed; inset:0; background:rgba(0,0,0,0.35); backdrop-filter: blur(4px); display:none; z-index:1300; align-items:center; justify-content:center; }
                            #notifDetailModal{ background:#fff; border-radius:12px; width:640px; max-width:92%; padding:22px; box-shadow:0 30px 80px rgba(0,0,0,.35); }
                            #notifDetailModal .panel-title{ font-weight:800; font-size:20px; margin:0; color:#111; }
                            /* Title pill: green background with white text (prominent) */
                            .notif-title-pill{ display:inline-block; background:#00A859; color:#ffffff; border:1px solid rgba(0,168,89,0.9); padding:8px 14px; border-radius:10px; font-weight:800; font-size:16px; }
                            .notif-title-pill.error{ background:#e53e3e; border-color:#e53e3e; }
                            .notif-title-pill.warn{ background:#f59e0b; border-color:#f59e0b; }
                            /* Small white box with green border for the matrix/file name */
                            .notif-matrix-box{ display:inline-block; background:#ffffff; color:#007a3d; border:1px solid #00A859; padding:6px 10px; border-radius:8px; font-weight:700; font-size:14px; margin-top:8px; }
                            #notifDetailModal .panel-body{ color:#222; white-space:pre-wrap; max-height:60vh; overflow:auto; line-height:1.5; font-size:15px; margin-top:14px; }
                            /* Structured content inside modal */
                            .notif-issue{ background:#fff5f5; border:1px solid #f5c6cb; color:#7a1a1a; padding:10px 12px; border-radius:8px; margin-bottom:10px; font-weight:700; }
                            .notif-list-danger{ background:#fff5f5; border:1px solid #f5c6cb; color:#7a1a1a; padding:10px 12px; border-radius:8px; margin-bottom:10px; }
                            .notif-list-danger ul{ margin:6px 0 0 18px; }
                            .notif-names{ background:#f7fff7; border:1px solid #dff4e6; color:#065f3b; padding:10px 12px; border-radius:8px; margin-bottom:10px; }
                            .notif-success-box{ background:#f3fff7; border:1px solid #bfe7d0; color:#0b5f3a; padding:10px 12px; border-radius:8px; margin-bottom:10px; font-weight:700; }
                            .notif-code-list{ display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
                            .notif-code-pill{ background:#fff3f3; border:1px solid #f5c6cb; color:#7a1a1a; padding:6px 10px; border-radius:8px; font-weight:800; display:inline-flex; gap:8px; align-items:center; }
                            .notif-code-copy{ background:transparent; border:none; color:#7a1a1a; cursor:pointer; padding:4px; display:inline-flex; align-items:center; justify-content:center; }
                            .notif-names ul{ margin:6px 0 0 18px; }
                            .notif-detail{ background:#fbfbfb; border:1px solid #f0f0f0; color:#333; padding:10px 12px; border-radius:8px; margin-top:12px; }
                            #notifDetailModal .panel-footer{ margin-top:18px; display:flex; gap:12px; justify-content:flex-end; }
                            #notifDetailModal .notif-delete-btn{ background:#e53e3e; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; box-shadow:0 6px 18px rgba(0,0,0,.08); }
                            #notifDetailModal .notif-close-btn{ background:#f1f1f1; color:#333; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; }
                            #notifDetailModal .modal-icon-wrap{ background: linear-gradient(180deg, rgba(229,62,62,0.08), rgba(229,62,62,0.02)); border-radius:8px; height:44px; }
                            #notifDetailModal .modal-icon-wrap.success{ background: linear-gradient(180deg, rgba(0,168,89,0.12), rgba(0,168,89,0.03)); }
                            /* make the description area more readable */
                            #notifDetailModal .panel-body p{ margin:8px 0; }
                            /* confirmation box inside overlay */
                            .notif-confirm-box{ max-width:560px; width:100%; }
                            .notif-confirm-wrapper{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; z-index:1401; }
                            /* Confirm box visuals */
                            .notif-confirm-box .confirm-card{ border:2px solid #00A859; border-radius:12px; padding:18px; background:#ffffff; }
                            .notif-confirm-box .confirm-title{ font-weight:800; font-size:18px; color:#064a2b; margin-bottom:6px; }
                            .notif-confirm-box .confirm-body{ color:#123; margin-bottom:12px; }
                            .notif-confirm-box .confirm-actions{ display:flex; gap:8px; justify-content:flex-end; }
                            .notif-confirm-box .confirm-no{ background:#f1f1f1; color:#333; border:1px solid #d0d0d0; padding:8px 12px; border-radius:8px; cursor:pointer; }
                            .notif-confirm-box .confirm-yes{ background:#00A859; color:#fff; border:none; padding:8px 12px; border-radius:8px; cursor:pointer; box-shadow:0 8px 20px rgba(0,168,89,0.12); }
                        </style>
                        <div id="notifDetailOverlay" role="dialog" aria-modal="true" aria-hidden="true">
                            <div id="notifDetailModal" onclick="event.stopPropagation();">
                                <div class="modal-header" style="display:flex; gap:12px; align-items:flex-start;">
                                    <div id="notifModalIconWrap" class="modal-icon-wrap" style="flex:0 0 44px; display:flex; align-items:center; justify-content:center;">
                                        <i id="notifModalIcon" class="bi bi-exclamation-triangle-fill" style="color:#e53e3e; font-size:24px;"></i>
                                    </div>
                                    <div style="flex:1;">
                                        <div class="panel-title"><span id="notifPanelTitle" class="notif-title-pill"></span></div>
                                        <div id="notifMatrixTitle" class="notif-matrix-box" style="display:none;"></div>
                                        <div id="notifPanelMeta" style="color:#666; font-size:13px; margin-top:8px;"></div>
                                    </div>
                                </div>
                                <div class="panel-body" id="notifPanelBody"></div>
                                <div class="panel-footer">
                                    <button id="notifPanelDelete" class="notif-delete-btn" type="button" title="Eliminar">
                                        <i class="bi bi-trash" style="font-size:18px; color:#fff; vertical-align:middle;"></i>
                                    </button>
                                    <button id="notifPanelClose" class="notif-close-btn" type="button">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                            <script>
                                // === NOTIFICACIONES REALES (BD) ===
                                (function(){
                                    const btn = document.getElementById('notifBtn');
                                    const dd = document.getElementById('notifDropdown');
                                    const dot = document.getElementById('notifDot');
                                    const listContainer = document.getElementById('notifListContainer');
                                    let notificaciones = [];
                                    let unreadCount = 0;
                                    let prevUnreadCount = null;
                                    let pollTimer = null;
                                    let ringTimer = null;

                                    function fetchNotificaciones() {
                                        console.debug('[notif] fetchNotificaciones start');
                                        fetch('/notificaciones', { credentials: 'same-origin' })
                                            .then(r => r.json())
                                            .then(data => {
                                                console.debug('[notif] fetched', data && data.length);
                                                notificaciones = data;
                                                renderNotificaciones();
                                            }).catch((err)=>{
                                                console.error('[notif] fetch error', err);
                                                listContainer.innerHTML = '<div class="notif-empty">Error cargando notificaciones</div>';
                                            });
                                    }

                                    function marcarTodasLeidas() {
                                        fetch('/notificaciones/marcar-todas', {
                                            method: 'POST',
                                            credentials: 'same-origin',
                                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                                        }).then(r => r.json()).then(() => {
                                            notificaciones = notificaciones.map(n => ({ ...n, estado: 2 }));
                                            renderNotificaciones();
                                        }).catch((err)=>{
                                            console.error('[notif] mark all error', err);
                                        });
                                    }

                                    function startNotifPolling() {
                                        if (pollTimer) return;
                                        // initial fetch so dot is accurate on load
                                        fetchNotificaciones();
                                        pollTimer = setInterval(() => {
                                            // avoid spamming while dropdown open; still update badge
                                            fetchNotificaciones();
                                        }, 30000);
                                        document.addEventListener('visibilitychange', () => {
                                            if (document.hidden) return;
                                            fetchNotificaciones();
                                        });
                                    }

                                    function formatTime12(hora) {
                                        // hora expected 'HH:MM:SS' or 'HH:MM'
                                        if (!hora) return '';
                                        const parts = hora.split(':');
                                        if (parts.length < 2) return hora;
                                        let hh = parseInt(parts[0],10);
                                        const mm = parts[1];
                                        const ampm = hh >= 12 ? 'PM' : 'AM';
                                        hh = hh % 12; if (hh === 0) hh = 12;
                                        return `${hh}:${mm} ${ampm}`;
                                    }

                                    function formatDate(d) {
                                        // d expected 'YYYY-MM-DD'
                                        if (!d) return '';
                                        const parts = d.split('-');
                                        if (parts.length !== 3) return d;
                                        return `${parts[2]}/${parts[1]}/${parts[0]}`; // DD/MM/YYYY
                                    }

                                    function setRingLoop(active){
                                        if (active) {
                                            if (ringTimer) return;
                                            ringTimer = setInterval(() => {
                                                if (unreadCount > 0) {
                                                    btn.classList.remove('ring');
                                                    void btn.offsetWidth;
                                                    btn.classList.add('ring');
                                                    setTimeout(() => btn.classList.remove('ring'), 1100);
                                                }
                                            }, 3000);
                                        } else {
                                            if (ringTimer) { clearInterval(ringTimer); ringTimer = null; }
                                        }
                                    }

                                    function renderNotificaciones() {
                                        // always compute unread count to drive animation
                                        unreadCount = notificaciones.filter(n => n.estado == 1).length;
                                        if (prevUnreadCount === null) prevUnreadCount = 0;
                                        if (unreadCount > prevUnreadCount) {
                                            btn.classList.remove('ring');
                                            // trigger reflow to restart animation
                                            void btn.offsetWidth;
                                            btn.classList.add('ring');
                                            setTimeout(() => btn.classList.remove('ring'), 1100);
                                        }
                                        prevUnreadCount = unreadCount;
                                        setRingLoop(unreadCount > 0);
                                        if (!notificaciones.length) {
                                            listContainer.innerHTML = '<div class="notif-empty">No hay notificaciones</div>';
                                            dot.style.display = 'none';
                                            return;
                                        }
                                        dot.style.display = unreadCount > 0 ? 'inline-block' : 'none';
                                        let html = '<ul class="notif-list">';
                                        notificaciones.forEach(n => {
                                            // compact dropdown item: shorter excerpt, single-line preview
                                            const excerpt = (n.descripcion||'').replace(/\s+/g,' ').substring(0,80) + ((n.descripcion||'').length>80?'...':'');
                                            const time12 = formatTime12(n.hora_noti || '');
                                            const dateFmt = formatDate(n.fch_noti || '');
                                            const title = n.titulo || '';
                                            const isSuccess = /exito|éxito|subida con éxito|subidas con éxito/i.test(title);
                                            const isError = /error|fallo|no cargado|sin cc|sin c[ée]dula/i.test(title);
                                            const isWarn = /advertencia|aviso|precaucion/i.test(title);
                                            const itemClass = `notif-item${n.estado==1?' notif-unread':''}${isSuccess?' notif-success':''}${isError?' notif-error':''}${(!isError && isWarn)?' notif-warn':''}`;
                                            const iconClass = isSuccess ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
                                            html += `<li class="${itemClass}" data-id="${n.id_noti}">
                                                <div class="notif-icon${isSuccess?' success':''}"><i class="bi ${iconClass}" aria-hidden="true"></i></div>
                                                <div style="flex:1;">
                                                    <div style="display:flex; gap:8px; align-items:center;">
                                                        <div class="notif-title">${n.titulo}</div>
                                                        <div class="notif-status-badge ${n.estado==1? 'notif-status-new' : 'notif-status-read'}">${n.estado==1? 'NUEVO' : 'LEÍDO'}</div>
                                                    </div>
                                                    <div class="notif-desc">${excerpt}</div>
                                                </div>
                                                <div style="display:flex; flex-direction:column; align-items:flex-end;">
                                                    <div class="notif-meta">${dateFmt}</div>
                                                    <div class="notif-meta">${time12}</div>
                                                    <div class="notif-actions" style="margin-top:6px;"><button class="notif-view-btn" data-id="${n.id_noti}">Ver</button></div>
                                                </div>
                                            </li>`;
                                        });
                                        html += '</ul>';
                                        listContainer.innerHTML = html;
                                        // attach handlers to view buttons
                                        document.querySelectorAll('.notif-view-btn').forEach(btn => {
                                            btn.addEventListener('click', function(e){
                                                e.stopPropagation();
                                                const id = this.getAttribute('data-id');
                                                verDetalleNotificacion(id);
                                            });
                                        });
                                    }

                                    function verDetalleNotificacion(id) {
                                        console.debug('[notif] verDetalleNotificacion', id);
                                        if (dd) {
                                            dd.style.display = 'none';
                                        }
                                        if (btn) {
                                            btn.setAttribute('aria-expanded', 'false');
                                        }
                                        fetch(`/notificaciones/${id}`, { credentials: 'same-origin' })
                                            .then(r => r.json())
                                            .then(n => {
                                                // populate side panel
                                                const titleText = n.titulo || '';
                                                document.getElementById('notifPanelTitle').textContent = titleText;
                                                // success/warn/error styling for modal
                                                const isSuccessModal = /exito|éxito|subida con éxito|subidas con éxito/i.test(titleText);
                                                const isErrorModal = /error|fallo|no cargado|sin cc|sin c[ée]dula/i.test(titleText);
                                                const isWarnModal = /advertencia|aviso|precaucion/i.test(titleText);
                                                const iconWrap = document.getElementById('notifModalIconWrap');
                                                const iconEl = document.getElementById('notifModalIcon');
                                                const titlePill = document.getElementById('notifPanelTitle');
                                                if (titlePill) {
                                                    titlePill.classList.remove('error', 'warn');
                                                    if (isErrorModal) titlePill.classList.add('error');
                                                    else if (isWarnModal) titlePill.classList.add('warn');
                                                }
                                                if (iconWrap && iconEl) {
                                                    if (isSuccessModal) {
                                                        iconWrap.classList.add('success');
                                                        iconEl.className = 'bi bi-check-circle-fill';
                                                        iconEl.style.color = '#00A859';
                                                    } else if (isWarnModal) {
                                                        iconWrap.classList.remove('success');
                                                        iconEl.className = 'bi bi-exclamation-triangle-fill';
                                                        iconEl.style.color = '#f59e0b';
                                                    } else {
                                                        iconWrap.classList.remove('success');
                                                        iconEl.className = 'bi bi-exclamation-triangle-fill';
                                                        iconEl.style.color = '#e53e3e';
                                                    }
                                                }
                                                const dateFmt = formatDate(n.fch_noti || '');
                                                const time12 = formatTime12(n.hora_noti || '');
                                                document.getElementById('notifPanelMeta').textContent = `Fecha: ${dateFmt} ${time12}`;
                                                // attempt to extract matrix/file name (e.g., 'MATRIZ...xlsx') from description
                                                const matrixEl = document.getElementById('notifMatrixTitle');
                                                const descText = (n.descripcion || '');
                                                let matrixName = '';
                                                const fileMatch = descText.match(/([\w\-\. ]+\.xlsx)/i);
                                                if (fileMatch) matrixName = fileMatch[1];
                                                if (!matrixName && n.matriz_nombre) matrixName = n.matriz_nombre; // fallback if server provides field
                                                if (matrixName) { matrixEl.textContent = matrixName; matrixEl.style.display = 'inline-block'; } else { matrixEl.style.display = 'none'; }
                                                // build structured content: problem summary, names list, and details
                                                const body = document.getElementById('notifPanelBody');
                                                body.innerHTML = '';
                                                const fullText = (n.descripcion || '').trim();
                                                const filaMatches = fullText.match(/Fila\s+\d+\s*:\s*[^;]+/gi) || [];
                                                // Try to parse the common pattern we generate for duplicated codes
                                                const codeMatch = fullText.match(/Código de competencia repetido:\s*([\w\-]+)/i);
                                                const codigo = codeMatch ? codeMatch[1] : null;
                                                // Extract all names wrapped in single quotes after the 'Nombres encontrados en la matriz' phrase
                                                let nombresEncontrados = [];
                                                const startIdx = fullText.search(/Nombres encontrados en la matriz:/i);
                                                if (startIdx !== -1) {
                                                    // prefer end at '. Nota' if present
                                                    const notaIdx = fullText.indexOf('. Nota', startIdx);
                                                    let slice;
                                                    if (notaIdx !== -1 && notaIdx > startIdx) {
                                                        slice = fullText.substring(startIdx, notaIdx);
                                                    } else {
                                                        // fallback: take until the next dot after startIdx or to end
                                                        const dotIdx = fullText.indexOf('.', startIdx);
                                                        slice = (dotIdx !== -1) ? fullText.substring(startIdx, dotIdx) : fullText.substring(startIdx);
                                                    }
                                                    const quoteRe = /'([^']+)'/g;
                                                    let qm;
                                                    while ((qm = quoteRe.exec(slice)) !== null) {
                                                        nombresEncontrados.push(qm[1].trim());
                                                    }
                                                }
                                                if (codigo || nombresEncontrados.length || filaMatches.length) {
                                                        if (codigo) {
                                                            const issue = document.createElement('div');
                                                            issue.className = 'notif-issue';
                                                            issue.textContent = `Problema: Código de competencia repetido — ${codigo}`;
                                                            body.appendChild(issue);
                                                        }
                                                        if (filaMatches.length) {
                                                            const listBox = document.createElement('div');
                                                            listBox.className = 'notif-list-danger';
                                                            const title = document.createElement('div');
                                                            title.style.fontWeight = '700';
                                                            title.textContent = 'Usuarios sin cédula:';
                                                            listBox.appendChild(title);
                                                            const ul = document.createElement('ul');
                                                            filaMatches.forEach(item => {
                                                                const li = document.createElement('li');
                                                                li.textContent = item.replace(/\s+/g, ' ').trim();
                                                                ul.appendChild(li);
                                                            });
                                                            listBox.appendChild(ul);
                                                            body.appendChild(listBox);
                                                        }
                                                        // Extract all codes present in the description (may be multiple)
                                                        const codes = [];
                                                        const codeRe = /Código de competencia repetido:\s*([\w\-]+)/ig;
                                                        let cm;
                                                        while ((cm = codeRe.exec(fullText)) !== null) {
                                                            if (cm[1]) codes.push(cm[1]);
                                                        }
                                                        // Unique
                                                        const uniqueCodes = Array.from(new Set(codes));
                                                        if (uniqueCodes.length) {
                                                            const codeContainer = document.createElement('div');
                                                            codeContainer.className = 'notif-code-list';
                                                            uniqueCodes.forEach(c => {
                                                                const pill = document.createElement('span');
                                                                pill.className = 'notif-code-pill';
                                                                pill.innerHTML = `<span style="font-family:monospace">${c}</span>`;
                                                                const copyBtn = document.createElement('button');
                                                                copyBtn.className = 'notif-code-copy';
                                                                copyBtn.setAttribute('type','button');
                                                                copyBtn.setAttribute('aria-label','Copiar código');
                                                                copyBtn.setAttribute('data-code', c);
                                                                copyBtn.innerHTML = `<i class="bi bi-clipboard" aria-hidden="true"></i>`;
                                                                copyBtn.addEventListener('click', function(e){
                                                                    e.stopPropagation();
                                                                    const code = this.getAttribute('data-code');
                                                                    if (navigator.clipboard && navigator.clipboard.writeText) {
                                                                        navigator.clipboard.writeText(code).then(()=>{
                                                                            if (window.showToast) window.showToast('Código copiado: ' + code, 'success');
                                                                        }).catch(()=>{
                                                                            if (window.showToast) window.showToast('No se pudo copiar', 'error');
                                                                        });
                                                                    } else {
                                                                        // fallback
                                                                        const ta = document.createElement('textarea'); ta.value = code; document.body.appendChild(ta); ta.select(); try { document.execCommand('copy'); if (window.showToast) window.showToast('Código copiado: ' + code, 'success'); } catch(e){ if (window.showToast) window.showToast('No se pudo copiar', 'error'); } ta.remove();
                                                                    }
                                                                });
                                                                pill.appendChild(copyBtn);
                                                                codeContainer.appendChild(pill);
                                                            });
                                                            body.appendChild(codeContainer);
                                                        }
                                                    if (nombresEncontrados.length) {
                                                        const namesBox = document.createElement('div');
                                                        namesBox.className = 'notif-names';
                                                        const namesTitle = document.createElement('div');
                                                        namesTitle.style.fontWeight = '700';
                                                        namesTitle.textContent = 'Nombres encontrados en la matriz:';
                                                        namesBox.appendChild(namesTitle);
                                                        const ul = document.createElement('ul');
                                                        nombresEncontrados.forEach(name => {
                                                            if (!name) return;
                                                            const li = document.createElement('li');
                                                            li.textContent = name;
                                                            ul.appendChild(li);
                                                        });
                                                        namesBox.appendChild(ul);
                                                        body.appendChild(namesBox);
                                                    }
                                                    // also append the full detail block
                                                    const details = document.createElement('div');
                                                    details.className = 'notif-detail';
                                                    details.innerHTML = '<strong>Detalle completo:</strong><br>' + fullText.replace(/\n/g, '<br>');
                                                    body.appendChild(details);
                                                } else {
                                                    if (isSuccessModal) {
                                                        const okBox = document.createElement('div');
                                                        okBox.className = 'notif-success-box';
                                                        okBox.textContent = 'Éxito: carga completada.';
                                                        body.appendChild(okBox);
                                                    }
                                                    // fallback: render paragraphs
                                                    const paragraphs = fullText.split('\n').filter(Boolean);
                                                    if (paragraphs.length === 0) paragraphs.push(fullText || '');
                                                    paragraphs.forEach(p => {
                                                        const el = document.createElement('p');
                                                        el.textContent = p;
                                                        body.appendChild(el);
                                                    });
                                                }
                                                const overlay = document.getElementById('notifDetailOverlay');
                                                overlay.style.display = 'flex';
                                                overlay.setAttribute('aria-hidden','false');
                                                // set delete handler: show trash icon and ask confirmation before deleting
                                                const delBtn = document.getElementById('notifPanelDelete');
                                                delBtn.onclick = function(){
                                                    showDeleteConfirm(id, codigo);
                                                };
                                                document.getElementById('notifPanelClose').onclick = function(){ overlay.style.display='none'; overlay.setAttribute('aria-hidden','true'); const cb = document.getElementById('notifConfirmBox'); if(cb) cb.remove(); };
                                                // click outside modal closes
                                                overlay.onclick = function(e){ if(e.target === this){ this.style.display='none'; this.setAttribute('aria-hidden','true'); const cb = document.getElementById('notifConfirmBox'); if(cb) cb.remove(); } };
                                                
                                                // Custom confirmation dialog (inserted into overlay)
                                                function showDeleteConfirm(id, codigo){
                                                    // remove existing
                                                    const existing = document.getElementById('notifConfirmWrapper'); if(existing) existing.remove();
                                                    const overlay = document.getElementById('notifDetailOverlay');
                                                    if(!overlay) return;
                                                    overlay.style.display = 'flex'; overlay.setAttribute('aria-hidden','false');
                                                    // wrapper ensures centering and does not reflow modal content
                                                    const wrapper = document.createElement('div');
                                                    wrapper.id = 'notifConfirmWrapper';
                                                    wrapper.className = 'notif-confirm-wrapper';
                                                    // inner box
                                                    const box = document.createElement('div');
                                                    box.id = 'notifConfirmBox';
                                                    box.className = 'notif-confirm-box';
                                                    box.style.cssText = 'position:relative; z-index:1402;';
                                                    box.innerHTML = `
                                                        <div class="confirm-card">
                                                            <div class="confirm-title">¿Eliminar notificación?</div>
                                                            <div class="confirm-body">¿Estás seguro de eliminar esta notificación?${codigo ? '<br><strong>Código:</strong> <span style="font-family:monospace">'+codigo+'</span>' : ''}</div>
                                                            <div class="confirm-actions">
                                                                <button id="confirmDeleteNo" class="confirm-no" type="button">Cancelar</button>
                                                                <button id="confirmDeleteYes" class="confirm-yes" type="button">Eliminar</button>
                                                            </div>
                                                        </div>`;
                                                    // clicking wrapper outside box cancels
                                                    wrapper.addEventListener('click', function(e){ if(e.target === this){ this.remove(); } });
                                                    wrapper.appendChild(box);
                                                    overlay.appendChild(wrapper);
                                                    // handlers
                                                    document.getElementById('confirmDeleteNo').onclick = function(e){ e.stopPropagation(); wrapper.remove(); };
                                                    document.getElementById('confirmDeleteYes').onclick = function(e){ e.stopPropagation(); wrapper.remove(); eliminarNotificacion(id); };
                                                }
                                            }).catch((err)=>{
                                                console.error('[notif] detalle error', err);
                                                alert('No se pudo cargar el detalle de la notificación');
                                            });
                                    }

                                    function eliminarNotificacion(id) {
                                        console.debug('[notif] eliminar', id);
                                        fetch(`/notificaciones/${id}`, { 
                                            method: 'DELETE', 
                                            credentials: 'same-origin',
                                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                                        })
                                            .then(r => r.json())
                                            .then(() => {
                                                const overlay = document.getElementById('notifDetailOverlay');
                                                if(overlay){ overlay.style.display='none'; overlay.setAttribute('aria-hidden','true'); }
                                                const cb = document.getElementById('notifConfirmBox'); if(cb) cb.remove();
                                                // refresh list
                                                fetchNotificaciones();
                                            }).catch((err)=>{
                                                console.error('[notif] eliminar error', err);
                                                alert('No se pudo eliminar la notificación');
                                            });
                                    }

                                    console.debug('[notif] script init', !!btn, !!dd, !!listContainer);
                                    if (btn) {
                                        btn.addEventListener('click', (e) => {
                                            console.debug('[notif] btn click');
                                            e.preventDefault();
                                            const open = dd.style.display === 'block';
                                            dd.style.display = open ? 'none' : 'block';
                                            btn.setAttribute('aria-expanded', open ? 'false' : 'true');
                                            if (!open) fetchNotificaciones();
                                        });
                                    }
                                    const markAllBtn = document.getElementById('notifMarkAll');
                                    if (markAllBtn) {
                                        markAllBtn.addEventListener('click', (e) => {
                                            e.preventDefault();
                                            marcarTodasLeidas();
                                        });
                                    }
                                    // start background polling to keep badge updated
                                    startNotifPolling();
                                    document.addEventListener('click', (e) => {
                                        if (dd && btn && !dd.contains(e.target) && !btn.contains(e.target)) {
                                            dd.style.display = 'none';
                                            btn.setAttribute('aria-expanded', 'false');
                                        }
                                    });
                                    const closeBtn = document.getElementById('notifDetailClose') || document.getElementById('notifPanelClose');
                                    if(closeBtn) closeBtn.onclick = function() {
                                        const overlay = document.getElementById('notifDetailOverlay');
                                        if (overlay) { overlay.style.display = 'none'; overlay.setAttribute('aria-hidden','true'); }
                                        const cb = document.getElementById('notifConfirmBox'); if(cb) cb.remove();
                                    };
                                    // Cerrar modal al hacer click fuera del overlay (ya manejado), pero asegurar que clicks en el modal no lo cierren por accidente
                                    const modal = document.getElementById('notifDetailModal');
                                    if(modal) modal.addEventListener('click', function(e){ e.stopPropagation(); });
                                    // Marcar que ya se inicializó el manejador real de notificaciones
                                    try { window.__notifRealInit = true; } catch(e){}
                                })();
                            </script>
                    <div class="user-menu" id="userMenu">
                        <button class="user-avatar" id="userMenuBtn" aria-haspopup="true" aria-expanded="false" title="Cuenta">
                            @php($avatar = $appAuth['avatar'] ?? null)
                            @if($avatar)
                                <img src="{{ asset('storage/'.$avatar) }}" alt="Avatar" style="width:28px; height:28px; border-radius:50%; object-fit:cover; border:2px solid #00A859;">
                            @else
                                <i class="bi bi-person-circle" aria-hidden="true" style="font-size:26px;"></i>
                            @endif
                        </button>
                        <div class="user-dropdown" id="userDropdown" style="display:none;">
                            <div class="user-info" style="padding:6px 10px 8px 10px;">
                                <div class="user-name" style="font-weight:600;">{{ $appAuth['nombre'] ?? 'admin' }}</div>
                                @if(!empty($appAuth['email']))
                                <div class="user-mail" style="opacity:.75; font-size:12px;">{{ $appAuth['email'] }}</div>
                                @endif
                            </div>
                            <div style="height:1px; background:#eee; margin:6px 0;"></div>
                            <a href="{{ route('user.panel') }}" class="dropdown-item" style="display:block; width:100%; text-align:left; background:none; border:none; padding:8px 10px; border-radius:8px; cursor:pointer; text-decoration:none;">
                                <i class="bi bi-person-badge" aria-hidden="true" style="margin-right:8px;"></i>
                                Ingresar al panel de Usuario
                            </a>
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
    <footer class="global-footer {{ session('app_auth') ? 'inverted' : '' }}" role="contentinfo">
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
        // === BURBUJAS DE FONDO (SVG) ===
        (function() {
            const svg = document.getElementById('bgBubblesSvg');
            if (!svg) return;

            const NS = 'http://www.w3.org/2000/svg';
            let width = window.innerWidth;
            let height = window.innerHeight;

            // Posición del mouse para interacción con burbujas
            let mouseX = null;
            let mouseY = null;
            let mouseActive = false;

            // Estado de arrastre de burbujas
            let draggingBubble = null;
            let dragOffsetX = 0;
            let dragOffsetY = 0;
            let wasDragged = false;
            let hoverBubble = null;

            window.addEventListener('mousemove', function(e) {
                mouseX = e.clientX;
                mouseY = e.clientY;
                mouseActive = true;

                if (draggingBubble) {
                    const x = e.clientX;
                    const y = e.clientY;
                    draggingBubble.x = x + dragOffsetX;
                    draggingBubble.y = y + dragOffsetY;
                    draggingBubble.vx = 0;
                    draggingBubble.vy = 0;
                    wasDragged = true;
                } else if (bubbles.length) {
                    // Detectar si el mouse está sobre alguna burbuja para mostrar cursor de mano
                    const mx = e.clientX;
                    const my = e.clientY;
                    let closest = null;
                    let closestDistSq = Infinity;
                    for (let i = 0; i < bubbles.length; i++) {
                        const b = bubbles[i];
                        const dx = mx - b.x;
                        const dy = my - b.y;
                        const distSq = dx * dx + dy * dy;
                        if (distSq < closestDistSq) {
                            closestDistSq = distSq;
                            closest = b;
                        }
                    }
                    if (closest && closestDistSq <= closest.r * closest.r) {
                        hoverBubble = closest;
                        document.body.style.cursor = 'grab';
                    } else {
                        hoverBubble = null;
                        document.body.style.cursor = '';
                    }
                }
            });

            window.addEventListener('mouseleave', function() {
                mouseActive = false;
                draggingBubble = null;
                hoverBubble = null;
                document.body.style.cursor = '';
            });

            window.addEventListener('mouseup', function() {
                draggingBubble = null;
                // Si seguimos sobre una burbuja, mantener cursor de mano
                if (!hoverBubble) {
                    document.body.style.cursor = '';
                }
            });

            // Iniciar arrastre al presionar cerca de una burbuja
            window.addEventListener('mousedown', function(e) {
                if (!bubbles.length) return;
                const x = e.clientX;
                const y = e.clientY;
                let closest = null;
                let closestDistSq = Infinity;
                for (let i = 0; i < bubbles.length; i++) {
                    const b = bubbles[i];
                    const dx = x - b.x;
                    const dy = y - b.y;
                    const distSq = dx * dx + dy * dy;
                    if (distSq < closestDistSq) {
                        closestDistSq = distSq;
                        closest = b;
                    }
                }
                if (closest && closestDistSq <= closest.r * closest.r) {
                    draggingBubble = closest;
                    dragOffsetX = closest.x - x;
                    dragOffsetY = closest.y - y;
                    wasDragged = false;
                    document.body.style.cursor = 'grabbing';
                    e.preventDefault();
                }
            });

            function resize() {
                width = window.innerWidth;
                height = window.innerHeight;
                svg.setAttribute('width', width);
                svg.setAttribute('height', height);
                svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
            }

            resize();
            window.addEventListener('resize', resize);

            const bubbles = [];
            const particles = [];
            const BUBBLE_COUNT = 32; // 4 burbujas extra (2 medianas, 1 grande, 1 pequeña)
            const MAX_SPEED = 0.35; // límite de velocidad para mantener suavidad

            // Puntos base (normalizados 0..1) donde suelen haber "huecos" de fondo.
            // Prioriza la franja superior para que allí siempre haya varias burbujas visibles.
            const BASE_SPOTS = [
                // Franja superior (más presencia de burbujas)
                { x: 0.12, y: 0.12 },
                { x: 0.32, y: 0.10 },
                { x: 0.52, y: 0.11 },
                { x: 0.72, y: 0.13 },
                { x: 0.88, y: 0.12 },
                // Zona media
                { x: 0.18, y: 0.38 },
                { x: 0.46, y: 0.42 },
                { x: 0.80, y: 0.40 },
                // Parte inferior y laterales
                { x: 0.10, y: 0.76 },
                { x: 0.30, y: 0.88 },
                { x: 0.54, y: 0.82 },
                { x: 0.78, y: 0.90 },
                { x: 0.94, y: 0.78 }
            ];

            function rand(min, max) {
                return Math.random() * (max - min) + min;
            }

            function spawnParticles(x, y, r) {
                const count = Math.round(rand(10, 18));
                for (let i = 0; i < count; i++) {
                    const angle = rand(0, Math.PI * 2);
                    const speed = rand(0.7, 1.9);
                    const vx = Math.cos(angle) * speed;
                    const vy = Math.sin(angle) * speed;

                    // Partícula como línea corta que nace en el borde de la burbuja
                    const startX = x + Math.cos(angle) * (r + 4);
                    const startY = y + Math.sin(angle) * (r + 4);
                    const length = rand(r * 0.18, r * 0.32);
                    const endX = startX + Math.cos(angle) * length;
                    const endY = startY + Math.sin(angle) * length;

                    const line = document.createElementNS(NS, 'line');
                    line.classList.add('bg-bubble-particle');
                    line.setAttribute('x1', startX);
                    line.setAttribute('y1', startY);
                    line.setAttribute('x2', endX);
                    line.setAttribute('y2', endY);
                    svg.appendChild(line);

                    particles.push({
                        el: line,
                        x1: startX,
                        y1: startY,
                        x2: endX,
                        y2: endY,
                        vx,
                        vy,
                        life: 0,
                        maxLife: rand(26, 46)
                    });
                }
            }

            function triggerBubblePop(bubble) {
                if (!bubble || bubble.popping) return;
                const circle = bubble.el;
                if (!circle) return;
                bubble.popping = true;
                // Generar partículas de explosión alrededor de la burbuja
                spawnParticles(bubble.x, bubble.y, bubble.r);
                circle.classList.remove('bg-bubble-pop');
                // Forzar reflujo para reiniciar la animación si se dispara varias veces
                void circle.offsetWidth;
                circle.classList.add('bg-bubble-pop');
            }

            function createBubble(index) {
                // Tamaños variados: unas pocas grandes, la mayoría pequeñas/medianas
                // y 4 burbujas adicionales controladas (2 medianas, 1 grande, 1 pequeña)
                let r;
                if (index < 5 || index === 30) {
                    // Burbujas grandes (las primeras + 1 extra)
                    r = rand(96, 140);
                } else if (index === 28 || index === 29) {
                    // 2 burbujas medianas extra
                    r = rand(46, 88);
                } else if (index === 31) {
                    // 1 burbuja pequeña extra
                    r = rand(18, 32);
                } else {
                    // Resto pequeñas/medianas
                    r = rand(22, 72);
                }

                // Posición inicial: tomar spot base y ajustarlo al tamaño actual de la ventana
                const spot = BASE_SPOTS[index % BASE_SPOTS.length];
                let x = spot.x * width;
                let y = spot.y * height;

                // Asegurar que el círculo completo quede dentro de la vista
                if (x - r < 0) x = r;
                if (x + r > width) x = width - r;
                if (y - r < 0) y = r;
                if (y + r > height) y = height - r;
                const circle = document.createElementNS(NS, 'circle');
                circle.classList.add('bg-bubble');
                circle.setAttribute('r', r);
                circle.setAttribute('cx', x);
                circle.setAttribute('cy', y);
                // Más oscuras que el fondo: verde profundo y sutil sombra
                circle.setAttribute('fill', 'rgba(0, 90, 35, 0.55)');
                // Sin contorno, solo el relleno más oscuro que el fondo
                circle.setAttribute('stroke', 'none');
                svg.appendChild(circle);

                const speedBase = 0.08; // muy suave (se ajusta con el mouse)
                const vx = rand(-speedBase, speedBase);
                const vy = rand(-speedBase, speedBase);

                const bubble = { el: circle, x, y, vx, vy, r, popping: false };

                // Al terminar la animación, la burbuja desaparece y se reemplaza por una nueva
                circle.addEventListener('animationend', function() {
                    circle.classList.remove('bg-bubble-pop');
                    // Quitar burbuja actual del arreglo y del SVG
                    const idx = bubbles.indexOf(bubble);
                    if (idx !== -1) {
                        bubbles.splice(idx, 1);
                    }
                    if (circle.parentNode === svg) {
                        svg.removeChild(circle);
                    }
                    // Después de un pequeño tiempo, crear una nueva burbuja en otro punto
                    setTimeout(function() {
                        const newBubble = createBubble(Math.floor(Math.random() * BUBBLE_COUNT));
                        bubbles.push(newBubble);
                    }, 600);
                });

                return bubble;
            }

            for (let i = 0; i < BUBBLE_COUNT; i++) {
                bubbles.push(createBubble(i));
            }

            // Click global aproximado: si el usuario hace clic cerca de una burbuja,
            // se busca la burbuja más cercana cuyo radio cubra ese punto y se "totea".
            document.addEventListener('click', function(e) {
                // Si venimos de un arrastre, ignorar este clic
                if (wasDragged) {
                    wasDragged = false;
                    return;
                }
                if (!bubbles.length) return;
                const x = e.clientX;
                const y = e.clientY;
                let closest = null;
                let closestDistSq = Infinity;

                for (let i = 0; i < bubbles.length; i++) {
                    const b = bubbles[i];
                    const dx = x - b.x;
                    const dy = y - b.y;
                    const distSq = dx * dx + dy * dy;
                    if (distSq < closestDistSq) {
                        closestDistSq = distSq;
                        closest = b;
                    }
                }

                if (closest && closestDistSq <= closest.r * closest.r) {
                    triggerBubblePop(closest);
                }
            });

            function step() {
                const len = bubbles.length;

                // Movimiento básico + gestión de bordes
                for (let i = 0; i < len; i++) {
                    const b = bubbles[i];
                    b.x += b.vx;
                    b.y += b.vy;

                    // Rebote horizontal (paredes izquierda/derecha)
                    if (b.x - b.r < 0) {
                        b.x = b.r;
                        b.vx *= -1;
                    } else if (b.x + b.r > width) {
                        b.x = width - b.r;
                        b.vx *= -1;
                    }

                    // Wrap vertical: si sale por abajo, reaparece arriba y viceversa
                    if (b.y - b.r > height) {
                        // salió completamente por abajo → reaparece por arriba
                        b.y = -b.r;
                    } else if (b.y + b.r < 0) {
                        // salió completamente por arriba → reaparece por abajo
                        b.y = height + b.r;
                    }
                }

                // Interacción con el mouse: repulsión suave alrededor del cursor
                if (mouseActive && mouseX !== null && mouseY !== null) {
                    const influenceRadius = 260; // zona de influencia del mouse
                    const influenceRadiusSq = influenceRadius * influenceRadius;
                    for (let i = 0; i < len; i++) {
                        const b = bubbles[i];
                        const dx = b.x - mouseX;
                        const dy = b.y - mouseY;
                        const distSq = dx * dx + dy * dy;
                        if (distSq > 0 && distSq < influenceRadiusSq) {
                            const dist = Math.sqrt(distSq);
                            const strength = (influenceRadius - dist) / influenceRadius; // 0..1
                            const force = strength * 0.18; // fuerza pequeña para mantener suavidad
                            const nx = dx / dist;
                            const ny = dy / dist;
                            b.vx += nx * force;
                            b.vy += ny * force;
                        }
                    }
                }

                // Colisiones aproximadas entre burbujas
                for (let i = 0; i < len; i++) {
                    for (let j = i + 1; j < len; j++) {
                        const a = bubbles[i];
                        const b = bubbles[j];
                        const dx = b.x - a.x;
                        const dy = b.y - a.y;
                        const dist = Math.hypot(dx, dy);
                        const minDist = a.r + b.r - 6; // leve solapamiento permitido

                        if (dist > 0 && dist < minDist) {
                            // Separar ligeramente para que no se "peguen"
                            const overlap = (minDist - dist) / 2;
                            const nx = dx / dist;
                            const ny = dy / dist;
                            a.x -= nx * overlap;
                            a.y -= ny * overlap;
                            b.x += nx * overlap;
                            b.y += ny * overlap;

                            // Intercambiar componentes de velocidad (choque elástico simple)
                            const tvx = a.vx;
                            const tvy = a.vy;
                            a.vx = b.vx;
                            a.vy = b.vy;
                            b.vx = tvx;
                            b.vy = tvy;
                        }
                    }
                }

                // Limitar velocidad para que no se disparen por la interacción
                for (let i = 0; i < len; i++) {
                    const b = bubbles[i];
                    const speed = Math.hypot(b.vx, b.vy);
                    if (speed > MAX_SPEED) {
                        const scale = MAX_SPEED / speed;
                        b.vx *= scale;
                        b.vy *= scale;
                    }
                }

                // Actualizar partículas (líneas que salen del borde de la burbuja)
                for (let i = particles.length - 1; i >= 0; i--) {
                    const p = particles[i];
                    // Los extremos se alejan un poco más rápido para que
                    // se separen visualmente de la burbuja al desvanecerse
                    p.x1 += p.vx * 1.15;
                    p.y1 += p.vy * 1.15;
                    p.x2 += p.vx * 1.6;
                    p.y2 += p.vy * 1.6;
                    p.life += 1;
                    const t = p.life / p.maxLife;
                    if (t >= 1) {
                        if (p.el.parentNode === svg) {
                            svg.removeChild(p.el);
                        }
                        particles.splice(i, 1);
                        continue;
                    }
                    p.el.setAttribute('x1', p.x1);
                    p.el.setAttribute('y1', p.y1);
                    p.el.setAttribute('x2', p.x2);
                    p.el.setAttribute('y2', p.y2);
                    p.el.setAttribute('opacity', String(1 - t));
                }

                // Aplicar posiciones de burbujas al SVG
                for (let i = 0; i < len; i++) {
                    const b = bubbles[i];
                    b.el.setAttribute('cx', b.x);
                    b.el.setAttribute('cy', b.y);
                }

                requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        })();

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
            { name: 'Cargar Excel', url: '{{ route("excel.upload") }}', keywords: ['cargar', 'excel', 'importar', 'subir', 'archivo'] },
            { name: 'Panel de Usuario', url: '{{ route("user.panel") }}', keywords: ['usuario', 'perfil', 'panel', 'cuenta'] }
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

        // === NOTIFICACIONES (logs de carga) ===
        (function(){
            // Evitar inicializar si ya existe el manejador real de notificaciones
            if (window.__notifRealInit) return;
            const btn = document.getElementById('notifBtn');
            const dd = document.getElementById('notifDropdown');
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
            window.clearUploadLogs = function(){
                // Limpia los logs en sesión sin activar el loader global
                fetch('{{ url('/api/clear-upload-logs') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                }).then(() => {
                    const dropdown = document.getElementById('notifDropdown');
                    if (dropdown) dropdown.innerHTML = '<div class="notif-empty">No hay notificaciones</div>';
                    const dot = document.querySelector('.notif-dot');
                    if (dot) dot.remove();
                }).catch(() => {
                    if (window.showToast) window.showToast('No se pudo limpiar notificaciones', 'error');
                });
            };
        })();
    </script>
    @yield('scripts')
</body>
</html>

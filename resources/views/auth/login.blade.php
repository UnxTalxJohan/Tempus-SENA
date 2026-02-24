@extends('layouts.app')

@section('content')

    <!-- Header solo de concepto para esta vista -->
    <header class="login-header">
        <div class="login-header-inner">
            {{-- Header "invisible": sin texto de marca, solo menú arriba a la derecha --}}
            <span class="login-header-logo" aria-hidden="true"></span>
            <nav class="login-header-nav">
                <a href="#">Inicio</a>
                <a href="#">Ayuda</a>
            </nav>
        </div>
    </header>

    <div class="login-bg">
        <div class="login-wrapper">
            <div class="login-green-bar" id="loginGreenBar">
                <div class="login-green-brand">
                    <img src="{{ asset('images/logo-sena.svg') }}" alt="Logo SENA" class="login-green-logo">
                    {{-- Texto TEMPUS eliminado para que no aparezca sobre el fondo --}}
                    <div class="login-green-title" aria-hidden="true"></div>
                </div>
            </div>

            <!-- Hoja grande blanca decorativa a la izquierda del login -->
            <div class="login-leaf-graphic" aria-hidden="true">
                <img src="https://www.svgrepo.com/show/195611/leaf.svg" alt="" class="login-leaf-img">
            </div>
            <div class="login-card" id="loginCardRef">
                <h2 class="login-title">Iniciar sesión</h2>
                <form method="POST" action="{{ route('login.post') }}" class="login-form">
                    @csrf
                    <div class="login-field">
                        <label for="email">Correo o usuario</label>
                        <input type="text" id="email" name="email" class="login-input" required autofocus placeholder="tucorreo@ejemplo.com o tu usuario">
                    </div>
                    <div class="login-field">
                        <label for="password">Contraseña</label>
                        <div class="login-password-wrap">
                            <input type="password" id="password" name="password" class="login-input login-input-password" required placeholder="Tu contraseña">
                            <span class="login-show" aria-label="Mostrar contraseña" onclick="togglePassword()" title="Mostrar/ocultar">
                                <svg id="eyeIcon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <g id="eyeOpen">
                                        <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle id="eyeBall" cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                                    </g>
                                    <g id="eyeClosed" style="opacity:0;">
                                        <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <line x1="5" y1="19" x2="19" y2="5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </g>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="login-remember">
                        <a href="#" class="login-link" onclick="forgotPassword(event)">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="login-btn">Entrar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Ola blanca inferior (solo decorativa) -->
    <div class="login-wave-bottom" aria-hidden="true">
        <svg viewBox="0 0 1440 320" preserveAspectRatio="none" class="login-wave-svg">
            <!-- Varias montañas: pico a la izquierda, valle al centro y pico a la derecha -->
            <path fill="#ffffff" fill-opacity="1" d="M0,260C160,200,320,140,480,160C640,180,800,260,960,240C1120,220,1280,260,1440,240L1440,320L0,320Z"></path>
        </svg>
    </div>

    <!-- Footer solo de concepto para esta vista -->
    <footer class="login-footer">
        <div class="login-footer-inner">
            <span>© TEMPUS · SENA</span>
        </div>
    </footer>

<style>
/* Fuente más amigable para el login */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* Fondo general: deja que el degradado lo controle body.login-page desde el CSS global */
html, body {
    min-height: 100vh;
    height: auto;
    margin: 0;
    background: transparent;
}

/* Header y footer translúcidos (concepto) */
.login-header,
.login-footer {
    position: fixed;
    left: 0;
    right: 0;
    top: 0;
    z-index: 20;
    padding: 14px 3vw;
    color: #ffffff;
    background: transparent;
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
    pointer-events: none; /* que no bloquee clics sobre el fondo */
}

.login-header-inner,
.login-footer-inner {
    display: flex;
    align-items: center;
    justify-content: flex-end; /* empuja el nav a la esquina superior derecha */
    max-width: 1120px;
    margin: 0 auto;
}

.login-header-logo {
    display: none; /* ya no mostramos marca de texto en el login */
}

.login-header-nav {
    display: flex;
    gap: 18px;
    pointer-events: auto; /* el menú sí debe ser clickeable */
}

.login-header-nav a {
    color: #ffffff;
    text-decoration: none;
    font-size: 0.95rem;
}

.login-footer {
    position: relative;
    padding-top: 0;
    font-size: 0.9rem;
}

/* Ola blanca en la parte inferior, similar al ejemplo de referencia */
.login-wave-bottom {
    position: fixed;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 220px;
    pointer-events: none;
    z-index: 2;
}

.login-wave-svg {
    width: 100%;
    height: 100%;
    display: block;
}

/* Barra verde animada detrás del formulario */
.login-green-bar {
    position: absolute;
    left: 4%;
    top: 50%;
    width: 0;
    height: 100%;
    background: rgba(1, 43, 18, 0.78);
    border-radius: 22px 0 0 22px;
    z-index: 2;
    box-shadow: 0 4px 24px rgba(25,143,58,0.13);
    transition: width 0.55s cubic-bezier(.24,1.16,.24,1), background 0.3s;
    opacity: 1;
    transform: translate(-100%, -50%);
    backdrop-filter: blur(10px) saturate(1.5);
    -webkit-backdrop-filter: blur(10px) saturate(1.5);
    border: 1.5px solid rgba(0, 174, 49, 0.18);
}
.login-green-brand {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.login-green-logo {
    width: 92px;
    max-width: 70%;
    height: auto;
    filter: brightness(0) invert(1) drop-shadow(0 3px 8px rgba(0,0,0,0.35));
}
.login-green-title {
    margin-top: 10px;
    font-weight: 800;
    font-size: 1.08rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #ffffff;
}
.login-green-title::before,
.login-green-title::after {
    content: '';
    display: block;
    margin: 4px auto 0;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(255,255,255,0.1), rgba(255,255,255,0.9), rgba(255,255,255,0.1));
}
.login-green-title::before {
    width: 78%;
    height: 2px;
}
.login-green-title::after {
    width: 46%;
    height: 1px;
    opacity: 0.9;
    margin-top: 3px;
}
.login-leaf-graphic {
    position: absolute;
    left: -150%;
    top: 40%;
    transform: translate(-50%, -30%);
    z-index: 3;
}
.login-leaf-img {
    width: 220px;
    height: auto;
    filter: brightness(0) invert(1);
    opacity: 0.9;
    transition: opacity 0.3s ease;
}
.login-bg {
    /* Ocupa casi toda la ventana, dejando espacio para header y footer */
    min-height: calc(100vh - 140px);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 6vw;
    /* Fondo transparente: usa el degradado definido en body */
    background: transparent;
    position: relative;
    z-index: 1;
}
.login-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}
.login-card {
    /* Efecto cristal: blanco translúcido con desenfoque */
    background: rgba(255, 255, 255, 0.16) !important;
    backdrop-filter: blur(18px) saturate(1.4);
    -webkit-backdrop-filter: blur(18px) saturate(1.4);
    border-radius: 22px;
    box-shadow: 0 18px 45px 0 rgba(0,0,0,0.26);
    padding: 46px 42px 38px 42px;
    min-width: 380px;
    max-width: 430px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    opacity: 1;
    position: relative;
    z-index: 11;
}
/* Aplicar la nueva fuente en todo el recuadro de login */
.login-card,
.login-card * {
    font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
.login-title {
    font-size: 1.7rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 18px;
    text-align: center;
}
.login-form {
    width: 100%;
}
.login-field {
    margin-bottom: 16px;
}
.login-field label {
    display: block;
    margin-bottom: 6px;
    color: #ffffff;
    font-weight: 600;
}
.login-input {
    width: 100%;
    padding: 12px 14px;
    border-radius: 32px;
    border: 2px solid rgba(255,255,255,0.45);
    background: rgba(255,255,255,0.16);
    outline: none;
    transition: box-shadow .15s, border-color .15s;
    font-size: 1rem;
    color: #ffffff;
    box-sizing: border-box;
}
.login-input-password {
    padding-right: 44px;
    transition: background 0.25s;
}
.login-input:focus {
    border-color: rgba(255,255,255,0.9);
    box-shadow: 0 8px 20px rgba(0,0,0,0.32);
    background: rgba(255,255,255,0.22);
}
.login-password-wrap {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
}
.login-show {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 4px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background 0.18s, box-shadow 0.18s;
    z-index: 2;
}
.login-show:hover {
    background: transparent;
    box-shadow: none;
}
.login-show svg {
    transition: stroke 0.2s, filter 0.2s;
    color: #ffffff;
    cursor: pointer;
}
.login-show svg #eyeBall {
    transition: all 0.25s cubic-bezier(.4,2,.6,1);
}
.login-show {
    background: transparent;
    border: none;
    padding: 6px;
    border-radius: 8px;
    cursor: pointer;
}
.login-remember {
    text-align: left;
    margin-bottom: 10px;
}
.login-link {
    color: #ffffff;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.98rem;
}

/* Placeholders en blanco suave para inputs del login */
.login-input::placeholder {
    color: rgba(255, 255, 255, 0.75);
}
.login-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(180deg,#0fa234,#0b7c25);
    color: #fff;
    border-radius: 16px;
    border: none;
    font-weight: 700;
    margin-top: 8px;
    box-shadow: 0 6px 18px rgba(11,124,37,0.18);
    cursor: pointer;
    font-size: 1.1rem;
    transition: background 0.22s, box-shadow 0.22s, transform 0.13s;
    outline: none;
}
.login-btn:hover, .login-btn:focus {
    background: linear-gradient(180deg,#13c13d,#0fa234);
    box-shadow: 0 10px 28px rgba(11,124,37,0.22);
    transform: translateY(-2px) scale(1.03);
}
.login-btn:active {
    background: linear-gradient(180deg,#0b7c25,#0fa234);
    box-shadow: 0 2px 8px rgba(11,124,37,0.13);
    transform: scale(0.98);
}
@media (max-width: 600px) {
    .login-card { padding: 18px 6vw; min-width: 0; max-width: 98vw; }
    .login-green-bar { max-width: 98vw; }
	.login-bg { justify-content: center; padding-right: 0; }
}
</style>

<script>
// Animación barra verde detrás del login
document.addEventListener('DOMContentLoaded', () => {
    const bar = document.getElementById('loginGreenBar');
    if(bar) {
        setTimeout(() => {
              bar.style.width = '260px';
        }, 120);
    }
});
function forgotPassword(e){
    e.preventDefault();
    const email = document.getElementById('email').value;
    if(!email){ alert('Ingresa tu correo arriba.'); return; }
    fetch('/api/password/forgot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
    }).then(r => r.json()).then(() => {
        alert('Si el correo existe, se generó un enlace en los logs.');
    }).catch(() => alert('Error solicitando recuperación.'));
}

function togglePassword(){
    const input = document.getElementById('password');
    const eye = document.getElementById('eyeIcon');
    const eyeOpen = eye.querySelector('#eyeOpen');
    const eyeClosed = eye.querySelector('#eyeClosed');
    if(input.type === 'password'){
        input.type = 'text';
        input.classList.add('showing');
        if(eyeOpen && eyeClosed){
            eyeOpen.style.opacity = 0;
            eyeClosed.style.opacity = 1;
        }
    } else {
        input.type = 'password';
        input.classList.remove('showing');
        if(eyeOpen && eyeClosed){
            eyeOpen.style.opacity = 1;
            eyeClosed.style.opacity = 0;
        }
    }
    // animación suave
    input.style.transition = 'background 0.25s';
    input.style.background = input.type === 'text'
        ? 'rgba(255,255,255,0.24)'
        : 'rgba(255,255,255,0.16)';
    setTimeout(() => {
        input.style.background = 'rgba(255,255,255,0.16)';
    }, 250);
}

// Carrusel simple para el ícono decorativo (hoja / trabajador)
document.addEventListener('DOMContentLoaded', () => {
    const leafImg = document.querySelector('.login-leaf-img');
    if (!leafImg) return;

    const icons = [
        'https://www.svgrepo.com/show/195611/leaf.svg',
        'https://www.svgrepo.com/show/115899/industry-worker-with-cap-protection-and-a-laptop.svg'
    ];

    let iconIndex = 0;

    const swapIcon = () => {
        iconIndex = (iconIndex + 1) % icons.length;
        leafImg.style.opacity = '0';
        setTimeout(() => {
            leafImg.src = icons[iconIndex];
            leafImg.style.opacity = '0.9';
        }, 200);
    };

    setInterval(swapIcon, 5000);
});

// === Fondo login: capa izquierda transparente (deja ver solo el degradado del body) ===
document.addEventListener('DOMContentLoaded', () => {
    const left = document.getElementById('bgcLeft');
    if (!left) return;

    left.style.backgroundImage = 'none';
    left.style.backgroundColor = 'transparent';
    left.style.filter = 'none';
    left.style.opacity = '1';
});
</script>

@endsection

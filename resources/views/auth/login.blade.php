@extends('layouts.app')

@section('content')

    <div class="login-bg">
        <div class="login-wrapper">
            <div class="login-green-bar" id="loginGreenBar">
				<div class="login-green-brand">
					<img src="{{ asset('images/logo-sena.svg') }}" alt="Logo SENA" class="login-green-logo">
					<div class="login-green-title">TEMPUS</div>
				</div>
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
                                    <circle id="eyeBall" cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="#198f3a"/>
                                </g>
                                <g id="eyeClosed" style="opacity:0;">
                                    <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="5" y1="19" x2="19" y2="5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </g>
                            </svg>
                        </span>
                    </div>
                </div>

                <button type="submit" class="login-btn">Entrar</button>
            </form>
        </div>
        </div>
    </div>

<style>
/* Barra verde animada detrás del formulario */
.login-green-bar {
    position: absolute;
    left: 50%;
    top: 50%;
    width: 65%;
    max-width: 200px;
    height: 89%;
    background: rgba(25, 143, 58, 0.18);
    border-radius: 22px 0 0 22px;
    z-index: 2;
    box-shadow: 0 4px 24px rgba(25,143,58,0.13);
    transition: transform 0.55s linear, background 0.3s;
    opacity: 1;
    transform: translate(-50%, -50%) scaleX(0);
    transform-origin: left center;
    backdrop-filter: blur(10px) saturate(1.2);
    -webkit-backdrop-filter: blur(10px) saturate(1.2);
    border: 1.5px solid rgba(0, 174, 49, 0.18);
}
.login-green-brand {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -56%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    pointer-events: none;
}
.login-green-logo {
    width: 92px;
    max-width: 70%;
    height: auto;
    filter: drop-shadow(0 3px 8px rgba(0,0,0,0.18));
}
.login-green-title {
    margin-top: 10px;
    font-weight: 800;
    font-size: 1.08rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #0b7c25;
}
.login-green-title::before,
.login-green-title::after {
    content: '';
    display: block;
    margin: 4px auto 0;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(0,168,89,0.08), rgba(0,168,89,0.8), rgba(0,168,89,0.08));
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
.login-bg {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 6vw;
    background: none;
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
    background: #fff !important;
    border-radius: 22px;
    box-shadow: 0 12px 40px 0 rgba(0,0,0,0.22);
    padding: 38px 36px 32px 36px;
    min-width: 340px;
    max-width: 370px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    opacity: 1;
    position: relative;
    z-index: 11;
}
.login-title {
    font-size: 1.7rem;
    font-weight: 700;
    color: #198f3a;
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
    color: #2b6f36;
    font-weight: 600;
}
.login-input {
    width: 100%;
    padding: 12px 14px;
    border-radius: 32px;
    border: 2px solid #e6f3ea;
    background: #f8fff8;
    outline: none;
    transition: box-shadow .15s, border-color .15s;
    font-size: 1rem;
    box-sizing: border-box;
}
.login-input-password {
    padding-right: 44px;
    transition: background 0.25s;
}
.login-input:focus {
    border-color: #2ea64b;
    box-shadow: 0 8px 20px rgba(46,166,75,0.12);
    background: #fff;
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
    background: #e6f3ea;
    box-shadow: 0 2px 8px rgba(46,166,75,0.10);
}
.login-show svg {
    transition: stroke 0.2s, filter 0.2s;
    color: #198f3a;
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
    color: #198f3a;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.98rem;
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
              bar.style.transform = 'translate(calc(-50% - 7cm), -50%) scaleX(1)';
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
    input.style.background = input.type === 'text' ? '#f2fff2' : '#f8fff8';
    setTimeout(() => {
        input.style.background = '#f8fff8';
    }, 250);
}

// === Fondo login: rotación de imágenes en el lado izquierdo ===
document.addEventListener('DOMContentLoaded', () => {
    const left = document.getElementById('bgcLeft');
    if (!left) return;

    const images = [
        '{{ asset("images/img_fondo/cazuca.png") }}',
        '{{ asset("images/img_fondo/cide.png") }}',
        '{{ asset("images/img_fondo/ciudad verde.png") }}',
        '{{ asset("images/img_fondo/sibate.png") }}'
    ];
    let idx = 0;
    const applyLeft = (url) => {
        left.style.opacity = '0.55';
        left.style.filter = 'saturate(1.25) contrast(1.05)';
        // Ajustes por imagen: permitir posicionar mejor si la imagen nueva tiene encuadre distinto
        const filename = (url || '').split('/').pop().toLowerCase();
        // valores por defecto
        left.style.backgroundPosition = 'center center';
        left.style.backgroundSize = 'cover';
        if (filename.includes('cazuca')) {
            // mostrar más la parte izquierda/centro de la foto (edificio)
            left.style.backgroundPosition = 'left center';
            left.style.backgroundSize = 'cover';
        } else if (filename.includes('ciudad') || filename.includes('ciudad verde')) {
            left.style.backgroundPosition = 'center center';
        } else if (filename.includes('cide')) {
            left.style.backgroundPosition = 'center center';
        } else if (filename.includes('sibate')) {
            left.style.backgroundPosition = 'center center';
        }
        setTimeout(() => {
            left.style.backgroundImage = `url("${url}")`;
            left.style.opacity = '0.80';
        }, 220);
    };
    applyLeft(images[idx]);
    setInterval(() => {
        idx = (idx + 1) % images.length;
        applyLeft(images[idx]);
    }, 6000);
});
</script>

@endsection

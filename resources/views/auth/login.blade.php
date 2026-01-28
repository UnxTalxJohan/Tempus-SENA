@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-split-card">
        <div class="auth-left">
        </div>
        <div class="auth-right">
            <div class="auth-brand">
                <img src="{{ asset('images/logo-sena.svg') }}" alt="SENA" aria-hidden="true">
                <h1 class="auth-title">Bienvenido</h1>
                <p class="auth-subtitle">Inicia sesión para continuar</p>
            </div>

            @if ($errors->any())
                <div class="auth-alert">
                    @foreach ($errors->all() as $error)
                        <div class="auth-alert-item">{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="auth-form">
                @csrf
                <div class="auth-field">
                    <label for="email">Correo o usuario</label>
                    <input type="text" id="email" name="email" class="auth-input" value="{{ old('email') }}" required autofocus placeholder="tucorreo@ejemplo.com o tu usuario">
                </div>
                <div class="auth-field">
                    <label for="password">Contraseña</label>
                    <div class="auth-password">
                        <input type="password" id="password" name="password" class="auth-input" required placeholder="Tu contraseña">
                        <button type="button" class="auth-show" aria-label="Mostrar contraseña" onclick="togglePassword()" title="Mostrar/ocultar">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="auth-remember">
                    <a class="auth-link" href="#" onclick="forgotPassword(event)">¿Olvidaste tu contraseña?</a>
                </div>
                <button type="submit" class="auth-btn">Entrar</button>
            </form>

            <p class="auth-hint">Usuario demo: <strong>test@example.com</strong> · Clave: <strong>password</strong></p>
        </div>
    </div>
</div>

<script>
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
    input.type = (input.type === 'password') ? 'text' : 'password';
}

// Inserta watermark y anima estilo máquina de escribir
document.addEventListener('DOMContentLoaded', () => {
    const left = document.querySelector('.auth-left');
    if (!left) return;

    let wm = document.getElementById('tempusWatermark');
    if (!wm) {
        wm = document.createElement('div');
        wm.id = 'tempusWatermark';
        wm.className = 'auth-watermark';
        left.appendChild(wm);
    } else {
        wm.textContent = '';
    }

    const text = 'Tempus-SENA';
    const textWrap = document.createElement('span');
    textWrap.className = 'wm-text';
    wm.appendChild(textWrap);
    const caret = document.createElement('span');
    caret.className = 'wm-caret';
    caret.setAttribute('aria-hidden', 'true');
    wm.appendChild(caret);

    let i = 0;
    const speed = 140; // ms por letra (más lento y fluido)
    const interval = setInterval(() => {
        if (i < text.length) {
            const span = document.createElement('span');
            span.className = 'wm-letter';
            span.textContent = text[i];
            textWrap.appendChild(span);
            i++;
        }
        if (i >= text.length) {
            clearInterval(interval);
            caret.remove();
            // Asegurar texto final completo
            textWrap.textContent = text;
        }
    }, speed);
});

// === Fondo login: dos capas con imágenes de sedes y blur al cambiar ===
document.addEventListener('DOMContentLoaded', () => {
    const left = document.getElementById('bgcLeft');
    const right = document.getElementById('bgcRight');
    if (!left || !right) return;

    // Configurar el lado derecho en blanco fijo y sin imagen
    right.style.backgroundImage = 'none';
    right.style.backgroundColor = '#ffffff';
    right.style.opacity = '1';
    right.style.filter = 'none';

    // Rotar únicamente la imagen del lado izquierdo
    const images = [
        '{{ asset('images/Sede_Principal_Soacha.jpg') }}',
        '{{ asset('images/Sena cide ciudad verde.jpg') }}',
        '{{ asset('images/sena cide ciudad verde 2.jpg') }}',
        '{{ asset('images/sede cazuca sena.jpeg') }}'
    ];
    let idx = 0;
    const applyLeft = (url) => {
        left.style.filter = 'none';
        left.style.opacity = '.55';
        setTimeout(() => {
            left.style.backgroundImage = `url(\"${url}\")`;
            left.style.filter = 'none';
            left.style.opacity = '.70';
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

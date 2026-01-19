@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-split-card">
        <div class="auth-left">
            <div class="auth-left-overlay"></div>
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

// Inserta texto estilizado verde "Tempus-SENA" mediante JS
document.addEventListener('DOMContentLoaded', () => {
    const wm = document.createElement('div');
    wm.id = 'tempusWatermark';
    wm.className = 'auth-watermark';
    wm.textContent = 'Tempus-SENA';
    const left = document.querySelector('.auth-left');
    (left || document.body).appendChild(wm);
});

// === Fondo login: sin imágenes y corte diagonal centrado (estilos vía CSS) ===
</script>
@endsection

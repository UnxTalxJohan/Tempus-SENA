@extends('layouts.app')

@section('title', 'Panel de Usuario')

@section('content')
@php($appAuth = session('app_auth', []))
@php($nombre = $appAuth['nombre'] ?? $appAuth['name'] ?? 'Usuario CIDE')
@php($correo = $appAuth['correo'] ?? $appAuth['email'] ?? null)
@php($cc = $appAuth['cc'] ?? $appAuth['documento'] ?? null)
@php($rolId = $appAuth['rol_id'] ?? null)
@php(
    $rolNombre = match(true) {
        $rolId === 1 || $rolId === '1' => 'Administrador',
        $rolId === 2 || $rolId === '2' => 'Contrato',
        $rolId === 3 || $rolId === '3' => 'Planta',
        default => 'Usuario',
    }
)

<div class="user-panel-page">
    <div class="user-panel-container">
        {{-- Tarjeta principal de perfil --}}
        <section class="user-card">
            <div class="user-card-header-bg"></div>

            <div class="user-card-avatar-wrapper">
                <div class="user-card-avatar user-card-avatar-empty">
                    <i class="bi bi-person-circle" aria-hidden="true"></i>
                </div>
                <button class="user-card-avatar-btn" id="avatarCameraBtn" title="Cambiar avatar" aria-label="Cambiar avatar">
                    <i class="bi bi-camera" aria-hidden="true"></i>
                </button>
            </div>

            <div class="user-card-body">
                <h1 class="user-card-name">{{ $nombre }}</h1>
                <p class="user-card-role">{{ $rolNombre }} &bull; Usuario CIDE</p>

                <div class="user-card-stats">
                    <div class="user-card-stat">
                        <span class="label">Programas</span>
                        <span class="value">—</span>
                    </div>
                    <div class="user-card-stat">
                        <span class="label">Fichas</span>
                        <span class="value">—</span>
                    </div>
                    <div class="user-card-stat">
                        <span class="label">Matriz</span>
                        <span class="value">—</span>
                    </div>
                </div>

                <div class="user-card-links">
                    @if($correo)
                        <a href="mailto:{{ $correo }}" class="user-card-link" aria-label="Correo institucional">
                            <i class="bi bi-envelope-fill" aria-hidden="true"></i>
                        </a>
                    @endif
                    <button type="button" class="user-card-link" title="Próximamente">
                        <i class="bi bi-person-badge-fill" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </section>

        {{-- Sobre mí --}}
        <section class="user-panel-section">
            <h2 class="section-title">Sobre mí</h2>
            <p class="section-text">
                Este panel resume tu información básica dentro del Sistema de Gestión CIDE.
                Próximamente podrás actualizar más datos de tu perfil y ver tu historial
                de actividades, cargas de matrices y participación en fichas de formación.
            </p>

            <div class="user-panel-grid">
                <div class="user-field">
                    <span class="label">Nombre completo</span>
                    <span class="value">{{ $nombre }}</span>
                </div>
                <div class="user-field">
                    <span class="label">Documento</span>
                    <span class="value">
                        @if($cc)
                            {{ number_format($cc, 0, ',', '.') }}
                        @else
                            <span class="muted">Sin documento registrado</span>
                        @endif
                    </span>
                </div>
                <div class="user-field">
                    <span class="label">Correo electrónico</span>
                    <span class="value">
                        @if($correo)
                            {{ $correo }}
                        @else
                            <span class="muted">Sin correo registrado</span>
                        @endif
                    </span>
                </div>
                <div class="user-field">
                    <span class="label">Rol en el sistema</span>
                    <span class="value">
                        <span class="pill">{{ $rolNombre }}</span>
                    </span>
                </div>
            </div>
        </section>

        {{-- "Habilidades" adaptado a contexto CIDE --}}
        <section class="user-panel-section">
            <h2 class="section-title">Áreas y accesos</h2>
            <p class="section-text">Funciones a las que tienes acceso dentro del CIDE.</p>

            <div class="user-tags">
                <span class="tag">Dashboard</span>
                <span class="tag">Matriz de programas</span>
                <span class="tag">Fichas de formación</span>
                <span class="tag">Sedes y ambientes</span>
                @if($rolNombre === 'Administrador')
                    <span class="tag">Gestión de usuarios</span>
                    <span class="tag">Cargas masivas</span>
                @endif
            </div>
        </section>
    </div>

    <form id="avatarForm" method="POST" action="{{ route('user.avatar.upload') }}" enctype="multipart/form-data" style="display:none;">
        @csrf
        <input type="file" name="avatar" id="avatarInput" accept="image/*">
    </form>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cameraBtn = document.getElementById('avatarCameraBtn');
    const input = document.getElementById('avatarInput');
    const form = document.getElementById('avatarForm');
    if (!cameraBtn || !input || !form) return;

    cameraBtn.addEventListener('click', function (e) {
        e.preventDefault();
        input.click();
    });

    input.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            form.submit();
        }
    });
});
</script>
@endsection
@endsection

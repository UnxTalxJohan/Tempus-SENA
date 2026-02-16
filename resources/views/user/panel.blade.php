@extends('layouts.app')

@section('title', 'Panel de Usuario')

@section('content')
@php($appAuth = session('app_auth', []))
@php($avatar = $appAuth['avatar'] ?? null)
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

<div class="page-container" style="padding:24px; max-width:1100px; margin:0 auto;">
    

    <div style="display:grid; grid-template-columns:minmax(260px, 320px) minmax(0, 1fr); gap:20px; align-items:flex-start; flex-wrap:wrap;">
        <!-- Columna izquierda: avatar + resumen -->
        <div class="card" style="background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:18px; text-align:left; cursor:default;">
            <h2 style="font-size:18px; font-weight:700; margin-bottom:14px; color:#064e3b;">Tu perfil</h2>

            <div class="avatar-section" style="display:flex; flex-direction:column; align-items:center; gap:14px;">
                <div class="avatar-wrapper" style="position:relative; width:180px; height:180px;">
                    <div class="avatar-circle" style="
                        width: 180px;
                        height: 180px;
                        border-radius: 50%;
                        background-size: cover;
                        background-position: center;
                        background-repeat: no-repeat;
                        border: 4px solid #00A859;
                        box-shadow: 0 6px 18px rgba(0,0,0,.12);
                        background-image: url('{{ $avatar ? asset('storage/'.$avatar) : asset('images/logo-sena.svg') }}');
                    "></div>
                    <button class="avatar-camera" id="avatarCameraBtn" title="Cambiar avatar" aria-label="Cambiar avatar" style="
                        position:absolute;
                        right:-6px;
                        bottom:-6px;
                        background:#00A859;
                        color:#fff;
                        border:none;
                        width:40px;
                        height:40px;
                        border-radius:50%;
                        box-shadow:0 6px 16px rgba(0,168,89,.25);
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        cursor:pointer;
                    ">
                        <i class="bi bi-camera" style="font-size:18px;" aria-hidden="true"></i>
                    </button>
                </div>

                <div style="text-align:center;">
                    <div style="font-size:18px; font-weight:700; color:#111827;">{{ $nombre }}</div>
                    @if($correo)
                        <div style="font-size:13px; color:#6b7280; margin-top:4px;">{{ $correo }}</div>
                    @endif
                    @if($cc)
                        <div style="font-size:12px; color:#6b7280; margin-top:2px;">Documento: <strong>{{ number_format($cc, 0, ',', '.') }}</strong></div>
                    @endif
                </div>

                <form id="avatarForm" method="POST" action="{{ route('user.avatar.upload') }}" enctype="multipart/form-data" style="display:none;">
                    @csrf
                    <input type="file" name="avatar" id="avatarInput" accept="image/*">
                </form>

                <div style="font-size:12px; color:#6b7280; text-align:left; width:100%; background:#f9fafb; border-radius:10px; padding:10px 12px; border:1px dashed #d1d5db;">
                    <div style="font-weight:600; margin-bottom:4px;">Consejos para tu foto</div>
                    <ul style="margin:0; padding-left:18px; line-height:1.4;">
                        <li>Formatos permitidos: JPG, PNG, WEBP (máx. 2&nbsp;MB).</li>
                        <li>Usa una foto clara y centrada.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Columna derecha: datos personales y de cuenta -->
        <div style="display:flex; flex-direction:column; gap:16px;">
            <div class="card" style="background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:18px; text-align:left; cursor:default;">
                <h2 style="font-size:16px; font-weight:700; margin-bottom:12px; color:#064e3b;">Datos personales</h2>
                <div style="display:grid; grid-template-columns:minmax(120px, 180px) minmax(0, 1fr); row-gap:8px; column-gap:18px; font-size:13px;">
                    <div style="font-weight:600; color:#6b7280;">Nombre completo</div>
                    <div style="font-weight:600; color:#111827;">{{ $nombre }}</div>

                    <div style="font-weight:600; color:#6b7280;">Documento</div>
                    <div>
                        @if($cc)
                            <span style="font-weight:600;">{{ number_format($cc, 0, ',', '.') }}</span>
                        @else
                            <span style="opacity:.7;">Sin documento registrado</span>
                        @endif
                    </div>

                    <div style="font-weight:600; color:#6b7280;">Correo electrónico</div>
                    <div>
                        @if($correo)
                            <span>{{ $correo }}</span>
                        @else
                            <span style="opacity:.7;">Sin correo registrado</span>
                        @endif
                    </div>

                    <div style="font-weight:600; color:#6b7280;">Rol</div>
                    <div>
                        <span style="display:inline-block; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:700; background:#e6fffa; color:#0b7c25;">
                            {{ $rolNombre }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:14px; padding:18px; text-align:left; cursor:default;">
                <h2 style="font-size:16px; font-weight:700; margin-bottom:10px; color:#064e3b;">Información de cuenta</h2>
                <p style="font-size:13px; color:#4b5563; margin:0 0 10px;">Estos datos se utilizan para el acceso al sistema y la trazabilidad de tus acciones dentro del CIDE.</p>
                <div style="display:grid; grid-template-columns:minmax(120px, 190px) minmax(0, 1fr); row-gap:8px; column-gap:18px; font-size:13px;">
                    <div style="font-weight:600; color:#6b7280;">Identificador interno</div>
                    <div>
                        @if(!empty($appAuth['usuario_id']))
                            <span>#{{ $appAuth['usuario_id'] }}</span>
                        @else
                            <span style="opacity:.7;">No disponible</span>
                        @endif
                    </div>

                    <div style="font-weight:600; color:#6b7280;">Sesión actual</div>
                    <div style="opacity:.8;">Iniciaste sesión como <strong>{{ $rolNombre }}</strong>.
                        @if($correo)
                            <span> Correo de acceso: {{ $correo }}.</span>
                        @endif
                    </div>

                    <div style="font-weight:600; color:#6b7280;">Último acceso</div>
                    <div>
                        @if(!empty($appAuth['last_login_at']))
                            <span>{{ $appAuth['last_login_at'] }}</span>
                        @else
                            <span style="opacity:.7;">Próximamente podrás ver el historial de accesos.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const cameraBtn = document.getElementById('avatarCameraBtn');
    const input = document.getElementById('avatarInput');
    const form = document.getElementById('avatarForm');
    if (!cameraBtn || !input || !form) return;
    cameraBtn.addEventListener('click', function(e){
        e.preventDefault();
        input.click();
    });
    input.addEventListener('change', function(){
        if (this.files && this.files.length > 0) {
            form.submit();
        }
    });
});
</script>
@endsection
@endsection

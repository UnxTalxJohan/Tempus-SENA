@extends('layouts.app')

@section('title', 'Panel de Usuario')

@section('content')
<div class="page-container" style="padding:24px; max-width:1100px; margin:0 auto;">
    <h1 style="font-size:24px; font-weight:700; margin-bottom:12px;">Panel de Usuario</h1>
    <p style="opacity:.8; margin-bottom:20px;">Gestiona tu cuenta, foto de perfil y preferencias.</p>

    <div class="card" style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px;">
        <h2 style="font-size:18px; font-weight:600; margin-bottom:12px;">Foto de perfil</h2>
        @php($appAuth = session('app_auth'))
        @php($avatar = $appAuth['avatar'] ?? null)

        <div class="avatar-section" style="display:flex; align-items:center; gap:18px;">
            <div class="avatar-wrapper">
                <div class="avatar-circle" style="background-image: url('{{ $avatar ? asset('storage/'.$avatar) : asset('images/logo-sena.svg') }}');"></div>
                <button class="avatar-camera" id="avatarCameraBtn" title="Cambiar avatar" aria-label="Cambiar avatar">
                    <i class="bi bi-camera"></i>
                </button>
            </div>

            <form id="avatarForm" method="POST" action="{{ route('user.avatar.upload') }}" enctype="multipart/form-data" style="display:none;">
                @csrf
                <input type="file" name="avatar" id="avatarInput" accept="image/*">
            </form>

            <div style="flex:1;">
                <p style="margin:0 0 8px 0; opacity:.8;">Usa el botón de cámara para subir o cambiar tu foto.</p>
                <ul style="margin:0; padding-left:18px; color:#6b7280; font-size:13px;">
                    <li>Formatos: JPG, PNG, WEBP (máx 2MB).</li>
                    <li>Se guarda temporalmente en el almacenamiento local.</li>
                </ul>
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

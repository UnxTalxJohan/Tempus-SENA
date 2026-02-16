@extends('layouts.app')

@section('title', 'Cargar sedes y ambientes')

@section('content')
<div class="content-wrapper">
    <div class="container">
        <main>
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:18px; flex-wrap:wrap;">
                <div>
                    <h1 style="font-size:24px; font-weight:700; margin:0;">Cargar sedes y ambientes</h1>
                    <p style="margin:4px 0 0; opacity:.8;">Sube un archivo Excel con las sedes en la fila 2 y los ambientes en las filas inferiores.</p>
                </div>
                <a href="{{ route('sede.index') }}" class="btn btn-secondary" style="border-radius:999px;">
                    Volver al listado
                </a>
            </div>

            @if(session('error'))
            <div class="alert alert-error">
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <div class="card" style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:20px 22px; width:100%; max-width:100%; box-shadow:0 18px 45px rgba(15,23,42,0.08);">
                <form method="POST" action="{{ route('sede.preview') }}" enctype="multipart/form-data">
                    @csrf

                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
                        <div>
                            <div style="font-size:13px; text-transform:uppercase; letter-spacing:.08em; color:#6b7280; font-weight:600;">Paso 1</div>
                            <h2 style="margin:2px 0 0; font-size:18px; font-weight:700; color:#111827;">Selecciona el archivo Excel de sedes y ambientes</h2>
                        </div>
                        <div style="display:flex; flex-direction:column; align-items:flex-end; font-size:11px; color:#6b7280; text-align:right;">
                            <span><strong>Formato permitido:</strong> .xlsx, .xls</span>
                            <span><strong>Tamaño máximo:</strong> 10&nbsp;MB</span>
                        </div>
                    </div>

                    <input type="file" name="excel_sedes" id="excel_sedes" accept=".xlsx,.xls" required style="display:none;">

                    <label for="excel_sedes" id="dropzone_sedes" style="border:2px dashed #d1d5db; border-radius:18px; padding:28px 22px; background:linear-gradient(135deg,#f9fafb,#f3f4ff); display:flex; flex-direction:column; align-items:center; text-align:center; gap:14px; margin-bottom:18px; cursor:pointer;">
                        <div style="width:64px; height:64px; border-radius:18px; background:#e5f9f0; display:flex; align-items:center; justify-content:center; color:#0f766e; font-size:30px; box-shadow:0 10px 24px rgba(15,118,110,0.22);">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                        </div>

                        <div>
                            <div style="font-weight:700; font-size:15px; color:#111827;">Arrastra y suelta tu archivo aquí o haz clic en "Elegir archivo"</div>
                            <p style="font-size:12px; color:#6b7280; margin:6px 0 0; max-width:620px;">
                                Recuerda: la <strong>fila 2</strong> debe contener los nombres de las sedes (por ejemplo: SIBATE, CIDE, TECNOPARQUE, ...)
                                y desde la <strong>fila 3 hacia abajo</strong> van los ambientes de cada sede, en columnas como en tu plantilla.
                            </p>
                        </div>

                        <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:center; margin-top:4px;">
                            <span class="btn btn-secondary" style="border-radius:999px; padding-inline:18px; pointer-events:none;">
                                Elegir archivo
                            </span>
                            <span id="excel_sedes_nombre" style="font-size:13px; color:#4b5563;">
                                Ningún archivo seleccionado
                            </span>
                        </div>
                    </label>

                    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div style="font-size:12px; color:#6b7280;">
                            <span style="font-weight:600; color:#059669;">Paso 2:</span> Previsualiza para confirmar que las sedes y ambientes fueron detectados correctamente.
                        </div>
                        <button type="submit" class="btn btn-primary" style="min-width:140px;">
                            Previsualizar
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('excel_sedes');
        var labelNombre = document.getElementById('excel_sedes_nombre');
        if (!input || !labelNombre) return;

        input.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                labelNombre.textContent = this.files[0].name;
            } else {
                labelNombre.textContent = 'Ningún archivo seleccionado';
            }
        });
    });
</script>
@endsection

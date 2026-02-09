@extends('layouts.app')

@section('title', 'Consolidado titulada')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <main>
                <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px; flex-wrap:wrap;">
                    <div>
                        <h1 style="font-size:24px; font-weight:700; margin:0;">Consolidado planta</h1>
                    </div>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left" aria-hidden="true" style="margin-right:6px;"></i>
                        Volver a gestión de usuarios
                    </a>
                </div>

                @if(session('success'))
                <div class="alert alert-success">
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-error">
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                <form action="{{ route('usuarios.titulada.preview') }}" method="POST" enctype="multipart/form-data" id="tituladaForm">
                    @csrf

                    <div class="upload-area" id="uploadAreaTitulada">
                        <div class="upload-icon"><i class="bi bi-file-earmark-arrow-up" aria-hidden="true"></i></div>
                        <h2>Selecciona o arrastra el Excel de planta</h2>
                        <p>Columnas esperadas: Nombre y apellidos, Cédula, Área, Estudios. Formatos: .xlsx, .xls (máximo 10MB)</p>

                        <div class="file-input-wrapper">
                            <label for="excel_titulada" class="btn-select">Seleccionar archivo</label>
                            <input type="file" id="excel_titulada" name="excel_titulada" accept=".xlsx,.xls" required>
                        </div>

                        <div class="selected-file" id="selectedTitulada" style="display:none;"></div>
                    </div>

                    <div class="buttons" style="justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary" id="btnTitulada" disabled>
                            Visualizar consolidado <i class="bi bi-arrow-right" aria-hidden="true" style="margin-left:8px;"></i>
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script>
        const fileInputT = document.getElementById('excel_titulada');
        const uploadAreaT = document.getElementById('uploadAreaTitulada');
        const selectedT = document.getElementById('selectedTitulada');
        const btnT = document.getElementById('btnTitulada');

        fileInputT.addEventListener('change', function(){
            const f = this.files && this.files[0];
            if (!f) { selectedT.style.display='none'; btnT.disabled = true; return; }
            selectedT.innerHTML = `<i class="bi bi-check2-circle" aria-hidden="true"></i> <strong>${f.name}</strong> (${(f.size/1024/1024).toFixed(2)} MB)`;
            selectedT.style.display = 'block';
            btnT.disabled = false;
        });

        uploadAreaT.addEventListener('dragover', function(e){
            e.preventDefault(); e.stopPropagation(); this.classList.add('dragover');
        });
        uploadAreaT.addEventListener('dragleave', function(e){
            e.preventDefault(); e.stopPropagation(); this.classList.remove('dragover');
        });
        uploadAreaT.addEventListener('drop', function(e){
            e.preventDefault(); e.stopPropagation(); this.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInputT.files = files;
                fileInputT.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endsection

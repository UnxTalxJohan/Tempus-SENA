@extends('layouts.app')

@section('title', 'Consolidado contratistas')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <main>
                <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px;">
                    <div>
                        <h1 style="font-size:24px; font-weight:700; margin:0;">Consolidado contratistas</h1>
                    </div>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding-inline:16px;">
                        <i class="bi bi-arrow-left" aria-hidden="true" style="font-size:16px;"></i>
                        <span>Volver a gestión de usuarios</span>
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

                <form action="{{ route('usuarios.contratistas.preview') }}" method="POST" enctype="multipart/form-data" id="contratistasForm">
                    @csrf

                    <div class="upload-area" id="uploadAreaContratistas">
                        <div class="upload-icon"><i class="bi bi-file-earmark-arrow-up" aria-hidden="true"></i></div>
                        <h2>Selecciona o arrastra el Excel de contratistas</h2>
                        <p>Formatos aceptados: .xlsx, .xls (máximo 10MB)</p>

                        <div class="file-input-wrapper">
                            <label for="excel_contratistas" class="btn-select">Seleccionar archivo</label>
                            <input type="file" id="excel_contratistas" name="excel_contratistas" accept=".xlsx,.xls" required>
                        </div>

                        <div class="selected-file" id="selectedContratistas" style="display:none;"></div>
                    </div>

                    <div class="buttons" style="justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary" id="btnContratistas" disabled>
                            Visualizar consolidado <i class="bi bi-arrow-right" aria-hidden="true" style="margin-left:8px;"></i>
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script>
        const fileInputC = document.getElementById('excel_contratistas');
        const uploadAreaC = document.getElementById('uploadAreaContratistas');
        const selectedC = document.getElementById('selectedContratistas');
        const btnC = document.getElementById('btnContratistas');

        fileInputC.addEventListener('change', function(){
            const f = this.files && this.files[0];
            if (!f) { selectedC.style.display='none'; btnC.disabled = true; return; }
            selectedC.innerHTML = `<i class="bi bi-check2-circle" aria-hidden="true"></i> <strong>${f.name}</strong> (${(f.size/1024/1024).toFixed(2)} MB)`;
            selectedC.style.display = 'block';
            btnC.disabled = false;
        });

        uploadAreaC.addEventListener('dragover', function(e){
            e.preventDefault(); e.stopPropagation(); this.classList.add('dragover');
        });
        uploadAreaC.addEventListener('dragleave', function(e){
            e.preventDefault(); e.stopPropagation(); this.classList.remove('dragover');
        });
        uploadAreaC.addEventListener('drop', function(e){
            e.preventDefault(); e.stopPropagation(); this.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInputC.files = files;
                fileInputC.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endsection

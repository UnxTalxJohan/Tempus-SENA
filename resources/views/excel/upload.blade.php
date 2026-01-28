@extends('layouts.app')

@section('title', 'Cargar Excel - SENA')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <main>
                @if(session('success'))
                <div class="alert alert-success">
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-error">
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-error">
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                <div class="instructions">
                    <h3><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> Instrucciones importantes:</h3>
                    <ul>
                        <li>El archivo debe estar en formato Excel (.xlsx o .xls)</li>
                        <li>La estructura debe coincidir con el formato especificado abajo</li>
                        <li>Los datos del programa deben estar en la fila 2</li>
                        <li>No se permiten programas duplicados (verificar código)</li>
                        <li>Todas las competencias deben tener código único</li>
                    </ul>
                </div>

                <div class="format-example">
                    <h3><i class="bi bi-table" aria-hidden="true"></i> Formato de columnas requerido:</h3>
                    <table class="format-table">
                        <thead>
                            <tr>
                                <th>A - Nivel</th>
                                <th>B - Nombre Programa</th>
                                <th>C - Código</th>
                                <th>D - Versión</th>
                                <th>E - Cód. Comp.</th>
                                <th>F - Competencia</th>
                                <th>G - Duración Comp. (h)</th>
                                <th>H - Resultado de Aprendizaje</th>
                                <th>I - H. Máx por Resultado</th>
                                <th>J - H. Mín por Resultado</th>
                                <th>K - Trimestre</th>
                                <th>L - H/Sem a Programar</th>
                                <th>M - H/Trim a Programar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tecnólogo</td>
                                <td>Análisis y Desarrollo...</td>
                                <td>228106</td>
                                <td>2</td>
                                <td>240201500</td>
                                <td>Promover la interacción...</td>
                                <td>120</td>
                                <td>Interactuar en los contextos...</td>
                                <td>40</td>
                                <td>32</td>
                                <td>3</td>
                                <td>4</td>
                                <td>44</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <form action="{{ route('excel.preview.multi') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon"><i class="bi bi-file-earmark-arrow-up" aria-hidden="true"></i></div>
                        <h2>Selecciona o arrastra tu archivo aquí</h2>
                        <p>Formatos aceptados: .xlsx, .xls (máximo 10MB)</p>
                        
                        <div class="file-input-wrapper">
                            <label for="excel_files" class="btn-select">Seleccionar hasta 5 archivos</label>
                            <input type="file" id="excel_files" name="excel_files[]" accept=".xlsx,.xls" multiple required>
                        </div>
                        
                        <div class="selected-file" id="selectedFile"></div>
                    </div>

                    <div class="buttons">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary"><i class="bi bi-arrow-left" aria-hidden="true" style="margin-right:8px;"></i>Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="btnSubmit" disabled>
                            Previsualizar Datos <i class="bi bi-arrow-right" aria-hidden="true" style="margin-left:8px;"></i>
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('excel_files');
        const uploadArea = document.getElementById('uploadArea');
        const selectedFile = document.getElementById('selectedFile');
        const btnSubmit = document.getElementById('btnSubmit');

        // Selección de archivo
        fileInput.addEventListener('change', function(e) {
            const files = Array.from(this.files || []);
            if (files.length === 0) { selectedFile.style.display='none'; btnSubmit.disabled = true; return; }
            if (files.length > 5) {
                selectedFile.innerHTML = '⚠️ Selecciona máximo 5 archivos.';
                btnSubmit.disabled = true;
                if (window.showToast) window.showToast('Solo se admiten 5 archivos por carga', 'warning');
                return;
            }
            const list = files.map(f => `• <strong>${f.name}</strong> (${(f.size/1024/1024).toFixed(2)} MB)`).join('<br>');
            selectedFile.innerHTML = `<i class=\"bi bi-check2-circle\" aria-hidden=\"true\"></i> ${files.length} archivo(s):<br>${list}`;
            selectedFile.style.display = 'block';
            btnSubmit.disabled = false;
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endsection

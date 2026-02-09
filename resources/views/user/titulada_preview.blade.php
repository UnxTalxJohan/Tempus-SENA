@extends('layouts.app')

@section('title', 'Previsualizacion consolidado planta')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <main>
                <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px;">
                    <div>
                        <h1 style="font-size:24px; font-weight:700; margin:0;">Previsualizacion consolidado planta</h1>
                        <p style="margin:6px 0 0; opacity:.8;">Revisa un resumen de los registros que se crearan o actualizaran antes de confirmar la carga.</p>
                    </div>
                    <a href="{{ route('usuarios.titulada.form') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left" aria-hidden="true" style="margin-right:6px;"></i>
                        Volver a subir archivo
                    </a>
                </div>

                <div class="alert alert-info" style="margin-bottom:16px;">
                    <p style="margin:0;">
                        <strong>Archivo:</strong> {{ $fileName }}<br>
                        <strong>Filas con datos:</strong> {{ $totalRows }}<br>
                        <strong>Filas con CC valida:</strong> {{ $validRows }}<br>
                        <strong>Filas sin CC (se omitiran al procesar):</strong> {{ $skippedNoCc }}
                    </p>
                </div>

                @if($skippedNoCc > 0)
                <div class="alert alert-error" style="margin-bottom:16px;">
                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true" style="font-size:16px;"></i>
                    <div>
                        <strong>Advertencia:</strong> se encontraron {{ $skippedNoCc }} fila(s) sin cedula.
                        <div style="margin-top:4px;">Puedes cancelar o subir de todas formas (esas filas se omitiran).</div>
                    </div>
                </div>
                @endif
                                Confirmar e insertar planta
                <div class="buttons" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
                    <form action="{{ route('usuarios.titulada.cancel') }}" method="POST">
                        @csrf
                        <input type="hidden" name="file_name" value="{{ $fileName }}">
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-x-circle" aria-hidden="true" style="margin-right:6px;"></i>
                            Cancelar
                        </button>
                    </form>

                    <form action="{{ route('usuarios.titulada.process') }}" method="POST" id="tituladaProcessForm">
                        @csrf
                        <input type="hidden" name="file_name" value="{{ $fileName }}">
                        @if($skippedNoCc > 0)
                            <button type="button" class="btn btn-primary" id="tituladaOpenConfirm">
                                Cargar
                                <i class="bi bi-check2" aria-hidden="true" style="margin-left:6px;"></i>
                            </button>
                        @else
                            <button type="submit" class="btn btn-primary">
                                Cargar
                                <i class="bi bi-check2" aria-hidden="true" style="margin-left:6px;"></i>
                            </button>
                        @endif
                    </form>
                </div>

                <div class="table-container" style="max-height:500px; overflow:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th># Fila</th>
                                <th>CC</th>
                                <th>Nombre</th>
                                <th>Area</th>
                                <th>Estudios</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $r)
                                <tr @if($r['sin_cc']) style="background:#fff5f5;" @endif>
                                    <td>{{ $r['row'] }}</td>
                                    <td>
                                        @if($r['cc'])
                                            <strong>{{ $r['cc'] }}</strong>
                                        @else
                                            <span style="color:#b91c1c; font-weight:600;">(sin CC)</span>
                                        @endif
                                    </td>
                                    <td>{{ $r['nombre'] }}</td>
                                    <td>{{ $r['area'] }}</td>
                                    <td>{{ $r['estudios'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:14px; opacity:.8;">No se encontraron filas con datos en el Excel.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <div id="tituladaConfirmOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); backdrop-filter: blur(4px); z-index:1400; align-items:center; justify-content:center;" aria-hidden="true">
        <div style="background:#ffffff; border-radius:14px; max-width:520px; width:92%; padding:20px 22px; box-shadow:0 20px 60px rgba(0,0,0,.25); border-top:4px solid #e53e3e;">
            <div style="display:flex; gap:12px; align-items:flex-start;">
                <div style="width:44px; height:44px; border-radius:10px; background:#fff5f5; display:flex; align-items:center; justify-content:center; color:#e53e3e; font-size:22px;">
                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                </div>
                <div style="flex:1;">
                    <h3 style="margin:0 0 6px; font-size:18px; font-weight:800; color:#0b7c25;">Confirmar carga</h3>
                    <p style="margin:0; color:#333;">Hay filas sin cedula. Deseas continuar y subir el archivo de todas formas?</p>
                    <p style="margin:8px 0 0; color:#7a1a1a; font-weight:700;">Esas filas se omitiran.</p>
                </div>
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:16px;">
                <button type="button" id="tituladaConfirmNo" class="btn btn-secondary">Cancelar</button>
                <button type="button" id="tituladaConfirmYes" class="btn btn-primary">Continuar</button>
            </div>
        </div>
    </div>

    <script>
        const skipped = {{ (int) $skippedNoCc }};
        const form = document.getElementById('tituladaProcessForm');
        const confirmOverlay = document.getElementById('tituladaConfirmOverlay');
        const confirmNo = document.getElementById('tituladaConfirmNo');
        const confirmYes = document.getElementById('tituladaConfirmYes');
        const openConfirm = document.getElementById('tituladaOpenConfirm');

        if (openConfirm && skipped > 0) {
            openConfirm.addEventListener('click', function() {
                if (confirmOverlay) {
                    confirmOverlay.style.display = 'flex';
                    confirmOverlay.setAttribute('aria-hidden', 'false');
                }
            });
        }

        if (confirmNo && confirmOverlay) {
            confirmNo.addEventListener('click', function() {
                confirmOverlay.style.display = 'none';
                confirmOverlay.setAttribute('aria-hidden', 'true');
            });
        }

        if (confirmYes && confirmOverlay && form) {
            confirmYes.addEventListener('click', function() {
                confirmOverlay.style.display = 'none';
                confirmOverlay.setAttribute('aria-hidden', 'true');
                form.submit();
            });
        }
    </script>
@endsection

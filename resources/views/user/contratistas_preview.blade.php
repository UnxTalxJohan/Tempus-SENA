@extends('layouts.app')

@section('title', 'Previsualización consolidado contratistas')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <main>
                <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px;">
                    <div>
                        <h1 style="font-size:24px; font-weight:700; margin:0;">Previsualización consolidado contratistas</h1>
                        <p style="margin:6px 0 0; opacity:.8;">Revisa un resumen de los registros que se crearán o actualizarán antes de confirmar la carga.</p>
                    </div>
                    <a href="{{ route('usuarios.contratistas.form') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left" aria-hidden="true" style="margin-right:6px;"></i>
                        Volver a subir archivo
                    </a>
                </div>

                <div class="alert alert-info" style="margin-bottom:16px;">
                    <p style="margin:0;">
                        <strong>Archivo:</strong> {{ $fileName }}<br>
                        <strong>Filas con datos:</strong> {{ $totalRows }}<br>
                        <strong>Filas con CC válida:</strong> {{ $validRows }}<br>
                        <strong>Filas sin CC (se omitirán al procesar):</strong> {{ $skippedNoCc }}
                    </p>
                </div>

                <form action="{{ route('usuarios.contratistas.process') }}" method="POST" style="margin-bottom:16px;">
                    @csrf
                    <input type="hidden" name="file_name" value="{{ $fileName }}">
                    <div class="buttons" style="display:flex; gap:10px; flex-wrap:wrap;">
                        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle" aria-hidden="true" style="margin-right:6px;"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Confirmar e insertar contratistas
                            <i class="bi bi-check2" aria-hidden="true" style="margin-left:6px;"></i>
                        </button>
                    </div>
                </form>

                <div class="table-container" style="max-height:500px; overflow:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th># Fila</th>
                                <th>CC</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Contrato</th>
                                <th>Nivel formación</th>
                                <th>Pregrado</th>
                                <th>Postgrado</th>
                                <th>Coordinación</th>
                                <th>Modalidad</th>
                                <th>Especialidad / Área</th>
                                <th>Inicio contrato</th>
                                <th>Fin contrato</th>
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
                                    <td>{{ $r['correo'] }}</td>
                                    <td>{{ $r['contrato'] }}</td>
                                    <td>{{ $r['nivel'] }}</td>
                                    <td>{{ $r['pregrado'] }}</td>
                                    <td>{{ $r['postgrado'] }}</td>
                                    <td>{{ $r['coord'] }}</td>
                                    <td>{{ $r['modalidad'] }}</td>
                                    <td>{{ $r['especial'] }}</td>
                                    <td>{{ $r['fch_inicio'] }}</td>
                                    <td>{{ $r['fch_fin'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" style="text-align:center; padding:14px; opacity:.8;">No se encontraron filas con datos en el Excel.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
@endsection

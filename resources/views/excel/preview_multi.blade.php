@extends('layouts.app')

@section('title', 'Previsualización múltiple - SENA')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <main>
                <div class="program-info">
                    <h2><i class="bi bi-journal-text" aria-hidden="true"></i> Previsualización de archivos</h2>
                    <div class="info-grid">
                        <div class="info-item"><strong>Archivos</strong><span>{{ count($previews) }}</span></div>
                    </div>
                </div>

                @php($validos = collect($previews)->where('ok', true)->values())
                @php($invalidos = collect($previews)->where('ok', false)->values())

                @if($invalidos->count() > 0)
                <div class="alert alert-error">
                    <span>Algunos archivos no se pueden cargar. Revisa el log en el ícono de notificaciones del encabezado.</span>
                </div>
                @endif

                <div class="preview-section">
                    <h3>Resumen por archivo</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Programa</th>
                                    <th>Competencias</th>
                                    <th>Resultados</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previews as $p)
                                    <tr>
                                        <td><strong>{{ $p['originalName'] ?? $p['fileName'] }}</strong></td>
                                        <td>
                                            @if($p['ok'])
                                                <div><strong>Código:</strong> {{ $p['codigo'] }}</div>
                                                <div><strong>Nombre:</strong> {{ $p['nombre'] }}</div>
                                                <div><strong>Nivel/Versión:</strong> {{ $p['nivel'] }} / {{ $p['version'] }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($p['ok'])
                                                {{ count($p['competencias']) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($p['ok'])
                                                {{ collect($p['competencias'])->reduce(function($carry,$c){return $carry + count($c['resultados']);},0) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($p['ok'])
                                                <span class="pill pill-success">Válido</span>
                                            @else
                                                <span class="pill pill-danger" title="{{ $p['error'] ?? 'Error' }}">Inválido</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($p['ok'])
                                                <form action="{{ route('excel.preview.file') }}" method="POST" style="display:inline-block; margin-right:6px;">
                                                    @csrf
                                                    <input type="hidden" name="file_name" value="{{ $p['fileName'] }}">
                                                    <button type="submit" class="btn btn-secondary"><i class="bi bi-eye" aria-hidden="true" style="margin-right:6px;"></i>Previsualizar</button>
                                                </form>
                                                <form action="{{ route('excel.process') }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    <input type="hidden" name="file_name" value="{{ $p['fileName'] }}">
                                                    <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle" aria-hidden="true" style="margin-right:6px;"></i>Cargar</button>
                                                </form>
                                            @else
                                                <span style="opacity:.7;">Sin acciones</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($validos->count() > 0)
                <form action="{{ route('excel.process.multi') }}" method="POST">
                    @csrf
                    @foreach($validos as $v)
                        <input type="hidden" name="file_names[]" value="{{ $v['fileName'] }}">
                    @endforeach
                    <div class="buttons">
                        <a href="{{ route('excel.upload') }}" class="btn btn-secondary"><i class="bi bi-arrow-left" aria-hidden="true" style="margin-right:8px;"></i>Volver</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-upload" aria-hidden="true" style="margin-right:8px;"></i>Cargar todos los válidos</button>
                    </div>
                </form>
                @else
                <div class="buttons">
                    <a href="{{ route('excel.upload') }}" class="btn btn-secondary"><i class="bi bi-arrow-left" aria-hidden="true" style="margin-right:8px;"></i>Volver</a>
                </div>
                @endif
            </main>
        </div>
    </div>
@endsection

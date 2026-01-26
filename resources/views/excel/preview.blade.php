@extends('layouts.app')

@section('title', 'Previsualización - SENA')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <main>
                <div class="program-info">
                    <h2><i class="bi bi-journal-text" aria-hidden="true"></i> {{ $nombre }}</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>CÓDIGO</strong>
                            <span>{{ $codigo }}</span>
                        </div>
                        <div class="info-item">
                            <strong>VERSIÓN</strong>
                            <span>{{ $version }}</span>
                        </div>
                        <div class="info-item">
                            <strong>NIVEL</strong>
                            <span>{{ $nivel }}</span>
                        </div>
                    </div>
                </div>

                <div class="stats">
                    <div class="stat-card">
                        <h3>Total de Competencias</h3>
                        <div class="number">{{ count($competencias) }}</div>
                    </div>
                    <div class="stat-card">
                        <h3>Total de Resultados</h3>
                        <div class="number">
                            @php
                                $totalResultados = 0;
                                foreach ($competencias as $comp) {
                                    $totalResultados += count($comp['resultados']);
                                }
                            @endphp
                            {{ $totalResultados }}
                        </div>
                    </div>
                </div>

                <div class="warning">
                    <strong><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> Importante:</strong>
                    Al confirmar, se cargarán {{ count($competencias) }} competencias con {{ $totalResultados }} resultados de aprendizaje. Esta acción es irreversible.
                </div>

                <div class="preview-section">
                    <h3>Vista Previa de Competencias y Resultados</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 12%">Código</th>
                                    <th style="width: 35%">Nombre</th>
                                    <th style="width: 10%">Duración</th>
                                    <th style="width: 8%">Min</th>
                                    <th style="width: 8%">Max</th>
                                    <th style="width: 7%">Trim</th>
                                    <th style="width: 8%">H/Sem</th>
                                    <th style="width: 12%">Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($competencias as $comp)
                                    <tr class="competencia-row">
                                        <td><strong>{{ $comp['codigo'] }}</strong></td>
                                        <td><strong>{{ $comp['nombre'] }}</strong></td>
                                        <td><strong>{{ $comp['duracion'] }}h</strong></td>
                                        <td colspan="4"></td>
                                        <td><strong>COMPETENCIA</strong></td>
                                    </tr>
                                    @foreach($comp['resultados'] as $resultado)
                                        <tr class="resultado-row">
                                            <td><i class="bi bi-arrow-return-right" aria-hidden="true"></i></td>
                                            <td>{{ $resultado['nombre'] }}</td>
                                            <td>{{ $resultado['hora_trim'] !== null ? $resultado['hora_trim'] . 'h' : '' }}</td>
                                            <td>{{ $resultado['hora_min'] }}</td>
                                            <td>{{ $resultado['hora_max'] }}</td>
                                            <td>{{ $resultado['trimestre'] }}</td>
                                            <td>{{ $resultado['hora_sema'] }}</td>
                                            <td>Resultado</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <form action="{{ route('excel.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="file_name" value="{{ $fileName }}">
                    
                    <div class="buttons">
                        <a href="{{ route('excel.preview.multi_view') }}" class="btn btn-secondary"><i class="bi bi-arrow-left" aria-hidden="true" style="margin-right:8px;"></i>Volver a la lista</a>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle" aria-hidden="true" style="margin-right:8px;"></i>Confirmar y Cargar al Sistema</button>
                    </div>
                </form>
            </main>
        </div>
    </div>
@endsection

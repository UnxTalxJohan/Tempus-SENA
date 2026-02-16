@extends('layouts.app')

@section('title', 'Panel de fichas')

@section('content')
<div class="content-wrapper">
    <div class="container">
        <main>
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:18px; flex-wrap:wrap;">
                <div>
                    <h1 style="font-size:24px; font-weight:700; margin:0;">Fichas de formación</h1>
                    <p style="margin:4px 0 0; opacity:.8;">Listado de instructores registrados a cada ficha con sus fechas lectivas.</p>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width:50px; text-align:center;">#</th>
                            <th>Instructor</th>
                            <th style="width:100px; text-align:center;">Ficha</th>
                            <th style="width:180px;">Fechas lectivas</th>
                            <th style="width:130px; text-align:center;">Horario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $index => $registro)
                            <tr>
                                <td style="text-align:center;">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $registro->usuario_nombre }}</strong>
                                    <div style="font-size:12px; color:#4b5563;">CC: {{ $registro->cc }}</div>
                                </td>
                                <td style="text-align:center; font-weight:600;">{{ $registro->id_fich }}</td>
                                <td style="font-size:12px; color:#4b5563;">
                                    @if($registro->fecha_inic_lec || $registro->fecha_fin_lec)
                                        <div><strong>Inicio:</strong> {{ $registro->fecha_inic_lec }}</div>
                                        <div><strong>Fin:</strong> {{ $registro->fecha_fin_lec }}</div>
                                    @else
                                        <span style="opacity:.7;">No registradas</span>
                                    @endif
                                </td>
                                <td style="text-align:center; font-size:12px; color:#6b7280;">
                                    <span style="opacity:.7;">Horario pendiente</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:14px; opacity:.8;">Aún no hay usuarios registrados a fichas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
@endsection

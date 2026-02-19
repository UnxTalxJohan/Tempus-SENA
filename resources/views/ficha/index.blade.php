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

                <div style="display:flex; gap:8px; align-items:center;">
                    <a href="{{ route('ficha.create') }}" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:999px; background:#00A859; color:#fff; text-decoration:none; font-weight:600; font-size:14px;">
                        <i class="bi bi-plus-circle"></i>
                        <span>Crear nueva ficha</span>
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div style="margin-bottom:12px; padding:10px 12px; border-radius:8px; background:#ecfdf3; color:#14532d; font-size:14px;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width:50px; text-align:center;">#</th>
                            <th style="width:110px; text-align:center;">Ficha</th>
                            <th>Programa</th>
                            <th style="width:200px;">Fechas lectivas</th>
                            <th style="width:120px; text-align:center;">Jornada</th>
                            <th style="width:130px; text-align:center;">Instructores</th>
                            <th style="width:150px; text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $index => $registro)
                            <tr>
                                <td style="text-align:center;">{{ $index + 1 }}</td>
                                <td style="text-align:center; font-weight:600;">
                                    {{ $registro->id_fich }}
                                    @if($registro->trimestre)
                                        <div style="font-size:11px; color:#4b5563;">T{{ $registro->trimestre }}</div>
                                    @endif
                                    <div style="font-size:11px; margin-top:2px;">
                                        @if($registro->abierta == 1)
                                            <span style="display:inline-block; padding:2px 8px; border-radius:999px; background:#ecfdf3; color:#166534; font-weight:600; font-size:11px;">Abierta</span>
                                        @elseif($registro->abierta == 2)
                                            <span style="display:inline-block; padding:2px 8px; border-radius:999px; background:#fef2f2; color:#b91c1c; font-weight:600; font-size:11px;">Cerrada</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $registro->programa_nombre ?? 'Sin programa' }}</strong>
                                </td>
                                <td style="font-size:12px; color:#4b5563;">
                                    @if($registro->fecha_inic_lec || $registro->fecha_fin_lec)
                                        <div><strong>Inicio:</strong> {{ $registro->fecha_inic_lec }}</div>
                                        <div><strong>Fin:</strong> {{ $registro->fecha_fin_lec }}</div>
                                    @else
                                        <span style="opacity:.7;">No registradas</span>
                                    @endif
                                </td>
                                <td style="text-align:center; font-size:12px; color:#4b5563;">
                                    {{ $registro->jornada ?: 'Sin jornada' }}
                                </td>
                                <td style="text-align:center; font-size:12px; color:#4b5563;">
                                    @if($registro->total_instructores > 0)
                                        {{ $registro->total_instructores }}
                                        {{ $registro->total_instructores == 1 ? 'instructor' : 'instructores' }}
                                    @else
                                        <span style="opacity:.7;">Sin instructores</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <a href="{{ route('ficha.horario.edit', $registro->id_fich) }}" title="Configurar horario" style="display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:6px 10px; font-size:12px; background:#ecfdf3; color:#166534; text-decoration:none; margin-right:4px;">
                                        <i class="bi bi-clock-history" style="margin-right:4px;"></i>
                                        Horario
                                    </a>

                                    <form method="POST" action="{{ route('ficha.destroy', $registro->id_fich) }}" onsubmit="return confirm('¿Eliminar esta ficha y sus datos asociados?');" style="display:inline-block; margin-left:4px;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Eliminar ficha" style="border:none; background:#fef2f2; color:#b91c1c; padding:6px 10px; border-radius:999px; font-size:12px; cursor:pointer;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center; padding:14px; opacity:.8;">Aún no hay fichas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
@endsection

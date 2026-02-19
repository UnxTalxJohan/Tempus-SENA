@extends('layouts.app')

@section('title', 'Horario de ficha')

@section('content')
<div class="content-wrapper">
    <div class="container">
        <main>
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:18px; flex-wrap:wrap;">
                <div>
                    <h1 style="font-size:24px; font-weight:700; margin:0;">Horario de ficha {{ $ficha->id_fich }}</h1>
                    <p style="margin:4px 0 0; opacity:.8;">Configura las franjas horarias por día según la jornada de la ficha.</p>
                    <div style="margin-top:6px; font-size:13px; color:#4b5563;">
                        <strong>Programa:</strong> {{ $ficha->programa_nombre ?? 'Sin programa' }}
                        @if($ficha->nivel)
                            <span style="margin-left:8px;">· <strong>Nivel:</strong> {{ $ficha->nivel }}</span>
                        @endif
                        @if($ficha->jornada)
                            <span style="margin-left:8px;">· <strong>Jornada:</strong> {{ $ficha->jornada }}</span>
                        @endif
                    </div>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <a href="{{ route('ficha.index') }}" style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:999px; background:#f3f4f6; color:#111827; text-decoration:none; font-weight:500; font-size:14px;">
                        <i class="bi bi-arrow-left"></i>
                        <span>Volver al listado</span>
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div style="margin-bottom:12px; padding:10px 12px; border-radius:8px; background:#ecfdf3; color:#14532d; font-size:14px;">
                    {{ session('success') }}
                </div>
            @endif

            @php
                $jornada = strtoupper($ficha->jornada ?? '');
                // Sugerencias básicas por jornada
                $sugerencias = [];
                foreach ($dias as $dia) {
                    $nombreDia = strtoupper($dia->dia);
                    $sugerencias[$dia->id_horario] = [null, null];
                    if ($jornada === 'DIURNA') {
                        if (in_array($nombreDia, ['LUNES','MARTES','MIÉRCOLES','MIERCOLES','JUEVES','VIERNES'])) {
                            $sugerencias[$dia->id_horario] = ['06:00', '18:00'];
                        }
                    } elseif ($jornada === 'MIXTA') {
                        if (in_array($nombreDia, ['LUNES','MARTES','MIÉRCOLES','MIERCOLES','JUEVES','VIERNES'])) {
                            $sugerencias[$dia->id_horario] = ['18:00', '22:00'];
                        } elseif ($nombreDia === 'SÁBADO' || $nombreDia === 'SABADO') {
                            $sugerencias[$dia->id_horario] = ['06:00', '18:00'];
                        }
                    } elseif ($jornada === 'FINES DE SEMANA') {
                        if ($nombreDia === 'VIERNES') {
                            $sugerencias[$dia->id_horario] = ['18:00', '22:00'];
                        } elseif (in_array($nombreDia, ['SÁBADO','SABADO','DOMINGO'])) {
                            $sugerencias[$dia->id_horario] = ['06:00', '18:00'];
                        }
                    }
                }
            @endphp

            <form method="POST" action="{{ route('ficha.horario.update', $ficha->id_fich) }}">
                @csrf

                <div class="table-container" style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; max-width:880px;">
                        <thead>
                            <tr style="background:#f3f4f6;">
                                <th style="border:1px solid #e5e7eb; padding:8px; font-size:12px; text-transform:uppercase; font-weight:700; color:#111827; background:#e5e7eb;">Día</th>
                                <th style="border:1px solid #e5e7eb; padding:8px; font-size:12px; text-transform:uppercase; font-weight:700; color:#111827; background:#e5e7eb;">Hora inicio</th>
                                <th style="border:1px solid #e5e7eb; padding:8px; font-size:12px; text-transform:uppercase; font-weight:700; color:#111827; background:#e5e7eb;">Hora fin</th>
                                <th style="border:1px solid #e5e7eb; padding:8px; font-size:12px; text-transform:uppercase; font-weight:700; color:#111827; background:#e5e7eb;">Sugerido</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dias as $dia)
                                @php
                                    $registro = $horas->get($dia->id_horario);
                                    $sug = $sugerencias[$dia->id_horario] ?? [null, null];
                                @endphp
                                <tr>
                                    <td style="border:1px solid #e5e7eb; padding:6px 8px; font-size:13px; font-weight:600;">{{ $dia->dia }}</td>
                                    <td style="border:1px solid #e5e7eb; padding:4px 6px;">
                                        <input type="time" name="hora_inicio[{{ $dia->id_horario }}]"
                                               value="{{ $registro->hora_inicio ?? '' }}"
                                               @if(!$sug[0]) placeholder="--:--" @endif
                                               style="width:100%; border:none; padding:4px 6px; font-size:13px;">
                                        <input type="hidden" name="dia[{{ $dia->id_horario }}]" value="1">
                                    </td>
                                    <td style="border:1px solid #e5e7eb; padding:4px 6px;">
                                        <input type="time" name="hora_fin[{{ $dia->id_horario }}]"
                                               value="{{ $registro->hora_fin ?? '' }}"
                                               @if(!$sug[1]) placeholder="--:--" @endif
                                               style="width:100%; border:none; padding:4px 6px; font-size:13px;">
                                    </td>
                                    <td style="border:1px solid #e5e7eb; padding:4px 6px; font-size:12px; color:#4b5563;">
                                        @if($sug[0] && $sug[1])
                                            {{ $sug[0] }} – {{ $sug[1] }}
                                        @else
                                            <span style="opacity:.6;">Sin sugerencia</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:16px; display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
                    <div style="font-size:12px; color:#4b5563; max-width:520px;">
                        <strong>Reglas de jornada (resumen):</strong>
                        <ul style="margin:6px 0 0 18px;">
                            <li><strong>Diurna:</strong> 06:00 a 18:00 de lunes a viernes.</li>
                            <li><strong>Mixta:</strong> 18:00 a 22:00 de lunes a viernes y 06:00 a 18:00 el sábado.</li>
                            <li><strong>Fines de semana:</strong> viernes 18:00 a 22:00 y 06:00 a 18:00 sábado/domingo.</li>
                            <li>Puedes ajustar manualmente las horas por cada día según necesidad.</li>
                        </ul>
                    </div>

                    <button type="submit" style="padding:10px 18px; border-radius:999px; border:none; background:#00A859; color:#fff; font-weight:700; font-size:14px; display:inline-flex; align-items:center; gap:8px; cursor:pointer; box-shadow:0 8px 20px rgba(0,168,89,0.25);">
                        <i class="bi bi-save"></i>
                        <span>Guardar horario</span>
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>
@endsection

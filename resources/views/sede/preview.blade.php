@extends('layouts.app')

@section('title', 'Previsualización sedes y ambientes')

@section('content')
<div class="content-wrapper">
    <div class="container">
        <main>
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:18px; flex-wrap:wrap;">
                <div>
                    <h1 style="font-size:22px; font-weight:700; margin:0;">Previsualización de sedes y ambientes</h1>
                    <p style="margin:4px 0 0; opacity:.8;">Verifica que las sedes y sus ambientes se vean correctos antes de guardar en el sistema.</p>
                </div>
                <a href="{{ route('sede.upload') }}" class="btn btn-secondary" style="border-radius:999px;">Volver</a>
            </div>

            <div class="card" style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px; margin-bottom:16px;">
                <p style="margin:0; font-size:13px; color:#4b5563;">Archivo: <strong>{{ $fileName }}</strong></p>
                <p style="margin:4px 0 0; font-size:13px; color:#4b5563;">Cada columna corresponde a una sede. Las celdas vacías se mostrarán como <em>"Sin ambiente por el momento"</em>.</p>
            </div>

            <div class="table-container" style="margin-bottom:18px;">
                <table>
                    <thead>
                        <tr>
                            <th style="width:60px; text-align:center;">#</th>
                            <th>Sede</th>
                            <th>Ambientes detectados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($i = 1)
                        @foreach($sedes as $info)
                            <tr>
                                <td style="text-align:center;">{{ $i++ }}</td>
                                <td><strong>{{ $info['nombre'] }}</strong></td>
                                <td>
                                    @if(count($info['ambientes']) === 1 && $info['ambientes'][0] === null)
                                        <span style="opacity:.8;">Sin ambiente por el momento.</span>
                                    @else
                                        <ul style="margin:0; padding-left:18px; font-size:13px;">
                                            @foreach($info['ambientes'] as $amb)
                                                <li>{{ $amb }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('sede.process') }}">
                @csrf
                <input type="hidden" name="file_name" value="{{ $fileName }}">
                <button type="submit" class="btn btn-primary">Guardar sedes y ambientes</button>
            </form>
        </main>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Inicio - Sistema CIDE')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <main>
                @if(session('success'))
                    <div class="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="welcome">
                    <h2>Sistema de Gestión CIDE</h2>
                    <p>Centro de Gestión de Programas de Formación</p>
                    @auth
                        <p style="margin-top:8px; opacity:0.85;">Hola, <strong>{{ auth()->user()->name }}</strong>. Bienvenido al dashboard.</p>
                    @endauth
                </div>

                <div class="cards">
                    <div class="card">
                        <div class="card-icon">
                            <i class="bi bi-clipboard" style="font-size:48px;" aria-hidden="true"></i>
                        </div>
                        <h3>Fichas</h3>
                        <p>Administrar fichas de formación</p>
                        <a href="#" class="btn">Ver Fichas</a>
                    </div>

                    <div class="card">
                        <div class="card-icon">
                            <i class="bi bi-people" style="font-size:48px;" aria-hidden="true"></i>
                        </div>
                        <h3>Usuarios</h3>
                        <p>Administrar usuarios e instructores</p>
                        <a href="#" class="btn">Ver Usuarios</a>
                    </div>
                </div>

                <h2 class="section-title">Programas Registrados</h2>


                @if(count($programas) > 0)
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre del Programa</th>
                                    <th>Versión</th>
                                    <th>Nivel</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programas as $programa)
                                    <tr>
                                        <td><strong>{{ $programa->id_prog }}</strong></td>
                                        <td>{{ $programa->nombre }}</td>
                                        <td>{{ $programa->version }}</td>
                                        <td>{{ $programa->nivel }}</td>
                                        <td>
                                            <a href="{{ route('matriz.show', $programa->hash ?? $programa->id_prog) }}" class="btn btn-small">
                                                Ver Matriz
                                            </a>
                                            <form method="POST" action="{{ route('matriz.destroy', $programa->id_prog) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar esta matriz y sus datos asociados?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-small" style="background:#e53e3e; color:#fff; margin-left:6px;">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <h3>No hay programas registrados aún</h3>
                        <p>Comienza cargando tu primer programa desde un archivo Excel</p>
                        <a href="{{ route('excel.upload') }}" class="btn">Cargar Primer Programa</a>
                    </div>
                @endif
            </main>
        </div>
    </div>
@endsection

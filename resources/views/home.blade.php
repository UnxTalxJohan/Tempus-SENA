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
                            <i class="bi bi-people" style="font-size:48px;" aria-hidden="true"></i>
                        </div>
                        <h3>Gestión de usuarios</h3>
                        <p>Administrar usuarios, roles y contrato</p>
                        <div class="user-stats">
                            <div class="user-stat">
                                <span class="user-stat-label">Contrato</span>
                                <span class="user-stat-value">{{ $countContratistas ?? 0 }}</span>
                            </div>
                            <div class="user-stat">
                                <span class="user-stat-label">Planta</span>
                                <span class="user-stat-value" style="color:#b45309;">{{ $countTitulada ?? 0 }}</span>
                            </div>
                            <div class="user-stat total">
                                <span class="user-stat-label">Total</span>
                                <span class="user-stat-value">{{ $countUsuarios ?? 0 }}</span>
                            </div>
                        </div>
                        <a href="{{ route('usuarios.index') }}" class="btn">Gestión de usuarios</a>
                    </div>

                    <div class="card">
                        <div class="card-icon">
                            <i class="bi bi-collection" style="font-size:48px;" aria-hidden="true"></i>
                        </div>
                        <h3>Fichas de formación</h3>
                        <p>Consulta las fichas, sus programas y los usuarios/resultados vinculados.</p>
                        <a href="{{ route('ficha.index') }}" class="btn">Ver fichas</a>
                    </div>

                    <div class="card">
                        <div class="card-icon">
                            <i class="bi bi-geo-alt" style="font-size:48px;" aria-hidden="true"></i>
                        </div>
                        <h3>Sedes y ambientes</h3>
                        <p>Consulta y carga las sedes del CIDE y sus ambientes de formación.</p>
                        <a href="{{ route('sede.index') }}" class="btn">Ver sedes</a>
                    </div>
                </div>

                <h2 class="section-title">Programas Registrados</h2>


                @if(count($programas) > 0)
                    <div class="programs-list-dashboard">
                        <div class="programs-header">
                            <div>Código</div>
                            <div>Programa</div>
                            <div>Versión</div>
                            <div>Nivel</div>
                            <div>Subido</div>
                            <div>Última actualización</div>
                            <div>Acciones</div>
                        </div>
                        @foreach($programas as $programa)
                            <div class="program-row">
                                <div class="program-code">{{ $programa->id_prog }}</div>
                                <div class="program-name">{{ $programa->nombre }}</div>
                                <div class="program-version">{{ $programa->version }}</div>
                                <div class="program-level">{{ $programa->nivel }}</div>
                                <div class="program-date">
                                    @if($programa->fch_sub_prg)
                                        {{ \Carbon\Carbon::parse($programa->fch_sub_prg)->format('Y-m-d H:i') }}
                                    @else
                                        <span style="opacity:.6;">Sin registro</span>
                                    @endif
                                </div>
                                <div class="program-date">
                                    @if($programa->fhc_utl_act_prg)
                                        {{ \Carbon\Carbon::parse($programa->fhc_utl_act_prg)->format('Y-m-d H:i') }}
                                    @else
                                        <span style="opacity:.6;">Sin registro</span>
                                    @endif
                                </div>
                                <div class="program-actions">
                                    <a href="{{ route('matriz.show', $programa->hash ?? $programa->id_prog) }}" class="btn btn-small">
                                        Ver Matriz
                                    </a>
                                    <form method="POST" action="{{ route('matriz.destroy', $programa->id_prog) }}" onsubmit="return confirm('¿Eliminar esta matriz y sus datos asociados?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-small btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
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

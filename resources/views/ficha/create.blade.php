@extends('layouts.app')

@section('title', 'Crear nueva ficha')

@section('content')
<div class="content-wrapper">
    <div class="container">
        <main class="page">
            <header class="page-header">
                <div class="page-title-block">
                    <h1>Crear nueva ficha</h1>
                </div>
                <a href="{{ route('ficha.index') }}" class="btn-secondary-outline">
                    <span class="icon">⟵</span>
                    Volver al listado
                </a>
            </header>

            @if($errors->any())
                <div style="margin-bottom:14px; padding:10px 12px; border-radius:10px; background:#fef2f2; color:#7f1d1d; font-size:14px; border:1px solid #fecaca;">
                    <strong>Revisa los campos:</strong>
                    <ul style="margin:6px 0 0 18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('ficha.store') }}">
                @csrf

                <!-- Datos principales de la ficha - panel tipo Excel en filas apiladas -->
                <section class="excel-wrapper" aria-label="Datos de la ficha">
                    <div class="excel-panel">
                        <div class="excel-row">
                            <div class="excel-field small">
                                <span class="excel-label">Ficha</span>
                                <input type="text" name="id_fich" id="id_fich" value="{{ old('id_fich') }}" placeholder="Ej: 2105647" aria-label="Número de ficha" required>
                            </div>
                            <div class="excel-field large">
                                <span class="excel-label">Programa</span>
                                <select name="cod_prog_fk" id="cod_prog_fk" required aria-label="Programa de formación">
                                    <option value="">Seleccione programa...</option>
                                    @foreach($programas as $programa)
                                        <option value="{{ $programa->id_prog }}"
                                                data-nivel="{{ $programa->nivel }}"
                                                data-cant-trim="{{ $programa->cant_trim }}"
                                                @selected(old('cod_prog_fk') == $programa->id_prog)>
                                            [{{ $programa->id_prog }}] {{ $programa->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="excel-field small">
                                <span class="excel-label">Nivel</span>
                                <input type="text" id="nivel_programa" value="" readonly aria-label="Nivel de formación">
                                <div id="nivel_help" class="ficha-nivel-help"></div>
                            </div>
                            <div class="excel-field small">
                                <span class="excel-label">Trimestre</span>
                                <select name="trimestre" aria-label="Trimestre">
                                    <option value="">Seleccione</option>
                                    @for($i = 1; $i <= 7; $i++)
                                        <option value="{{ $i }}" @selected(old('trimestre') == (string)$i)>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="excel-field small">
                                <span class="excel-label">Jornada</span>
                                <select name="jornada" aria-label="Jornada">
                                    <option value="">Seleccione</option>
                                    <option value="DIURNA" @selected(old('jornada') === 'DIURNA')>Diurna (6:00 a 18:00)</option>
                                    <option value="MIXTA" @selected(old('jornada') === 'MIXTA')>Mixta (18:00 a 22:00)</option>
                                    <option value="FINES DE SEMANA" @selected(old('jornada') === 'FINES DE SEMANA')>Fines de semana</option>
                                </select>
                            </div>
                        </div>

                        <div class="excel-row">
                            <div class="excel-field medium">
                                <span class="excel-label">Fecha inicio lec.</span>
                                <input type="date" name="fecha_inic_lec" value="{{ old('fecha_inic_lec') }}" aria-label="Fecha de inicio lectiva">
                            </div>
                            <div class="excel-field medium">
                                <span class="excel-label">Fecha fin lec.</span>
                                <input type="date" name="fecha_fin_lec" value="{{ old('fecha_fin_lec') }}" aria-label="Fecha de fin lectiva">
                            </div>
                            <div class="excel-field large">
                                <span class="excel-label">Proyecto formativo</span>
                                <input type="text" name="proy_formativo_enruto" value="{{ old('proy_formativo_enruto') }}" placeholder="Nombre del proyecto" aria-label="Proyecto formativo">
                            </div>
                            <div class="excel-field small">
                                <span class="excel-label">Abierta</span>
                                <select name="abierta" required aria-label="Abierta">
                                    <option value="1" @selected(old('abierta', '1') == '1')>Abierta</option>
                                    <option value="2" @selected(old('abierta') == '2')>Cerrada</option>
                                </select>
                            </div>
                            <div class="excel-field small">
                                <span class="excel-label">CDF</span>
                                <select name="CDF" aria-label="Cadena de formación" title="Cadena de formación">
                                    <option value="" @selected(old('CDF') === null || old('CDF') === '')>No aplica</option>
                                    <option value="Cadena de formación 5 trimestres" @selected(old('CDF') === 'Cadena de formación 5 trimestres')>Cadena de formación 5 trimestres</option>
                                    <option value="Cadena de formación 6 trimestres" @selected(old('CDF') === 'Cadena de formación 6 trimestres')>Cadena de formación 6 trimestres</option>
                                </select>
                            </div>
                            <div class="excel-field small">
                                <span class="excel-label">Cerr. convenio</span>
                                <input type="text" name="cerr_convenio" value="{{ old('cerr_convenio') }}" placeholder="convenio" aria-label="Cierre de convenio">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Horario base por días -->
                <section class="section" aria-label="Horario base por días">
                    <div class="section-header">
                        <h2>Horario base por días</h2>
                        <p>Configura el horario base por día y asigna instructor y competencia.</p>
                    </div>

                    <div class="schedule-shell">
                        <div class="schedule-shell-header">
                            <div class="schedule-shell-title">Turnos disponibles</div>
                            <div class="schedule-shell-subtitle">Semana base de la ficha</div>
                        </div>

                        <div class="schedule-list">
                            <!-- Fila 1: cabecera días (horizontal) -->
                            <div class="schedule-grid-corner"></div>
                            <div class="schedule-day-header schedule-col">Lunes</div>
                            <div class="schedule-day-header schedule-col">Martes</div>
                            <div class="schedule-day-header schedule-col">Miércoles</div>
                            <div class="schedule-day-header schedule-col">Jueves</div>
                            <div class="schedule-day-header schedule-col">Viernes</div>
                            <div class="schedule-day-header schedule-col schedule-col-last">Sábado</div>

                            <!-- Fila 2: Hora inicio -->
                            <div class="schedule-row-label">Hora inicio</div>
                            <div class="schedule-cell schedule-col">
                                <input type="time" aria-label="Hora inicio lunes">
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="time" aria-label="Hora inicio martes">
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="time" aria-label="Hora inicio miércoles">
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="time" aria-label="Hora inicio jueves">
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="time" aria-label="Hora inicio viernes">
                            </div>
                            <div class="schedule-cell schedule-col schedule-col-last">
                                <input type="time" aria-label="Hora inicio sábado">
                            </div>

                            <!-- Fila 3: Hora fin -->
                            <div class="schedule-row-label">Hora fin</div>
                            <div class="schedule-cell schedule-col">
                                <input type="time" aria-label="Hora fin lunes">
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="time" aria-label="Hora fin martes">
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="time" aria-label="Hora fin miércoles">
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="time" aria-label="Hora fin jueves">
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="time" aria-label="Hora fin viernes">
                            </div>
                            <div class="schedule-cell schedule-col schedule-col-last">
                                <input type="time" aria-label="Hora fin sábado">
                            </div>

                            <!-- Fila 4: Instructor -->
                            <div class="schedule-row-label">Instructor</div>
                            <div class="schedule-cell schedule-col">
                                <input type="text" class="schedule-instructor-search" data-dia="Lunes" placeholder="Buscar instructor...">
                                <button type="button" class="schedule-instructor-clear" data-dia="Lunes" aria-label="Limpiar búsqueda">×</button>
                                <div class="schedule-instructor-suggestions" data-suggest-dia="LUNES"></div>
                                <select class="schedule-select select-instructor-dia" data-dia="Lunes" aria-label="Instructor lunes">
                                    <option value="">Seleccione instructor...</option>
                                </select>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="text" class="schedule-instructor-search" data-dia="Martes" placeholder="Buscar instructor...">
                                <button type="button" class="schedule-instructor-clear" data-dia="Martes" aria-label="Limpiar búsqueda">×</button>
                                <div class="schedule-instructor-suggestions" data-suggest-dia="MARTES"></div>
                                <select class="schedule-select select-instructor-dia" data-dia="Martes" aria-label="Instructor martes">
                                    <option value="">Seleccione instructor...</option>
                                </select>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="text" class="schedule-instructor-search" data-dia="Miércoles" placeholder="Buscar instructor...">
                                <button type="button" class="schedule-instructor-clear" data-dia="Miércoles" aria-label="Limpiar búsqueda">×</button>
                                <div class="schedule-instructor-suggestions" data-suggest-dia="MIERCOLES"></div>
                                <select class="schedule-select select-instructor-dia" data-dia="Miércoles" aria-label="Instructor miércoles">
                                    <option value="">Seleccione instructor...</option>
                                </select>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="text" class="schedule-instructor-search" data-dia="Jueves" placeholder="Buscar instructor...">
                                <button type="button" class="schedule-instructor-clear" data-dia="Jueves" aria-label="Limpiar búsqueda">×</button>
                                <div class="schedule-instructor-suggestions" data-suggest-dia="JUEVES"></div>
                                <select class="schedule-select select-instructor-dia" data-dia="Jueves" aria-label="Instructor jueves">
                                    <option value="">Seleccione instructor...</option>
                                </select>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <input type="text" class="schedule-instructor-search" data-dia="Viernes" placeholder="Buscar instructor...">
                                <button type="button" class="schedule-instructor-clear" data-dia="Viernes" aria-label="Limpiar búsqueda">×</button>
                                <div class="schedule-instructor-suggestions" data-suggest-dia="VIERNES"></div>
                                <select class="schedule-select select-instructor-dia" data-dia="Viernes" aria-label="Instructor viernes">
                                    <option value="">Seleccione instructor...</option>
                                </select>
                            </div>
                            <div class="schedule-cell schedule-col schedule-col-last">
                                <input type="text" class="schedule-instructor-search" data-dia="Sábado" placeholder="Buscar instructor...">
                                <button type="button" class="schedule-instructor-clear" data-dia="Sábado" aria-label="Limpiar búsqueda">×</button>
                                <div class="schedule-instructor-suggestions" data-suggest-dia="SABADO"></div>
                                <select class="schedule-select select-instructor-dia" data-dia="Sábado" aria-label="Instructor sábado">
                                    <option value="">Seleccione instructor...</option>
                                </select>
                            </div>

                            <!-- Fila 5: Info instructor seleccionad@ -->
                            <div class="schedule-row-label schedule-row-label-info">Info instructor</div>
                            <div class="schedule-cell schedule-col">
                                <div class="info-instructor-dia" data-info-dia="LUNES"></div>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <div class="info-instructor-dia" data-info-dia="MARTES"></div>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <div class="info-instructor-dia" data-info-dia="MIERCOLES"></div>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <div class="info-instructor-dia" data-info-dia="JUEVES"></div>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <div class="info-instructor-dia" data-info-dia="VIERNES"></div>
                            </div>
                            <div class="schedule-cell schedule-col schedule-col-last">
                                <div class="info-instructor-dia" data-info-dia="SABADO"></div>
                            </div>

                            <!-- Fila 6: Competencia -->
                            <div class="schedule-row-label">Competencia</div>
                            <div class="schedule-cell schedule-col">
                                <select class="schedule-select select-competencia" data-dia="Lunes" aria-label="Competencia lunes">
                                    <option value="">Seleccione competencia...</option>
                                </select>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <select class="schedule-select select-competencia" data-dia="Martes" aria-label="Competencia martes">
                                    <option value="">Seleccione competencia...</option>
                                </select>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <select class="schedule-select select-competencia" data-dia="Miércoles" aria-label="Competencia miércoles">
                                    <option value="">Seleccione competencia...</option>
                                </select>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <select class="schedule-select select-competencia" data-dia="Jueves" aria-label="Competencia jueves">
                                    <option value="">Seleccione competencia...</option>
                                </select>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <select class="schedule-select select-competencia" data-dia="Viernes" aria-label="Competencia viernes">
                                    <option value="">Seleccione competencia...</option>
                                </select>
                            </div>
                            <div class="schedule-cell schedule-col schedule-col-last">
                                <select class="schedule-select select-competencia" data-dia="Sábado" aria-label="Competencia sábado">
                                    <option value="">Seleccione competencia...</option>
                                </select>
                            </div>
                            <!-- Fila 7: Resultados de aprendizaje -->
                            <div class="schedule-row-label">Resultados</div>
                            <div class="schedule-cell schedule-col">
                                <div class="resultados-competencia-dia" data-res-dia="LUNES"></div>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <div class="resultados-competencia-dia" data-res-dia="MARTES"></div>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <div class="resultados-competencia-dia" data-res-dia="MIERCOLES"></div>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <div class="resultados-competencia-dia" data-res-dia="JUEVES"></div>
                            </div>
                            <div class="schedule-cell schedule-col">
                                <div class="resultados-competencia-dia" data-res-dia="VIERNES"></div>
                            </div>
                            <div class="schedule-cell schedule-col schedule-col-last">
                                <div class="resultados-competencia-dia" data-res-dia="SABADO"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="ficha-footer">
                    <div class="ficha-notas">
                        <strong>Aspectos a tener en cuenta:</strong>
                        <ul style="margin:6px 0 0 18px;">
                            <li>Horas a programar por trimestre: 440.</li>
                            <li>Cada trimestre tiene 11 semanas.</li>
                            <li>Programas nivel tecnólogo: formación regular 7 trimestres.</li>
                            <li>En cadena de formación (CDF) la ficha puede estar entre 5 o 6 trimestres.</li>
                            <li>La jornada define el rango horario permitido para los instructores.</li>
                        </ul>
                    </div>

                    <button type="submit" class="ficha-submit-btn">
                        <i class="bi bi-save"></i>
                        <span>Guardar ficha</span>
                    </button>
                </div>
            </form>
            </div>
        </main>
    </div>
    </div>
</div>

<style>
    body {
        background:linear-gradient(180deg, #d9f0f2 0%, #c2e0e3 40%, #a4ced3 100%);
    }

    .content-wrapper {
        display:flex;
        justify-content:center;
        padding:32px 20px;
    }

    .content-wrapper > .container {
        width:100%;
        display:flex;
        justify-content:center;
    }

    .page {
        width:100%;
        max-width:1240px;
        background:#f9fbfb;
        border-radius:28px;
        padding:32px 40px 32px;
        box-shadow:none;
        margin:0 auto;
    }

    .page-header {
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:18px;
        margin-bottom:28px;
    }

    .page-title-block h1 {
        font-size:36px;
        font-weight:700;
        margin-bottom:10px;
        color:#102a2c;
        display:inline-block;
        padding-bottom:8px;
        position:relative;
    }

    .page-title-block h1::after {
        content:"";
        position:absolute;
        left:-16%;
        bottom:0;
        width:132%;
        height:3px;
        background:linear-gradient(90deg,
                transparent 0%,
                rgba(0,177,64,0.1) 8%,
                rgba(0,177,64,0.9) 50%,
                rgba(0,177,64,0.1) 92%,
                transparent 100%);
        border-radius:999px;
    }

    .page-title-block p {
        display:none;
    }

    .btn-secondary-outline {
        border-radius:999px;
        border:1px solid rgba(134,209,214,0.9);
        background:#ecf9fb;
        color:#0f4044;
        padding:8px 18px;
        font-size:14px;
        display:inline-flex;
        align-items:center;
        gap:8px;
        cursor:pointer;
        align-self:flex-start;
        box-shadow:none;
        text-decoration:none;
        transition:transform 0.12s ease, background-color 0.15s ease;
    }

    .btn-secondary-outline:hover {
        background:#e2f4f7;
        transform:translateY(-1px);
        box-shadow:none;
    }

    .btn-secondary-outline span.icon {
        width:auto;
        height:auto;
        border-radius:0;
        border:none;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:20px;
        background:transparent;
    }

    .excel-wrapper {
        border-radius:24px;
        border:none;
        margin-bottom:28px;
        background:linear-gradient(135deg,#00b140,#32d76b);
        overflow-x:auto;
        box-shadow:none;
    }

    .excel-panel {
        padding:16px 18px 18px;
        background:transparent;
        border-radius:24px;
        min-width:0;
    }

    .excel-row {
        display:flex;
        flex-wrap:wrap;
        gap:12px;
        margin-bottom:10px;
    }

    .excel-row:last-child {
        margin-bottom:0;
    }

    .excel-field {
        display:flex;
        flex-direction:column;
        gap:4px;
    }

    .excel-field.small {
        flex:0 1 120px;
    }

    .excel-field.medium {
        flex:1 1 180px;
    }

    .excel-field.large {
        flex:2 1 260px;
    }

    .excel-label {
        font-size:11px;
        font-weight:600;
        text-transform:uppercase;
        letter-spacing:0.06em;
        color:#ffffff;
    }

    .excel-panel input[type="text"],
    .excel-panel input[type="date"],
    .excel-panel select {
        width:100%;
        border-radius:999px;
        border:1px solid #dde9df;
        padding:7px 12px;
        font-size:13px;
        color:#111827;
        outline:none;
        background-color:#ffffff;
        border-color:#b7e5c9;
    }

    .excel-panel input[type="text"]::placeholder {
        color:#9ca3af;
        font-style:italic;
    }

    .excel-panel select {
        background-image:linear-gradient(45deg, transparent 50%, #10b981 50%),
            linear-gradient(135deg, #10b981 50%, transparent 50%);
        background-position:calc(100% - 16px) calc(50% - 2px), calc(100% - 12px) calc(50% - 2px);
        background-size:6px 6px, 6px 6px;
        background-repeat:no-repeat;
        padding-right:32px;
        appearance:none;
        -webkit-appearance:none;
        -moz-appearance:none;
    }

    .section {
        border-radius:24px;
        border:none;
        padding:20px 22px 18px;
        background:transparent;
        margin:8px 0 28px;
    }

    .section-header {
        margin-bottom:12px;
    }

    .section-header h2 {
        font-size:20px;
        color:#123132;
        margin-bottom:2px;
    }

    .section-header p {
        font-size:13px;
        color:#6b7280;
    }

    /* Horario estilo "turnos" moderno */
    .schedule-shell {
        background:linear-gradient(145deg,#00b140,#32d76b);
        border-radius:22px;
        padding:18px 18px 16px;
        box-shadow:none;
        position:relative;
        width:100%;
        box-sizing:border-box;
    }

    .schedule-shell-header {
        display:flex;
        justify-content:space-between;
        align-items:baseline;
        margin-bottom:12px;
        color:#f5fafb;
    }

    .schedule-shell-title {
        font-size:17px;
        font-weight:600;
    }

    .schedule-shell-subtitle {
        font-size:12px;
        opacity:0.7;
    }

    /* Grid horario: días en horizontal, info en vertical */
    .schedule-list {
        display:grid;
        grid-template-columns:110px repeat(6, minmax(0, 1fr));
        column-gap:8px;
        row-gap:10px;
        align-items:center;
        max-width:100%;
    }

    .schedule-grid-corner {
        background:transparent;
    }

    .schedule-day-header {
        font-size:11px;
        font-weight:700;
        letter-spacing:0.14em;
        text-transform:uppercase;
        color:#f3fafb;
        border:1px solid rgba(202,227,230,0.7);
        border-radius:4px;
        padding:4px 6px;
        text-align:center;
    }

    .schedule-row-label {
        font-size:11px;
        font-weight:600;
        text-transform:uppercase;
        letter-spacing:0.12em;
        color:rgba(215,234,237,0.9);
    }

    .schedule-row-label-info {
        opacity:0.85;
    }

    .schedule-col {
        border-left:1px solid rgba(235,252,252,0.5);
        padding-left:6px;
    }

    .schedule-col-last {
        border-right:1px solid rgba(235,252,252,0.5);
        padding-right:6px;
    }

    .schedule-cell {
        display:block;
        position:relative;
    }

    .schedule-instructor-search {
        width:100%;
        border-radius:999px;
        border:1px solid rgba(209,250,229,0.85);
        padding:3px 8px;
        font-size:10px;
        margin-bottom:4px;
        outline:none;
        color:#064e3b;
        background:rgba(250,253,255,0.96);
    }

    .schedule-instructor-search::placeholder {
        color:rgba(15,118,110,0.7);
    }

    .schedule-instructor-search:focus {
        border-color:#bbf7d0;
        box-shadow:0 0 0 1px rgba(22,163,74,0.55);
    }

    .schedule-instructor-clear {
        position:absolute;
        top:6px;
        right:10px;
        width:14px;
        height:14px;
        border-radius:999px;
        border:none;
        background:transparent;
        color:#0f766e;
        font-size:11px;
        line-height:1;
        padding:0;
        cursor:pointer;
    }

    .schedule-instructor-clear:hover {
        background:rgba(240,253,250,0.9);
    }

    .schedule-instructor-suggestions {
        display:none;
        margin-top:2px;
        border-radius:8px;
        background:#fdfefe;
        border:1px solid rgba(209,250,229,0.9);
        max-height:120px;
        overflow-y:auto;
    }

    .schedule-instructor-suggestion {
        width:100%;
        text-align:left;
        padding:4px 8px;
        border:none;
        background:transparent;
        font-size:10px;
        color:#064e3b;
        cursor:pointer;
    }

    .schedule-instructor-suggestion:hover {
        background:#e0f2fe;
    }

    .schedule-instructor-suggestion-name {
        font-weight:600;
    }

    .schedule-instructor-suggestion-esp {
        display:block;
        font-size:9px;
        color:#047857;
        margin-top:1px;
    }

    .info-instructor-dia {
        margin-top:2px;
        min-height:18px;
    }

    .info-instructor-line-nombre {
        font-size:11px;
        font-weight:600;
        color:#f9fafb;
        line-height:1.2;
    }

    .info-instructor-line-detalle {
        margin-top:1px;
    }

    .info-instructor-chip {
        display:inline-block;
        padding:2px 8px;
        border-radius:999px;
        background:#ffffff;
        color:#047857;
        font-size:10px;
        font-weight:500;
        margin-right:4px;
    }

    .resultados-competencia-dia {
        margin-top:2px;
        max-height:90px;
        overflow-y:auto;
        padding:4px 6px;
        background:rgba(250,253,255,0.96);
        border-radius:6px;
        border:1px solid rgba(209,250,229,0.85);
    }

    .resultado-item {
        font-size:10px;
        color:#064e3b;
        line-height:1.35;
    }

    .resultado-item + .resultado-item {
        margin-top:2px;
    }

    .resultado-item-empty {
        font-size:10px;
        color:rgba(15,118,110,0.7);
        font-style:italic;
    }

    /* estilo de hora y selects dentro del grid */
    .schedule-cell input[type="time"],
    .schedule-cell select {
        background:#fdfefe;
        border-radius:999px;
        border:1px solid rgba(5,74,38,0.45);
        padding:4px 9px;
        font-size:10px;
        color:#064e3b;
        transition:border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease, transform 0.1s ease;
        width:100%;
        box-sizing:border-box;
    }

    .schedule-cell input[type="time"]:focus,
    .schedule-cell select:focus {
        outline:none;
        border-color:#00b140;
        box-shadow:0 0 0 2px rgba(0,177,64,0.6);
        background-color:#ffffff;
        transform:translateY(-1px);
    }

    .schedule-select {
        background-color:#fdfefe;
        color:#064e3b;
        border-color:#00b140;
        padding:4px 26px 4px 10px;
        font-size:11px;
        cursor:pointer;
        background-image:linear-gradient(45deg, transparent 50%, #00b140 50%),
            linear-gradient(135deg, #00b140 50%, transparent 50%);
        background-position:calc(100% - 14px) calc(50% - 1px), calc(100% - 10px) calc(50% - 1px);
        background-size:6px 6px, 6px 6px;
        background-repeat:no-repeat;
    }

    .schedule-select:hover {
        background-color:#f2fffa;
        border-color:#00b140;
    }

    .schedule-select:focus {
        outline:none;
        box-shadow:0 0 0 2px rgba(0,177,64,0.9);
    }

    .ficha-card {
        background:#ffffff;
        border-radius:20px;
        padding:20px 22px 22px;
        box-shadow:0 18px 45px rgba(5,122,85,0.32);
        border:1px solid rgba(16,185,129,0.5);
        backdrop-filter:blur(6px);
    }

    .ficha-header {
        display:flex;
        justify-content:space-between;
        align-items:flex-end;
        gap:16px;
        margin-bottom:18px;
        flex-wrap:wrap;
    }

    .ficha-table-wrapper {
        overflow-x:auto;
        border-radius:16px;
        box-shadow:0 0 0 1px rgba(148,163,184,.35);
        background:linear-gradient(180deg,#f9fafb 0,#f3f4f6 100%);
    }

    .ficha-table {
        width:100%;
        border-collapse:separate;
        border-spacing:0;
        min-width:980px;
    }

    .ficha-table thead tr {
        background:linear-gradient(90deg,#ecfdf5 0,#dcfce7 40%,#bbf7d0 100%);
    }

    .ficha-table thead th {
        border-bottom:1px solid #6ee7b7;
        padding:8px;
        font-size:11px;
        letter-spacing:.06em;
        text-transform:uppercase;
        font-weight:700;
        color:#064e3b;
        text-align:left;
    }

    .ficha-td {
        border:1px solid #e5e7eb;
        padding:4px 6px;
        background:#ffffff;
    }

    .ficha-input {
        width:100%;
        border:none;
        padding:4px 6px;
        font-size:13px;
        border-radius:6px;
        background:transparent;
        box-shadow:none;
        transition:box-shadow .12s ease, background-color .12s ease, transform .05s ease;
    }

    .ficha-input:focus-visible {
        outline:none;
        box-shadow:0 0 0 1px #22c55e,0 0 0 3px rgba(34,197,94,0.2);
        background:#f0fdf4;
        transform:translateY(-0.5px);
    }

    .ficha-input-readonly {
        background:#f9fafb;
        cursor:default;
    }

    .ficha-textarea {
        resize:vertical;
        min-height:42px;
    }

    .ficha-nivel-help {
        font-size:11px;
        color:#6b7280;
        margin-top:2px;
    }

    .ficha-footer {
        margin-top:18px;
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:18px;
        flex-wrap:wrap;
    }

    .ficha-notas {
        font-size:12px;
        color:#4b5563;
        max-width:520px;
        background:#f9fafb;
        border-radius:12px;
        padding:10px 12px;
        border:1px dashed rgba(148,163,184,.7);
    }

    .ficha-notas ul {
        margin:6px 0 0 18px;
    }

    .ficha-submit-btn {
        padding:10px 22px;
        border-radius:999px;
        border:none;
        background:linear-gradient(135deg,#22c55e,#16a34a);
        color:#fff;
        font-weight:700;
        font-size:14px;
        display:inline-flex;
        align-items:center;
        gap:8px;
        cursor:pointer;
        box-shadow:0 12px 30px rgba(22,163,74,0.55);
        transition:transform .08s ease, box-shadow .08s ease, filter .06s ease;
    }

    .ficha-submit-btn:hover {
        transform:translateY(-1px);
        box-shadow:0 16px 40px rgba(22,163,74,0.65);
        filter:brightness(1.03);
    }

    .ficha-submit-btn:active {
        transform:translateY(0);
        box-shadow:0 8px 18px rgba(22,163,74,0.45);
    }

    .loader-instructores {
        position:absolute;
        inset:0;
        background:radial-gradient(circle at top, rgba(240,253,250,0.92), rgba(15,23,42,0.05));
        display:flex;
        align-items:center;
        justify-content:center;
        z-index:45;
    }

    .loader-instructores-box {
        background:#ffffff;
        border-radius:999px;
        padding:6px 14px 6px 10px;
        display:flex;
        align-items:center;
        gap:8px;
        box-shadow:0 10px 30px rgba(15,23,42,0.25);
        border:1px solid rgba(34,197,94,0.5);
        font-size:12px;
        color:#065f46;
        font-weight:600;
    }

    .loader-spinner {
        width:14px;
        height:14px;
        border-radius:999px;
        border:2px solid #6ee7b7;
        border-top-color:#16a34a;
        animation:spin-loader .7s linear infinite;
    }

    @keyframes spin-loader {
        to { transform:rotate(360deg); }
    }

    .ficha-select {
        border-radius:999px !important;
        border:1px solid #d1d5db !important;
        background-color:#ffffff;
        padding:4px 26px 4px 10px !important;
        font-size:13px;
        color:#111827;
        box-shadow:0 1px 2px rgba(15,23,42,0.08);
        background-image:linear-gradient(45deg, transparent 50%, #6b7280 50%),
                          linear-gradient(135deg, #6b7280 50%, transparent 50%);
        background-position:calc(100% - 14px) 50%, calc(100% - 9px) 50%;
        background-size:5px 5px, 5px 5px;
        background-repeat:no-repeat;
        -webkit-appearance:none;
        -moz-appearance:none;
        appearance:none;
    }

    .ficha-select:focus {
        outline:none;
        border-color:#22c55e !important;
        box-shadow:0 0 0 2px rgba(34,197,94,0.35);
    }

    .ficha-select option {
        font-size:13px;
    }

    .ficha-select-contrato {
        color:#b45309;
        border-color:#f97316 !important;
    }

    .ficha-select-planta {
        color:#065f46;
        border-color:#22c55e !important;
    }

    .badge-instructor {
        display:inline-block;
        padding:2px 8px;
        border-radius:999px;
        font-size:11px;
        font-weight:600;
        margin-right:4px;
        margin-top:2px;
        white-space:nowrap;
    }

    .badge-tipo-contrato {
        background:#fff7ed;
        color:#9a3412;
        border:1px solid #fdba74;
    }

    .badge-tipo-planta {
        background:#ecfdf5;
        color:#047857;
        border:1px solid #6ee7b7;
    }

    .badge-tipo-neutro {
        background:#eef2ff;
        color:#4338ca;
        border:1px solid #c7d2fe;
    }

    .badge-especialidad {
        background:#ecfdf5;
        color:#065f46;
        border:1px solid #6ee7b7;
        max-width:260px;
        overflow:hidden;
        text-overflow:ellipsis;
    }

    .badge-especialidad-list {
        max-width:180px;
    }

    .inst-select-wrapper {
        position:relative;
        min-width:260px;
        max-width:320px;
    }

    .inst-dropdown-panel {
        position:absolute;
        left:0;
        right:0;
        margin-top:4px;
        background:#ffffff;
        border-radius:12px;
        box-shadow:0 18px 40px rgba(15,23,42,0.3);
        border:1px solid rgba(148,163,184,0.6);
        z-index:40;
        max-height:340px;
        overflow:hidden;
    }

    .inst-search-bar {
        padding:6px 8px;
        border-bottom:1px solid #e5e7eb;
        background:#f9fafb;
    }

    .inst-search-input {
        width:100%;
        border-radius:999px;
        border:1px solid #d1d5db;
        padding:4px 10px;
        font-size:12px;
        outline:none;
    }

    .inst-search-input:focus {
        border-color:#22c55e;
        box-shadow:0 0 0 2px rgba(34,197,94,0.35);
    }

    .inst-options-list {
        max-height:290px;
        overflow-y:auto;
    }

    .inst-option {
        width:100%;
        text-align:left;
        padding:6px 10px;
        border:none;
        background:#ffffff;
        cursor:pointer;
        display:block;
    }

    .inst-option-main {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:6px;
        margin-bottom:2px;
    }

    .inst-option-nombre {
        font-size:13px;
        font-weight:600;
        color:#111827;
    }

    .inst-option-extra {
        font-size:11px;
        color:#4b5563;
        display:flex;
        align-items:center;
        gap:6px;
        flex-wrap:wrap;
    }

    .inst-option-text {
        white-space:nowrap;
    }

    .inst-option-contrato {
        background:#fff7ed;
    }

    .inst-option-planta {
        background:#ecfdf5;
    }

    .inst-option-neutro {
        background:#f9fafb;
    }

    .inst-option:hover {
        background:#e5f3ff;
    }

    .inst-option-empty {
        font-size:12px;
        color:#6b7280;
        cursor:default;
    }

    .badge-tipo-mini {
        background:#eef2ff;
        color:#4338ca;
        border-radius:999px;
        padding:1px 6px;
        font-size:10px;
        font-weight:600;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectProg = document.getElementById('cod_prog_fk');
        const nivelInput = document.getElementById('nivel_programa');
        const nivelHelp = document.getElementById('nivel_help');
        const fichaInput = document.getElementById('id_fich');
        const fechaInicioInput = document.querySelector('input[name="fecha_inic_lec"]');
        const fechaFinInput = document.querySelector('input[name="fecha_fin_lec"]');
        const jornadaSelect = document.querySelector('select[name="jornada"]');
        const btnCopiarHorarioLunes = document.getElementById('btnCopiarHorarioLunes');
        const filtroTipoInstructor = document.getElementById('filtroTipoInstructor');
        const competenciasData = @json($competencias ?? []);
        const resultadosData = @json($resultados ?? []);
        const instructoresContrato = @json($instructoresContrato ?? []);
        const instructoresPlanta = @json($instructoresPlanta ?? []);
        const instructoresAll = [...instructoresContrato, ...instructoresPlanta];
        const loaderInstructores = document.getElementById('loaderInstructores');
        let instGlobalClickBound = false;
        let resultadosPorCompetencia = {};

        // Precalcular el texto de búsqueda para cada instructor (mejora rendimiento)
        function prepararBusqueda(lista) {
            lista.forEach(function (inst) {
            const base =
                (inst.nombre || '') + ' ' +
                (inst.cc || '') + ' ' +
                (inst.especialidad || '') + ' ' +
                (inst.area || '') + ' ' +
                (inst.red || '');
            inst._searchText = base.toLowerCase();
            });
        }

        prepararBusqueda(instructoresContrato);
        prepararBusqueda(instructoresPlanta);

        function actualizarInfoInstructorDia(diaNorm, inst) {
            const infoDiv = document.querySelector('[data-info-dia="' + diaNorm + '"]');
            if (!infoDiv) return;

            if (!inst) {
                infoDiv.innerHTML = '';
                return;
            }

            const nombre = inst.nombre || '';
            const tip = inst.tip_vincul || inst.nombre_rol || 'Sin vinculación';
            const esp = inst.especialidad || 'Sin especialidad registrada';
            const espMostrar = esp || 'Sin especialidad registrada';

            infoDiv.innerHTML = `
                <div class="info-instructor-line-nombre">${nombre}</div>
                <div class="info-instructor-line-detalle">
                    <span class="info-instructor-chip">${tip}</span>
                    <span class="info-instructor-chip">${espMostrar}</span>
                </div>
            `;
        }

        function mostrarLoaderInstructores() {
            if (loaderInstructores) loaderInstructores.style.display = 'flex';
        }

        function ocultarLoaderInstructores() {
            if (loaderInstructores) loaderInstructores.style.display = 'none';
        }

        if (fichaInput) {
            fichaInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        function sincronizarFechas() {
            if (fechaInicioInput && fechaFinInput) {
                if (fechaInicioInput.value) {
                    fechaFinInput.min = fechaInicioInput.value;
                    if (fechaFinInput.value && fechaFinInput.value < fechaInicioInput.value) {
                        fechaFinInput.value = fechaInicioInput.value;
                    }
                } else {
                    fechaFinInput.min = '';
                }
            }
        }

        if (fechaInicioInput) {
            fechaInicioInput.addEventListener('change', sincronizarFechas);
        }
        if (fechaFinInput) {
            fechaFinInput.addEventListener('change', sincronizarFechas);
        }

        function normalizarDia(text) {
            return (text || '').toUpperCase().normalize('NFD').replace(/\p{Diacritic}/gu, '');
        }

        const mapaHorasPorDia = {};
        
        function construirMapaResultadosPorCompetencia(progId) {
            resultadosPorCompetencia = {};
            const pid = parseInt(progId || '0', 10);
            if (!pid) return;

            resultadosData.forEach(function (res) {
                const resProg = parseInt(res.id_prog_fk || '0', 10);
                if (resProg !== pid) return;
                const codComp = res.cod_comp_fk || '';
                if (!codComp) return;
                if (!resultadosPorCompetencia[codComp]) {
                    resultadosPorCompetencia[codComp] = [];
                }
                resultadosPorCompetencia[codComp].push(res);
            });
        }

        function limpiarResultadosCompetencias() {
            document.querySelectorAll('.resultados-competencia-dia').forEach(function (cont) {
                cont.innerHTML = '';
            });
        }

        function actualizarResultadosParaDia(diaNorm, codComp) {
            const cont = document.querySelector('.resultados-competencia-dia[data-res-dia="' + diaNorm + '"]');
            if (!cont) return;

            cont.innerHTML = '';

            if (!codComp) {
                return;
            }

            const lista = resultadosPorCompetencia[codComp] || [];
            if (!lista.length) {
                const vacio = document.createElement('div');
                vacio.className = 'resultado-item-empty';
                vacio.textContent = 'Sin resultados configurados para esta competencia.';
                cont.appendChild(vacio);
                return;
            }

            lista.forEach(function (res) {
                const div = document.createElement('div');
                div.className = 'resultado-item';
                const cod = res.cod_resu || '';
                const nombre = res.nombre || '';
                div.textContent = (cod ? '[' + cod + '] ' : '') + nombre;
                cont.appendChild(div);
            });
        }

        function obtenerFiltrosInstructoresPorDia() {
            const filtros = {};
            document.querySelectorAll('.schedule-instructor-search').forEach(function (input) {
                const diaNorm = normalizarDia(input.dataset.dia || '');
                if (!diaNorm) return;
                filtros[diaNorm] = (input.value || '').toLowerCase().trim();
            });
            return filtros;
        }

        function construirMapaHoras() {
            Object.keys(mapaHorasPorDia).forEach(function (k) { delete mapaHorasPorDia[k]; });

            document.querySelectorAll('.input-hora-inicio').forEach(function (input) {
                const diaNorm = normalizarDia(input.dataset.dia || '');
                if (!diaNorm) return;
                if (!mapaHorasPorDia[diaNorm]) mapaHorasPorDia[diaNorm] = {};
                mapaHorasPorDia[diaNorm].inicio = input;
            });

            document.querySelectorAll('.input-hora-fin').forEach(function (input) {
                const diaNorm = normalizarDia(input.dataset.dia || '');
                if (!diaNorm) return;
                if (!mapaHorasPorDia[diaNorm]) mapaHorasPorDia[diaNorm] = {};
                mapaHorasPorDia[diaNorm].fin = input;
            });
        }

        function inicializarSelectsInstructoresHorario() {
            const selects = document.querySelectorAll('.select-instructor-dia');

            selects.forEach(function (sel) {
                const diaNorm = normalizarDia(sel.dataset.dia || '');

                sel.innerHTML = '';
                const optBase = document.createElement('option');
                optBase.value = '';
                optBase.textContent = 'Seleccione instructor...';
                sel.appendChild(optBase);

                instructoresAll.forEach(function (inst) {
                    const opt = document.createElement('option');
                    opt.value = inst.cc;
                    opt.textContent = inst.nombre || ('CC ' + inst.cc);
                    sel.appendChild(opt);
                });

                sel.addEventListener('change', function () {
                    if (!diaNorm) return;
                    const cc = sel.value;
                    if (!cc) {
                        actualizarInfoInstructorDia(diaNorm, null);
                        return;
                    }
                    const inst = instructoresAll.find(function (i) {
                        return String(i.cc) === String(cc);
                    });
                    actualizarInfoInstructorDia(diaNorm, inst || null);
                });
                actualizarInfoInstructorDia(diaNorm, null);
            });
        }

        function actualizarNivel() {
            const opt = selectProg.options[selectProg.selectedIndex];
            const nivel = opt ? opt.getAttribute('data-nivel') || '' : '';
            const cantTrim = opt ? opt.getAttribute('data-cant-trim') || '' : '';
            nivelInput.value = nivel;

            let ayuda = '';
            if (nivel && nivel.toUpperCase().includes('TECNOLOG')) {
                ayuda = 'Nivel tecnólogo: formación regular 7 trimestres.';
            }
            if (cantTrim) {
                ayuda += (ayuda ? ' ' : '') + 'Cant. trimestres del programa: ' + cantTrim + '.';
            }
            nivelHelp.textContent = ayuda;
        }

        selectProg.addEventListener('change', actualizarNivel);
        actualizarNivel();

        function actualizarColumnaDiaOpacity(diaNorm, valorOpacity) {
            document.querySelectorAll('[data-dia-col="' + diaNorm + '"]').forEach(function (el) {
                el.style.opacity = valorOpacity;
            });
        }

        function aplicarSugerenciasJornada() {
            const jornada = (jornadaSelect.value || '').toUpperCase();

            Object.keys(mapaHorasPorDia).forEach(function (diaNorm) {
                const par = mapaHorasPorDia[diaNorm] || {};
                const inputIni = par.inicio;
                const inputFin = par.fin;
                if (!inputIni || !inputFin) return;

                let sugIni = '';
                let sugFin = '';

                // Manejo especial para sábado en jornada diurna: se bloquea
                if (diaNorm === 'SABADO' && jornada === 'DIURNA') {
                    inputIni.value = '';
                    inputIni.disabled = true;
                    inputFin.value = '';
                    inputFin.disabled = true;
                    actualizarColumnaDiaOpacity(diaNorm, '0.6');
                    return;
                } else {
                    inputIni.disabled = false;
                    inputFin.disabled = false;
                    actualizarColumnaDiaOpacity(diaNorm, '');
                }

                if (jornada === 'DIURNA') {
                    if (['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES'].includes(diaNorm)) {
                        sugIni = '06:00';
                        sugFin = '18:00';
                    }
                } else if (jornada === 'MIXTA') {
                    if (['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES'].includes(diaNorm)) {
                        sugIni = '18:00';
                        sugFin = '22:00';
                    } else if (diaNorm === 'SABADO') {
                        sugIni = '06:00';
                        sugFin = '18:00';
                    }
                } else if (jornada === 'FINES DE SEMANA') {
                    if (diaNorm === 'VIERNES') {
                        sugIni = '18:00';
                        sugFin = '22:00';
                    } else if (['SABADO','DOMINGO'].includes(diaNorm)) {
                        sugIni = '06:00';
                        sugFin = '18:00';
                    }
                }

                if (sugIni && sugFin) {
                    if (!inputIni.value) inputIni.value = sugIni;
                    if (!inputFin.value) inputFin.value = sugFin;
                }
            });
        }

        construirMapaHoras();
        inicializarSelectsInstructoresHorario();
        
        // Buscar por instructor a nivel de cada día (sugerencias debajo del input)
        document.querySelectorAll('.schedule-instructor-search').forEach(function (input) {
            const diaRaw = input.dataset.dia || '';
            const diaNorm = normalizarDia(diaRaw);
            const cont = document.querySelector('.schedule-instructor-suggestions[data-suggest-dia="' + diaNorm + '"]');
            const selectDia = document.querySelector('.select-instructor-dia[data-dia="' + diaRaw + '"]');
            const btnClear = document.querySelector('.schedule-instructor-clear[data-dia="' + diaRaw + '"]');
            if (!cont || !selectDia) return;

            function renderSugerencias() {
                const texto = (input.value || '').toLowerCase().trim();
                cont.innerHTML = '';

                if (!texto) {
                    cont.style.display = 'none';
                    return;
                }

                let count = 0;
                instructoresAll.forEach(function (inst) {
                    const base = inst._searchText || '';
                    if (!base.includes(texto)) return;
                    if (count >= 6) return; // limitar número de sugerencias

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'schedule-instructor-suggestion';

                    const nombre = inst.nombre || '';
                    const esp = inst.especialidad || '';

                    btn.innerHTML = `
                        <span class="schedule-instructor-suggestion-name">${nombre}</span>
                        ${esp ? `<span class="schedule-instructor-suggestion-esp">${esp}</span>` : ''}
                    `;

                    btn.addEventListener('click', function () {
                        selectDia.value = inst.cc;
                        actualizarInfoInstructorDia(diaNorm, inst);
                        input.value = nombre;
                        cont.innerHTML = '';
                        cont.style.display = 'none';
                    });

                    cont.appendChild(btn);
                    count++;
                });

                if (count === 0) {
                    const vacio = document.createElement('div');
                    vacio.className = 'schedule-instructor-suggestion';
                    vacio.textContent = 'Sin coincidencias';
                    cont.appendChild(vacio);
                }

                cont.style.display = 'block';
            }

            input.addEventListener('input', renderSugerencias);

            if (btnClear) {
                btnClear.addEventListener('click', function () {
                    input.value = '';
                    cont.innerHTML = '';
                    cont.style.display = 'none';
                    selectDia.value = '';
                    actualizarInfoInstructorDia(diaNorm, null);
                });
            }
        });

        if (jornadaSelect) {
            jornadaSelect.addEventListener('change', aplicarSugerenciasJornada);
            aplicarSugerenciasJornada();
        }

        // Eventos para actualizar resultados de aprendizaje al cambiar la competencia por día
        document.querySelectorAll('.select-competencia').forEach(function (sel) {
            const diaRaw = sel.dataset.dia || '';
            const diaNorm = normalizarDia(diaRaw);
            sel.addEventListener('change', function () {
                if (!diaNorm) return;
                const codComp = sel.value || '';
                actualizarResultadosParaDia(diaNorm, codComp);
            });
        });

        function construirOpcionesCompetencias() {
            const progId = selectProg ? parseInt(selectProg.value || '0', 10) : 0;
            const selects = document.querySelectorAll('.select-competencia');

            construirMapaResultadosPorCompetencia(progId);
            limpiarResultadosCompetencias();

            selects.forEach(function (sel) {
                const valorActual = sel.value;
                sel.innerHTML = '';

                const optBase = document.createElement('option');
                optBase.value = '';
                optBase.textContent = progId ? 'Seleccione competencia...' : 'Seleccione un programa...';
                sel.appendChild(optBase);

                if (!progId) {
                    sel.value = '';
                    sel.disabled = true;
                    return;
                }

                sel.disabled = false;

                competenciasData.forEach(function (comp) {
                    if (parseInt(comp.id_prog_fk || '0', 10) !== progId) return;

                    const opt = document.createElement('option');
                    opt.value = comp.cod_comp;
                    opt.textContent = '[' + comp.cod_comp + '] ' + comp.nombre;
                    sel.appendChild(opt);
                });

                if (valorActual) {
                    sel.value = valorActual;
                }
            });
        }

        // Reservado: lógica de filtrado por tipo de instructor si se necesitara fuera del dropdown.
        function pasaFiltroInstructor(inst) {
            const filtroVal = (filtroTipoInstructor ? filtroTipoInstructor.value : '') || '';
            if (!filtroVal) return true; // sin filtro

            const rolId = String(inst.id_rol_fk ?? '');
            return rolId === filtroVal;
        }

        function actualizarInfoInstructorResumen(wrapper, inst) {
            const row = wrapper.closest('tr');
            if (!row) return;

            const infoDiv = row.querySelector('.info-instructor');
            const btnVer = row.querySelector('.btn-ver-instructor');
            if (!infoDiv) return;

            if (!inst) {
                infoDiv.innerHTML = '';
                const toggle = wrapper.querySelector('.inst-toggle');
                if (toggle) toggle.classList.remove('ficha-select-contrato', 'ficha-select-planta');
                if (btnVer) btnVer.style.display = 'none';
                return;
            }

            const tip = inst.tip_vincul || inst.nombre_rol || 'Sin vinculación';
            const esp = inst.especialidad || 'Sin especialidad registrada';
            const tipUpper = String(tip).toUpperCase();
            let tipoClase = 'badge-tipo-neutro';
            const toggle = wrapper.querySelector('.inst-toggle');
            if (toggle) toggle.classList.remove('ficha-select-contrato', 'ficha-select-planta');

            if (tipUpper.includes('CONTRAT')) {
                tipoClase = 'badge-tipo-contrato';
                if (toggle) toggle.classList.add('ficha-select-contrato');
            } else if (tipUpper.includes('PLANTA')) {
                tipoClase = 'badge-tipo-planta';
                if (toggle) toggle.classList.add('ficha-select-planta');
            }

            const espMostrar = esp || 'Sin especialidad registrada';

            infoDiv.innerHTML = `
                <span class="badge-instructor ${tipoClase}">${tip}</span>
                <span class="badge-instructor badge-especialidad">${espMostrar}</span>
            `;
            if (btnVer) btnVer.style.display = 'inline-block';
        }

        function abrirModalInstructor(cc) {
            const inst = instructoresAll.find(function (i) {
                return String(i.cc) === String(cc);
            });
            if (!inst) return;

            const modal = document.getElementById('modalInstructor');
            if (!modal) return;

            modal.querySelector('[data-inst-nombre]').textContent = inst.nombre || '';
            modal.querySelector('[data-inst-cc]').textContent = inst.cc || '';
            modal.querySelector('[data-inst-vinc]').textContent = inst.tip_vincul || inst.nombre_rol || 'Sin vinculación';
            modal.querySelector('[data-inst-esp]').textContent = inst.especialidad || 'Sin especialidad registrada';
            modal.querySelector('[data-inst-pregrado]').textContent = inst.pregrado || 'Sin pregrado registrado';

            modal.style.display = 'flex';
        }

        function cerrarModalInstructor() {
            const modal = document.getElementById('modalInstructor');
            if (modal) modal.style.display = 'none';
        }

        function copiarHorarioDesdeLunes() {
            const parLunes = mapaHorasPorDia['LUNES'];
            if (!parLunes) return;

            const inputIniLunes = parLunes.inicio;
            const inputFinLunes = parLunes.fin;
            const horaIni = inputIniLunes ? inputIniLunes.value : '';
            const horaFin = inputFinLunes ? inputFinLunes.value : '';

            if (!horaIni || !horaFin) {
                alert('Primero define la hora de inicio y fin para Lunes.');
                return;
            }

            Object.keys(mapaHorasPorDia).forEach(function (diaNorm) {
                if (!['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES'].includes(diaNorm)) return;

                const par = mapaHorasPorDia[diaNorm] || {};
                const inputIni = par.inicio;
                const inputFin = par.fin;

                if (!inputIni || !inputFin || inputIni.disabled || inputFin.disabled) return;

                inputIni.value = horaIni;
                inputFin.value = horaFin;
            });
        }

        if (btnCopiarHorarioLunes) {
            btnCopiarHorarioLunes.addEventListener('click', copiarHorarioDesdeLunes);
        }

        construirOpcionesCompetencias();

        function initInstructorDropdowns() {
            const wrappers = document.querySelectorAll('.inst-select-wrapper');

            wrappers.forEach(function (wrapper) {
                const hiddenInput = wrapper.querySelector('.inst-hidden-input');
                const toggleBtn = wrapper.querySelector('.inst-toggle');
                const toggleLabel = wrapper.querySelector('.inst-toggle-label');
                const panel = wrapper.querySelector('.inst-dropdown-panel');
                if (!hiddenInput || !toggleBtn || !panel) return;

                panel.innerHTML = `
                    <div class="inst-search-bar">
                        <input type="text" class="inst-search-input" placeholder="Buscar por nombre o especialidad...">
                    </div>
                    <div class="inst-options-list"></div>
                `;

                const searchInput = panel.querySelector('.inst-search-input');
                const listEl = panel.querySelector('.inst-options-list');

                function renderList() {
                        const texto = (searchInput.value || '').toLowerCase();
                        const filtroVal = (filtroTipoInstructor ? filtroTipoInstructor.value : '') || '';
                    listEl.innerHTML = '';

                        let count = 0;
                        let fuente;
                        if (filtroVal === '2') {
                            fuente = instructoresContrato;
                        } else if (filtroVal === '3') {
                            fuente = instructoresPlanta;
                        } else {
                            fuente = instructoresAll;
                        }

                        fuente.forEach(function (inst) {

                        const base = inst._searchText || '';
                        if (texto && !base.includes(texto)) {
                            return;
                        }

                        const tip = inst.tip_vincul || inst.nombre_rol || '';
                        const tipUpper = tip.toUpperCase();
                        let tipoClase = 'inst-option-neutro';
                        if (tipUpper.includes('CONTRAT')) tipoClase = 'inst-option-contrato';
                        else if (tipUpper.includes('PLANTA')) tipoClase = 'inst-option-planta';

                        const optionBtn = document.createElement('button');
                        optionBtn.type = 'button';
                        optionBtn.className = 'inst-option ' + tipoClase;

                        const esp = inst.especialidad || '';
                        const area = inst.area || '';
                        const red = inst.red || '';

                        optionBtn.innerHTML = `
                            <div class="inst-option-main">
                                <span class="inst-option-nombre">${inst.nombre || ''}</span>
                                ${esp ? `<span class="badge-instructor badge-especialidad badge-especialidad-list">${esp}</span>` : ''}
                            </div>
                            <div class="inst-option-extra">
                                ${tip ? `<span class="badge-instructor badge-tipo-mini">${tip}</span>` : ''}
                                ${area ? `<span class="inst-option-text">${area}</span>` : ''}
                                ${red ? `<span class="inst-option-text">${red}</span>` : ''}
                            </div>
                        `;

                        optionBtn.addEventListener('click', function () {
                            hiddenInput.value = inst.cc;
                            if (toggleLabel) {
                                toggleLabel.textContent = inst.nombre || '';
                            }
                            renderSelectionStyles(wrapper, inst);
                            actualizarInfoInstructorResumen(wrapper, inst);
                            panel.style.display = 'none';
                        });

                        listEl.appendChild(optionBtn);
                        count++;
                    });

                    if (count === 0) {
                        const vacio = document.createElement('div');
                        vacio.className = 'inst-option inst-option-empty';
                        vacio.textContent = 'No hay instructores que coincidan con la búsqueda.';
                        listEl.appendChild(vacio);
                    }
                }

                function renderSelectionStyles(wrapperLocal, inst) {
                    const toggleLocal = wrapperLocal.querySelector('.inst-toggle');
                    if (!toggleLocal) return;
                    toggleLocal.classList.remove('ficha-select-contrato', 'ficha-select-planta');

                    const tip = (inst.tip_vincul || inst.nombre_rol || '').toUpperCase();
                    if (tip.includes('CONTRAT')) toggleLocal.classList.add('ficha-select-contrato');
                    else if (tip.includes('PLANTA')) toggleLocal.classList.add('ficha-select-planta');
                }

                toggleBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isOpen = panel.style.display === 'block';
                    document.querySelectorAll('.inst-dropdown-panel').forEach(function (p) { p.style.display = 'none'; });
                    panel.style.display = isOpen ? 'none' : 'block';
                    if (!isOpen && searchInput) {
                        searchInput.focus();
                    }
                });

                if (searchInput) {
                    searchInput.addEventListener('input', renderList);
                }

                renderList();

                const valorInicial = hiddenInput.value;
                if (valorInicial) {
                    const inst = instructoresAll.find(i => String(i.cc) === String(valorInicial));
                    if (inst) {
                        if (toggleLabel) toggleLabel.textContent = inst.nombre || '';
                        renderSelectionStyles(wrapper, inst);
                        actualizarInfoInstructorResumen(wrapper, inst);
                    }
                }
            });

            if (!instGlobalClickBound) {
                document.addEventListener('click', function () {
                    document.querySelectorAll('.inst-dropdown-panel').forEach(function (p) { p.style.display = 'none'; });
                });
                instGlobalClickBound = true;
            }
        }

        initInstructorDropdowns();

        if (selectProg) {
            selectProg.addEventListener('change', construirOpcionesCompetencias);
        }

        if (filtroTipoInstructor) {
            filtroTipoInstructor.addEventListener('change', function () {
                mostrarLoaderInstructores();
                setTimeout(function () {
                    initInstructorDropdowns();
                    ocultarLoaderInstructores();
                }, 10);
            });
        }

        document.querySelectorAll('.btn-ver-instructor').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const row = btn.closest('tr');
                if (!row) return;
                const hidden = row.querySelector('.inst-hidden-input');
                if (!hidden || !hidden.value) {
                    alert('Primero selecciona un instructor en este día.');
                    return;
                }
                abrirModalInstructor(hidden.value);
            });
        });

        document.querySelectorAll('[data-close-modal="instructor"]').forEach(function (el) {
            el.addEventListener('click', cerrarModalInstructor);
        });
    });
</script>

<div id="modalInstructor" style="position:fixed; inset:0; background:rgba(15,23,42,0.45); display:none; align-items:center; justify-content:center; z-index:50;">
    <div style="background:#fff; border-radius:12px; padding:16px 18px; width:100%; max-width:420px; box-shadow:0 20px 40px rgba(15,23,42,0.35);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <h3 style="margin:0; font-size:16px; font-weight:700;">Detalle del instructor</h3>
            <button type="button" data-close-modal="instructor" style="border:none; background:transparent; font-size:18px; cursor:pointer; line-height:1;">×</button>
        </div>
        <div style="font-size:13px; color:#111827;">
            <p style="margin:0 0 4px;"><strong>Nombre:</strong> <span data-inst-nombre></span></p>
            <p style="margin:0 0 4px;"><strong>Documento:</strong> <span data-inst-cc></span></p>
            <p style="margin:0 0 4px;"><strong>Vinculación:</strong> <span data-inst-vinc></span></p>
            <p style="margin:0 0 4px;"><strong>Especialidad:</strong> <span data-inst-esp></span></p>
            <p style="margin:0 0 0;"><strong>Pregrado:</strong> <span data-inst-pregrado></span></p>
        </div>
        <div style="margin-top:12px; text-align:right;">
            <button type="button" data-close-modal="instructor" style="padding:6px 12px; border-radius:999px; border:1px solid #d1d5db; background:#f9fafb; color:#111827; font-size:12px; cursor:pointer;">Cerrar</button>
        </div>
    </div>
</div>
@endsection

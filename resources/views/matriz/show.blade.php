@extends('layouts.app')

@section('title', 'Matriz - ' . $programa->nombre)

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <!-- Mini barra de guardado (aparece al hacer scroll) -->
            <div id="miniSaveBar" class="mini-save-bar">
                <div class="mini-save-content">
                    <div class="mini-meta">
                        <span><strong>Código:</strong> {{ $programa->id_prog }}</span>
                        <span><strong>Versión:</strong> {{ $programa->version }}</span>
                        <span><strong>Nivel:</strong> {{ $programa->nivel }}</span>
                    </div>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <div id="miniMetrics" class="mini-metrics summary-badge" style="display:none;"></div>
                        <button id="guardarCambiosBtnMini" class="btn btn-small" onclick="guardarCambiosMatriz()">
                            <i class="bi bi-save" aria-hidden="true"></i>
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </div>
            <main>
                <div class="program-info">
                    <div class="program-header-top">
                        <a href="{{ route('matriz.index') }}" class="btn-back-arrow">
                            <i class="bi bi-arrow-left-circle" aria-hidden="true"></i>
                        </a>
                        <h2>
                            <i class="bi bi-book" aria-hidden="true"></i>
                            {{ $programa->nombre }}
                        </h2>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Código</strong>
                            <span>{{ $programa->id_prog }}</span>
                        </div>
                        <div class="info-item">
                            <strong>Versión</strong>
                            <span>{{ $programa->version }}</span>
                        </div>
                        <div class="info-item">
                            <strong>Nivel</strong>
                            <span>{{ $programa->nivel }}</span>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <div class="stats">
                        <div class="stat-badge">
                            <i class="bi bi-clipboard" aria-hidden="true"></i>
                            {{ count($competencias) }} Competencias
                        </div>
                        <div class="stat-badge">
                            <i class="bi bi-check2" aria-hidden="true"></i>
                            {{ $competencias->sum(function($c) { return count($c->resultados); }) }} Resultados
                        </div>
                        <div id="compHmaxSummary" class="stat-badge summary-badge" style="display:none;"></div>
                        <!-- Filtro de trimestres desactivado temporalmente -->
                        <input type="text" 
                               id="searchCompetencias" 
                               class="search-competencias-input" 
                               placeholder="Buscar competencia..."
                               onkeyup="filtrarCompetencias()">
                    </div>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <button id="guardarCambiosBtn" class="btn">
                            <i class="bi bi-save" aria-hidden="true"></i>
                            Guardar cambios
                        </button>
                        <a href="{{ route('matriz.exportar', $programa->id_prog) }}" class="btn btn-success">
                            <i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i>
                            Exportar a Excel
                        </a>
                    </div>
                </div>


                @if(count($competencias) > 0)
                    <div id="excelView" class="excel-view">
                        <div class="table-wrapper">
                            <table id="excelGrid" class="excel-table">
                                <colgroup id="excelColGroup">
                                    <col>
                                    <col>
                                    <col>
                                    <col>
                                    <col>
                                    <col>
                                    <col>
                                    <col>
                                    <col>
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="excel-th">Competencia</th>
                                        <th class="excel-th narrow col-codigo">Código</th>
                                        <th class="excel-th narrow col-duracion">Duración (h)</th>
                                        <th class="excel-th">Resultado de Aprendizaje</th>
                                        <th class="excel-th narrow">Horas Max</th>
                                        <th class="excel-th narrow">Horas Min</th>
                                        <th class="excel-th narrow">Trimestre</th>
                                        <th class="excel-th narrow col-hsem">H/Sem Prog.</th>
                                        <th class="excel-th narrow col-htrim">H/Trim Prog.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($competencias as $competencia)
                                        @php $rowspan = max(1, $competencia->resultados->count()); @endphp
                                        @foreach($competencia->resultados as $resultado)
                                            <tr data-comp-codigo="{{ $competencia->cod_comp }}" data-comp-nombre="{{ $competencia->nombre }}" class="comp-group @if($loop->first) comp-start @endif @if($loop->last) comp-end @endif">
                                                @if($loop->first)
                                                    <td rowspan="{{ $rowspan }}" class="group-left">
                                                        <div class="comp-view" id="grid-comp-view-{{ $competencia->cod_comp }}" onclick="toggleCompEdit({{ $competencia->cod_comp }})">{{ $competencia->nombre }}</div>
                                                        <div class="comp-edit" id="grid-comp-edit-{{ $competencia->cod_comp }}" style="display:none;">
                                                            <input type="text" value="{{ $competencia->nombre }}" class="input-text" id="grid-comp-nombre-{{ $competencia->cod_comp }}" style="width:100%">
                                                            <button class="btn btn-small" onclick="guardarCompetencia({{ $competencia->cod_comp }})">
                                                                <i class="bi bi-save" aria-hidden="true"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td rowspan="{{ $rowspan }}" class="col-codigo"><strong>{{ $competencia->cod_comp }}</strong></td>
                                                    <td rowspan="{{ $rowspan }}" class="col-duracion"><strong>{{ $competencia->duracion_hora }}</strong></td>
                                                @endif
                                                <td>
                                                    <div class="resultado-texto-grid">{{ $resultado->nombre }}</div>
                                                </td>
                                                <td class="col-hora">
                                                    <input type="number" min="0" id="grid-res-hmax-{{ $resultado->id_resu }}" class="input-number compact" value="{{ $resultado->duracion_hora_max }}">
                                                </td>
                                                <td class="col-hora">
                                                    <div class="value-static" id="grid-res-hmin-view-{{ $resultado->id_resu }}">{{ $resultado->duracion_hora_min }}</div>
                                                </td>
                                                <td class="col-tri">
                                                    <input type="number" min="1" max="7" id="grid-res-tri-{{ $resultado->id_resu }}" class="input-number compact" value="{{ $resultado->trim_prog }}">
                                                </td>
                                                <td class="col-hsem">
                                                    <input type="number" min="0" id="grid-res-hsem-{{ $resultado->id_resu }}" class="input-number compact" value="{{ $resultado->hora_sema_programar }}">
                                                </td>
                                                <td class="col-htrim group-right">
                                                    <div class="value-static" id="grid-res-htrim-view-{{ $resultado->id_resu }}">{{ $resultado->hora_sema_programar * 11 }}</div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="excel-help">Arrastra los bordes de los encabezados para ajustar el ancho de las columnas. Las horas son de solo lectura.</div>
                    </div>
                @else
                    <div class="empty-state">
                        <h3>No hay competencias registradas</h3>
                        <p>Este programa aún no tiene competencias cargadas</p>
                    </div>
                @endif

    <script>
        // ===== Estado de cambios y botones de guardado =====
        let initialState = new Map();

        function getAllResultadoIds() {
            return Array.from(document.querySelectorAll('[id^="grid-res-hmax-"]'))
                .map(el => el.id.replace('grid-res-hmax-',''));
        }

        function getRowState(idResu) {
            const parse = (v, d=0)=>{ const n=parseInt(v,10); return isNaN(n)?d:n; };
            const hmax = parse(document.getElementById(`grid-res-hmax-${idResu}`)?.value);
            const hmin = parse(document.getElementById(`grid-res-hmin-${idResu}`)?.value);
            const hsem = parse(document.getElementById(`grid-res-hsem-${idResu}`)?.value);
            const htrim = parse(document.getElementById(`grid-res-htrim-${idResu}`)?.value);
            const tri = parse(document.getElementById(`grid-res-tri-${idResu}`)?.value,1);
            // Calcular derivados en lugar de leer inputs de min/htrim
            const calcHmin = Math.round(hmax * 0.8);
            const calcHtrim = hsem * 11;
            return JSON.stringify({hmax,hmin:calcHmin,hsem,htrim:calcHtrim,tri});
        }

        function captureInitialState() {
            initialState.clear();
            getAllResultadoIds().forEach(id => initialState.set(id, getRowState(id)));
        }

        function getChangedIds() {
            const changed = [];
            getAllResultadoIds().forEach(id => {
                const now = getRowState(id);
                if (!initialState.has(id) || initialState.get(id) !== now) changed.push(id);
            });
            return changed;
        }

        function setSaveButtons(label, iconClass) {
            const primary = document.getElementById('guardarCambiosBtn');
            const mini = document.getElementById('guardarCambiosBtnMini');
            const html = `<i class="bi ${iconClass}" aria-hidden="true"></i> ${label}`;
            if (primary) primary.innerHTML = html;
            if (mini) mini.innerHTML = html;
        }

        function updateSaveButtonsUI() {
            const hasChanges = getChangedIds().length > 0;
            if (hasChanges) setSaveButtons('Guardar cambios', 'bi-save');
            else setSaveButtons('Guardado', 'bi-check2-circle');
        }

        function bindChangeTracking() {
            const inputs = document.querySelectorAll('[id^="grid-res-hmax-"], [id^="grid-res-hsem-"], [id^="grid-res-tri-"]');
            inputs.forEach(el => el.addEventListener('input', () => {
                if (el.id.startsWith('grid-res-hmax-')) {
                    const id = el.id.replace('grid-res-hmax-','');
                    updateRowDerivedValues(id);
                    const cod = getCompCodeByResId(id);
                    if (cod) updateMiniMetrics(cod);
                }
                if (el.id.startsWith('grid-res-hsem-')) updateRowDerivedValues(el.id.replace('grid-res-hsem-',''));
                updateSaveButtonsUI();
            }));
        }

        // Actualiza H/Min (80% de H/Max) y H/Trim (H/Sem * 11) visualmente
        function updateRowDerivedValues(idResu) {
            const hmaxInput = document.getElementById(`grid-res-hmax-${idResu}`);
            const hsemInput = document.getElementById(`grid-res-hsem-${idResu}`);
            if (!hmaxInput || !hsemInput) return;
            const hmax = parseInt(hmaxInput.value, 10) || 0;
            const hsem = parseInt(hsemInput.value, 10) || 0;
            const calcHmin = Math.round(hmax * 0.8);
            const calcHtrim = hsem * 11;
            const minView = document.getElementById(`grid-res-hmin-view-${idResu}`);
            const trimView = document.getElementById(`grid-res-htrim-view-${idResu}`);
            if (minView) minView.textContent = calcHmin;
            if (trimView) trimView.textContent = calcHtrim;
        }

        // ===== Suma por competencia (H Máx) y validación contra duración =====
        const prevHmax = new Map();

        function getCompCodeByResId(idResu) {
            const row = document.querySelector(`#grid-res-hmax-${idResu}`)?.closest('tr');
            return row ? row.getAttribute('data-comp-codigo') : null;
        }

        function sumHmaxByComp(codComp) {
            let total = 0;
            const rows = document.querySelectorAll(`tr[data-comp-codigo='${codComp}']`);
            rows.forEach(r => {
                const input = r.querySelector("[id^='grid-res-hmax-']");
                const v = parseInt(input?.value || '0', 10) || 0;
                total += v;
            });
            return total;
        }

        function getCompDuration(codComp) {
            const c = competenciasData[codComp];
            return c ? parseInt(c.duracion, 10) || 0 : 0;
        }

        function updateMiniMetrics(codComp) {
            const box = document.getElementById('miniMetrics');
            if (!box || !codComp) return;
            const total = sumHmaxByComp(codComp);
            box.classList.add('summary-badge');
            box.textContent = `Suma Horas de Resultado (${codComp}) = ${total}`;
            box.style.display = '';
            const headerBox = document.getElementById('compHmaxSummary');
            if (headerBox) {
                headerBox.classList.add('summary-badge');
                headerBox.textContent = `Suma Horas de Resultado (${codComp}) = ${total}`;
                headerBox.style.display = '';
            }
        }

        function hideMiniMetrics() {
            const box = document.getElementById('miniMetrics');
            if (box) box.style.display = 'none';
            const headerBox = document.getElementById('compHmaxSummary');
            if (headerBox) headerBox.style.display = 'none';
        }

        // Datos de competencias con resultados
        const competenciasData = {!! json_encode($competencias->map(function($c) {
            return [
                'cod_comp' => $c->cod_comp,
                'nombre' => $c->nombre,
                'duracion' => $c->duracion_hora,
                'resultados' => $c->resultados->map(function($r) {
                    return [
                        'id_resu' => $r->id_resu,
                        'nombre' => $r->nombre,
                        'hora_max' => $r->duracion_hora_max,
                        'hora_min' => $r->duracion_hora_min,
                        'trimestre' => $r->trim_prog,
                        'hora_sema' => $r->hora_sema_programar,
                        'hora_trim' => $r->hora_trim_programar
                    ];
                })
            ];
        })->keyBy('cod_comp')) !!};

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        async function guardarCompetencia(codComp) {
            const nombre = document.getElementById(`grid-comp-nombre-${codComp}`).value.trim();
            const resp = await fetch(`{{ route('matriz.competencia.update', ['cod_comp' => 'CODIGO_PLACE']) }}`.replace('CODIGO_PLACE', codComp), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ nombre: nombre })
            });
            const data = await resp.json();
            if (data.ok) {
                // Actualiza la vista
                document.getElementById(`grid-comp-view-${codComp}`).textContent = data.competencia.nombre;
                const editDiv = document.getElementById(`grid-comp-edit-${codComp}`);
                const viewDiv = document.getElementById(`grid-comp-view-${codComp}`);
                if (editDiv && viewDiv) {
                    editDiv.style.display = 'none';
                    viewDiv.style.display = '';
                }
            } else {
                alert('No se pudo guardar la competencia');
            }
        }
        

        // Resizers de columnas estilo Excel
        function attachColumnResizers(tableId, colGroupId) {
            const table = document.getElementById(tableId);
            const colGroup = document.getElementById(colGroupId);
            if (!table || !colGroup) return;
            const cols = colGroup.querySelectorAll('col');
            const ths = table.querySelectorAll('thead th');

            ths.forEach((th, i) => {
                const width = th.offsetWidth;
                if (cols[i]) cols[i].style.width = width + 'px';
                th.style.width = width + 'px';
                const handle = document.createElement('div');
                handle.className = 'col-resizer';
                th.appendChild(handle);
                let startX = 0;
                let startWidth = width;
                const onMouseMove = (e) => {
                    const delta = e.clientX - startX;
                    const newWidth = Math.max(80, startWidth + delta);
                    if (cols[i]) cols[i].style.width = newWidth + 'px';
                    th.style.width = newWidth + 'px';
                };
                const onMouseUp = () => {
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                };
                handle.addEventListener('mousedown', (e) => {
                    startX = e.clientX;
                    startWidth = th.offsetWidth;
                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });
            });
        }

        function toggleCompEdit(codComp) {
            const editDiv = document.getElementById(`grid-comp-edit-${codComp}`);
            const viewDiv = document.getElementById(`grid-comp-view-${codComp}`);
            if (!editDiv || !viewDiv) return;
            const isHidden = editDiv.style.display === 'none' || editDiv.style.display === '';
            editDiv.style.display = isHidden ? '' : 'none';
            viewDiv.style.display = isHidden ? 'none' : '';
        }

        // Validación de trimestre al salir del campo (no mientras se escribe)
        function validarTrimestreOnBlur(el) {
            let val = parseInt(el.value, 10);
            if (isNaN(val)) return; // permite borrar temporalmente durante la edición
            if (val < 1) val = 1;
            if (val > 7) {
                const miniBar = document.getElementById('miniSaveBar');
                const top = 70 + ((miniBar && miniBar.classList.contains('visible')) ? miniBar.offsetHeight : 0);
                showToast('El trimestre máximo es 7', 'warning', { offsetTop: top });
                val = 7;
            }
            el.value = val;
        }

        // Inicialización de UI al cargar
        setTimeout(() => {
            attachColumnResizers('excelGrid', 'excelColGroup');
            captureInitialState();
            bindChangeTracking();
            updateSaveButtonsUI();
        }, 0);

        // Eventos de enfoque/validación para H Máx
        let currentHighlightCod = null;

        function highlightCompetencia(cod) {
            if (!cod) return;
            // limpiar anterior
            if (currentHighlightCod) {
                document.querySelectorAll(`tr[data-comp-codigo='${currentHighlightCod}']`).forEach(r => r.classList.remove('comp-highlight'));
            }
            document.querySelectorAll(`tr[data-comp-codigo='${cod}']`).forEach(r => r.classList.add('comp-highlight'));
            currentHighlightCod = cod;
        }

        function clearCompetenciaHighlight() {
            if (!currentHighlightCod) return;
            document.querySelectorAll(`tr[data-comp-codigo='${currentHighlightCod}']`).forEach(r => r.classList.remove('comp-highlight'));
            currentHighlightCod = null;
        }

        document.addEventListener('focusin', (e) => {
            const el = e.target;
            if (!el || !el.id) return;
            if (el.id.startsWith('grid-res-hmax-')) {
                const idResu = el.id.replace('grid-res-hmax-','');
                prevHmax.set(idResu, el.value);
                const cod = getCompCodeByResId(idResu);
                updateMiniMetrics(cod);
                highlightCompetencia(cod);
            } else if (el.id.startsWith('grid-res-hsem-') || el.id.startsWith('grid-res-tri-')) {
                const idResu = el.id.replace('grid-res-hsem-','').replace('grid-res-tri-','');
                const cod = getCompCodeByResId(idResu);
                highlightCompetencia(cod);
            } else if (el.id.startsWith('grid-comp-nombre-')) {
                const cod = el.id.replace('grid-comp-nombre-','');
                highlightCompetencia(cod);
            }
        });

        document.addEventListener('focusout', async (e) => {
            const el = e.target;
            if (el && el.id && el.id.startsWith('grid-res-hmax-')) {
                const idResu = el.id.replace('grid-res-hmax-','');
                const cod = getCompCodeByResId(idResu);
                const total = sumHmaxByComp(cod);
                const dur = getCompDuration(cod);
                if (total > dur) {
                    const compNombre = competenciasData[cod]?.nombre || cod;
                    const confirmar = await showConfirmModal({
                        title: 'Horas Máx superan la duración',
                        message: `La suma de Horas Máx (${total}) supera la duración permitida (${dur}) de la competencia.`,
                        meta: `Competencia: ${compNombre} (${cod})`,
                        confirmText: 'Continuar',
                        cancelText: 'Corregir'
                    });
                    if (!confirmar) {
                        // Revertir
                        const prev = prevHmax.get(idResu) || '0';
                        el.value = prev;
                        updateRowDerivedValues(idResu);
                        updateMiniMetrics(cod);
                        return;
                    }
                }
                updateMiniMetrics(cod);
            } else if (el && el.id && el.id.startsWith('grid-res-tri-')) {
                validarTrimestreOnBlur(el);
                updateSaveButtonsUI();
            }
        });

        // Ocultar los badges de suma al hacer clic fuera de las celdas H/Máx o los propios badges
        document.addEventListener('click', (e) => {
            const target = e.target;
            const isHmax = target.closest && target.closest("input[id^='grid-res-hmax-']");
            const inBadges = target.closest && (target.closest('#compHmaxSummary') || target.closest('#miniMetrics'));
            if (!isHmax && !inBadges) hideMiniMetrics();

            // limpiar resaltado si el clic es fuera del bloque resaltado
            const inHighlighted = currentHighlightCod && target.closest && target.closest(`tr[data-comp-codigo='${currentHighlightCod}']`);
            const inCompEdit = currentHighlightCod && target.closest && target.closest(`#grid-comp-edit-${currentHighlightCod}`);
            if (!inHighlighted && !inCompEdit) clearCompetenciaHighlight();
        });

        async function guardarHorasResultadoGrid(idResu, codComp) {
            const hmax = parseInt(document.getElementById(`grid-res-hmax-${idResu}`).value, 10) || 0;
            const hsem = parseInt(document.getElementById(`grid-res-hsem-${idResu}`).value, 10) || 0;
            const hmin = Math.round(hmax * 0.8);
            const htrim = hsem * 11;
            const tri = parseInt(document.getElementById(`grid-res-tri-${idResu}`).value, 10) || 1;
            const payload = {
                duracion_hora_max: hmax,
                duracion_hora_min: hmin,
                hora_sema_programar: hsem,
                hora_trim_programar: htrim,
                trim_prog: tri
            };
            const resp = await fetch(`{{ route('matriz.resultado.update', ['id_resu' => 'ID_PLACE']) }}`.replace('ID_PLACE', idResu), {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload)
            });
            const data = await resp.json();
            if (!data.ok) {
                const miniBar = document.getElementById('miniSaveBar');
                const top = 70 + ((miniBar && miniBar.classList.contains('visible')) ? miniBar.offsetHeight : 0);
                showToast('No se pudo guardar las horas del resultado', 'error', { offsetTop: top });
            }
        }

        // Guardado masivo (horas + trimestre de todas las filas)
        async function guardarCambiosMatriz() {
            // Detectar si hay cambios
            const ids = getChangedIds();
            if (ids.length === 0) {
                // Nada que guardar: mostrar estado Guardado
                setSaveButtons('Guardado', 'bi-check2-circle');
                return;
            }
            // Estado "Guardando..."
            setSaveButtons('Guardando...', 'bi-arrow-repeat');
            let errores = 0;
            for (const idResu of ids) {
                const hmax = parseInt(document.getElementById(`grid-res-hmax-${idResu}`).value, 10) || 0;
                const hsem = parseInt(document.getElementById(`grid-res-hsem-${idResu}`).value, 10) || 0;
                const hmin = Math.round(hmax * 0.8);
                const htrim = hsem * 11;
                const tri = parseInt(document.getElementById(`grid-res-tri-${idResu}`).value, 10) || 1;
                const payload = {
                    duracion_hora_max: hmax,
                    duracion_hora_min: hmin,
                    hora_sema_programar: hsem,
                    hora_trim_programar: htrim,
                    trim_prog: tri
                };
                try {
                    const resp = await fetch(`{{ route('matriz.resultado.update', ['id_resu' => 'ID_PLACE']) }}`.replace('ID_PLACE', idResu), {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    const data = await resp.json();
                    if (!data.ok) errores++;
                } catch(e) { errores++; }
            }
            if (errores > 0) {
                const miniBar = document.getElementById('miniSaveBar');
                const top = 70 + ((miniBar && miniBar.classList.contains('visible')) ? miniBar.offsetHeight : 0);
                showToast(`Algunas filas no se guardaron correctamente (${errores}).`, 'error', { offsetTop: top });
                // Mantener botón en estado "Guardar cambios"
                setSaveButtons('Guardar cambios', 'bi-save');
            } else {
                // Actualizar snapshot y estado de botones
                captureInitialState();
                setSaveButtons('Guardado', 'bi-check2-circle');
                const miniBar = document.getElementById('miniSaveBar');
                const top = 70 + ((miniBar && miniBar.classList.contains('visible')) ? miniBar.offsetHeight : 0);
                showToast('Cambios guardados correctamente.', 'success', { offsetTop: top });
            }
        }

        // Click del botón global
        const guardarBtn = document.getElementById('guardarCambiosBtn');
        if (guardarBtn) guardarBtn.addEventListener('click', guardarCambiosMatriz);

        // Mostrar mini barra al hacer scroll
        const miniBar = document.getElementById('miniSaveBar');
        const threshold = 500; // altura a partir de la cual aparece
        window.addEventListener('scroll', () => {
            if (!miniBar) return;
            const shouldShow = window.pageYOffset > threshold;
            miniBar.classList.toggle('visible', shouldShow);
        });

        // Función para filtrar competencias (agrupadas por rowspan)
        function filtrarCompetencias() {
            const searchValue = document.getElementById('searchCompetencias').value.toLowerCase();
            const rows = document.querySelectorAll('#excelGrid tbody tr');
            let visibleByComp = new Map();

            rows.forEach(row => {
                const nombre = (row.dataset.compNombre || '').toLowerCase();
                const codigo = (row.dataset.compCodigo || '').toLowerCase();
                const match = !searchValue || codigo.includes(searchValue) || nombre.includes(searchValue);
                const key = `${codigo}|${nombre}`;
                if (!visibleByComp.has(key)) visibleByComp.set(key, match);
                // Ocultar/mostrar fila individual según el match del grupo
                row.style.display = match ? '' : 'none';
            });

            // Actualizar contador si existe
            const statBadge = document.querySelector('.stat-badge');
            if (statBadge) {
                const total = Array.from(visibleByComp.keys()).length;
                const visibles = Array.from(visibleByComp.values()).filter(Boolean).length;
                const clipIcon = `<i class=\"bi bi-clipboard\" aria-hidden=\"true\"></i>`;
                statBadge.innerHTML = searchValue ? `${clipIcon} ${visibles} de ${total} Competencias` : `${clipIcon} ${total} Competencias`;
            }
        }

        // Función para filtrar por trimestre
        function filtrarPorTrimestre() {
            const trimestreSeleccionado = document.getElementById('trimestreFilter').value;
            const competenciasData = @json($competencias);
            const rows = document.querySelectorAll('table tbody tr');
            
            if (!trimestreSeleccionado) {
                // Mostrar todas las competencias
                rows.forEach(row => row.style.display = '');
                return;
            }
            
            // Filtrar competencias que tienen al menos un resultado en el trimestre seleccionado
            rows.forEach(row => {
                const codigoComp = row.getAttribute('data-competencia-codigo');
                const competencia = competenciasData.find(c => c.cod_comp == codigoComp);
                
                if (competencia) {
                    const tieneResultadoEnTrimestre = competencia.resultados.some(r => r.trim_prog == trimestreSeleccionado);
                    row.style.display = tieneResultadoEnTrimestre ? '' : 'none';
                }
            });
        }

        // ===== Modal de confirmación centrado =====
        const programaInfo = {!! json_encode(['codigo' => $programa->id_prog, 'nombre' => $programa->nombre]) !!};

        function ensureConfirmModalDom() {
            let overlay = document.getElementById('confirmOverlay');
            if (overlay) return overlay;
            const tpl = `
                <div id="confirmOverlay" class="confirm-overlay" role="dialog" aria-modal="true">
                    <div class="confirm-modal">
                        <div class="header">
                            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                            <div class="title">Aviso</div>
                        </div>
                        <div class="message"></div>
                        <div class="program-meta"></div>
                        <div class="actions">
                            <button type="button" class="btn btn-danger" data-action="cancel">Corregir</button>
                            <button type="button" class="btn" data-action="confirm">Continuar</button>
                        </div>
                    </div>
                </div>`;
            const div = document.createElement('div');
            div.innerHTML = tpl.trim();
            document.body.appendChild(div.firstChild);
            return document.getElementById('confirmOverlay');
        }

        function showConfirmModal(opts) {
            const overlay = ensureConfirmModalDom();
            const titleEl = overlay.querySelector('.title');
            const msgEl = overlay.querySelector('.message');
            const metaEl = overlay.querySelector('.program-meta');
            const btnCancel = overlay.querySelector('[data-action="cancel"]');
            const btnConfirm = overlay.querySelector('[data-action="confirm"]');
            if (opts.title) titleEl.textContent = opts.title;
            msgEl.textContent = opts.message || '';
            metaEl.textContent = opts.meta || `Programa: ${programaInfo.nombre} (${programaInfo.codigo})`;
            btnCancel.textContent = opts.cancelText || 'Cancelar';
            btnConfirm.textContent = opts.confirmText || 'Aceptar';

            overlay.classList.add('show');
            return new Promise((resolve) => {
                const cleanup = () => {
                    overlay.classList.remove('show');
                    btnCancel.removeEventListener('click', onCancel);
                    btnConfirm.removeEventListener('click', onConfirm);
                    overlay.removeEventListener('click', onBackdrop);
                    document.removeEventListener('keydown', onKey);
                };
                const onCancel = () => { cleanup(); resolve(false); };
                const onConfirm = () => { cleanup(); resolve(true); };
                const onBackdrop = (ev) => { if (ev.target === overlay) { onCancel(); } };
                const onKey = (ev) => { if (ev.key === 'Escape') { onCancel(); } };
                btnCancel.addEventListener('click', onCancel);
                btnConfirm.addEventListener('click', onConfirm);
                overlay.addEventListener('click', onBackdrop);
                document.addEventListener('keydown', onKey);
            });
        }
    </script>
@endsection

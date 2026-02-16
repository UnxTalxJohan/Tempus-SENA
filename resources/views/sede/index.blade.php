@extends('layouts.app')

@section('title', 'Sedes y ambientes')

@section('content')
<div class="content-wrapper">
    <div class="container">
        <main>
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:18px; flex-wrap:wrap;">
                <div>
                    <h1 style="font-size:24px; font-weight:700; margin:0;">Sedes y ambientes</h1>
                    <p style="margin:4px 0 0; opacity:.8;">Panel para consultar las sedes del CIDE y sus ambientes de formación.</p>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="{{ route('sede.upload') }}" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding-inline:16px;">
                        <i class="bi bi-file-earmark-arrow-up" aria-hidden="true" style="font-size:16px;"></i>
                        <span>Cargar sedes y ambientes</span>
                    </a>
                    <button type="button" id="btn_registrar_lugar" class="btn btn-secondary" style="display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding-inline:16px;">
                        <i class="bi bi-geo-alt" aria-hidden="true" style="font-size:16px;"></i>
                        <span>Registrar lugar</span>
                    </button>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success">
                <span>{{ session('success') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-error">
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <div style="margin-bottom:14px; max-width:420px;">
                <label for="busqueda_sede" style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Buscar sede</label>
                <div style="position:relative;">
                    <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:14px;">
                        <i class="bi bi-search"></i>
                    </span>
                    <input id="busqueda_sede" type="text" placeholder="Escribe el nombre o parte del nombre" autocomplete="off"
                        style="width:100%; padding:8px 10px 8px 32px; border-radius:999px; border:1px solid #d1d5db; font-size:13px;">
                </div>
                <div style="margin-top:6px; display:flex; align-items:center; gap:6px; font-size:12px; color:#4b5563;">
                    <input type="checkbox" id="filtro_solo_con_ambientes" style="width:14px; height:14px; cursor:pointer;">
                    <label for="filtro_solo_con_ambientes" style="margin:0; cursor:pointer;">Mostrar solo sedes que tienen ambientes registrados</label>
                </div>
                <p id="busqueda_sede_mensaje" style="margin:4px 0 0; font-size:12px; color:#6b7280; display:none;">No se encontraron sedes que coincidan con el filtro.</p>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width:60px; text-align:center;">#</th>
                            <th>Sede</th>
                            <th style="width:140px; text-align:center;">Ambientes registrados</th>
                        </tr>
                    </thead>
                    <tbody id="tabla_sedes_body">
                        @forelse($sedes as $index => $sede)
                            @php($ambientes = $detalle[$sede->cod_sede] ?? collect())
                            <tr class="fila-sede"
                                data-nombre="{{ Str::slug($sede->nom_sede, ' ') }}"
                                data-sede-nombre="{{ $sede->nom_sede }}"
                                data-total-ambientes="{{ $sede->total_ambientes }}"
                                data-ambientes="{{ e($ambientes->pluck('denominacion')->filter()->values()->implode('||')) }}">
                                <td style="text-align:center;">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $sede->nom_sede }}</strong>
                                    <div style="margin-top:6px; font-size:12px; color:#4b5563;">
                                        @if($ambientes->count() > 0)
                                            <span style="font-weight:600;">Ambientes:</span>
                                            <span>
                                                {{ $ambientes->pluck('denominacion')->implode(' • ') }}
                                            </span>
                                        @else
                                            <span style="opacity:.8;">Sin ambiente por el momento.</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align:center;">{{ $sede->total_ambientes }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center; padding:14px; opacity:.8;">Aún no hay sedes registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-8EO9QABoVsNv8zZ6YBv1fZqv4RBzG3oho5Tg1v7F6ik=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Búsqueda y filtros de sedes
        var inputBusqueda = document.getElementById('busqueda_sede');
        var filas = Array.prototype.slice.call(document.querySelectorAll('.fila-sede'));
        var mensajeBusqueda = document.getElementById('busqueda_sede_mensaje');
        var chkSoloConAmbientes = document.getElementById('filtro_solo_con_ambientes');

        function normalizarTexto(texto) {
            var t = (texto || '').toString().toLowerCase();
            if (typeof t.normalize === 'function') {
                t = t.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }
            return t;
        }

        function coincideDifuso(nombre, termino) {
            if (!termino) return true;
            // Coincidencia directa
            if (nombre.indexOf(termino) !== -1) return true;
            // Coincidencia aproximada: las letras del término aparecen en orden en el nombre
            var i = 0, j = 0;
            while (i < nombre.length && j < termino.length) {
                if (nombre[i] === termino[j]) {
                    j++;
                }
                i++;
            }
            return j === termino.length;
        }

        function aplicarFiltros() {
            if (!filas.length) return;

            var termino = normalizarTexto(inputBusqueda ? inputBusqueda.value.trim() : '');
            var soloConAmbientes = chkSoloConAmbientes && chkSoloConAmbientes.checked;
            var encontrados = 0;

            filas.forEach(function (fila) {
                var nombre = normalizarTexto(fila.getAttribute('data-nombre'));
                var total = parseInt(fila.getAttribute('data-total-ambientes') || '0', 10);

                var coincideNombre = coincideDifuso(nombre, termino);
                var cumpleAmbientes = !soloConAmbientes || total > 0;

                if (coincideNombre && cumpleAmbientes) {
                    fila.style.display = '';
                    encontrados++;
                } else {
                    fila.style.display = 'none';
                }
            });

            if (mensajeBusqueda) {
                var hayFiltro = (termino && termino.length > 0) || soloConAmbientes;
                mensajeBusqueda.style.display = (hayFiltro && encontrados === 0) ? 'block' : 'none';
            }
        }

        if (inputBusqueda) {
            inputBusqueda.addEventListener('input', aplicarFiltros);
        }
        if (chkSoloConAmbientes) {
            chkSoloConAmbientes.addEventListener('change', aplicarFiltros);
        }

        aplicarFiltros();

        if (!filas.length) {
            return; // nada más que hacer si no hay filas
        }

        // Panel modal para ambientes
        var overlay = document.createElement('div');
        overlay.id = 'overlay_ambientes_sede';
        overlay.style.position = 'fixed';
        overlay.style.inset = '0';
        overlay.style.background = 'rgba(0,0,0,0.35)';
        overlay.style.display = 'none';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.zIndex = '60';

        var modal = document.createElement('div');
        modal.style.background = '#ffffff';
        modal.style.borderRadius = '14px';
        modal.style.boxShadow = '0 18px 45px rgba(15,23,42,0.25)';
        modal.style.maxWidth = '640px';
        modal.style.width = '100%';
        modal.style.margin = '0 16px';
        modal.style.maxHeight = '80vh';
        modal.style.display = 'flex';
        modal.style.flexDirection = 'column';

        modal.innerHTML = `
            <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; gap:8px;">
                <div>
                    <div style="font-size:13px; text-transform:uppercase; letter-spacing:.08em; color:#6b7280;">Sede</div>
                    <h2 id="modal_sede_titulo" style="font-size:18px; margin:2px 0 0; font-weight:700; color:#111827;"></h2>
                </div>
                <button type="button" id="modal_sede_cerrar" style="border:none; background:transparent; cursor:pointer; padding:4px; border-radius:999px;">
                    <span style="font-size:18px; line-height:1; color:#6b7280;">&times;</span>
                </button>
            </div>
            <div style="padding:14px 18px; overflow:auto;">
                <p id="modal_sede_subtitulo" style="font-size:13px; color:#6b7280; margin:0 0 10px;">Ambientes de formación asociados a la sede.</p>
                <ul id="modal_sede_lista" style="list-style:none; padding:0; margin:0; font-size:14px; color:#111827;"></ul>
                <p id="modal_sede_vacio" style="font-size:13px; color:#6b7280; margin:0; display:none;">Esta sede aún no tiene ambientes registrados.</p>
            </div>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        function abrirModalSede(nombreSede, ambientesCadena) {
            var titulo = document.getElementById('modal_sede_titulo');
            var lista = document.getElementById('modal_sede_lista');
            var vacio = document.getElementById('modal_sede_vacio');

            if (titulo) titulo.textContent = nombreSede || '';

            if (lista) {
                lista.innerHTML = '';
                var items = (ambientesCadena || '').split('||').filter(function (v) { return v && v.trim() !== ''; });
                if (items.length === 0) {
                    if (vacio) vacio.style.display = 'block';
                } else {
                    if (vacio) vacio.style.display = 'none';
                    items.forEach(function (texto, index) {
                        var li = document.createElement('li');
                        li.style.padding = '6px 0';

                        var card = document.createElement('div');
                        card.style.display = 'flex';
                        card.style.alignItems = 'center';
                        card.style.gap = '10px';
                        card.style.padding = '8px 10px';
                        card.style.borderRadius = '10px';
                        card.style.border = '1px solid #e5e7eb';
                        card.style.background = '#f9fafb';

                        var badge = document.createElement('span');
                        badge.textContent = index + 1;
                        badge.style.minWidth = '26px';
                        badge.style.height = '26px';
                        badge.style.borderRadius = '999px';
                        badge.style.background = '#00A859';
                        badge.style.color = '#ffffff';
                        badge.style.display = 'flex';
                        badge.style.alignItems = 'center';
                        badge.style.justifyContent = 'center';
                        badge.style.fontSize = '12px';
                        badge.style.fontWeight = '700';

                        var nombre = document.createElement('span');
                        nombre.textContent = texto;
                        nombre.style.flex = '1';
                        nombre.style.fontSize = '14px';
                        nombre.style.color = '#111827';

                        card.appendChild(badge);
                        card.appendChild(nombre);
                        li.appendChild(card);
                        lista.appendChild(li);
                    });
                }
            }

            overlay.style.display = 'flex';
        }

        function cerrarModalSede() {
            overlay.style.display = 'none';
        }

        var btnCerrar = document.getElementById('modal_sede_cerrar');
        if (btnCerrar) {
            btnCerrar.addEventListener('click', cerrarModalSede);
        }
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) cerrarModalSede();
        });

        filas.forEach(function (fila) {
            fila.style.cursor = 'pointer';
            fila.addEventListener('click', function () {
                var nombreSede = this.getAttribute('data-sede-nombre');
                var ambientes = this.getAttribute('data-ambientes');
                abrirModalSede(nombreSede, ambientes);
            });
        });

        // === Panel "Registrar lugar" con mapa ===
        var btnRegistrarLugar = document.getElementById('btn_registrar_lugar');
        var overlayLugar = null;
        var lugarMap = null;
        var lugarMarker = null;

        function crearOverlayLugar() {
            if (overlayLugar) return overlayLugar;
            overlayLugar = document.createElement('div');
            overlayLugar.id = 'overlay_registrar_lugar';
            overlayLugar.style.position = 'fixed';
            overlayLugar.style.inset = '0';
            overlayLugar.style.background = 'rgba(0,0,0,0.35)';
            overlayLugar.style.display = 'none';
            overlayLugar.style.alignItems = 'center';
            overlayLugar.style.justifyContent = 'center';
            overlayLugar.style.zIndex = '70';

            overlayLugar.innerHTML = `
                <div style="background:#ffffff; border-radius:16px; max-width:960px; width:100%; margin:0 16px; box-shadow:0 22px 60px rgba(15,23,42,0.4); display:flex; flex-direction:column; max-height:90vh;">
                    <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; gap:8px;">
                        <div>
                            <div style="font-size:13px; text-transform:uppercase; letter-spacing:.08em; color:#6b7280;">Ubicación</div>
                            <h2 style="font-size:18px; margin:2px 0 0; font-weight:700; color:#111827;">Registrar lugar</h2>
                        </div>
                        <button type="button" data-rl-close style="border:none; background:transparent; cursor:pointer; padding:4px; border-radius:999px;">
                            <span style="font-size:20px; line-height:1; color:#6b7280;">&times;</span>
                        </button>
                    </div>
                    <div style="padding:14px 18px; overflow:auto;">
                        <div style="display:flex; flex-wrap:wrap; gap:16px;">
                            <div style="flex:1 1 320px; min-width:280px;">
                                <label for="rl_busqueda" style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Buscar dirección en el mapa</label>
                                <div style="display:flex; gap:6px; margin-bottom:8px;">
                                    <input id="rl_busqueda" type="text" placeholder="Ejemplo: Calle 10 #20-30, Bogotá" style="flex:1; padding:6px 8px; border-radius:999px; border:1px solid #d1d5db; font-size:13px;">
                                    <button type="button" id="rl_btn_buscar" class="btn" style="padding:6px 12px; font-size:13px; border-radius:999px;">
                                        <i class="bi bi-search" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div id="rl_resultados" style="font-size:12px; color:#4b5563; margin-bottom:8px;"></div>
                                <div id="rl_mapa" style="height:260px; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;"></div>
                            </div>
                            <div style="flex:1 1 320px; min-width:280px;">
                                <form id="rl_form" method="POST" action="{{ route('sede.store.lugar') }}">
                                    @csrf
                                    <div style="margin-bottom:10px;">
                                        <label for="rl_nombre_sede" style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Nombre de la sede</label>
                                        <input id="rl_nombre_sede" name="nombre_sede" type="text" placeholder="Ej: CIDE Sibaté" required style="width:100%; padding:8px 10px; border-radius:10px; border:1px solid #d1d5db; font-size:13px;">
                                    </div>
                                    <div style="margin-bottom:10px;">
                                        <label for="rl_direccion" style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Dirección seleccionada</label>
                                        <input id="rl_direccion" name="direccion" type="text" placeholder="Se llenará al elegir un punto en el mapa" style="width:100%; padding:8px 10px; border-radius:10px; border:1px solid #d1d5db; font-size:13px;">
                                    </div>
                                    <div style="margin-bottom:10px;">
                                        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Ambientes de la sede</label>
                                        <div id="rl_ambientes_container" style="display:flex; flex-direction:column; gap:6px;">
                                            <input type="text" name="ambientes[]" placeholder="Ambiente 1" style="width:100%; padding:6px 10px; border-radius:10px; border:1px solid #d1d5db; font-size:13px;">
                                        </div>
                                        <button type="button" id="rl_add_ambiente" class="btn btn-secondary" style="margin-top:6px; padding:4px 10px; font-size:12px; border-radius:999px;">
                                            <i class="bi bi-plus-circle" aria-hidden="true"></i> Agregar ambiente
                                        </button>
                                    </div>
                                    <div style="margin-top:12px; display:flex; justify-content:flex-end; gap:8px;">
                                        <button type="button" data-rl-close class="btn btn-secondary" style="border-radius:999px; padding-inline:14px;">Cancelar</button>
                                        <button type="submit" class="btn btn-primary" style="border-radius:999px; padding-inline:18px;">Registrar sede</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(overlayLugar);

            overlayLugar.addEventListener('click', function (ev) {
                if (ev.target === overlayLugar || ev.target.hasAttribute('data-rl-close')) {
                    overlayLugar.style.display = 'none';
                }
            });

            document.addEventListener('keydown', function (ev) {
                if (ev.key === 'Escape' && overlayLugar && overlayLugar.style.display === 'flex') {
                    overlayLugar.style.display = 'none';
                }
            });

            setTimeout(function () {
                if (!lugarMap) {
                    lugarMap = L.map('rl_mapa').setView([4.60971, -74.08175], 11);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(lugarMap);
                }
                lugarMap.invalidateSize();
            }, 100);

            var inputBusqueda = document.getElementById('rl_busqueda');
            var btnBuscar = document.getElementById('rl_btn_buscar');
            var resultadosDiv = document.getElementById('rl_resultados');
            var inputDireccion = document.getElementById('rl_direccion');
            var btnAddAmb = document.getElementById('rl_add_ambiente');
            var contAmb = document.getElementById('rl_ambientes_container');

            function colocarMarcador(lat, lon, label) {
                if (!lugarMap) return;
                if (lugarMarker) {
                    lugarMap.removeLayer(lugarMarker);
                }
                lugarMarker = L.marker([lat, lon]).addTo(lugarMap);
                lugarMap.setView([lat, lon], 16);
                if (inputDireccion && label) {
                    inputDireccion.value = label;
                }
            }

            function buscarDireccion() {
                var q = (inputBusqueda ? inputBusqueda.value.trim() : '');
                if (!q) {
                    resultadosDiv.textContent = 'Escribe una dirección para buscar.';
                    return;
                }
                resultadosDiv.textContent = 'Buscando...';
                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=5&q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!Array.isArray(data) || data.length === 0) {
                            resultadosDiv.textContent = 'No se encontraron resultados.';
                            return;
                        }
                        var html = '<div style="margin-bottom:4px;">Haz clic en un resultado para centrar el mapa:</div>';
                        html += '<ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:2px;">';
                        data.forEach(function (item) {
                            html += '<li><button type="button" data-lat="' + item.lat + '" data-lon="' + item.lon + '" data-label="' + (item.display_name || '').replace(/"/g,'&quot;') + '" style="width:100%; text-align:left; padding:4px 6px; border-radius:8px; border:1px solid #e5e7eb; background:#f9fafb; font-size:12px; cursor:pointer;">' + (item.display_name || '') + '</button></li>';
                        });
                        html += '</ul>';
                        resultadosDiv.innerHTML = html;

                        Array.prototype.slice.call(resultadosDiv.querySelectorAll('button[data-lat]')).forEach(function (btn) {
                            btn.addEventListener('click', function () {
                                var lat = parseFloat(this.getAttribute('data-lat'));
                                var lon = parseFloat(this.getAttribute('data-lon'));
                                var label = this.getAttribute('data-label');
                                colocarMarcador(lat, lon, label);
                            });
                        });
                    })
                    .catch(function () {
                        resultadosDiv.textContent = 'No se pudo completar la búsqueda.';
                    });
            }

            if (btnBuscar) {
                btnBuscar.addEventListener('click', buscarDireccion);
            }
            if (inputBusqueda) {
                inputBusqueda.addEventListener('keydown', function (ev) {
                    if (ev.key === 'Enter') {
                        ev.preventDefault();
                        buscarDireccion();
                    }
                });
            }

            if (btnAddAmb && contAmb) {
                btnAddAmb.addEventListener('click', function () {
                    var idx = contAmb.querySelectorAll('input[name="ambientes[]"]').length + 1;
                    var input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'ambientes[]';
                    input.placeholder = 'Ambiente ' + idx;
                    input.style.width = '100%';
                    input.style.padding = '6px 10px';
                    input.style.borderRadius = '10px';
                    input.style.border = '1px solid #d1d5db';
                    input.style.fontSize = '13px';
                    contAmb.appendChild(input);
                });
            }

            return overlayLugar;
        }

        function abrirOverlayLugar() {
            var ov = crearOverlayLugar();
            if (ov) {
                ov.style.display = 'flex';
                setTimeout(function () {
                    if (lugarMap) {
                        lugarMap.invalidateSize();
                    }
                }, 100);
            }
        }

        if (btnRegistrarLugar) {
            btnRegistrarLugar.addEventListener('click', abrirOverlayLugar);
        }
    });
</script>
@endsection

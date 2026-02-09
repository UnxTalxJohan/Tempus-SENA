@extends('layouts.app')

@section('title', 'Gestión de usuarios')

@section('content')
<div class="content-wrapper">
    <div class="container">
        <main>
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:18px; flex-wrap:wrap;">
                <div style="min-width:220px;">
                    <h1 style="font-size:24px; font-weight:700; margin:0;">Gestión de usuarios</h1>
                @if(session('error'))
                <div class="alert alert-error">
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @if(session('error_details'))
                <div class="alert alert-warning" style="margin-bottom:16px; max-height:160px; overflow:auto;">
                    <strong>Advertencias al procesar el consolidado:</strong>
                    <ul style="margin:6px 0 0 18px;">
                        @foreach(session('error_details') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                    <p style="margin:6px 0 0; opacity:.8;">Lista de usuarios registrados en el sistema y acceso al consolidado de contratistas.</p>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                    <div style="position:relative;">
                        <select id="user-role-filter" style="padding:8px 28px 8px 10px; border-radius:999px; border:1px solid #d0d0d0; min-width:190px; font-size:13px; appearance:none; background:#fff;">
                            <option value="">Todos</option>
                            <option value="2">Contrato</option>
                            <option value="3">Planta</option>
                        </select>
                        <span class="bi bi-filter" aria-hidden="true" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:14px; opacity:.7;"></span>
                    </div>
                    <div style="position:relative;">
                        <input id="user-search-input" type="text" placeholder="Buscar por nombre, documento o especialidad" style="padding:8px 30px 8px 10px; border-radius:999px; border:1px solid #d0d0d0; min-width:260px; font-size:13px;">
                        <span class="bi bi-search" aria-hidden="true" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:14px; opacity:.7;"></span>
                    </div>
                    <a href="{{ route('usuarios.contratistas.form') }}" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding-inline:16px;">
                        <i class="bi bi-file-earmark-arrow-up" aria-hidden="true" style="font-size:16px;"></i>
                        <span>Subir contratistas</span>
                    </a>
                    <a href="{{ route('usuarios.titulada.form') }}" class="btn btn-secondary" style="display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding-inline:16px; background:#ffffff; color:#0b7c25; border:1px solid #0b7c25;">
                        <i class="bi bi-mortarboard-fill" aria-hidden="true" style="font-size:16px;"></i>
                        <span>Subir consolidado planta</span>
                    </a>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success">
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width:60px; text-align:center;">#</th>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Coordinacion</th>
                            <th style="width:90px; text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr data-user-row="1" data-rol-id="{{ $usuario->id_rol_fk ?? '' }}">
                                <td style="text-align:center;">{{ $loop->iteration }}</td>
                                <td><strong>{{ $usuario->cc }}</strong></td>
                                <td>{{ $usuario->nombre }}</td>
                                <td>{{ $usuario->correo }}</td>
                                <td>
                                    @if(($usuario->id_rol_fk ?? null) === 2)
                                        <span style="display:inline-block; padding:2px 8px; border-radius:999px; background:#e6fffa; color:#0b7c25; font-size:11px; font-weight:700;">Contrato</span>
                                    @else
                                        {{ $usuario->coord_pertenece ?? '' }}
                                        @if(($usuario->id_rol_fk ?? null) === 3)
                                            <span style="display:inline-block; margin-left:6px; padding:2px 8px; border-radius:999px; background:#fff3e6; color:#b45309; font-size:11px; font-weight:700;">Planta</span>
                                        @endif
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <button type="button" class="btn btn-secondary btn-sm btn-ver-usuario"
                                        data-cc="{{ $usuario->cc }}"
                                        data-nombre="{{ $usuario->nombre }}"
                                        data-correo="{{ $usuario->correo }}"
                                        data-rol="{{ $usuario->nombre_rol ?? 'Sin rol' }}"
                                        data-rol-id="{{ $usuario->id_rol_fk ?? '' }}"
                                        data-tip_vincul="{{ $usuario->tip_vincul ?? 'Contrato' }}"
                                        data-contrato="{{ $usuario->nmr_contrato ?? '' }}"
                                        data-nivel="{{ $usuario->nvl_formacion ?? '' }}"
                                        data-pregrado="{{ $usuario->pregrado ?? '' }}"
                                        data-postgrado="{{ $usuario->postgrado ?? '' }}"
                                        data-coord="{{ $usuario->coord_pertenece ?? '' }}"
                                        data-modalidad="{{ $usuario->modalidad ?? '' }}"
                                        data-especialidad="{{ $usuario->especialidad ?? '' }}"
                                        data-fch_ini="{{ $usuario->fch_inic_contrato ?? '' }}"
                                        data-fch_fin="{{ $usuario->fch_fin_contrato ?? '' }}"
                                        data-area="{{ $usuario->area ?? '' }}"
                                        data-estudios="{{ $usuario->estudios ?? '' }}"
                                        data-red="{{ $usuario->red ?? '' }}">
                                        Ver
                                    </button>
                                    <span style="display:none;" class="user-extra-text">{{ $usuario->nvl_formacion }} {{ $usuario->especialidad }} {{ $usuario->coord_pertenece }} {{ $usuario->modalidad }} {{ $usuario->pregrado }} {{ $usuario->postgrado }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:14px; opacity:.8;">No hay usuarios registrados todavía.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Modal de detalle de usuario -->
            <div id="user-detail-backdrop" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.35); z-index:9998; align-items:center; justify-content:center;">
                <div id="user-detail-modal" style="background:#fff; border-radius:16px; max-width:820px; width:100%; box-shadow:0 18px 45px rgba(0,0,0,.28); padding:22px 28px 24px; position:relative; max-height:85vh; overflow:auto; border-top:5px solid #0b7c25;">
                    <button type="button" id="user-detail-close" aria-label="Cerrar" style="position:absolute; top:14px; right:18px; border:none; background:#0b7c25; color:#fff; width:32px; height:32px; border-radius:999px; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:700; cursor:pointer; box-shadow:0 3px 8px rgba(0,0,0,.28); transition:background .15s ease, transform .15s ease;" onmouseover="this.style.background='#0fa040'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='#0b7c25'; this.style.transform='scale(1)';">&times;</button>
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                        <img src="{{ asset('images/logo-sena.svg') }}" alt="SENA" style="width:42px; height:auto; flex-shrink:0;">
                        <div>
                            <h2 style="font-size:22px; font-weight:800; margin:0 0 2px; color:#0b7c25;">Detalle del usuario</h2>
                            <p id="user-detail-subtitle" style="margin:0; font-size:15px; font-weight:600;"></p>
                        </div>
                    </div>

                    <!-- Sección: Datos básicos -->
                    <div id="ud-section-basic" style="margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid #e5e5e5;">
                        <div style="font-size:13px; font-weight:700; color:#0b7c25; margin-bottom:6px;">Datos básicos</div>
                        <div style="display:grid; grid-template-columns:repeat(2, minmax(260px, 1fr)); gap:10px 24px; font-size:13px; align-items:flex-end;">
                            <div>
                                <div style="font-weight:600; opacity:.75;">Documento</div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span id="ud-cc" style="font-weight:700; font-size:15px;"></span>
                                    <button type="button" class="btn btn-secondary btn-xs" data-copy-target="ud-cc" style="border-radius:999px; padding:3px 10px; font-size:11px; line-height:1; background:#0b7c25; color:#fff; border:1px solid #0b7c25; box-shadow:0 1px 3px rgba(0,0,0,.25);">Copiar</button>
                                </div>
                            </div>
                            <div id="ud-row-correo" style="margin-top:10px;">
                                <div style="font-weight:600; opacity:.75;">Correo</div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span id="ud-correo" style="font-size:14px; border-bottom:2px solid #0b7c25; padding-bottom:1px;"></span>
                                    <button type="button" class="btn btn-secondary btn-xs" data-copy-target="ud-correo" style="border-radius:999px; padding:3px 10px; font-size:11px; line-height:1; background:#0b7c25; color:#fff; border:1px solid #0b7c25; box-shadow:0 1px 3px rgba(0,0,0,.25);">Copiar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Vinculación -->
                    <div id="ud-section-vinculacion" style="margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid #e5e5e5;">
                        <div style="font-size:13px; font-weight:700; color:#0b7c25; margin-bottom:6px;">Vinculación</div>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:10px 24px; font-size:13px; align-items:flex-start;">
                            <div>
                                <div style="font-weight:600; opacity:.75;">Tipo de vinculación</div>
                                <div id="ud-tip_vincul" style="font-size:12px; display:inline-block; padding:2px 10px; border-radius:999px; background:#e6fffa; color:#0b7c25; font-weight:700; margin-top:2px;"></div>
                            </div>
                            <div>
                                <div style="font-weight:600; opacity:.75;">Coordinación a la que pertenece</div>
                                <div id="ud-coord" style="font-size:12px; display:inline-block; padding:2px 10px; border-radius:999px; background:#e6fffa; color:#0b7c25; font-weight:700; margin-top:2px;"></div>
                            </div>
                            <div>
                                <div style="font-weight:600; opacity:.75;">N° de contrato</div>
                                <div style="display:flex; flex-direction:column; align-items:flex-start; gap:4px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span id="ud-contrato" style="font-size:14px;"></span>
                                        <button type="button" class="btn btn-secondary btn-xs" data-copy-target="ud-contrato" style="border-radius:999px; padding:3px 10px; font-size:11px; line-height:1; background:#0b7c25; color:#fff; border:1px solid #0b7c25; box-shadow:0 1px 3px rgba(0,0,0,.25);">Copiar</button>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div style="font-weight:600; opacity:.75;">Modalidad</div>
                                <div id="ud-modalidad" style="font-size:12px; display:inline-block; padding:2px 10px; border-radius:999px; background:#e6fffa; color:#0b7c25; font-weight:700; margin-top:2px;"></div>
                            </div>
                            <div style="min-width:220px;">
                                <div style="background:#e6fffa; border-radius:10px; padding:10px 14px; border:1px solid #0b7c25;">
                                    <div style="font-size:12px; font-weight:700; color:#0b7c25; margin-bottom:4px;">Vigencia del contrato</div>
                                    <div style="font-size:13px;"><span style="font-weight:600;">Inicio:</span> <span id="ud-fch_ini"></span></div>
                                    <div style="font-size:13px; margin-top:2px;"><span style="font-weight:600;">Fin:</span> <span id="ud-fch_fin"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Formación -->
                    <div id="ud-section-formacion" style="margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid #e5e5e5;">
                        <div style="font-size:13px; font-weight:700; color:#0b7c25; margin-bottom:6px;">Formación</div>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:10px 24px; font-size:13px; align-items:flex-start;">
                            <div>
                                <div style="font-weight:600; opacity:.75;">Nivel de formación</div>
                                <div id="ud-nivel" style="font-size:14px; font-weight:600;"></div>
                            </div>
                            <div>
                                <div style="font-weight:600; opacity:.75;">Pregrado</div>
                                <div id="ud-pregrado" style="font-size:14px; line-height:1.4; margin-bottom:6px;"></div>
                                <div style="font-weight:600; opacity:.75; margin-top:4px;">Postgrado</div>
                                <div id="ud-postgrado" style="font-size:14px; line-height:1.4;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Especialidad se muestra como parte de formación para contratistas -->
                    <div id="ud-section-especialidad" style="margin-top:10px;">
                        <div style="font-size:13px; font-weight:700; color:#0b7c25; margin-bottom:4px;">Especialidad / área en el CIDE</div>
                        <div id="ud-especialidad" style="font-size:14px; line-height:1.4;"></div>
                    </div>

                    <!-- Sección: Área y estudios (titulada) -->
                    <div id="ud-section-area" style="margin-top:14px; display:none;">
                        <div style="font-size:13px; font-weight:700; color:#0b7c25; margin-bottom:6px;">Área y estudios</div>
                        <div style="display:grid; grid-template-columns:repeat(2, minmax(220px, 1fr)); gap:10px 24px; font-size:13px; align-items:flex-start;">
                            <div>
                                <div style="font-weight:600; opacity:.75;">Área</div>
                                <div id="ud-area" style="font-size:14px; line-height:1.4;"></div>
                            </div>
                            <div>
                                <div style="font-weight:600; opacity:.75;">Estudios</div>
                                <div id="ud-estudios" style="font-size:14px; line-height:1.4;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                (function() {
                    const input = document.getElementById('user-search-input');
                    const roleFilter = document.getElementById('user-role-filter');
                    const rows = Array.from(document.querySelectorAll('tbody tr[data-user-row="1"]'));

                    function applyFilters() {
                        const term = (input ? input.value : '').toLowerCase().trim();
                        const roleVal = roleFilter ? roleFilter.value : '';

                        rows.forEach(function(row) {
                            const text = row.innerText.toLowerCase();
                            const rowRole = row.getAttribute('data-rol-id') || '';
                            const matchesText = !term || text.includes(term);
                            const matchesRole = !roleVal || rowRole === roleVal;
                            row.style.display = (matchesText && matchesRole) ? '' : 'none';
                        });
                    }

                    if (input) {
                        input.addEventListener('input', applyFilters);
                    }

                    if (roleFilter) {
                        roleFilter.addEventListener('change', applyFilters);
                    }

                    const backdrop = document.getElementById('user-detail-backdrop');
                    const closeBtn = document.getElementById('user-detail-close');
                    const subtitle = document.getElementById('user-detail-subtitle');

                    function setText(id, value) {
                        const el = document.getElementById(id);
                        if (!el) return;
                        el.textContent = value && value.toString().trim() !== '' ? value : 'No registra';
                    }

                    function setTextOrHide(id, value) {
                        const el = document.getElementById(id);
                        if (!el) return false;
                        const hasValue = value && value.toString().trim() !== '';
                        el.textContent = hasValue ? value : '';
                        return !!hasValue;
                    }

                    function setSectionVisible(id, visible) {
                        const el = document.getElementById(id);
                        if (!el) return;
                        el.style.display = visible ? '' : 'none';
                    }

                    function copyFrom(targetId) {
                        const el = document.getElementById(targetId);
                        if (!el) return;
                        const text = (el.textContent || '').trim();
                        if (!text) return;

                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text).catch(function() {});
                        } else {
                            const ta = document.createElement('textarea');
                            ta.value = text;
                            document.body.appendChild(ta);
                            ta.select();
                            try { document.execCommand('copy'); } catch (e) {}
                            document.body.removeChild(ta);
                        }
                    }

                    function openModal(btn) {
                        const nombre = btn.getAttribute('data-nombre') || '';
                        const rolId = btn.getAttribute('data-rol-id') || '';
                        const isTitulada = rolId === '3';
                        subtitle.textContent = nombre ? nombre : '';

                        setText('ud-cc', btn.getAttribute('data-cc'));

                        if (isTitulada) {
                            const hasArea = setTextOrHide('ud-area', btn.getAttribute('data-area'));
                            const hasEst = setTextOrHide('ud-estudios', btn.getAttribute('data-estudios'));
                            setSectionVisible('ud-section-area', hasArea || hasEst);
                            setSectionVisible('ud-section-vinculacion', false);
                            setSectionVisible('ud-section-formacion', false);
                            setSectionVisible('ud-section-especialidad', false);
                            setSectionVisible('ud-row-correo', false);
                        } else {
                            setSectionVisible('ud-section-area', false);
                            setSectionVisible('ud-section-vinculacion', true);
                            setSectionVisible('ud-section-formacion', true);
                            setSectionVisible('ud-section-especialidad', true);
                            setSectionVisible('ud-row-correo', true);

                            setText('ud-correo', btn.getAttribute('data-correo'));
                            setText('ud-rol', btn.getAttribute('data-rol'));
                            setText('ud-tip_vincul', btn.getAttribute('data-tip_vincul'));
                            setText('ud-contrato', btn.getAttribute('data-contrato'));
                            setText('ud-nivel', btn.getAttribute('data-nivel'));
                            setText('ud-especialidad', btn.getAttribute('data-especialidad'));
                            setText('ud-coord', btn.getAttribute('data-coord'));
                            setText('ud-modalidad', btn.getAttribute('data-modalidad'));
                            setText('ud-pregrado', btn.getAttribute('data-pregrado'));
                            setText('ud-postgrado', btn.getAttribute('data-postgrado'));
                            setText('ud-red', btn.getAttribute('data-red'));
                            setText('ud-fch_ini', btn.getAttribute('data-fch_ini'));
                            setText('ud-fch_fin', btn.getAttribute('data-fch_fin'));
                        }

                        if (backdrop) {
                            backdrop.style.display = 'flex';
                        }
                    }

                    function closeModal() {
                        if (backdrop) {
                            backdrop.style.display = 'none';
                        }
                    }

                    document.addEventListener('click', function(e) {
                        const btn = e.target.closest('.btn-ver-usuario');
                        if (btn) {
                            e.preventDefault();
                            openModal(btn);
                            return;
                        }

                        const copyBtn = e.target.closest('[data-copy-target]');
                        if (copyBtn) {
                            e.preventDefault();
                            const targetId = copyBtn.getAttribute('data-copy-target');
                            if (targetId) {
                                copyFrom(targetId);
                            }
                        }
                    });

                    if (closeBtn) {
                        closeBtn.addEventListener('click', function() {
                            closeModal();
                        });
                    }

                    if (backdrop) {
                        backdrop.addEventListener('click', function(e) {
                            if (e.target === backdrop) {
                                closeModal();
                            }
                        });
                    }
                })();
            </script>
        </main>
    </div>
</div>
@endsection

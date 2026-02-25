@extends('layouts.app')

@section('content')

    <!-- Header solo de concepto para esta vista -->
    <header class="login-header">
        <div class="login-header-inner">
            {{-- Header "invisible": sin texto de marca, solo menú arriba a la derecha --}}
            <span class="login-header-logo" aria-hidden="true"></span>
            <nav class="login-header-nav">
                <a href="#">Inicio</a>
                <a href="#">Ayuda</a>
            </nav>
        </div>
    </header>

    <div class="login-bg">
        <div class="login-wrapper">
            <div class="login-green-bar" id="loginGreenBar">
                <div class="login-green-brand">
                    <img src="{{ asset('images/logo-sena.svg') }}" alt="Logo SENA" class="login-green-logo">
                    {{-- Texto TEMPUS eliminado para que no aparezca sobre el fondo --}}
                    <div class="login-green-title" aria-hidden="true"></div>
                </div>
            </div>

            <!-- Ícono decorativo a la izquierda del login (carrusel de SVG) -->
            <div class="login-leaf-graphic" aria-hidden="true">
                <!-- Ícono inicial: hoja; luego el JS va rotando entre todos los SVG (incluido el bombillo) -->
                <img src="https://www.svgrepo.com/show/195611/leaf.svg" alt="" class="login-leaf-img">
            </div>
            <div class="login-card" id="loginCardRef">
                <h2 class="login-title">Iniciar sesión</h2>
                <form method="POST" action="{{ route('login.post') }}" class="login-form">
                    @csrf
                    <div class="login-field">
                        <label for="email">Correo o usuario</label>
                        <input type="text" id="email" name="email" class="login-input" required autofocus placeholder="tucorreo@ejemplo.com o tu usuario">
                    </div>
                    <div class="login-field">
                        <label for="password">Contraseña</label>
                        <div class="login-password-wrap">
                            <input type="password" id="password" name="password" class="login-input login-input-password" required placeholder="Tu contraseña">
                            <span class="login-show" aria-label="Mostrar contraseña" onclick="togglePassword()" title="Mostrar/ocultar">
                                <svg id="eyeIcon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <g id="eyeOpen">
                                        <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle id="eyeBall" cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                                    </g>
                                    <g id="eyeClosed" style="opacity:0;">
                                        <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <line x1="5" y1="19" x2="19" y2="5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </g>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="login-remember">
                        <a href="#" class="login-link" onclick="forgotPassword(event)">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="login-btn">Entrar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Ola blanca inferior (solo decorativa) -->
    <div class="login-wave-bottom" aria-hidden="true">
        <svg class="login-wave-svg" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <!-- Path dinámico generado por JS para la ola -->
            <path id="loginWaveDynamicPath" fill="#ffffff" fill-opacity="1"></path>
        </svg>
    </div>

    <!-- Capa SVG para burbujas blancas del footer solo en login -->
    <svg id="loginBubblesSvg" class="login-bubbles-svg" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"></svg>

    <!-- Footer solo de concepto para esta vista (sin texto visible) -->
    <footer class="login-footer">
        <div class="login-footer-inner">
            <span class="login-footer-brand" aria-hidden="true"></span>
        </div>
    </footer>

<style>
/* Fuente más amigable para el login */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* Fondo general: deja que el degradado lo controle body.login-page desde el CSS global */
html, body {
    min-height: 100vh;
    height: auto;
    margin: 0;
    background: transparent;
}

/* Header y footer translúcidos (concepto) */
.login-header,
.login-footer {
    position: fixed;
    left: 0;
    right: 0;
    top: 0;
    z-index: 20;
    padding: 14px 3vw;
    color: #ffffff;
    background: transparent;
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
    pointer-events: none; /* que no bloquee clics sobre el fondo */
}

.login-header-inner,
.login-footer-inner {
    display: flex;
    align-items: center;
    justify-content: flex-end; /* empuja el nav hacia la derecha */
    max-width: 100%; /* usar todo el ancho disponible para llegar más al borde */
    margin: 0 auto;
}

.login-header-logo {
    display: none; /* ya no mostramos marca de texto en el login */
}

.login-header-nav {
    display: flex;
    gap: 18px;
    pointer-events: auto; /* el menú sí debe ser clickeable */
}

.login-header-nav a {
    color: #ffffff;
    text-decoration: none;
    font-size: 0.95rem;
}

.login-footer {
    position: relative;
    padding-top: 0;
    font-size: 0.9rem;
}

/* Ola blanca en la parte inferior, similar al ejemplo de referencia */
.login-wave-bottom {
    position: fixed;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 220px;
    pointer-events: none;
    z-index: 2;
}

.login-wave-svg {
    width: 100%;
    height: 100%;
    display: block;
}

/* Animación suave tipo "mar" para la curva blanca */
.login-wave-svg path {
    /* Brillo suave similar al de las burbujas */
    filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.8));
}

/* Barra verde animada detrás del formulario */
.login-green-bar {
    position: absolute;
    left: 4%;
    top: 50%;
    width: 0;
    height: 100%;
    background: rgba(43, 245, 124, 0.08);
    border-radius: 22px 0 0 22px;
    z-index: 2;
    box-shadow: 0 4px 24px rgba(68, 230, 114, 0.13);
    transition: width 0.55s cubic-bezier(.24,1.16,.24,1), background 0.3s;
    opacity: 1;
    transform: translate(-100%, -50%);
    backdrop-filter: blur(10px) saturate(1.5);
    -webkit-backdrop-filter: blur(10px) saturate(1.5);
    border: 1.5px solid rgba(98, 235, 137, 0.18);
}
.login-green-brand {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.login-green-logo {
    width: 92px;
    max-width: 70%;
    height: auto;
    filter: brightness(0) invert(1) drop-shadow(0 3px 8px rgba(0,0,0,0.35));
}
.login-green-title {
    margin-top: 10px;
    font-weight: 800;
    font-size: 1.08rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #ffffff;
}
.login-green-title::before,
.login-green-title::after {
    content: '';
    display: block;
    margin: 4px auto 0;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(255,255,255,0.1), rgba(255,255,255,0.9), rgba(255,255,255,0.1));
}
.login-green-title::before {
    width: 78%;
    height: 2px;
}
.login-green-title::after {
    width: 46%;
    height: 1px;
    opacity: 0.9;
    margin-top: 3px;
}
.login-leaf-graphic {
    position: absolute;
    left: -215%;
    top: 40%;
    /* 20px extra hacia la izquierda respecto a la posición original */
    transform: translate(calc(-50% - 20px), -30%);
    z-index: 3;
}
.login-leaf-img {
    width: 220px;
    height: auto;
    filter: brightness(0) invert(1);
    opacity: 0.9;
    transition: opacity 0.3s ease;
}
.login-bg {
    /* Ocupa casi toda la ventana, dejando espacio para header y footer */
    min-height: calc(100vh - 140px);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 6vw;
    /* Fondo transparente: usa el degradado definido en body */
    background: transparent;
    position: relative;
    z-index: 1;
}
.login-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}
.login-card {
    /* Efecto cristal: blanco translúcido con desenfoque */
    background: rgba(255, 255, 255, 0.16) !important;
    backdrop-filter: blur(18px) saturate(1.4);
    -webkit-backdrop-filter: blur(18px) saturate(1.4);
    border-radius: 22px;
    box-shadow: 0 18px 45px 0 rgba(0,0,0,0.26);
    padding: 46px 42px 38px 42px;
    min-width: 380px;
    max-width: 430px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    opacity: 1;
    position: relative;
    z-index: 11;
}
/* Aplicar la nueva fuente en todo el recuadro de login */
.login-card,
.login-card * {
    font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
.login-title {
    font-size: 1.7rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 18px;
    text-align: center;
}
.login-form {
    width: 100%;
}
.login-field {
    margin-bottom: 16px;
}
.login-field label {
    display: block;
    margin-bottom: 6px;
    color: #ffffff;
    font-weight: 600;
}
.login-input {
    width: 100%;
    padding: 12px 14px;
    border-radius: 32px;
    border: 2px solid rgba(255,255,255,0.45);
    background: rgba(255,255,255,0.16);
    outline: none;
    transition: box-shadow .15s, border-color .15s;
    font-size: 1rem;
    color: #ffffff;
    box-sizing: border-box;
}
.login-input-password {
    padding-right: 44px;
    transition: background 0.25s;
}
.login-input:focus {
    border-color: rgba(255,255,255,0.9);
    box-shadow: 0 8px 20px rgba(0,0,0,0.32);
    background: rgba(255,255,255,0.22);
}
.login-password-wrap {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
}
.login-show {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 4px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background 0.18s, box-shadow 0.18s;
    z-index: 2;
}
.login-show:hover {
    background: transparent;
    box-shadow: none;
}
.login-show svg {
    transition: stroke 0.2s, filter 0.2s;
    color: #ffffff;
    cursor: pointer;
}
.login-show svg #eyeBall {
    transition: all 0.25s cubic-bezier(.4,2,.6,1);
}
.login-show {
    background: transparent;
    border: none;
    padding: 6px;
    border-radius: 8px;
    cursor: pointer;
}
.login-remember {
    text-align: left;
    margin-bottom: 10px;
}
.login-link {
    color: #ffffff;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.98rem;
}

/* Placeholders en blanco suave para inputs del login */
.login-input::placeholder {
    color: rgba(255, 255, 255, 0.75);
}
.login-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(180deg,#0fa234,#0b7c25);
    color: #fff;
    border-radius: 16px;
    border: none;
    font-weight: 700;
    margin-top: 8px;
    box-shadow: 0 6px 18px rgba(11,124,37,0.18);
    cursor: pointer;
    font-size: 1.1rem;
    transition: background 0.22s, box-shadow 0.22s, transform 0.13s;
    outline: none;
}
.login-btn:hover, .login-btn:focus {
    background: linear-gradient(180deg,#13c13d,#0fa234);
    box-shadow: 0 10px 28px rgba(11,124,37,0.22);
    transform: translateY(-2px) scale(1.03);
}
.login-btn:active {
    background: linear-gradient(180deg,#0b7c25,#0fa234);
    box-shadow: 0 2px 8px rgba(11,124,37,0.13);
    transform: scale(0.98);
}
@media (max-width: 600px) {
    .login-card { padding: 18px 6vw; min-width: 0; max-width: 98vw; }
    .login-green-bar { max-width: 98vw; }
	.login-bg { justify-content: center; padding-right: 0; }
}
</style>

<script>
// Animación barra verde detrás del login
document.addEventListener('DOMContentLoaded', () => {
    const bar = document.getElementById('loginGreenBar');
    if(bar) {
        setTimeout(() => {
              bar.style.width = '260px';
        }, 120);
    }
});
function forgotPassword(e){
    e.preventDefault();
    const email = document.getElementById('email').value;
    if(!email){ alert('Ingresa tu correo arriba.'); return; }
    fetch('/api/password/forgot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
    }).then(r => r.json()).then(() => {
        alert('Si el correo existe, se generó un enlace en los logs.');
    }).catch(() => alert('Error solicitando recuperación.'));
}

function togglePassword(){
    const input = document.getElementById('password');
    const eye = document.getElementById('eyeIcon');
    const eyeOpen = eye.querySelector('#eyeOpen');
    const eyeClosed = eye.querySelector('#eyeClosed');
    if(input.type === 'password'){
        input.type = 'text';
        input.classList.add('showing');
        if(eyeOpen && eyeClosed){
            eyeOpen.style.opacity = 0;
            eyeClosed.style.opacity = 1;
        }
    } else {
        input.type = 'password';
        input.classList.remove('showing');
        if(eyeOpen && eyeClosed){
            eyeOpen.style.opacity = 1;
            eyeClosed.style.opacity = 0;
        }
    }
    // animación suave
    input.style.transition = 'background 0.25s';
    input.style.background = input.type === 'text'
        ? 'rgba(255,255,255,0.24)'
        : 'rgba(255,255,255,0.16)';
    setTimeout(() => {
        input.style.background = 'rgba(255,255,255,0.16)';
    }, 250);
}

// Carrusel simple para el ícono decorativo (hoja / trabajador / bombillo)
document.addEventListener('DOMContentLoaded', () => {
    const leafImg = document.querySelector('.login-leaf-img');
    if (!leafImg) return;

    const icons = [
        'https://www.svgrepo.com/show/195611/leaf.svg',
        'https://www.svgrepo.com/show/115899/industry-worker-with-cap-protection-and-a-laptop.svg',
        'https://www.svgrepo.com/show/347782/light-bulb.svg',
        'https://www.svgrepo.com/show/479989/building-2.svg',
        'https://www.svgrepo.com/show/237885/cross-faith.svg'
    ];

    let iconIndex = 0;

    const swapIcon = () => {
        iconIndex = (iconIndex + 1) % icons.length;
        leafImg.style.opacity = '0';
        setTimeout(() => {
            leafImg.src = icons[iconIndex];
            leafImg.style.opacity = '0.9';
        }, 200);
    };

    setInterval(swapIcon, 5000);
});

// === Fondo login: capa izquierda transparente (deja ver solo el degradado del body) ===
document.addEventListener('DOMContentLoaded', () => {
    const left = document.getElementById('bgcLeft');
    if (!left) return;

    left.style.backgroundImage = 'none';
    left.style.backgroundColor = 'transparent';
    left.style.filter = 'none';
    left.style.opacity = '1';
});

// Burbujas blancas pequeñas saliendo del footer en el login
document.addEventListener('DOMContentLoaded', () => {
    const svg = document.getElementById('loginBubblesSvg');
    if (!svg) return;

    const NS = 'http://www.w3.org/2000/svg';
    let width = window.innerWidth;
    const height = 220; // igual a .login-wave-bottom

    function resize() {
        width = window.innerWidth;
        svg.setAttribute('width', String(width));
        svg.setAttribute('height', String(height));
    }

    resize();
    window.addEventListener('resize', resize);

    const particles = [];

    function rand(min, max) {
        return Math.random() * (max - min) + min;
    }

    function spawnParticles(x, y, r) {
        const count = Math.round(rand(12, 20));
        for (let i = 0; i < count; i++) {
            const angle = rand(0, Math.PI * 2);
            // Un poco más de velocidad para que la animación se vea más rápida
            const speed = rand(0.7, 1.3);
            const vx = Math.cos(angle) * speed;
            const vy = Math.sin(angle) * speed;

            const startX = x + Math.cos(angle) * (r + 1);
            const startY = y + Math.sin(angle) * (r + 1);
            // Líneas cortas y claramente proporcionales al tamaño de la burbuja
            const length = rand(r * 0.25, r * 0.6);
            const endX = startX + Math.cos(angle) * length;
            const endY = startY + Math.sin(angle) * length;

            const line = document.createElementNS(NS, 'line');
            line.classList.add('login-bubble-particle');
            line.setAttribute('x1', startX);
            line.setAttribute('y1', startY);
            line.setAttribute('x2', endX);
            line.setAttribute('y2', endY);
            svg.appendChild(line);

            particles.push({
                el: line,
                x1: startX,
                y1: startY,
                x2: endX,
                y2: endY,
                vx,
                vy,
                life: 0,
                // Vida aún más corta para que la explosión sea más rápida
                maxLife: rand(10, 20)
            });
        }
    }

    function createBubble() {
        // Burbujas más pequeñas, con algo más de variación de tamaño
        const r = rand(4, 14);
        const x = rand(r, width - r);
        // Más arriba: cerca del borde donde empieza la curva blanca
        const baseY = height - 80;

        const circle = document.createElementNS(NS, 'circle');
        circle.classList.add('login-bubble');
        circle.setAttribute('r', r);
        circle.setAttribute('cx', x);
        circle.setAttribute('cy', baseY);
        svg.appendChild(circle);

        // Explosión de partículas cerca del final de la animación (1.5s),
        // en la zona donde la burbuja desaparece visualmente
        setTimeout(() => {
            // "60" coincide con el translateY(-60px) del keyframe login-bubble-rise
            spawnParticles(x, baseY - 60, r);
        }, 1400);

        // Retirar la burbuja después de completar su animación
        setTimeout(() => {
            if (circle.parentNode === svg) svg.removeChild(circle);
        }, 1700);
    }

    function tick() {
        for (let i = particles.length - 1; i >= 0; i--) {
            const p = particles[i];
            // Paso moderado: explosión rápida pero en radio contenido
            p.x1 += p.vx * 0.8;
            p.y1 += p.vy * 0.8;
            p.x2 += p.vx * 1.1;
            p.y2 += p.vy * 1.1;
            p.life += 1;
            const t = p.life / p.maxLife;
            if (t >= 1) {
                if (p.el.parentNode === svg) {
                    svg.removeChild(p.el);
                }
                particles.splice(i, 1);
                continue;
            }
            p.el.setAttribute('x1', p.x1);
            p.el.setAttribute('y1', p.y1);
            p.el.setAttribute('x2', p.x2);
            p.el.setAttribute('y2', p.y2);
            p.el.setAttribute('opacity', String(1 - t));
        }
        requestAnimationFrame(tick);
    }

    tick();

    // Control de creación de burbujas con pausa cuando la pestaña no está visible
    let bubbleInterval = null;

    function startBubbles() {
        if (bubbleInterval) return;
        bubbleInterval = setInterval(() => {
            createBubble();
            if (Math.random() < 0.6) {
                createBubble();
            }
        }, 480);
    }

    function stopBubbles() {
        if (!bubbleInterval) return;
        clearInterval(bubbleInterval);
        bubbleInterval = null;
    }

    // Iniciar solo cuando la pestaña está visible
    if (!document.hidden) {
        startBubbles();
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopBubbles();
        } else {
            startBubbles();
        }
    });
});

// Ola dinámica generada por SVG: varias ondas que se desplazan de derecha a izquierda
document.addEventListener('DOMContentLoaded', () => {
    const svg = document.querySelector('.login-wave-svg');
    const path = document.getElementById('loginWaveDynamicPath');
    if (!svg || !path) return;

    let width = svg.clientWidth || window.innerWidth;
    const height = svg.clientHeight || 220;

    // Parámetros de la ola (se interpolan hacia objetivos para evitar saltos)
    // La bajamos un poco más y reducimos amplitud para no tapar las burbujas
    let baseHeight = height * 0.68; // línea media de la ola, más cerca del borde inferior
    let amplitude = 18;             // altura inicial de las crestas, más baja
    let waves = 1.8;                // cantidad de ondas visibles (actual)

    // Objetivos hacia los que interpolamos suavemente
    let targetBaseHeight = baseHeight;
    let targetAmplitude = amplitude;
    let targetWaves = waves;
    let phase = 0;                 // fase para desplazar la ola

    function resizeWave() {
        width = svg.clientWidth || window.innerWidth;
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    }

    resizeWave();
    window.addEventListener('resize', resizeWave);

    function randomizeParams() {
        // Definir nuevos objetivos: tamaños y cantidad de olas algo aleatorios, pero más bajos y más cerca del borde
        targetAmplitude = 10 + Math.random() * 10;   // 10–20 (olas más bajitas)
        targetWaves = 1.2 + Math.random() * 1.6;     // 1.2–2.8 ondas
        targetBaseHeight = height * (0.62 + Math.random() * 0.12); // 62–74% alto
    }

    randomizeParams();
    setInterval(randomizeParams, 6000);

    function buildWavePath() {
        const points = [];
        const segments = 80; // más segmentos = curva más suave

        // Frecuencia base y una frecuencia secundaria para variar tamaños/separación
        const baseFreq = (Math.PI * 2 * waves) / width;
        const freq2 = baseFreq * 2.3;
        const amp2 = amplitude * 0.4;

        for (let i = 0; i <= segments; i++) {
            const x = (width / segments) * i;
            // Combinación de dos senos para que unas olas sean más grandes y otras más pequeñas
            const y = baseHeight
                + Math.sin(baseFreq * x + phase) * amplitude
                + Math.sin(freq2 * x + phase * 1.7) * amp2;
            points.push({ x, y });
        }

        if (points.length < 2) return '';

        // Construir path tipo "ola" usando curvas cuadráticas para suavizar
        let d = `M 0 ${height} L ${points[0].x} ${points[0].y}`;

        for (let i = 0; i < points.length - 1; i++) {
            const p = points[i];
            const next = points[i + 1];
            const mx = (p.x + next.x) / 2;
            const my = (p.y + next.y) / 2;
            d += ` Q ${p.x} ${p.y} ${mx} ${my}`;
        }

        const last = points[points.length - 1];
        // Cerrar en la parte inferior derecha y volver a la izquierda sin crear un corte visible en la esquina
        d += ` L ${last.x} ${height} L 0 ${height} Z`;
        return d;
    }

    function animate() {
        // Interpolar suavemente hacia los objetivos para que los cambios no sean bruscos
        const lerpFactor = 0.02; // 2% por frame aprox.
        amplitude += (targetAmplitude - amplitude) * lerpFactor;
        waves += (targetWaves - waves) * lerpFactor;
        baseHeight += (targetBaseHeight - baseHeight) * lerpFactor;

        phase -= 0.03; // velocidad de desplazamiento (derecha→izquierda)
        const d = buildWavePath();
        if (d) path.setAttribute('d', d);
        requestAnimationFrame(animate);
    }

    animate();
});
</script>

@endsection

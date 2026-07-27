import { init } from 'https://esm.sh/pptx-preview@1.0.5';

/* ── Config ── */
const FILE_URL   = window.FILE_URL;
const MS_PPT_URL = window.MS_PPT_URL;

/* ── DOM ── */
const pptxLoading   = document.getElementById('pptxLoading');
const pptxWrapper   = document.getElementById('pptxWrapper');
const pptxStage     = document.getElementById('pptxStage');
const pptxNav       = document.getElementById('pptxNav');
const navInfo       = document.getElementById('navInfo');
const btnFirst      = document.getElementById('btnFirst');
const btnPrev       = document.getElementById('btnPrev');
const btnNext       = document.getElementById('btnNext');
const btnLast       = document.getElementById('btnLast');
const btnThumbs     = document.getElementById('btnThumbs');
const btnPresent    = document.getElementById('btnPresent');
const btnExitPresent= document.getElementById('btnExitPresent');
const btnOpenPpt    = document.getElementById('btnOpenPpt');

/* ── Estado ── */
let totalSlides = 0;
let current     = 1;
let presentMode = false;
let allSlideEls = [];  /* los divs que genera pptx-preview */

/* ══════════════════════════════
    INICIALIZAR pptx-preview
══════════════════════════════ */
const previewer = init(pptxWrapper, {
width:  960,
height: 540,
});

/* ══════════════════════════════
    CARGAR EL ARCHIVO
══════════════════════════════ */
try {
const response = await fetch(FILE_URL);
if (!response.ok) throw new Error('HTTP ' + response.status);
const arrayBuffer = await response.arrayBuffer();

await previewer.preview(arrayBuffer);

/* pptx-preview ya renderizó — recoger los slides generados */
onPreviewReady();

} catch (err) {
showError('No se pudo cargar la presentación.', err.message);
}

/* ══════════════════════════════
    TRAS RENDERIZAR
══════════════════════════════ */
function onPreviewReady() {
/* Los slides son los hijos directos de pptxWrapper */
allSlideEls = Array.from(pptxWrapper.children);
totalSlides = allSlideEls.length;

if (!totalSlides) {
    showError('La presentación no contiene diapositivas.');
    return;
}

/* Añadir índice data para navegación */
allSlideEls.forEach(function(el, i) {
    el.dataset.slideIdx = i;
});

pptxLoading.style.display = 'none';
pptxWrapper.style.display = '';
pptxNav.classList.add('show');

goTo(1);
}

/* ══════════════════════════════
    NAVEGACIÓN
══════════════════════════════ */
function goTo(n) {
n = Math.max(1, Math.min(totalSlides, n));
current = n;

if (presentMode) {
    /* En presentación solo mostramos el slide activo */
    allSlideEls.forEach(function(el, i) {
    el.classList.toggle('slide-active', i === n - 1);
    });
} else {
    /* En modo normal todos visibles, scroll al activo */
    allSlideEls.forEach(function(el) { el.style.display = ''; });
    const activeEl = allSlideEls[n - 1];
    if (activeEl) {
    activeEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

navInfo.textContent = n + ' / ' + totalSlides;
btnFirst.disabled = btnPrev.disabled = n <= 1;
btnNext.disabled  = btnLast.disabled = n >= totalSlides;
}

btnFirst.addEventListener('click', () => goTo(1));
btnPrev.addEventListener ('click', () => goTo(current - 1));
btnNext.addEventListener ('click', () => goTo(current + 1));
btnLast.addEventListener ('click', () => goTo(totalSlides));

/* Teclado */
document.addEventListener('keydown', function(e) {
if (!totalSlides) return;
if (e.key === 'ArrowRight' || e.key === ' ') { e.preventDefault(); goTo(current + 1); }
if (e.key === 'ArrowLeft')                    { e.preventDefault(); goTo(current - 1); }
if (e.key === 'Escape' && presentMode)         exitPresent();
if ((e.key === 'f' || e.key === 'F') && !e.ctrlKey) togglePresent();
});

/* ══════════════════════════════
    MODO: LISTA vs UNO A LA VEZ
══════════════════════════════ */
// let showAll = true;

// btnThumbs.addEventListener('click', function() {
// showAll = !showAll;
// btnThumbs.innerHTML = showAll
//     ? '<i class="bi bi-grid-3x2-gap"></i> Diapositivas'
//     : '<i class="bi bi-layout-sidebar"></i> Una a la vez';

// if (showAll) {
//     /* Mostrar todas */
//     allSlideEls.forEach(el => { el.style.display = ''; });
// } else {
//     /* Mostrar solo la actual */
//     allSlideEls.forEach((el, i) => {
//     el.style.display = i === current - 1 ? '' : 'none';
//     });
// }
// });

/* ══════════════════════════════
    MODO PRESENTACIÓN
══════════════════════════════ */
btnPresent.addEventListener    ('click', togglePresent);
btnExitPresent.addEventListener('click', exitPresent);

function togglePresent() {
presentMode = !presentMode;
document.body.classList.toggle('present-mode', presentMode);

if (presentMode) {
    btnPresent.innerHTML = '<i class="bi bi-fullscreen-exit"></i> Salir';
    allSlideEls.forEach((el, i) => {
    el.classList.toggle('slide-active', i === current - 1);
    });
} else {
    btnPresent.innerHTML = '<i class="bi bi-fullscreen"></i> Presentar';
    allSlideEls.forEach(el => {
    el.classList.remove('slide-active');
    el.style.display = '';
    });
}
}

function exitPresent() {
if (presentMode) togglePresent();
}

/* ══════════════════════════════
    BOTÓN ABRIR EN POWERPOINT
══════════════════════════════ */
setTimeout(() => btnOpenPpt?.classList.remove('pulsing'), 4000);

btnOpenPpt?.addEventListener('click', function() {
setTimeout(function() {
    if (document.visibilityState === 'visible') showToast();
}, 2500);
});

function showToast() {
if (document.getElementById('pptToast')) return;
const t = document.createElement('div');
t.id = 'pptToast';
t.innerHTML =
    '<span style="font-size:1.3rem">💡</span>' +
    '<span>¿No se abrió PowerPoint? Usa el botón ' +
    '<strong>"Descargar"</strong> y ábrelo desde tu carpeta de descargas.</span>' +
    '<button onclick="this.parentElement.remove()" ' +
    'style="background:none;border:none;color:rgba(255,255,255,.45);cursor:pointer;font-size:1.1rem;flex-shrink:0">✕</button>';
document.body.appendChild(t);
setTimeout(() => {
    t.style.transition = 'opacity .4s';
    t.style.opacity = '0';
    setTimeout(() => t.remove(), 500);
}, 6000);
}

/* ══════════════════════════════
    ERROR
══════════════════════════════ */
function showError(msg, detail) {
pptxLoading.style.display = 'none';
const div = document.createElement('div');
div.className = 'pptx-error';
div.innerHTML =
    '<span class="ei">⚠️</span>' +
    '<h3>' + msg + '</h3>' +
    (detail ? '<p style="font-size:.7rem;color:rgba(255,255,255,.2);margin-bottom:.5rem">' + detail + '</p>' : '') +
    '<p>Usa <strong>"Abrir en PowerPoint"</strong> para ver la presentación completa con fidelidad total.</p>' +
    '<div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">' +
    '<a href="' + MS_PPT_URL + '" class="emc-btn emc-btn-primary">' +
        '<i class="bi bi-play-circle-fill"></i> Abrir en PowerPoint' +
    '</a>' +
    '<a href="uploads/organigrama.pptx" download class="emc-btn emc-btn-outline">' +
        '<i class="bi bi-download"></i> Descargar' +
    '</a>' +
    '</div>';
pptxStage.appendChild(div);
}

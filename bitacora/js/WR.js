const num = v => { const n = parseFloat(String(v).replace(',', '.')); return isNaN(n) ? 0 : n; };
// suma celdas tipo "5+10" o "5 10"
const parseCell = v => String(v).split(/[+\s]+/).filter(Boolean).reduce((a, b) => a + num(b), 0);

// Turnos/Basura_Destino (para saber a donde se exporta cada cosa)/MIN_PESO_ROWS(Minimo para la generacion de filas en el peso de toallas y pañales)  para WasteReclaim.php
const TURNO = { t1: '1ero', t2: '2do', t3: '3ero' };
const BASURA_DESTINO = 'export_panal';
const MIN_PESO_ROWS = 2;

// ---------- totales por grupo (data-sum -> data-total) ----------
function totalPorGrupo() {
  const g = {};
  document.querySelectorAll('[data-sum]').forEach(i => {
    const key = i.dataset.sum; g[key] = (g[key] || 0) + num(i.value);
  });
  Object.entries(g).forEach(([k, v]) => {
    const dst = document.querySelector(`[data-total="${k}"]`);
    if (dst) dst.value = v ? +v.toFixed(2) : '';
  });
}

// ---------- helpers de totales por columna / gran total ----------
function sumarColumnasEn(attr, keys) {
  keys.forEach(c => {
    let s = 0;
    document.querySelectorAll(`[${attr}="${c}"]`).forEach(i => s += num(i.value));
    const d = document.querySelector(`[data-total="${c}"]`);
    if (d) d.value = s ? +s.toFixed(2) : '';
  });
}
function ajusteEn(key) {
  let s = 0;
  document.querySelectorAll(`[data-gtotal="${key}"]`).forEach(i => s += num(i.value));
  const d = document.querySelector(`[data-total="${key}"]`);
  if (d) d.value = s ? +s.toFixed(2) : '';
}

function columnasTotales() {
  sumarColumnasEn('data-col', ['disp1', 'disp2', 'disp3']);
  sumarColumnasEn('data-colkg', ['kg1', 'kg2', 'kg3']);
  sumarColumnasEn('data-colpct', ['pctr1', 'pctr2', 'pctr3']);
  sumarColumnasEn('data-colwrkg', ['wrkg1', 'wrkg2', 'wrkg3']);
  sumarColumnasEn('data-colwrpct', ['wrpct1', 'wrpct2', 'wrpct3']);
  ajusteEn('kgtot');
  ajusteEn('wrkgtot');
  ajusteEn('wrpcttot');
}

// ---------- Peso de bolsas -> total -> Merma Máquinas (W.R.) ----------
function pesoTotal() {
  document.querySelectorAll('tbody[data-peso]').forEach(tb => {
    const key = tb.dataset.peso;
    let s = 0;
    tb.querySelectorAll('[data-pesocell]').forEach(i => s += parseCell(i.value));
    const tot = document.querySelector(`[data-pesototal="${key}"]`);
    if (tot) tot.value = s ? +s.toFixed(2) : '';
    const merma = document.querySelector(`[data-wrmerma="${key}"]`);
    if (merma) merma.value = s ? +s.toFixed(2) : '';
  });
}

// ---------- SAM tolvas -> total del día ----------
function samDia() {
  let s = 0;
  document.querySelectorAll('[data-total^="samtolva_"]').forEach(i => s += num(i.value));
  const d = document.querySelector('[data-total="samdia"]'); if (d) d.value = s ? s : '';
}

// ---------- Pacas merma (W.R.) -> Tiempos ----------
function envMermaATiempos() {
  document.querySelectorAll('[data-mermapacas]').forEach(i => {
    const [tipo, t] = i.dataset.mermapacas.split('_');   // BASURA_t1
    const turno = TURNO[t];
    const val = i.value;
    if (tipo === 'RECORTE') {
      const a = document.querySelector(`[data-from-recorte="${turno}"][name^="pacas_locales"]`);
      const b = document.querySelector(`[data-from-recorte="${turno}"][name^="pacas_recorte"]`);
      if (a) a.value = val; if (b) b.value = val;
    } else if (tipo === 'BASURA') {
      const c = document.querySelector(`[data-from-basura="${turno}"][name^="${BASURA_DESTINO}"]`);
      if (c) c.value = val;
    }
  });
}

// ---------- Máquinas alimentadas (W.R.) KG y % -> Recuperado (Tiempos) ----------
function envMermaARecuperado() {
  document.querySelectorAll('[data-recup]').forEach(i => {
    const [eq, t, kind] = i.dataset.recup.split('|');   // bcm_1 | t1 | kg
    const turno = TURNO[t];
    const campo = kind === 'kg' ? 'recup_kg' : 'recup_pct';
    const dst = document.querySelector(`[name="${campo}[${eq}][${turno}]"]`);
    if (dst) dst.value = i.value;
  });
}

function recalc() {
  pesoTotal();
  envMermaARecuperado();
  envMermaATiempos();
  totalPorGrupo();
  columnasTotales();
  samDia();
}

document.addEventListener('input', e => { if (e.target.matches('input,select,textarea')) recalc(); });

// =================== Peso de bolsas: agregar / eliminar fila ===================
function addelPesos(tb) {
  tb.querySelectorAll('tr').forEach((tr, i) => {
    const c = tr.querySelector('td'); if (c) c.textContent = i + 1;
  });
}

document.addEventListener('click', e => {
  const add = e.target.closest('[data-add]');
  if (add) {
    const key = add.dataset.add, tipo = add.dataset.tipo, turno = add.dataset.turno;
    const tb = document.querySelector(`tbody[data-peso="${key}"]`);
    const n = tb.querySelectorAll('tr').length + 1;
    const tr = document.createElement('tr');
    tr.innerHTML = `<td class="text-center text-muted">${n}</td>`
      + `<td><input type="text" inputmode="decimal" name="peso[${tipo}][${turno}][]" `
      + `class="form-control form-control-sm text-center" data-pesocell="${key}" autocomplete="off"></td>`;
    tb.appendChild(tr);
    reEtiquetarCambio();
    return;
  }
  const del = e.target.closest('[data-del]');
  if (del) {
    const key = del.dataset.del;
    const tb = document.querySelector(`tbody[data-peso="${key}"]`);
    const rows = tb.querySelectorAll('tr');
    if (rows.length <= MIN_PESO_ROWS) return;
    rows[rows.length - 1].remove();
    addelPesos(tb);
    reEtiquetarCambio();
  }
});

// ---------- Presión vacía -> M.P. al salir del campo ----------
document.addEventListener('blur', e => {
  if (e.target.classList && e.target.classList.contains('js-presion') && e.target.value.trim() === '') {
    e.target.value = 'M.P.';
  }
}, true);

// =================== TURNO ACTIVO: enfoque + readonly ===================
const TURNO_INPUT = document.getElementById('turno_activo');
const TURNO_SPAN  = document.getElementById('turnoenctext');

function turnoDe(name) {
  if (/\[1ero\]|\[t1\]|\[1er\]/.test(name)) return 1;
  if (/\[2do\]|\[t2\]/.test(name))          return 2;
  if (/\[3ero\]|\[t3\]/.test(name))         return 3;
  const m = name.match(/^wr_conductor\[(\d)\]/);
  if (m) return +m[1];
  return 0;
}
function tagTurnos() {
  document.querySelectorAll('[name]').forEach(el => {
    if (el.dataset.t === undefined) el.dataset.t = turnoDe(el.getAttribute('name'));
  });
}
function turnoPorHora() {
  const h = new Date().getHours();
  if (h >= 6 && h < 14) return 1;
  if (h >= 14 && h < 22) return 2;
  return 3;
}
function activarTurno() {
  if (TURNO_SPAN) {
    const n = parseInt((TURNO_SPAN.textContent || '').trim(), 10);
    if (n >= 1 && n <= 3) return n;
  }
  if (TURNO_INPUT) {
    const n = parseInt(TURNO_INPUT.value, 10);
    if (n >= 1 && n <= 3) return n;
  }
  return turnoPorHora();
}
function dejarReadonlyCampos() {
  document.querySelectorAll('input, textarea, select').forEach(el => {
    if (el.dataset.roNative === undefined) {
      el.dataset.roNative = (el.readOnly || el.disabled) ? '1' : '0';
    }
  });
}
function aplicarReadonlyPorTurno(n) {
  document.querySelectorAll('[name]').forEach(el => {
    const t = +(el.dataset.t ?? turnoDe(el.getAttribute('name')));
    if (!(t >= 1 && t <= 3)) return;
    const lock = (t !== n);
    if (el.tagName === 'SELECT') {
      el.classList.toggle('locked', lock);
      el.style.pointerEvents = lock ? 'none' : '';
      el.tabIndex = lock ? -1 : 0;
    } else {
      if (el.dataset.roNative === '1') { el.readOnly = true; return; }
      el.readOnly = lock;
      el.classList.toggle('ro-turno', lock);
    }
  });
}
function colocarTurno(n) {
  document.body.dataset.turno = n;
  document.body.classList.add('enfocar');
  const pill = document.querySelector(`#tab4 [data-bs-target="#rep${n}"]`);
  if (pill && window.bootstrap) bootstrap.Tab.getOrCreateInstance(pill).show();
}
function aplicarActivarTurno() {
  const n = activarTurno();
  if (TURNO_INPUT && +TURNO_INPUT.value !== n) TURNO_INPUT.value = n;
  colocarTurno(n);
  aplicarReadonlyPorTurno(n);
}
function reEtiquetarCambio() {
  tagTurnos();
  dejarReadonlyCampos();
  aplicarReadonlyPorTurno(activarTurno());
  recalc();
}

// ---------- Init ----------
console.log('[Bitácora WR] JS cargado');
tagTurnos();
dejarReadonlyCampos();
aplicarActivarTurno();
recalc();

if (TURNO_SPAN) {
  new MutationObserver(aplicarActivarTurno)
    .observe(TURNO_SPAN, { childList: true, characterData: true, subtree: true });
}
if (TURNO_INPUT) {
  TURNO_INPUT.addEventListener('input', aplicarActivarTurno);
  TURNO_INPUT.addEventListener('change', aplicarActivarTurno);
}
let _lastTurno = activarTurno();
setInterval(() => {
  const t = activarTurno();
  if (t !== _lastTurno) { _lastTurno = t; aplicarActivarTurno(); }
}, 800);

// =================== Guardar bitácora (AJAX por folio) ===================
const GUARDAR_URL = '../bitacora/php/guardar.php';

function swalOk()  { return !!window.Swal; }

// Validaciones antes de agregar datos
async function guardarBitacora(btn) {
  const form = document.getElementById('bitacora');
  // 1.- Si no hay form
  if (!form) { console.error('[Guardar] No existe el form #bitacora'); return; }

  const folio = (document.getElementById('folio')?.value
              || document.getElementById('folioenctext')?.textContent || '').trim();
  const turno = activarTurno();

  console.group('%c[Bitácora WR] Guardar', 'color:#0b6dbf;font-weight:bold');
  console.log('Folio:', folio || '(vacío)', '| Turno:', turno, '| URL:', GUARDAR_URL);

  // 2.- Si no hay folio valido
  if (!folio) {
    console.warn('Cancelado: sin folio en el encabezado.');
    console.groupEnd();
    if (swalOk()) Swal.fire({ icon: 'warning', title: 'Sin folio', text: 'No se encontró el folio del turno en el encabezado.' });
    else alert('Sin folio: revisa el encabezado.');
    return;
  }

  // 3.- Busqueda de datos para rellenar en caso de faltar
  const faltas = validarTurno();
  if (faltas.length) {
    console.warn('Cancelado: faltan datos obligatorios:', faltas.map(f => f.msg));
    console.groupEnd();

    // Mostrar por pasos todos los elementos que hacen falta
    const tabNames = { tab1: '① Tiempos', tab2: '② Producciones W.R.', tab3: '③ Peso de bolsas', tab4: '④ Reporte' };
    if (swalOk()) {
      const porTab = {};
      faltas.forEach(f => { (porTab[f.tabId] = porTab[f.tabId] || []).push(f.msg); });
      const html = '<div style="text-align:left">' + Object.entries(porTab).map(([t, msgs]) =>
        `<div class="mb-1"><b>${tabNames[t]}</b><ul style="margin:.2rem 0 .5rem 1rem;padding:0">`
        + msgs.map(m => `<li>${m}</li>`).join('') + '</ul></div>'
      ).join('') + '</div>';
      // Sweet Alert para indicar aspectos faltantes y marcar en rojo los elementso que faltan por llenar asi como las pestañas
      Swal.fire({ icon: 'warning', title: 'Faltan datos obligatorios', html, confirmButtonText: 'Ir a corregir' })
        .then(() => marcarFaltas(faltas));
    } else {
      alert('Faltan datos obligatorios:\n' + faltas.map(f => '• ' + f.msg).join('\n'));
      marcarFaltas(faltas);
    }
    return;
  }
  // limpiar marcas de sombreado al momento de escribir en un campo
  limpiarMarcasFalta();

  const fd = new FormData(form);
  fd.set('folio', folio);
  fd.set('turno', turno);

  // log resumido de lo que se envía
  const resumen = {};
  for (const [k] of fd.entries()) resumen[k.split('[')[0]] = (resumen[k.split('[')[0]] || 0) + 1;
  console.log('Campos enviados (por grupo):', resumen);

  // Modal visual de guardado (progeso)
  if (swalOk()) Swal.fire({ title: 'Guardando…', text: `Folio ${folio} · turno ${turno}`, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
  if (btn) btn.disabled = true;

  try {
    const r = await fetch(GUARDAR_URL, { method: 'POST', body: fd });
    console.log('HTTP:', r.status, r.statusText);

    const raw = await r.text();
    console.log('Respuesta cruda del servidor:', raw);

    // Validacion de archivo principal faltante
    if (!r.ok) {
      throw new Error(`HTTP ${r.status} ${r.statusText} — Configuracion incorrecta de bitacora - Contacta al área de sistemas !`);
    }

    let data;
    try {
      data = JSON.parse(raw);
    } catch (parseErr) {
      // Error en formato de JSON
      console.error('La respuesta no es JSON válido:', parseErr);
      if (swalOk())
        Swal.fire({
          icon: 'error',
          title: 'Respuesta no válida',
          html: 'Configuracion incorrecta - Contacta al área de sistemas !'
        });
      else
        alert('Respuesta no válida del servidor.');
      return;
    }

    console.log('Respuesta JSON:', data);

    // Caso de que todo este bien
    if (data.ok) {
      const n = (data.insertados ?? '') !== '' ? ` · ${data.insertados} registros` : '';
      if (swalOk()) Swal.fire({ icon: 'success', title: 'Guardado', text: `Folio ${data.folio} · turno ${data.turno}${n}.` });
      else alert(`Guardado. Folio ${data.folio} · turno ${data.turno}${n}.`);
    } else {
      console.error('El servidor respondió error:', data.error);
      if (swalOk()) Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: data.error || 'Error desconocido.' });
      else alert('No se pudo guardar: ' + (data.error || 'Error desconocido.'));
    }
  } catch (e) {
    console.error('Error en el guardado:', e);
    if (swalOk()) Swal.fire({ icon: 'error', title: 'Error', text: e.message });
    else alert('Error: ' + e.message);
  } finally {
    if (btn) btn.disabled = false;
    console.groupEnd();
  }
}

// Boton de guardar turno
const _formBit = document.getElementById('bitacora');
if (_formBit) {
  _formBit.addEventListener('submit', (e) => {
    e.preventDefault();
    const btn = e.submitter || _formBit.querySelector('button[type="submit"]');
    guardarBitacora(btn);
  });
}
// Botón con id="btnGuardarBitacora" type="button"
document.getElementById('btnGuardarBitacora')?.addEventListener('click', () => guardarBitacora(document.getElementById('btnGuardarBitacora')));


// =================== SAM recuperado: columnas de valor dinámicas ===================
// Tabla #tablaSam con encabezado #samHead, en donde cada fila lleva data-sam-turno="<slug>"
function samSumaColumnas() {
  return document.querySelectorAll('#samHead .sam-col').length;
}

// Funcion de agregado de columnas
function samAgregaColumnas() {
  const head = document.getElementById('samHead');
  if (!head) return;
  const n = samSumaColumnas() + 1;
  const th = document.createElement('th');
  th.className = 'text-center sam-col';
  th.textContent = 'Valor ' + n;
  head.appendChild(th);
  document.querySelectorAll('#tablaSam tr[data-sam-turno]').forEach(tr => {
    const turno = tr.dataset.samTurno;
    const td = document.createElement('td');
    td.innerHTML = `<input type="text" name="sam_recuperado[${turno}][c${n}]" `
      + `class="form-control form-control-sm text-center" autocomplete="off">`;
    tr.appendChild(td);
  });
  reEtiquetarCambio();   // re-etiqueta turnos y reaplica readonly del turno activo
}
function samDelCol() {
  if (samSumaColumnas() <= 1) return;
  document.querySelector('#samHead .sam-col:last-of-type')?.remove();
  document.querySelectorAll('#tablaSam tr[data-sam-turno]').forEach(tr => {
    tr.querySelector('td:last-child')?.remove();
  });
  reEtiquetarCambio();
}
document.getElementById('samAgregaColumnas')?.addEventListener('click', samAgregaColumnas);
document.getElementById('samDelCol')?.addEventListener('click', samDelCol);


// =================== CARGAR bitácora por folio ===================
const CARGAR_URL = '../bitacora/php/cargar.php';
const PESO_BASE_ROWS = 5;
let _wrLastFolio = null;

// Limpia el formulario antes de cargar otro folio.
function limpiarWR() {
  const form = document.getElementById('bitacora');
  if (!form) return;
  // SAM -> 3 columnas base
  while (samSumaColumnas() > 3) samDelCol();
  // Peso -> filas base (5);
  document.querySelectorAll('tbody[data-peso]').forEach(tb => {
    let rows = tb.querySelectorAll('tr');
    while (rows.length > PESO_BASE_ROWS) { rows[rows.length - 1].remove(); rows = tb.querySelectorAll('tr'); }
  });
  // valores -> default del HTML ('' o 'M.P.' en presión); selects -> primera opción
  form.querySelectorAll('input, textarea').forEach(el => {
    if (el.type === 'hidden') return;
    el.value = el.defaultValue;
  });
  form.querySelectorAll('select').forEach(s => { s.selectedIndex = 0; });
}

// Asegura N columnas de SAM y coloca los valores del turno (índice 0 = Valor 1).
function colocarValoresSam(turno, values) {
  while (samSumaColumnas() < values.length) samAgregaColumnas();
  const slugTurno = ['1ero', '2do', '3ero'][turno - 1];
  const tr = document.querySelector(`#tablaSam tr[data-sam-turno="${slugTurno}"]`);
  if (!tr) return;
  const cells = tr.querySelectorAll('input');
  values.forEach((v, i) => { if (cells[i]) cells[i].value = (v == null ? '' : v); });
}

// Asegura filas de Peso para (tipo, turno) y coloca los valores en orden.
function colocarColPeso(tipo, tShort, values) {
  const key = `${tipo}_${tShort}`;
  const tb = document.querySelector(`tbody[data-peso="${key}"]`);
  if (!tb) return;
  let rows = tb.querySelectorAll('tr');
  while (rows.length < values.length) {
    const n = rows.length + 1;
    const tr = document.createElement('tr');
    tr.innerHTML = `<td class="text-center text-muted">${n}</td>`
      + `<td><input type="text" inputmode="decimal" name="peso[${tipo}][${tShort}][]" `
      + `class="form-control form-control-sm text-center" data-pesocell="${key}" autocomplete="off"></td>`;
    tb.appendChild(tr);
    rows = tb.querySelectorAll('tr');
  }
  const inputs = tb.querySelectorAll('[data-pesocell]');
  inputs.forEach((inp, i) => { inp.value = (values[i] == null ? '' : values[i]); });
}

async function cargarBitacoraWR(folio) {
  folio = (folio
        || document.getElementById('folio')?.value
        || document.getElementById('folioenctext')?.textContent || '').toString().trim();
  if (!folio) return;

  console.group('%c[Bitácora WR] Cargar', 'color:#0b6dbf;font-weight:bold');
  console.log('Folio:', folio, '| URL:', CARGAR_URL);

  try {
    const r = await fetch(`${CARGAR_URL}?folio=${encodeURIComponent(folio)}`);
    const raw = await r.text();
    let data;
    try { data = JSON.parse(raw); }
    catch (e) { console.error('Respuesta no JSON:', raw); console.groupEnd(); return; }

    if (!data.ok) {
      console.warn('Sin datos / error:', data.error);
      // folio nuevo sin datos -> formulario en blanco
      limpiarWR();
      _wrLastFolio = folio;
      tagTurnos(); dejarReadonlyCampos(); aplicarActivarTurno(); recalc();
      console.groupEnd();
      return;
    }

    limpiarWR();

    // SAM (columnas variables) y Peso (filas variables) del turno
    if (Array.isArray(data.sam)) colocarValoresSam(data.turno, data.sam);
    const tShort = ['t1', 't2', 't3'][data.turno - 1];
    ['PANAL', 'TOALLA'].forEach(tp => {
      colocarColPeso(tp, tShort, (data.peso && data.peso[tp]) ? data.peso[tp] : []);
    });

    // Campos simples: nombre exacto -> valor
    const form = document.getElementById('bitacora');
    let aplicados = 0, sinControl = 0;
    Object.entries(data.fields || {}).forEach(([name, val]) => {
      const el = form.querySelector(`[name="${name}"]`);
      if (!el) { sinControl++; return; }
      // null/0/vacío manejados
      el.value = (val == null ? '' : val);
      aplicados++;
    });
    console.log(`Campos aplicados: ${aplicados}`, sinControl ? `(sin control en DOM: ${sinControl})` : '');

    // Enfocar el turno del folio + recalcular totales/enlaces + readonly
    if (TURNO_INPUT) TURNO_INPUT.value = data.turno;
    colocarTurno(data.turno);
    tagTurnos();
    dejarReadonlyCampos();
    // recalcula totales y enlaces W.R. -> Tiempos/Recuperado
    recalc();
    aplicarReadonlyPorTurno(data.turno);
    _wrLastFolio = folio;
    console.log(`OK · Folio ${data.folio} · turno ${data.turno}.`);
  } catch (e) {
    console.error('Error al cargar:', e);
  } finally {
    console.groupEnd();
  }
}

// Carga automática cuando cambia el folio (al moverse de día/turno con el modal).
function CargarWR() {
  const f = (document.getElementById('folio')?.value
          || document.getElementById('folioenctext')?.textContent || '').toString().trim();
  if (f && f !== _wrLastFolio) cargarBitacoraWR(f);
}
// El span del folio cambia vía textContent
const FOLIO_SPAN = document.getElementById('folioenctext');
if (FOLIO_SPAN) {
  new MutationObserver(CargarWR)
    .observe(FOLIO_SPAN, { childList: true, characterData: true, subtree: true });
}

setInterval(CargarWR, 800);
CargarWR();


// =================== Validación estricta del turno + guía visual ===================
//   Tab1: ≥1 equipo disponible · TODAS las áreas de orden y limpieza · nombre de inspeccionó
//   Tab2: ≥1 dato en máquinas alimentadas (kg/%) · ≥1 dato en pacas merma · nombre del conductor
//   Tab3: ≥1 cifra en Toalla y ≥1 cifra en Pañal
//   Tab4: todos los campos del reporte excepto Comentarios

// Inyeccion de estilos por unica visualizacion
(function inyectarWREstilos() {
  if (document.getElementById('wr-falta-styles')) return;
  const s = document.createElement('style');
  s.id = 'wr-falta-styles';
  s.textContent = `
    .wr-falta { outline:2px solid #dc3545 !important; outline-offset:1px;
                border-color:#dc3545 !important; background-color:#fff5f5 !important; }
    .nav-tabs .nav-link.wr-tab-falta { color:#dc3545 !important; font-weight:600; }
    .nav-tabs .nav-link.wr-tab-falta::after { content:" ●"; color:#dc3545; }
  `;
  document.head.appendChild(s);
})();

function validarTurno() {
  const n = activarTurno();
  // disponible / orden / inspeccionó
  const tLong  = ['1ero', '2do', '3ero'][n - 1];
  // wr_maq / wr_pacasmerma / peso / reporte
  const tShort = ['t1', 't2', 't3'][n - 1];
  // wr_conductor
  const tDigit = String(n);
  const form = document.getElementById('bitacora');
  const faltas = [];
  if (!form) return faltas;

  const val = el => (el && (el.value || '').trim()) || '';
  const algo = els => Array.from(els).some(e => val(e) !== '');
  const push = (tabId, msg, focusEl, highlightEls, pill) =>
    faltas.push({ tabId, msg, focusEl: focusEl || null, highlightEls: highlightEls || [], pill: pill || null });

  /* --- TAB 1 --- */
  const disp = form.querySelectorAll(`[name^="disponible["][name$="[${tLong}]"]`);
  if (!algo(disp)) push('tab1', 'Equipos disponibles: captura al menos un equipo.', disp[0], Array.from(disp));

  const ordenSel = form.querySelectorAll(`select[name^="orden["][name$="[${tLong}]"]`);
  const ordenVacios = Array.from(ordenSel).filter(s => val(s) === '');
  if (ordenVacios.length) push('tab1', `Orden y limpieza: falta marcar ${ordenVacios.length} área(s) (Limpio/Sucio).`, ordenVacios[0], ordenVacios);

  const insp = form.querySelector(`[name="inspecciono[${tLong}]"]`);
  if (insp && val(insp) === '') push('tab1', 'Orden y limpieza: falta el nombre de quien inspeccionó.', insp, [insp]);

  /* --- TAB 2 --- */
  const maq = form.querySelectorAll(`[name^="wr_maq["][name*="[${tShort}]["]`);
  if (!algo(maq)) push('tab2', 'Máquinas alimentadas: captura al menos un dato en KG / % del turno actual.', maq[0], Array.from(maq));

  const merma = form.querySelectorAll(`[name^="wr_pacasmerma["][name*="[${tShort}]["]`);
  if (!algo(merma)) push('tab2', 'Pacas merma: captura al menos un dato del turno.', merma[0], Array.from(merma));

  const cond = form.querySelector(`[name="wr_conductor[${tDigit}]"]`);
  if (cond && val(cond) === '') push('tab2', 'Notas y conductores: falta el nombre del conductor.', cond, [cond]);

  /* --- TAB 3 --- */
  [['TOALLA', 'Toalla'], ['PANAL', 'Pañal']].forEach(([tp, lbl]) => {
    const tb = form.querySelector(`tbody[data-peso="${tp}_${tShort}"]`);
    const cells = tb ? tb.querySelectorAll('[data-pesocell]') : [];
    if (!algo(cells)) push('tab3', `Peso de bolsas (${lbl}): captura al menos una cifra.`, cells[0], Array.from(cells));
  });

  /* --- TAB 4 --- */
  // No se integran los comentarios pues son opcionales
  const repReq = { operador: 'Operador', ayudante: 'Ayudante', linea_confort: 'Línea Confort', supervisor: 'Supervisor', trabajos_diversos: 'Trabajos Diversos'};
  const repFaltan = [], repEls = [];
  Object.entries(repReq).forEach(([k, lbl]) => {
    const el = form.querySelector(`[name="reporte[${tShort}][${k}]"]`);
    if (el && val(el) === '') {
      repFaltan.push(lbl); repEls.push(el);
    }
  });
  if (repFaltan.length) push('tab4', `Reporte de operación: falta ${repFaltan.join(', ')}.`, repEls[0], repEls, n);

  /* --- Numéricos con texto inválido (cualquier pestaña del turno activo) --- */
  const malos = [];
  form.querySelectorAll('input').forEach(el => {
    if (!esNumerico(el) || el.readOnly) return;
    if (el.classList.contains('ro-turno')) return;
    const v = (el.value || '').trim();
    if (v === '') return;
    if (!numericoValido(el, v)) malos.push(el);
  });
  if (malos.length) {
    const tabId = (malos[0].closest('.tab-pane')?.id) || 'tab1';
    push(tabId, `Hay ${malos.length} campo(s) numérico(s) con texto no válido (solo números).`, malos[0], malos);
  }

  return faltas;
}

function limpiarMarcasFalta() {
  document.querySelectorAll('.wr-falta').forEach(e => e.classList.remove('wr-falta'));
  document.querySelectorAll('.wr-tab-falta').forEach(e => e.classList.remove('wr-tab-falta'));
}

function actualizarMarcasTab() {
  ['tab1', 'tab2', 'tab3', 'tab4'].forEach(tabId => {
    const pane = document.getElementById(tabId);
    const btn = document.querySelector(`[data-bs-target="#${tabId}"]`);
    if (!pane || !btn) return;
    btn.classList.toggle('wr-tab-falta', !!pane.querySelector('.wr-falta'));
  });
}

function marcarFaltas(faltas) {
  limpiarMarcasFalta();
  faltas.forEach(f => f.highlightEls.forEach(e => e && e.classList.add('wr-falta')));
  actualizarMarcasTab();

  const first = faltas[0];
  if (!first) return;
  // ir a la pestaña del primer faltante
  const btn = document.querySelector(`[data-bs-target="#${first.tabId}"]`);
  if (btn && window.bootstrap) bootstrap.Tab.getOrCreateInstance(btn).show();
  // si es del reporte, además activar el pill del turno
  if (first.pill) {
    const pill = document.querySelector(`#tab4 [data-bs-target="#rep${first.pill}"]`);
    if (pill && window.bootstrap) bootstrap.Tab.getOrCreateInstance(pill).show();
  }
  // enfocar/scroll al primer campo faltante
  setTimeout(() => {
    if (first.focusEl) {
      first.focusEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      try { first.focusEl.focus({ preventScroll: true }); } catch (e) {}
    }
  }, 250);
}

// Al escribir/seleccionar en un campo marcado, se le quita el rojo y se reevalúa su pestaña.
['input', 'change'].forEach(ev => {
  document.addEventListener(ev, e => {
    if (e.target.classList && e.target.classList.contains('wr-falta')) {
      e.target.classList.remove('wr-falta');
      actualizarMarcasTab();
    }
  });
});


// =================== Campos numéricos: filtro al teclear  ===================
function esNumerico(el) {
  if (!el || el.tagName !== 'INPUT') return false;
  if (el.classList.contains('js-presion')) return false;      // M.P. permitido
  if ((el.getAttribute('inputmode') || '') === 'decimal') return true;
  const n = el.getAttribute('name') || '';
  return /^(disponible\[|pacas_locales|pacas_recorte|export_panal|export_toalla|pacas_recibidas|pacas_alimentadas|sam_recuperado|recup_)/.test(n);}

// El peso admite la suma rápida "5+10", así que permite + y espacios.
function permitePlus(el) { return el.hasAttribute('data-pesocell'); }

// Quita en vivo cualquier caracter no permitido mientras se teclea.
function filtrarNumerico(el) {
  if (!esNumerico(el)) return;
  const permitido = permitePlus(el) ? /[^0-9.,+\s]/g : /[^0-9.,]/g; // quitamos el guion
  const limpio = el.value.replace(permitido, '');
  if (limpio !== el.value) {
    const pos = el.selectionStart;
    el.value = limpio;
    try { el.setSelectionRange(pos - 1, pos - 1); } catch (e) {}
  }
}

// ¿El valor final es un número válido? (acepta coma o punto; el peso acepta sumas)
function numericoValido(el, v) {
  if (!/\d/.test(v)) return false;                            // debe tener al menos un dígito
  return permitePlus(el) ? /^[0-9.,+\s]+$/.test(v) : /^\d*[.,]?\d*$/.test(v); // sin guion
}

// Filtro al teclear
document.addEventListener('input', e => {
  if (e.target.matches('input')) filtrarNumerico(e.target);
});

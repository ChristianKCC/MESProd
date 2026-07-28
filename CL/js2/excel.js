(function () {
  'use strict';

  // Estas variables vendrán del HTML
  var FILE_URL = window.FILE_URL;
  var FILE_EXT = window.FILE_EXT;
  var FILE_NAME = window.FILE_NAME;

  var xlContent = document.getElementById('xlContent');
  var xlLoading = document.getElementById('xlLoading');
  var xlSheets = document.getElementById('xlSheets');
  var xlSearchBar = document.getElementById('xlSearchBar');
  var xlSearch = document.getElementById('xlSearch');
  var xlStats = document.getElementById('xlStats');
  var btnFullscreen = document.getElementById('btnFullscreen');
  var xlShell = document.getElementById('xlShell');

  var workbook = null;
  var currentSheet= null;
  var allRows = [];
  var allHeaders = [];
  var sortCol = -1;
  var sortAsc = true;

  /* ══════════════════════
     CARGAR ARCHIVO
  ══════════════════════ */
  fetch(FILE_URL)
    .then(function(r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.arrayBuffer();
    })
    .then(function(buf) {
      workbook = XLSX.read(new Uint8Array(buf), {
        type: 'array',
        cellStyles: false,
        cellFormulas:false,
        cellDates: true,
        raw: false /* convertir a string amigable */
      });

      showSheetTabs();
      loadSheet(workbook.SheetNames[0]);
    })
    .catch(function(e) {
      showError('No se pudo leer el archivo.', e.message);
    });

  /* ══════════════════════
     PESTAÑAS DE HOJAS
  ══════════════════════ */
  function showSheetTabs() {
    if (workbook.SheetNames.length <= 1) return; /* CSV o libro de 1 hoja: sin tabs */

    xlSheets.style.display = 'flex';
    workbook.SheetNames.forEach(function(name) {
      var tab = document.createElement('button');
      tab.className = 'xl-sheet-tab';
      tab.textContent = name;
      tab.addEventListener('click', function() {
        document.querySelectorAll('.xl-sheet-tab').forEach(function(t) { t.classList.remove('active'); });
        tab.classList.add('active');
        xlSearch.value = '';
        loadSheet(name);
      });
      xlSheets.appendChild(tab);
    });

    xlSheets.firstChild.classList.add('active');
  }

  /* ══════════════════════
     CARGAR HOJA
  ══════════════════════ */
  function loadSheet(name) {
    currentSheet = name;
    sortCol = -1;
    sortAsc = true;

    var sheet = workbook.Sheets[name];
    /* Convertir a array de arrays (incluye encabezados) */
    var raw = XLSX.utils.sheet_to_json(sheet, {
      header: 1,
      defval: '',
      raw: false,
      dateNF: 'dd/mm/yyyy'
    });

    if (!raw.length) {
      renderTable([], []);
      return;
    }

    /* Primera fila = encabezados */
    allHeaders = raw[0].map(function(h) { return h !== undefined ? String(h) : ''; });
    allRows = raw.slice(1);

    renderTable(allHeaders, allRows);
    xlSearchBar.style.display = 'flex';
    updateStats(allRows.length, allHeaders.length);
  }

  /* ══════════════════════
     RENDERIZAR TABLA
  ══════════════════════ */
  function renderTable(headers, rows) {
    /* Limpiar contenido anterior */
    var old = document.getElementById('xlTableWrap');
    if (old) old.remove();
    xlLoading.style.display = 'none';

    if (!headers.length && !rows.length) {
      showError('La hoja no contiene datos.');
      return;
    }

    var wrap = document.createElement('div');
    wrap.className = 'xl-table-wrap';
    wrap.id = 'xlTableWrap';

    var table = document.createElement('table');
    table.className = 'xl-table';
    table.id = 'xlTable';

    /* ── Encabezado ── */
    var thead = document.createElement('thead');
    var tr = document.createElement('tr');

    /* Columna de número de fila */
    var thNum = document.createElement('th');
    thNum.textContent = '#';
    tr.appendChild(thNum);

    headers.forEach(function(h, ci) {
      var th = document.createElement('th');
      th.innerHTML = escHtml(h || '(sin nombre)') +
                     ' <span class="sort-icon"><i class="bi bi-arrow-down-up"></i></span>';
      th.dataset.col = ci;
      th.addEventListener('click', function() { sortBy(ci, th); });
      tr.appendChild(th);
    });

    thead.appendChild(tr);
    table.appendChild(thead);

    /* ── Cuerpo ── */
    var tbody = document.createElement('tbody');
    tbody.id = 'xlTbody';

    rows.forEach(function(row, ri) {
      tbody.appendChild(buildRow(row, ri + 1, headers.length));
    });

    table.appendChild(tbody);
    wrap.appendChild(table);
    xlContent.appendChild(wrap);
  }

  function buildRow(row, num, colCount) {
    var tr = document.createElement('tr');

    var tdNum = document.createElement('td');
    tdNum.className = 'row-num';
    tdNum.textContent = num;
    tr.appendChild(tdNum);

    for (var ci = 0; ci < colCount; ci++) {
      var td = document.createElement('td');
      var val = row[ci] !== undefined ? String(row[ci]) : '';
      td.textContent = val;
      td.title = val;
      tr.appendChild(td);
    }
    return tr;
  }

  /* ══════════════════════
     ORDENAR COLUMNA
  ══════════════════════ */
  function sortBy(col, thEl) {
    if (sortCol === col) {
      sortAsc = !sortAsc;
    } else {
      sortCol = col;
      sortAsc = true;
    }

    /* Actualizar iconos */
    document.querySelectorAll('.xl-table thead th').forEach(function(th) {
      th.classList.remove('sort-asc', 'sort-desc');
      th.querySelector('.sort-icon') && (th.querySelector('.sort-icon').innerHTML = '<i class="bi bi-arrow-down-up"></i>');
    });
    thEl.classList.add(sortAsc ? 'sort-asc' : 'sort-desc');
    thEl.querySelector('.sort-icon').innerHTML = sortAsc
      ? '<i class="bi bi-sort-alpha-down"></i>'
      : '<i class="bi bi-sort-alpha-up"></i>';

    var sorted = allRows.slice().sort(function(a, b) {
      var va = a[col] !== undefined ? String(a[col]) : '';
      var vb = b[col] !== undefined ? String(b[col]) : '';
      /* Intentar comparación numérica */
      var na = parseFloat(va), nb = parseFloat(vb);
      if (!isNaN(na) && !isNaN(nb)) return sortAsc ? na - nb : nb - na;
      return sortAsc ? va.localeCompare(vb) : vb.localeCompare(va);
    });

    var tbody = document.getElementById('xlTbody');
    tbody.innerHTML = '';
    sorted.forEach(function(row, ri) {
      tbody.appendChild(buildRow(row, ri + 1, allHeaders.length));
    });

    /* Re-aplicar búsqueda si hay texto */
    if (xlSearch.value.trim()) applySearch(xlSearch.value.trim());
  }

  /* ══════════════════════
     BÚSQUEDA
  ══════════════════════ */
  xlSearch.addEventListener('input', function() {
    applySearch(this.value.trim());
  });

  function applySearch(term) {
    var tbody = document.getElementById('xlTbody');
    if (!tbody) return;
    var rows = tbody.querySelectorAll('tr');
    var visible = 0;
    var lower = term.toLowerCase();

    rows.forEach(function(row) {
      var cells = row.querySelectorAll('td:not(.row-num)');
      var match = false;

      cells.forEach(function(td) {
        var text = td.textContent;
        if (!term || text.toLowerCase().indexOf(lower) !== -1) {
          match = true;
          /* Highlight */
          if (term) {
            var re = new RegExp('(' + escRegex(term) + ')', 'gi');
            td.innerHTML = escHtml(text).replace(re, '<mark>$1</mark>');
          } else {
            td.textContent = text;
          }
        } else {
          td.textContent = td.textContent; /* quitar highlight */
        }
      });

      row.classList.toggle('hidden', !match);
      if (match) visible++;
    });

    updateStats(visible, allHeaders.length, term ? rows.length : null);
  }

  /* ══════════════════════
     ESTADÍSTICAS
  ══════════════════════ */
  function updateStats(rows, cols, total) {
    var text = rows.toLocaleString() + ' fila(s)';
    if (total !== null && total !== undefined) text += ' de ' + total.toLocaleString();
    text += ' &nbsp;·&nbsp; ' + cols + ' columna(s)';
    xlStats.innerHTML = text;
  }

  /* ══════════════════════
     PANTALLA COMPLETA
  ══════════════════════ */
  btnFullscreen.addEventListener('click', function() {
    if (!document.fullscreenElement) {
      xlShell.requestFullscreen && xlShell.requestFullscreen();
      btnFullscreen.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
    } else {
      document.exitFullscreen && document.exitFullscreen();
      btnFullscreen.innerHTML = '<i class="bi bi-fullscreen"></i>';
    }
  });
  document.addEventListener('fullscreenchange', function() {
    if (!document.fullscreenElement) {
      btnFullscreen.innerHTML = '<i class="bi bi-fullscreen"></i>';
    }
  });

  /* ══════════════════════
     ERROR
  ══════════════════════ */
  function showError(msg, detail) {
    xlLoading.style.display = 'none';
    var div = document.createElement('div');
    div.className = 'xl-error';
    div.innerHTML =
        '<span class="ei">⚠️</span>' +
        '<h3>' + msg + '</h3>' +
        (detail ? '<p style="font-size:.7rem;color:rgba(255,255,255,.2)">' + escHtml(detail) + '</p>' : '') +
        '<p>Descarga el archivo para abrirlo con Excel o LibreOffice Calc.</p>' +
        '<a href="uploads/' + escHtml(FILE_NAME) + '" download class="emc-btn emc-btn-primary">' +
        '<i class="bi bi-download"></i> Descargar archivo' +
        '</a>';
    xlContent.appendChild(div);
    }

  /* ══════════════════════
     UTILS
  ══════════════════════ */
  function escHtml(s) {
    return String(s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function escRegex(s) {
    return s.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');
  }

})();
/* =============================================
   OrganigramaEMC - JavaScript Principal
   ============================================= */

(function () {
  'use strict';

  /* ── DRAG & DROP ──────────────────────────── */
  document.querySelectorAll('.emc-dropzone').forEach(function (zone) {
    zone.addEventListener('dragover', function (e) {
      e.preventDefault();
      zone.classList.add('dragover');
    });

    zone.addEventListener('dragleave', function () {
      zone.classList.remove('dragover');
    });

    zone.addEventListener('drop', function (e) {
      e.preventDefault();
      zone.classList.remove('dragover');

      var input = zone.querySelector('input[type="file"]');
      if (input && e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  });

  /* ── FILE SELECTED → SHOW NAME ────────────── */
  document.querySelectorAll('input[type="file"].emc-file-input').forEach(function (input) {
    input.addEventListener('change', function () {
      var labelEl = document.querySelector('#selected-file-name');
      var dzTitle = document.querySelector('.dz-title');

      if (this.files.length) {
        var fileName = this.files[0].name;
        var fileSize = (this.files[0].size / 1024).toFixed(1) + ' KB';

        if (labelEl) {
          labelEl.textContent = fileName + ' (' + fileSize + ')';
          labelEl.closest('.emc-selected-file').classList.add('show');
        }

        if (dzTitle) {
          dzTitle.textContent = fileName;
        }
      }
    });
  });

  /* ── FORM UPLOAD CON PROGRESS ─────────────── */
  var uploadForm = document.getElementById('emc-upload-form');
  if (uploadForm) {
    uploadForm.addEventListener('submit', function (e) {
      e.preventDefault();

      var fileInput = uploadForm.querySelector('input[type="file"]');
      if (!fileInput || !fileInput.files.length) {
        showAlert('Debes seleccionar un archivo antes de subir.', 'warning');
        return;
      }

      var progressWrap = document.getElementById('emc-progress-wrap');
      var progressFill = document.getElementById('emc-progress-fill');
      var progressLabel = document.getElementById('emc-progress-pct');
      var submitBtn = uploadForm.querySelector('[type="submit"]');

      if (progressWrap) progressWrap.classList.add('show');
      if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Subiendo…'; }

      var formData = new FormData(uploadForm);
      var xhr = new XMLHttpRequest();

      xhr.upload.addEventListener('progress', function (e) {
        if (e.lengthComputable) {
          var pct = Math.round((e.loaded / e.total) * 100);
          if (progressFill) progressFill.style.width = pct + '%';
          if (progressLabel) progressLabel.textContent = pct + '%';
        }
      });

      xhr.addEventListener('load', function () {
        try {
          var res = JSON.parse(xhr.responseText);
          if (res.success) {
            if (progressFill) progressFill.style.width = '100%';
            showAlert(res.message || 'Archivo cargado correctamente.', 'success');
            refreshStatusPanel(res);
            setTimeout(function () { location.reload(); }, 1200);
          } else {
            showAlert(res.message || 'Ocurrió un error al subir el archivo.', 'danger');
            if (progressWrap) progressWrap.classList.remove('show');
          }
        } catch (ex) {
          showAlert('Error inesperado del servidor.', 'danger');
        }

        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Subir archivo'; }
      });

      xhr.addEventListener('error', function () {
        showAlert('Error de red al subir el archivo.', 'danger');
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Subir archivo'; }
        if (progressWrap) progressWrap.classList.remove('show');
      });

      xhr.open('POST', uploadForm.action || window.location.href);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.send(formData);
    });
  }

  /* ── REFRESH STATUS PANEL ─────────────────── */
  function refreshStatusPanel(data) {
    var nameEl = document.getElementById('status-file-name');
    var metaEl = document.getElementById('status-file-meta');
    var badgeEl = document.getElementById('status-badge');

    if (nameEl && data.filename) nameEl.textContent = data.filename;
    if (metaEl && data.updated) metaEl.textContent = 'Actualizado: ' + data.updated;
    if (badgeEl) {
      badgeEl.textContent = 'Disponible';
      badgeEl.className = 'status-badge badge-ok';
    }
  }

  /* ── SHOW ALERT ───────────────────────────── */
  function showAlert(msg, type) {
    var container = document.getElementById('emc-alert-container');
    if (!container) return;

    var icons = { success: '✅', danger: '❌', warning: '⚠️', info: 'ℹ️' };
    var div = document.createElement('div');
    div.className = 'emc-alert emc-alert-' + type;
    div.innerHTML = '<span>' + (icons[type] || 'ℹ️') + '</span><span>' + msg + '</span>';
    container.innerHTML = '';
    container.appendChild(div);

    if (type === 'success') {
      setTimeout(function () { div.style.opacity = '0'; div.style.transition = 'opacity .4s'; }, 3000);
    }
  }

  /* ── TABLA BÚSQUEDA EN TIEMPO REAL ────────── */
  var searchInput = document.getElementById('emc-search-input');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      var term = this.value.toLowerCase().trim();
      var rows = document.querySelectorAll('#emc-data-table tbody tr');
      var visible = 0;

      rows.forEach(function (row) {
        var text = row.textContent.toLowerCase();
        var match = !term || text.includes(term);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
      });

      var countEl = document.getElementById('emc-row-count');
      if (countEl) countEl.textContent = visible + ' registro(s)';
    });
  }

  /* ── FULLSCREEN VIEWER ────────────────────── */
  var fsBtn = document.getElementById('btn-fullscreen');
  if (fsBtn) {
    fsBtn.addEventListener('click', function () {
      var frame = document.getElementById('emc-viewer-frame') ||
                  document.getElementById('emc-table-wrap');
      if (!frame) return;

      if (!document.fullscreenElement) {
        frame.requestFullscreen && frame.requestFullscreen();
        fsBtn.title = 'Salir de pantalla completa';
        fsBtn.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
      } else {
        document.exitFullscreen && document.exitFullscreen();
        fsBtn.title = 'Pantalla completa';
        fsBtn.innerHTML = '<i class="bi bi-fullscreen"></i>';
      }
    });
  }

  /* ── AUTO-HIDE PHP ALERTS AFTER 5s ───────── */
  document.querySelectorAll('.emc-alert.auto-hide').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity .6s';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 700);
    }, 5000);
  });

})();
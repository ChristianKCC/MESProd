/* =============================================
   OrganigramaEMC - JavaScript Principal
   (compartido por Excel/Matriz y PowerPoint/Organigrama)
   ============================================= */

(function () {
  "use strict";

  /* =========================================================
     RECORTE DE ENLACE (espejo de funciones_enlace.php)
     ========================================================= */
  function recortarEnlaceEMC(raw) {
    raw = (raw || "").trim();
    if (!raw) return null;

    if (raw.toLowerCase().indexOf("<iframe") !== -1) {
      var m = raw.match(/src\s*=\s*["']([^"']+)["']/i);
      if (m) raw = m[1];
    }

    raw = raw.replace(/&amp;/g, "&");

    var url;
    try {
      url = new URL(raw);
    } catch (e) {
      return null;
    }

    var sourcedoc = url.searchParams.get("sourcedoc");
    if (!sourcedoc) return null;

    var base = url.origin + url.pathname;
    return { sourcedoc: sourcedoc, enlace: base + "?sourcedoc=" + sourcedoc };
  }

  /* ── Preview en vivo del enlace recortado ── */
  var enlaceInput = document.getElementById("emc-enlace-input");
  var previewWrap = document.getElementById("emc-link-preview-wrap");
  var previewValue = document.getElementById("emc-link-preview-value");
  var linkError = document.getElementById("emc-link-error");

  function actualizarPreviewEnlace() {
    if (!enlaceInput) return;
    var raw = enlaceInput.value;

    if (!raw.trim()) {
      if (previewWrap) previewWrap.style.display = "none";
      if (linkError) linkError.style.display = "none";
      return;
    }

    var cut = recortarEnlaceEMC(raw);
    if (cut) {
      if (previewValue) previewValue.textContent = cut.enlace;
      if (previewWrap) previewWrap.style.display = "block";
      if (linkError) linkError.style.display = "none";
    } else {
      if (previewWrap) previewWrap.style.display = "none";
      if (linkError) linkError.style.display = "flex";
    }
  }

  if (enlaceInput) {
    enlaceInput.addEventListener("input", actualizarPreviewEnlace);
    enlaceInput.addEventListener("paste", function () {
      setTimeout(actualizarPreviewEnlace, 0);
    });
  }

  /* =========================================================
     DRAG & DROP
     ========================================================= */
  document.querySelectorAll(".emc-dropzone").forEach(function (zone) {
    zone.addEventListener("dragover", function (e) {
      e.preventDefault();
      zone.classList.add("dragover");
    });
    zone.addEventListener("dragleave", function () {
      zone.classList.remove("dragover");
    });
    zone.addEventListener("drop", function (e) {
      e.preventDefault();
      zone.classList.remove("dragover");
      var input = zone.querySelector('input[type="file"]');
      if (input && e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event("change", { bubbles: true }));
      }
    });
  });

  /* ── FILE SELECTED → SHOW NAME ────────────── */
  document
    .querySelectorAll('input[type="file"].emc-file-input')
    .forEach(function (input) {
      input.addEventListener("change", function () {
        var labelEl = document.querySelector("#selected-file-name");
        var dzTitle = document.querySelector(".dz-title");

        if (this.files.length) {
          var fileName = this.files[0].name;
          var fileSize = (this.files[0].size / 1024).toFixed(1) + " KB";

          if (labelEl) {
            labelEl.textContent = fileName + " (" + fileSize + ")";
            labelEl.closest(".emc-selected-file").classList.add("show");
          }
          if (dzTitle) dzTitle.textContent = fileName;
        }
      });
    });

  /* =========================================================
     FORM UPLOAD CON PROGRESS  (+ guardado del enlace por tipo)
     ========================================================= */
  var uploadForm = document.getElementById("emc-upload-form");
  if (uploadForm) {
    // El tipo lo define cada pagina con data-tipo en el <form>
    var TIPO = parseInt(uploadForm.getAttribute("data-tipo"), 10) || 1;

    uploadForm.addEventListener("submit", function (e) {
      e.preventDefault();

      var fileInput = uploadForm.querySelector('input[type="file"]');
      if (!fileInput || !fileInput.files.length) {
        showAlert("Debes seleccionar un archivo antes de subir.", "warning");
        return;
      }

      // Validar / recortar enlace antes de enviar nada
      var cut = recortarEnlaceEMC(enlaceInput ? enlaceInput.value : "");
      if (!cut) {
        showAlert(
          'El enlace no es válido. Revisa que contenga "sourcedoc=".',
          "warning",
        );
        if (linkError) linkError.style.display = "flex";
        return;
      }

      var progressWrap = document.getElementById("emc-progress-wrap");
      var progressFill = document.getElementById("emc-progress-fill");
      var progressLabel = document.getElementById("emc-progress-pct");
      var submitBtn = uploadForm.querySelector('[type="submit"]');
      var btnText = submitBtn ? submitBtn.innerHTML : "";

      if (progressWrap) progressWrap.classList.add("show");
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Subiendo…";
      }

      var nombreArchivo = fileInput.files[0].name;

      // FormData con el archivo, usando el NOMBRE real del input
      // (excel_file en Matriz, pptx_file en Organigrama)
      var formData = new FormData();
      formData.append(fileInput.name, fileInput.files[0]);

      var xhr = new XMLHttpRequest();

      xhr.upload.addEventListener("progress", function (e) {
        if (e.lengthComputable) {
          var pct = Math.round((e.loaded / e.total) * 100);
          if (progressFill) progressFill.style.width = pct + "%";
          if (progressLabel) progressLabel.textContent = pct + "%";
        }
      });

      xhr.addEventListener("load", function () {
        var res;
        try {
          res = JSON.parse(xhr.responseText);
        } catch (ex) {
          showAlert(
            "Error inesperado del servidor al subir el archivo.",
            "danger",
          );
          resetBoton();
          return;
        }

        if (!res.success) {
          showAlert(
            res.message || "Ocurrió un error al subir el archivo.",
            "danger",
          );
          if (progressWrap) progressWrap.classList.remove("show");
          resetBoton();
          return;
        }

        if (progressFill) progressFill.style.width = "100%";
        guardarEnlace(nombreArchivo, cut.enlace, res);
      });

      xhr.addEventListener("error", function () {
        showAlert("Error de red al subir el archivo.", "danger");
        if (progressWrap) progressWrap.classList.remove("show");
        resetBoton();
      });

      // Sube el archivo a tu logica de siempre (misma pagina)
      xhr.open("POST", uploadForm.action || window.location.href);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      xhr.send(formData);

      function resetBoton() {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = btnText;
        }
      }

      /* Segundo request: guarda el enlace recortado en tblMXPREnlaceEMC */
      function guardarEnlace(nombre, enlace, resArchivo) {
        var fd = new FormData();
        fd.append("accion", "guardarEnlace");
        fd.append("tipo", TIPO);
        fd.append("nombre_archivo", nombre);
        fd.append("enlace", enlace);

        fetch("php/index.php", {
          method: "POST",
          headers: { "X-Requested-With": "XMLHttpRequest" },
          body: fd,
        })
          .then(function (r) {
            return r.json();
          })
          .then(function (data) {
            if (data.success) {
              showAlert("Archivo y enlace guardados correctamente.", "success");
              refreshStatusPanel(resArchivo);
              setTimeout(function () {
                location.reload();
              }, 1200);
            } else {
              showAlert(
                data.message ||
                  "El archivo se subió pero el enlace no se pudo guardar.",
                "danger",
              );
              resetBoton();
            }
          })
          .catch(function () {
            showAlert(
              "El archivo se subió pero hubo un error de red al guardar el enlace.",
              "danger",
            );
            resetBoton();
          });
      }
    });
  }

  /* ── REFRESH STATUS PANEL ─────────────────── */
  function refreshStatusPanel(data) {
    var nameEl = document.getElementById("status-file-name");
    var metaEl = document.getElementById("status-file-meta");
    var badgeEl = document.getElementById("status-badge");

    if (nameEl && data && data.filename) nameEl.textContent = data.filename;
    if (metaEl && data && data.updated)
      metaEl.textContent = "Actualizado: " + data.updated;
    if (badgeEl) {
      badgeEl.textContent = "Disponible";
      badgeEl.className = "status-badge badge-ok";
    }
  }

  /* ── SHOW ALERT ───────────────────────────── */
  function showAlert(msg, type) {
    var container = document.getElementById("emc-alert-container");
    if (!container) return;

    var icons = { success: "✅", danger: "❌", warning: "⚠️", info: "ℹ️" };
    var div = document.createElement("div");
    div.className = "emc-alert emc-alert-" + type;
    div.innerHTML =
      "<span>" + (icons[type] || "ℹ️") + "</span><span>" + msg + "</span>";
    container.innerHTML = "";
    container.appendChild(div);

    if (type === "success") {
      setTimeout(function () {
        div.style.opacity = "0";
        div.style.transition = "opacity .4s";
      }, 3000);
    }
  }

  /* ── TABLA BÚSQUEDA EN TIEMPO REAL ────────── */
  var searchInput = document.getElementById("emc-search-input");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      var term = this.value.toLowerCase().trim();
      var rows = document.querySelectorAll("#emc-data-table tbody tr");
      var visible = 0;
      rows.forEach(function (row) {
        var text = row.textContent.toLowerCase();
        var match = !term || text.includes(term);
        row.style.display = match ? "" : "none";
        if (match) visible++;
      });
      var countEl = document.getElementById("emc-row-count");
      if (countEl) countEl.textContent = visible + " registro(s)";
    });
  }

  /* ── FULLSCREEN VIEWER ────────────────────── */
  var fsBtn = document.getElementById("btn-fullscreen");
  if (fsBtn) {
    fsBtn.addEventListener("click", function () {
      var frame =
        document.getElementById("emc-viewer-frame") ||
        document.getElementById("emc-table-wrap");
      if (!frame) return;
      if (!document.fullscreenElement) {
        frame.requestFullscreen && frame.requestFullscreen();
        fsBtn.title = "Salir de pantalla completa";
        fsBtn.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
      } else {
        document.exitFullscreen && document.exitFullscreen();
        fsBtn.title = "Pantalla completa";
        fsBtn.innerHTML = '<i class="bi bi-fullscreen"></i>';
      }
    });
  }

  /* ── AUTO-HIDE PHP ALERTS AFTER 5s ───────── */
  document.querySelectorAll(".emc-alert.auto-hide").forEach(function (el) {
    setTimeout(function () {
      el.style.transition = "opacity .6s";
      el.style.opacity = "0";
      setTimeout(function () {
        el.remove();
      }, 700);
    }, 5000);
  });

  /* ── Capa que tapa la barra inferior de SharePoint ── */
  function applyBarCover(wrapper, barHeight, bgColor) {
    if (!wrapper) return;
    var cover = wrapper.querySelector(".sharepoint-bar-cover");
    if (!cover) {
      cover = document.createElement("div");
      cover.className = "sharepoint-bar-cover";
      cover.style.cssText = [
        "position: absolute",
        "bottom: 0",
        "left: 0",
        "width: 100%",
        "z-index: 99",
        "pointer-events: all",
        "display: block",
      ].join(";");
      wrapper.appendChild(cover);
    }
    cover.style.height = barHeight + "px";
    cover.style.background = bgColor;
  }

  function watchIframe(iframe, wrapper, barHeight, bgColor) {
    applyBarCover(wrapper, barHeight, bgColor);
    iframe.addEventListener("load", function () {
      applyBarCover(wrapper, barHeight, bgColor);
      setTimeout(function () {
        applyBarCover(wrapper, barHeight, bgColor);
      }, 800);
      setTimeout(function () {
        applyBarCover(wrapper, barHeight, bgColor);
      }, 2000);
      setTimeout(function () {
        applyBarCover(wrapper, barHeight, bgColor);
      }, 4000);
    });
  }

  /* ── Excel (MatrizEMC) ── */
  var xlWrapper = document.querySelector(".xl-iframe-wrap");
  var xlIframe = xlWrapper ? xlWrapper.querySelector(".xl-iframe") : null;
  if (xlWrapper && xlIframe) {
    xlWrapper.style.position = "relative";
    watchIframe(xlIframe, xlWrapper, 0, "#1e1e22");
  }

  /* ── PowerPoint (OrganigramaEMC) ── */
  var pptWrapper = document.querySelector(".pptx-stage");
  var pptIframe = pptWrapper ? pptWrapper.querySelector(".pptx-iframe") : null;
  if (pptWrapper && pptIframe) {
    pptWrapper.style.position = "relative";
    watchIframe(pptIframe, pptWrapper, 50, "#2a2a30");
  }
})();

const $ = (id) => document.getElementById(id);
const modalEl = $("modalImpresora");
const alertBox = $("impresoraAlert");

function showAlert(tipo, msg) {
  alertBox.className = `alert alert-${tipo}`;
  alertBox.textContent = msg;
  alertBox.classList.remove("d-none");
}
const clearAlert = () => alertBox.classList.add("d-none");

function setEstado(configurada) {
  const est = $("impEstado");
  est.textContent = configurada ? "Configurada" : "Sin configurar";
  est.className = configurada
    ? "badge bg-success"
    : "badge bg-warning text-dark";
}

function payload() {
  return {
    host: $("impHost").value.trim(),
    puerto: parseInt($("impPuerto").value, 10) || 9100,
  };
}

// async function cargarConfig() {
//   clearAlert();
//   try {
//     const d = await (
//       await fetch("../../../../Mes/KCMes/bitacora/php/impresora_get.php")
//     ).json();
//     if (!d.ok) throw new Error(d.error || "Error");
//     $("impMaquina").textContent = d.id_maquina;
//     $("impHost").value = d.host || "";
//     $("impPuerto").value = d.puerto || 9100;
//     setEstado(d.existe);
//   } catch (e) {
//     showAlert("danger", e.message);
//   }
// }

async function cargarConfig() {
  clearAlert();
  try {
    const d = await (
      await fetch("../../../../Mes/KCMes/bitacora/php/impresora_get.php")
    ).json();
    if (!d.ok) throw new Error(d.error || "Error");

    // Mostrar nombre en el badge
    $("impMaquina").textContent = d.nombre_maquina || d.id_maquina;

    $("impHost").value = d.host || "";
    $("impPuerto").value = d.puerto || 9100;
    setEstado(d.existe);
  } catch (e) {
    showAlert("danger", e.message);
  }
}

async function guardar() {
  clearAlert();
  const p = payload();
  if (!p.host)
    return showAlert("warning", "Captura la IP o nombre de la impresora.");
  try {
    const d = await (
      await fetch("../../../../Mes/KCMes/bitacora/php/impresora_guardar.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(p),
      })
    ).json();
    if (!d.ok) throw new Error(d.error || "No se pudo guardar");
    showAlert("success", "Guardado correctamente.");
    setEstado(true);
  } catch (e) {
    showAlert("danger", e.message);
  }
}

// async function probar() {
//   clearAlert();
//   const p = payload();
//   if (!p.host) return showAlert("warning", "Captura la IP o nombre primero.");
//   showAlert("info", "Probando conexión…");
//   try {
//     const d = await // ../../../../MES/KCMes/zpl/imprimirl.php
//     // ../php/impresora_test.php
//     (
//       await fetch("../../../../Mes/KCMes/bitacora/php/impresora_test.php", {
//         method: "POST",
//         headers: { "Content-Type": "application/json" },
//         body: JSON.stringify(p),
//       })
//     ).json();
//     showAlert(d.ok ? "success" : "danger", d.ok ? d.mensaje : d.error);
//   } catch (e) {
//     showAlert("danger", e.message);
//   }
// }

async function probar() {
  clearAlert();
  const p = payload();
  if (!p.host) return showAlert("warning", "Captura la IP o nombre primero.");
  showAlert("info", "Probando conexión…");
  console.log("Probando con payload:", p);

  try {
    const res = await fetch(
      "../../../../Mes/KCMes/bitacora/php/impresora_test.php",
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(p),
      },
    );
    const d = await res.json();
    console.log("JSON recibido:", d);

    showAlert(d.ok ? "success" : "danger", d.ok ? d.mensaje : d.error);
  } catch (e) {
    console.error("Error en fetch:", e);
    showAlert("danger", e.message);
  }
}

modalEl.addEventListener("show.bs.modal", cargarConfig);
$("btnGuardarImp").addEventListener("click", guardar);
$("btnProbarImp").addEventListener("click", probar);

// const d = await (
//   await fetch("../../../../MES/KCMes/zpl/imprimir.php", {
//     method: "POST",
//     body: fd,
//   })
// ).json();
// if (!d.ok) {
//   if (d.sin_config) {
//     bootstrap.Modal.getOrCreateInstance(
//       document.getElementById("modalImpresora"),
//     ).show();
//   }
//   // muestra d.error al usuario
// }

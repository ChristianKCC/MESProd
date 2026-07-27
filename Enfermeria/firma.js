let timerHandle = null;

window.addEventListener("beforeunload", () => {
  try {
    ClearTablet();
    SetTabletState(0, timerHandle);
  } catch (e) {}
});

const cnv = document.getElementById("signatureCanvas");
const ctx1 = cnv.getContext("2d");

document.getElementById("btnSign").addEventListener("click", onSign);
document.getElementById("btnClear").addEventListener("click", onClear);
document.getElementById("btnDone").addEventListener("click", onDone);

function onSign() {
  try {
    SetDisplayXSize(cnv.width);
    SetDisplayYSize(cnv.height);

    SetTabletState(0, timerHandle);
    ClearTablet();

    timerHandle = SetTabletState(1, ctx1, 50);
  } catch (e) {
    alert(
      "No se pudo inicializar SigWeb. ¿Está instalado y permitido el acceso de red local en el navegador?",
    );
    console.error(e);
  }
}

function onClear() {
  try {
    ClearTablet();
  } catch (e) {
    console.error(e);
  }
}

function onDone() {
  try {
    if (NumberOfTabletPoints() === 0) {
      alert("Primero firma en el pad.");
      return;
    }

    // 1) Detén captura antes de solicitar imagen
    try {
      SetTabletState(0, timerHandle);
    } catch (e) {
      console.warn("[Topaz] No se pudo detener la tableta:", e);
    }

    // 2) Obtén el SigString biométrico (texto)
    const sigString = GetSigString();
    console.log("[Topaz] SigString length:", sigString ? sigString.length : 0);

    // 3) Configura parámetros de imagen (solo los que existan en tu build)
    try {
      if (typeof SetJustifyMode === "function") SetJustifyMode(0); // izquierda/arriba
      if (typeof SetImageXSize === "function") SetImageXSize(500);
      if (typeof SetImageYSize === "function") SetImageYSize(100);
      if (typeof SetImagePenWidth === "function") SetImagePenWidth(0); // auto si existe
      // No usar SetImageColor / SetImageBackgroundColor si no existen en tu build
      // Algunas builds necesitan formato antes:
      if (typeof SetImageFormat === "function") SetImageFormat(2); // 0 BN, 1 Gris, 2 Color (si existe)
      if (typeof SetTransparent === "function") SetTransparent(false); // si existe
    } catch (e) {
      console.warn("[Topaz] Setters de imagen no soportados en esta build:", e);
    }

    // 4) Intento directo: en tu build length=1 => el único arg es callback
    //    Primero definimos la función global que el SDK invocará
    try {
      delete window.SigImageCallback;
    } catch (_) {}
    window.SigImageCallback = function (b64) {
      console.log("[Topaz] Callback recibido. length:", b64 ? b64.length : 0);
      if (!b64) {
        alert("No se pudo generar la imagen de la firma (callback).");
        return;
      }
      renderSignatureImage(sigString, b64);
    };

    // 5) Llamar GetSigImageB64 con UN SOLO parámetro (el callback)
    try {
      console.log(
        "[Topaz] Disparando GetSigImageB64 con callback (por FUNCIÓN)...",
      );
      GetSigImageB64(window.SigImageCallback); // ← tu build lo espera así
      return; // el flujo continúa en la función global
    } catch (e1) {
      console.warn(
        "[Topaz] Variante por FUNCIÓN falló, probando por NOMBRE...",
        e1,
      );
      try {
        console.log(
          "[Topaz] Disparando GetSigImageB64 con callback (por NOMBRE)...",
        );
        GetSigImageB64("SigImageCallback"); // ← algunos builds aceptan nombre
        return;
      } catch (e2) {
        console.error("[Topaz] Ambas variantes de callback fallaron:", e2);
        alert("No se pudo obtener la imagen Base64 de la firma.");
        return;
      }
    }
  } catch (e) {
    alert("Error al finalizar la firma.");
    console.error(e);
  }
}

function renderSignatureImage(sigString, imgB64) {
  // Guarda los datos para que puedas enviarlos al servidor
  document.getElementById("sigStringData").value = sigString; // biométrico
  document.getElementById("sigImageData").value = imgB64;
  document.getElementById("bioSigData").value = sigString;

  // Dibuja en el mismo canvas
  const img = new Image();
  img.onload = () => {
    ctx1.clearRect(0, 0, cnv.width, cnv.height);
    ctx1.drawImage(img, 0, 0);
  };
  img.src = "data:image/png;base64," + imgB64;
  console.log(object);

}

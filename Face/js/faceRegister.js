const video = document.getElementById("video");
const canvas = document.getElementById("overlay");
const ctx = canvas.getContext("2d");
const result = document.getElementById("result");
let stream = null;
let recognizing = false;
let intervalId = null;
let scanY = 0;
let scanDirection = 1;
let scanBox = { x: 150, y: 50, width: 280, height: 330 };
Promise.all([
  faceapi.nets.tinyFaceDetector.loadFromUri("/models"),
  faceapi.nets.faceLandmark68Net.loadFromUri("/models"),
  faceapi.nets.faceRecognitionNet.loadFromUri("/models"),
]).then(() => {
  console.log("Modelos cargados");
});
function startVideo() {
  navigator.mediaDevices
    .getUserMedia({ video: {} })
    .then((s) => {
      stream = s;
      video.srcObject = stream;
    })
    .catch((err) => console.error("Error al acceder a la cámara:", err));
}
startVideo();

async function saveFace() {
  const name = document.getElementById("name").value;
  if (!name) return alert("Ingresa un nombre");

  const detection = await faceapi
    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
    .withFaceLandmarks()
    .withFaceDescriptor();
  if (!detection) return alert("No se detectó ningún rostro");

  const descriptor = Array.from(detection.descriptor);
 
  fetch("php/save_face.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ name, descriptor }),
  })
    .then((res) => res.text())
    .then(alert);
}

 function animateScanLine() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.strokeStyle = "lime";
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(0, scanY);
    ctx.lineTo(canvas.width, scanY);
    ctx.stroke();

    ctx.strokeStyle = "red";
    ctx.lineWidth = 2;
    ctx.strokeRect(
      scanBox.x,
      scanBox.y,
      scanBox.width,
      scanBox.height
    );

    scanY += scanDirection * 5;
    if (scanY >= canvas.height || scanY <= 0) {
      scanDirection *= -1;
    }
  }

  function startScanAnimation() {
    scanAnimationId = setInterval(() => animateScanLine(), 50);
  }
startScanAnimation();

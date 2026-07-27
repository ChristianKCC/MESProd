export class Firma {
  constructor(canvas) {
    this.canvas = canvas;
    this.ctx = canvas.getContext("2d");
    this.dibujando = false;
    this.ajustarResolucion();
    this.agregarEventos();
  }

  ajustarResolucion() {
    const dpr = window.devicePixelRatio || 1;
    const width = 1200;
    const height = 600;
    this.canvas.style.width = `${width}px`;
    this.canvas.style.height = `${height}px`;

    // Establece el tamaño interno (resolución real)
    this.canvas.width = width * dpr;
    this.canvas.height = height * dpr;
    this.ctx.setTransform(1, 0, 0, 1, 0, 0);
    this.ctx.scale(dpr, dpr);
  }

  getPos(e) {
    const rect = this.canvas.getBoundingClientRect();
    const scaleX = this.canvas.width / rect.width;
    const scaleY = this.canvas.height / rect.height;

    const x = (e.clientX - rect.left) * scaleX;
    const y = (e.clientY - rect.top) * scaleY;

    return { x, y };
  }
  empezarDibujo(e) {
    e.preventDefault();
    this.dibujando = true;
    this.canvas.setPointerCapture(e.pointerId);
    const { x, y } = this.getPos(e);
    this.startPos = { x, y };
    this.lastPos = null;
    this.ctx.beginPath();
    this.ctx.moveTo(x, y);
  }
  terminarDibujo(e) {
    if (this.dibujando && !this.lastPos && this.startPos) {
      this.ctx.beginPath();
      this.ctx.arc(this.startPos.x, this.startPos.y, 1.5, 0, 2 * Math.PI);
      this.ctx.fillStyle = "#000";
      this.ctx.fill();
    }
    this.dibujando = false;
    this.lastPos = null;
    this.startPos = null;
    if (e?.pointerId) {
      this.canvas.releasePointerCapture(e.pointerId);
    }
    this.ctx.beginPath();
  }
  dibujar(e) {
    if (!this.dibujando) return;
    const rect = this.canvas.getBoundingClientRect();
    const x = e.clientX;
    const y = e.clientY;
    const dentroDelCanvas =
      x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom;
    if (!dentroDelCanvas) return;
    const pos = this.getPos(e);
    if (!this.lastPos) {
      this.lastPos = pos;
      return;
    }
    const midPoint = {
      x: (this.lastPos.x + pos.x) / 2,
      y: (this.lastPos.y + pos.y) / 2,
    };
    this.ctx.lineWidth = 2;
    this.ctx.lineCap = "round";
    this.ctx.strokeStyle = "#000";
    this.ctx.quadraticCurveTo(
      this.lastPos.x,
      this.lastPos.y,
      midPoint.x,
      midPoint.y
    );
    this.ctx.stroke();
    this.lastPos = pos;
  }
  limpiarCanvas() {
    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    this.ctx.beginPath();
  }
  agregarEventos() {
    this.canvas.addEventListener("pointerdown", (e) => this.empezarDibujo(e));
    this.canvas.addEventListener("pointermove", (e) => this.dibujar(e));
    this.canvas.addEventListener("pointerup", () => this.terminarDibujo());
    this.canvas.addEventListener("pointerleave", () => this.terminarDibujo());

    this.canvas.style.touchAction = "none";
  }
}



export class RFacial {
  constructor(videoId, canvasId, resultId, dom) {
    this.video = document.getElementById(videoId);
    this.canvas = document.getElementById(canvasId);
    this.ctx = this.canvas.getContext("2d");
    this.result = document.getElementById(resultId);
    this.dom = dom;

    this.scanBox = { x: 130, y: 10, width: 280, height: 330 };
    this.stream = null;
    this.recognizing = false;
    this.intervalId = null;
    this.scanAnimationId = null;
    this.scanY = 0;
    this.scanDirection = 1;

    this.loadModels();
  }

  async loadModels() {
    await Promise.all([
      faceapi.nets.tinyFaceDetector.loadFromUri("/models"),
      faceapi.nets.faceLandmark68Net.loadFromUri("/models"),
      faceapi.nets.faceRecognitionNet.loadFromUri("/models"),
    ]);
  }

  animateScanLine() {
    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    this.ctx.strokeStyle = "lime";
    this.ctx.lineWidth = 2;
    this.ctx.beginPath();
    this.ctx.moveTo(0, this.scanY);
    this.ctx.lineTo(this.canvas.width, this.scanY);
    this.ctx.stroke();

    this.ctx.strokeStyle = "red";
    this.ctx.lineWidth = 2;
    this.ctx.strokeRect(
      this.scanBox.x,
      this.scanBox.y,
      this.scanBox.width,
      this.scanBox.height
    );

    this.scanY += this.scanDirection * 5;
    if (this.scanY >= this.canvas.height || this.scanY <= 0) {
      this.scanDirection *= -1;
    }
  }

  startScanAnimation() {
    this.scanAnimationId = setInterval(() => this.animateScanLine(), 50);
  }

  async startRecognition() {
    if (this.recognizing) return '';
    this.recognizing = true;
    this.result.innerText = "Iniciando cámara...";

    try {
      this.stream = await navigator.mediaDevices.getUserMedia({ video: {} });
      this.video.srcObject = this.stream;
      this.result.innerText = "Buscando rostro...";

      return new Promise((resolve) => {
        this.intervalId = setInterval(async () => {
          const name = await this.autoRecognize();
          if (name) {
            clearInterval(this.intervalId);
            resolve(name);
          }
        }, 1000);
        this.startScanAnimation();
      });
    } catch (err) {
      console.error("Error al acceder a la cámara:", err);
      this.result.innerText = "No se pudo acceder a la cámara.";
      this.recognizing = false;
      return '';
    }
  }

  async autoRecognize() {
    const tempCanvas = document.createElement("canvas");
    tempCanvas.width = this.scanBox.width + 50;
    tempCanvas.height = this.scanBox.height + 50;
    const tempCtx = tempCanvas.getContext("2d");

    tempCtx.drawImage(
      this.video,
      this.scanBox.x + 50,
      this.scanBox.y+80,
      this.scanBox.width,
      this.scanBox.height,
      0,
      0,
      this.scanBox.width,
      this.scanBox.height
    );
    // document.body.appendChild(tempCanvas);
    const detection = await faceapi
      .detectSingleFace(tempCanvas, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();
    if (!detection) return "";
    const descriptor = detection.descriptor;
    const res = await fetch("../Face/php/find_face.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ descriptor: Array.from(descriptor) }),
    });

    const name = await res.text();
    if (name !== "No encontrado") {
      this.result.innerText = "";
      const modalElement = document.getElementById("modalFace");
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance.hide();
      clearInterval(this.intervalId);
      clearInterval(this.scanAnimationId);
      if (this.stream) {
        this.stream.getTracks().forEach((track) => track.stop());
      }
      this.video.srcObject = null;
      this.recognizing = false;
      this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
      return name;
    }
    return '';
  }
}

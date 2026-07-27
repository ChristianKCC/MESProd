<canvas id="canvas" width="400" height="200" style="border:2px solid #000;"></canvas><br>
<button onclick="guardarFirma()">Guardar Firma</button>
<button onclick="limpiarCanvas()">Limpiar</button>

<form id="formFirma" method="POST" action="guardar_firma.php">
  <input type="hidden" name="imagen" id="imagen">
</form>

<script>
  const canvas = document.getElementById('canvas');
  const ctx = canvas.getContext('2d');
  let dibujando = false;

  function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX || e.touches?.[0].clientX) - rect.left;
    const y = (e.clientY || e.touches?.[0].clientY) - rect.top;
    return { x, y };
  }

  function empezarDibujo(e) {
    dibujando = true;
    const { x, y } = getPos(e);
    ctx.beginPath();
    ctx.moveTo(x, y);
  }

  function terminarDibujo() {
    dibujando = false;
    ctx.beginPath(); // Reinicia el trazo
  }

  function dibujar(e) {
    if (!dibujando) return;
    const { x, y } = getPos(e);
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#000';
    ctx.lineTo(x, y);
    ctx.stroke();
  }

  // Eventos para mouse
  canvas.addEventListener('mousedown', empezarDibujo);
  canvas.addEventListener('mouseup', terminarDibujo);
  canvas.addEventListener('mouseout', terminarDibujo);
  canvas.addEventListener('mousemove', dibujar);

  // Eventos para touch
  canvas.addEventListener('touchstart', e => {
    e.preventDefault();
    empezarDibujo(e);
  });
  canvas.addEventListener('touchend', e => {
    e.preventDefault();
    terminarDibujo();
  });
  canvas.addEventListener('touchmove', e => {
    e.preventDefault();
    dibujar(e);
  });

  function limpiarCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.beginPath();
  }

  function guardarFirma() {
    const dataURL = canvas.toDataURL();
    document.getElementById('imagen').value = dataURL;
    document.getElementById('formFirma').submit();
  }
</script>

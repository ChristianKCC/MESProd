export class Monitor {
  async datanow(maquinainput) {
    const respuestaraw = await fetch(
      "../bitacora/php/bitacora.php?getDataNowMult&maquina=" + maquinainput,
    );
    const respuesta = await respuestaraw.json();
    const puntero = document.getElementById("puntero");
    const puntero2 = document.getElementById("puntero2");
    const deg1 =
      respuesta.length != 0
        ? respuesta[0].velocidadprom == 0
          ? -100
          : 100
        : -100;
    const deg2 =
      respuesta.length != 0
        ? respuesta[0].velocidadact == 0
          ? -100
          : 100
        : -100;
    puntero.style.transform = "translateX(-50%) rotate(" + deg1 + "deg)";
    puntero2.style.transform = "translateX(-50%) rotate(" + deg2 + "deg)";
    document.getElementById("velprom").innerHTML =
      respuesta.length != 0 && respuesta[0].velocidadprom + " Vel P";
    document.getElementById("velact").innerHTML =
      respuesta.length != 0 && respuesta[0].velocidadact + " Vel";
    document.getElementById("tc").innerHTML =
      respuesta.length != 0 && respuesta[0].cortes + "";
    document.getElementById("tr").innerHTML =
      respuesta.length != 0 && respuesta[0].rechazos + "";
    document.getElementById("ta").innerHTML =
      respuesta.length != 0 && respuesta[0].tcorrida + " m";
    document.getElementById("tp").innerHTML =
      respuesta.length != 0 && respuesta[0].tparo + " m";
    document.getElementById("tat").innerHTML =
      respuesta.length != 0 && respuesta[0].TiempoarribaTurno + " m";
    document.getElementById("tpt").innerHTML =
      respuesta.length != 0 && respuesta[0].TiempoabajoTurno + " m";
    document.getElementById("cortescorrida").innerHTML =
      respuesta.length != 0 && respuesta[0].CortesCorrida + "";
    document.getElementById("rechazoscorrida").innerHTML =
      respuesta.length != 0 && respuesta[0].RechazosCorrida + "";
  }
  async datamaquinaall(
    merma,
    ta,
    tp,
    estado,
    rc,
    cc,
    maquina,
    tpt = "",
    vel = "",
  ) {
    const respuestaraw = await fetch(
      "../bitacora/php/bitacora.php?getDataNowMult&maquina=" + maquina,
    );
    const respuesta = await respuestaraw.json();
    let mermacalc = 0;
    mermacalc =
      ((respuesta[0].rechazos / respuesta[0].cortes) * 100).toFixed(2) + "";
    isNaN(mermacalc) ? (mermacalc = 0) : (mermacalc = mermacalc);
    document.getElementById(merma).innerHTML = mermacalc + " %";
    document.getElementById(ta).innerHTML =
      respuesta[0].TiempoarribaTurno.toFixed(0) + " m";
    document.getElementById(tp).innerHTML =
      respuesta[0].TiempoabajoTurno.toFixed(0) + " m";
    document.getElementById(cc).innerHTML = respuesta[0].cortes;
    document.getElementById(rc).innerHTML = respuesta[0].rechazos;
    vel != "" &&
      (document.getElementById(vel).innerHTML = respuesta[0].velocidadact);
    let tpcalc =
      (respuesta[0].TiempoabajoTurno /
        (respuesta[0].TiempoabajoTurno + respuesta[0].TiempoarribaTurno)) *
      100;
    isNaN(tpcalc) ? (tpcalc = 0) : (tpcalc = tpcalc);
    tpt == ""
      ? ""
      : (document.getElementById(tpt).innerHTML = tpcalc.toFixed(2) + " %");
    document.getElementById(estado).innerHTML =
      respuesta[0].estado === 0
        ? '<i class="fas fa-circle text-danger"></i>'
        : '<i class="fas fa-circle text-success"></i>';
  }
  CreaGrafica(maquinainput, nomgrafica) {
    const monitorGrafica = new Grafica(nomgrafica);
    monitorGrafica.getDataDBMonitorMult(maquinainput, 1).then((datagraf) => {
      const datosGrafica = {
        labels: datagraf.hora,
        datasets: [
          {
            label: "Operacion",
            data: datagraf.datos,
            backgroundColor: "rgba(0, 145, 48, 0.88)",
            borderColor: "rgba(0, 145, 48, 0.88)",
            borderWidth: 3,
            tension: 0.4,
            pointRadius: 0,
            pointHoverRadius: 0,
          },
          {
            label: "Merma",
            data: datagraf.merma,
            backgroundColor: "rgba(204, 0, 0, 0.8)",
            borderColor: "rgba(204, 0, 0, 0.8)",
            borderWidth: 3,
            tension: 0.4,
            pointRadius: 0,
            pointHoverRadius: 0,
          },
          {
            label: "Velocidad",
            data: datagraf.velocidad,
            backgroundColor: "rgba(190, 204, 0, 0.8)",
            borderColor: "rgba(184, 204, 0, 0.8)",
            borderWidth: 3,
            tension: 0.4,
            pointRadius: 0,
            pointHoverRadius: 0,
          },
        ],
      };
      const opcionesGrafica = {
        responsive: true,
        plugins: {
          tooltip: {
            enabled: true,
            mode: "nearest",
            intersect: false,
            callbacks: {
              label: function (context) {
                var label = context.label;
                var value = context.parsed.y;
                return label + ": " + value;
              },
            },
          },
        },
        title: {
          display: true,
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: "rgba(255, 255, 255, 0.1)", // Cambia el color de las líneas traseras en el eje x
            },
            ticks: {
              color: "white",
            },
          },
          x: {
            grid: {
              color: "rgba(255, 255, 255, 0.1)", // Cambia el color de las líneas traseras en el eje y
            },
            ticks: {
              color: "white",
            },
          },
        },
      };
      monitorGrafica.crearGrafica("line", datosGrafica, opcionesGrafica);
    });
  }
  CreateGraf(nomgrafica, folio, numeroreg) {
    const monitorGrafica = new Grafica(nomgrafica);
    this.CreaGrafica(folio, nomgrafica);
    setInterval(() => {
      const numreg = document.getElementById(numeroreg).value;
      monitorGrafica.getDataDBMonitorMult(folio, numreg).then((respuesta) => {
        monitorGrafica.actualizarGrafica(nomgrafica, respuesta);
      });
    }, 20000);

    document.getElementById(numeroreg).addEventListener("change", (e) => {
      monitorGrafica
        .getDataDBMonitorMult(folio, e.target.value)
        .then((respuesta) => {
          monitorGrafica.actualizarGrafica(nomgrafica, respuesta);
        });
    });
  }
}

export class Grafica {
  constructor(idCanvas) {
    this.canvas = document.getElementById(idCanvas);
    this.ctx = this.canvas.getContext("2d");
    this.chart = null;
  }
  crearGrafica(tipo, datos, opciones) {
    this.chart = new Chart(this.ctx, {
      type: tipo,
      data: datos,
      options: opciones,
    });
  }
  actualizarGrafica(grafica, dataDb) {
    const chart = Chart.getChart(grafica);
    chart.data.labels = dataDb.hora;
    chart.data.datasets[0].data = dataDb.datos;
    chart.data.datasets[1].data = dataDb.merma;
    chart.data.datasets[2].data = dataDb.velocidad;
    chart.update();
  }
  async getDataDBMonitor() {
    let numreg = document.getElementById("numregmonitor").value;
    const respuestaraw = await fetch(
      "../Bitacora/php/bitacora.php?GetDataMonitor&numhrs=" + numreg,
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
  async getDataDBMonitorMult(maquina, numeroreg) {
    const respuestaraw = await fetch(
      "../Bitacora/php/bitacora.php?GetDataMonitorMult&numhrs=" +
        numeroreg +
        "&maquina=" +
        maquina,
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
}

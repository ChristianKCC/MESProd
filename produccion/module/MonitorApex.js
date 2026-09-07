// MonitorApex.js

export class Monitor {
  async datanow(maquinainput) {
    const respuestaraw = await fetch(
      "php/opc_monitor.php?getDataNowMult&maquina=" + maquinainput,
    );
    const respuesta = await respuestaraw.json();
    const puntero  = document.getElementById("puntero");
    const puntero2 = document.getElementById("puntero2");
    const deg1 = respuesta.length != 0 ? (respuesta[0].velocidadprom == 0 ? -100 : 100) : -100;
    const deg2 = respuesta.length != 0 ? (respuesta[0].velocidadact  == 0 ? -100 : 100) : -100;
    puntero.style.transform  = "translateX(-50%) rotate(" + deg1 + "deg)";
    puntero2.style.transform = "translateX(-50%) rotate(" + deg2 + "deg)";
    document.getElementById("velprom").innerHTML         = respuesta.length != 0 && respuesta[0].velocidadprom + " Vel P";
    document.getElementById("velact").innerHTML          = respuesta.length != 0 && respuesta[0].velocidadact  + " Vel";
    document.getElementById("tc").innerHTML              = respuesta.length != 0 && respuesta[0].cortes        + "";
    document.getElementById("tr").innerHTML              = respuesta.length != 0 && respuesta[0].rechazos      + "";
    document.getElementById("ta").innerHTML              = respuesta.length != 0 && respuesta[0].tcorrida      + " m";
    document.getElementById("tp").innerHTML              = respuesta.length != 0 && respuesta[0].tparo         + " m";
    document.getElementById("tat").innerHTML             = respuesta.length != 0 && respuesta[0].TiempoarribaTurno + " m";
    document.getElementById("tpt").innerHTML             = respuesta.length != 0 && respuesta[0].TiempoabajoTurno  + " m";
    document.getElementById("cortescorrida").innerHTML   = respuesta.length != 0 && respuesta[0].CortesCorrida    + "";
    document.getElementById("rechazoscorrida").innerHTML = respuesta.length != 0 && respuesta[0].RechazosCorrida  + "";
  }

  async datamaquinaall(merma, ta, tp, estado, rc, cc, maquina, tpt = "", vel = "") {
    const respuestaraw = await fetch(
      "php/opc_monitor.php?getDataNowMult&maquina=" + maquina,
    );
    const respuesta = await respuestaraw.json();
    let mermacalc = ((respuesta[0].rechazos / respuesta[0].cortes) * 100).toFixed(2) + "";
    isNaN(mermacalc) ? (mermacalc = 0) : (mermacalc = mermacalc);
    document.getElementById(merma).innerHTML = mermacalc + " %";
    document.getElementById(ta).innerHTML    = respuesta[0].TiempoarribaTurno.toFixed(0) + " m";
    document.getElementById(tp).innerHTML    = respuesta[0].TiempoabajoTurno.toFixed(0)  + " m";
    document.getElementById(cc).innerHTML    = respuesta[0].cortes;
    document.getElementById(rc).innerHTML    = respuesta[0].rechazos;
    vel != "" && (document.getElementById(vel).innerHTML = respuesta[0].velocidadact);
    let tpcalc = (respuesta[0].TiempoabajoTurno / (respuesta[0].TiempoabajoTurno + respuesta[0].TiempoarribaTurno)) * 100;
    isNaN(tpcalc) ? (tpcalc = 0) : (tpcalc = tpcalc);
    tpt == "" ? "" : (document.getElementById(tpt).innerHTML = tpcalc.toFixed(2) + " %");
    document.getElementById(estado).innerHTML =
      respuesta[0].estado === 0
        ? '<i class="fas fa-circle text-danger"></i>'
        : '<i class="fas fa-circle text-success"></i>';
  }

  CreateGraf(nomgrafica, folio, numeroreg, scaleSelectId = null) {
    const monitorGrafica = new Grafica(nomgrafica);
    let actualizando = false;

    const cargarGrafica = async (numreg) => {
      if (actualizando) return;
      actualizando = true;
      try {
        const datos = await monitorGrafica.getDataDBMonitorMult(folio, numreg);
        monitorGrafica.actualizarGrafica(datos);
      } finally {
        actualizando = false;
      }
    };

    // Carga inicial
    const numregInicial = document.getElementById(numeroreg)?.value || 1;
    cargarGrafica(numregInicial);

    // Actualización cada 20 segundos
    setInterval(() => {
      const numreg = document.getElementById(numeroreg)?.value || 1;
      cargarGrafica(numreg);
    }, 20000);

    // Cambio manual de horas
    document.getElementById(numeroreg)?.addEventListener("change", (e) => {
      cargarGrafica(e.target.value);
    });

    // Cambio de escala de merma
    document.getElementById(scaleSelectId)?.addEventListener("change", (e) => {
      monitorGrafica.updateScale(e.target.value);
    });
  }
}

export class Grafica {
  constructor(idContainer) {
    this.idContainer = idContainer;
    this.chart = null;
  }

  baseOptions(datagraf) {
    return {
      chart: {
        type: "area",
        height: 350,
        toolbar: { show: false },
        animations: { enabled: false },
      },
      series: [
        { name: "Velocidad", data: datagraf.velocidad.map(Number) },
        { name: "Merma",     data: datagraf.merma.map(Number) },
      ],
      xaxis: {
        categories: datagraf.hora,
        tickAmount: 30,
        labels: { rotate: -45, style: { fontSize: "10px" } },
      },
      yaxis: [
        {
          opposite: true,
          title: { text: "Velocidad" },
          tickAmount: 10,
          min: 0,
          max: 2000,
          labels: { formatter: (v) => Math.round(v) },
        },
        {
          title: { text: "Merma (%)" },
          tickAmount: 10,
          labels: { formatter: (v) => v.toFixed(2) },
        },
      ],
      colors: ["#0091fa", "#e02424"],
      fill: {
        type: "gradient",
        gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1 },
      },
      stroke: { curve: "smooth", width: 3 },
      dataLabels: { enabled: false },
      tooltip: { shared: true, intersect: false },
      legend: { position: "top" },
    };
  }

  actualizarGrafica(datagraf) {
    if (this.chart) {
      this.chart.destroy();
      this.chart = null;
    }
    this.chart = new ApexCharts(
      document.getElementById(this.idContainer),
      this.baseOptions(datagraf)
    );
    this.chart.render();
  }

  updateScale(scale) {
    if (!this.chart) return;
    const yaxisMax = scale === "" ? undefined : parseFloat(scale);
    this.chart.updateOptions({
      yaxis: [
        {
          opposite: true,
          title: { text: "Velocidad" },
          tickAmount: 10,
          min: 0,
          max: 2000,
        },
        {
          title: { text: "Merma (%)" },
          tickAmount: 10,
          max: yaxisMax,
        },
      ],
    });
  }

  async getDataDBMonitorMult(maquina, numeroreg) {
    const respuestaraw = await fetch(
      "php/opc_monitor.php?GetDataMonitorMult&numhrs=" + numeroreg + "&maquina=" + maquina,
    );
    return await respuestaraw.json();
  }
}
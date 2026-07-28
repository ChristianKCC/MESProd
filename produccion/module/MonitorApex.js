// Version con ApexCharts del modulo Monitor.js original.
// Mismos nombres de metodo (CreaGrafica, CreateGraf, datanow, datamaquinaall)
// y mismo endpoint bitacora.php sin cambios - solo cambia como se dibuja.
//
// PARA PROBAR EN UNA SOLA MAQUINA:
//   en monitorpanal.js, cambia solo el import de una linea, por ejemplo:
//     import { Monitor } from "../module/Monitor.js";
//   por:
//     import { Monitor } from "../module/MonitorApex.js";
//   y prueba. Si todo bien, luego se aplica a los demas archivos que lo usan.

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

  async datamaquinaall(merma, ta, tp, estado, rc, cc, maquina, tpt = "", vel = "") {
    const respuestaraw = await fetch(
      "../bitacora/php/bitacora.php?getDataNowMult&maquina=" + maquina,
    );
    const respuesta = await respuestaraw.json();
    let mermacalc = 0;
    mermacalc = ((respuesta[0].rechazos / respuesta[0].cortes) * 100).toFixed(2) + "";
    isNaN(mermacalc) ? (mermacalc = 0) : (mermacalc = mermacalc);
    document.getElementById(merma).innerHTML = mermacalc + " %";
    document.getElementById(ta).innerHTML = respuesta[0].TiempoarribaTurno.toFixed(0) + " m";
    document.getElementById(tp).innerHTML = respuesta[0].TiempoabajoTurno.toFixed(0) + " m";
    document.getElementById(cc).innerHTML = respuesta[0].cortes;
    document.getElementById(rc).innerHTML = respuesta[0].rechazos;
    vel != "" && (document.getElementById(vel).innerHTML = respuesta[0].velocidadact);
    let tpcalc =
      (respuesta[0].TiempoabajoTurno /
        (respuesta[0].TiempoabajoTurno + respuesta[0].TiempoarribaTurno)) *
      100;
    isNaN(tpcalc) ? (tpcalc = 0) : (tpcalc = tpcalc);
    tpt == "" ? "" : (document.getElementById(tpt).innerHTML = tpcalc.toFixed(2) + " %");
    document.getElementById(estado).innerHTML =
      respuesta[0].estado === 0
        ? '<i class="fas fa-circle text-danger"></i>'
        : '<i class="fas fa-circle text-success"></i>';
  }

  CreaGrafica(maquinainput, nomgrafica) {
    const monitorGrafica = new Grafica(nomgrafica);
    monitorGrafica.getDataDBMonitorMult(maquinainput, 1).then((datagraf) => {
      monitorGrafica.crearGrafica(datagraf);
    });
  }

  CreateGraf(nomgrafica, folio, numeroreg, scaleSelectId = null) {
    const monitorGrafica = new Grafica(nomgrafica);
    this.CreaGrafica(folio, nomgrafica);
    setInterval(() => {
      const numreg = document.getElementById(numeroreg).value;
      monitorGrafica.getDataDBMonitorMult(folio, numreg).then((respuesta) => {
        monitorGrafica.actualizarGrafica(respuesta);
      });
    }, 20000);

    document.getElementById(numeroreg).addEventListener("change", (e) => {
      monitorGrafica.getDataDBMonitorMult(folio, e.target.value).then((respuesta) => {
        monitorGrafica.actualizarGrafica(respuesta);
      });
    });

    // Escuchar cambios en el select de escala de merma
    const scaleSelect = document.getElementById(scaleSelectId);
    if (scaleSelect) {
      scaleSelect.addEventListener("change", (e) => {
        const scaleValue = e.target.value;
        monitorGrafica.updateScale(scaleValue);
      });
    }
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
        animations: { enabled: true, easing: "easeinout", speed: 400 },
      },
      series: [
        { name: "Velocidad", data: datagraf.velocidad.map(Number), yAxisIndex: 0 },
        { name: "Merma", data: datagraf.merma.map(Number), yAxisIndex: 1 },
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
        },
        {
          title: { text: "Merma (%)" },
          tickAmount: 10,
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

  crearGrafica(datagraf) {
    this.chart = new ApexCharts(document.getElementById(this.idContainer), this.baseOptions(datagraf));
    this.chart.render();
  }

  actualizarGrafica(datagraf) {
    if (!this.chart) {
      this.crearGrafica(datagraf);
      return;
    }
    this.chart.updateOptions({
      series: [
        { name: "Velocidad", data: datagraf.velocidad.map(Number) },
        { name: "Merma", data: datagraf.merma.map(Number) },
      ],
      xaxis: { categories: datagraf.hora, tickAmount: 30 },
    });
  }

  updateScale(scale) {
    if (!this.chart) return;
    
    const yaxisMax = scale === "" ? undefined : parseFloat(scale);
    
    // Obtener configuración actual para preservar el eje de Velocidad (índice 0)
    const currentYaxis = this.chart.w.config.yaxis || [];
    const velocityAxisConfig = currentYaxis[0] || {};
    
    this.chart.updateOptions({
      yaxis: [
        {
          opposite: true,
          title: { text: "Velocidad" },
          tickAmount: 10,
          min: velocityAxisConfig.min,
          max: velocityAxisConfig.max,  // PRESERVA Velocidad
        },
        {
          title: { text: "Merma (%)" },
          tickAmount: 10,
          max: yaxisMax,  // ACTUALIZA Merma
        },
      ],
    });
  }

  async getDataDBMonitor() {
    let numreg = document.getElementById("numregmonitor").value;
    const respuestaraw = await fetch("../Bitacora/php/bitacora.php?GetDataMonitor&numhrs=" + numreg);
    const respuesta = await respuestaraw.json();
    return respuesta;
  }

  async getDataDBMonitorMult(maquina, numeroreg) {
    const respuestaraw = await fetch(
      "../Bitacora/php/bitacora.php?GetDataMonitorMult&numhrs=" + numeroreg + "&maquina=" + maquina,
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
}
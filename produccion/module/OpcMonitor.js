export class OpcMonitor {
  async datanow(maquina, ids) {
    const respuestaraw = await fetch(
      "php/opc_monitor.php?getDataNow&maquina=" + maquina,
    );
    const respuesta = await respuestaraw.json();
    if (respuesta.length === 0) return;

    const d = respuesta[0];
    document.getElementById(ids.velocidad).innerHTML = d.velocidadActual + " m/min";
    document.getElementById(ids.merma).innerHTML = d.merma + " %";
    document.getElementById(ids.paros).innerHTML = d.paros;
    document.getElementById(ids.porcentajeTiempoPerdido).innerHTML =
      d.porcentajeTiempoPerdido + " %";
    document.getElementById(ids.turno).innerHTML = "Turno " + d.turno;
    document.getElementById(ids.estado).innerHTML =
      d.corriendoParada == 0
        ? '<i class="fas fa-circle text-danger"></i>'
        : '<i class="fas fa-circle text-success"></i>';
  }

  CreateGraf(idCanvas, maquina, numregInputId, scaleSelectId = null, defaultNumreg = 1) {
    const grafica = new OpcGrafica(idCanvas);

    const cargar = (numreg) => {
      grafica.getDataDB(maquina, numreg).then((data) => grafica.render(data));
    };

    cargar(document.getElementById(numregInputId)?.value || defaultNumreg);

    setInterval(() => {
      const numreg = document.getElementById(numregInputId)?.value || defaultNumreg;
      grafica.getDataDB(maquina, numreg).then((data) => grafica.update(data));
    }, 20000);

    const input = document.getElementById(numregInputId);
    if (input) {
      input.addEventListener("change", (e) => cargar(e.target.value));
    }

    // Escuchar cambios en el select de escala de merma
    const scaleSelect = document.getElementById(scaleSelectId);
    if (scaleSelect) {
      scaleSelect.addEventListener("change", (e) => {
        const scaleValue = e.target.value;
        grafica.updateScale(scaleValue);
      });
    }

    return grafica;
  }
}

class OpcGrafica {
  constructor(idContainer) {
    this.idContainer = idContainer;
    this.chart = null;
  }

  async getDataDB(maquina, numhrs) {
    const respuestaraw = await fetch(
      "php/opc_monitor.php?getDataMonitor&maquina=" + maquina + "&numhrs=" + numhrs,
    );
    return await respuestaraw.json();
  }

  baseOptions(data) {
    console.log(data);
    return {
      chart: {
        type: "area",
        height: 300,
        toolbar: { show: false },
        animations: { enabled: true, easing: "easeinout", speed: 400 },
      },
      series: [
        { name: "Velocidad", data: data.velocidad.map(Number), yAxisIndex: 0 },
        { name: "Merma", data: data.merma.map(Number), yAxisIndex: 1 },
      ],
      xaxis: {
        categories: data.hora,
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

  render(data) {
    this.chart = new ApexCharts(
      document.getElementById(this.idContainer),
      this.baseOptions(data),
    );
    this.chart.render();
  }

  update(data) {
    if (!this.chart) {
      this.render(data);
      return;
    }
    this.chart.updateOptions({
      series: [
        { name: "Velocidad", data: data.velocidad.map(Number) },
        { name: "Merma", data: data.merma.map(Number) },
      ],
      xaxis: { categories: data.hora, tickAmount: 30 },
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
}
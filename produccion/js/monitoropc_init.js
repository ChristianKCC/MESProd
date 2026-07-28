import { OpcMonitor } from "../module/OpcMonitor.js";

const OpcMonitorObj = new OpcMonitor();

const NO_MAQUINA = 67;

const ids = {
  velocidad: "velocidadOpc67",
  merma: "mermaOpc67",
  paros: "parosOpc67",
  porcentajeTiempoPerdido: "pctPerdidoOpc67",
  turno: "turnoOpc67",
  estado: "estadoOpc67",
};

// CreateGraf ahora acepta scaleSelectId como parámetro
OpcMonitorObj.CreateGraf("GrafOpc67", NO_MAQUINA, "numregOpc67", "scaleMermaOpc67");

setInterval(() => OpcMonitorObj.datanow(NO_MAQUINA, ids), 20000);
OpcMonitorObj.datanow(NO_MAQUINA, ids);
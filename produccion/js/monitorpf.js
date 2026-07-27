import { Monitor } from "../module/Monitor.js";
const MonitorObj = new Monitor();

MonitorObj.CreateGraf("GrafMaquina", 76, "numregmonitor1");
MonitorObj.CreateGraf("GrafMaquina2", 73, "numregmonitor2");
MonitorObj.CreateGraf("GrafMaquina3", 74, "numregmonitor3");
MonitorObj.CreateGraf("GrafMaquina4", 77, "numregmonitor4");
MonitorObj.CreateGraf("GrafMaquina5", 137, "numregmonitor5");

setInterval(
  () =>
    MonitorObj.datamaquinaall(
      "mermamp12",
      "tamp12",
      "tpmp12",
      "estadomp12",
      "rc12",
      "cc12",
      76,
      "tptBCM4",
      "velocidadBCM4",
    ),
  1000,
);
setInterval(
  () =>
    MonitorObj.datamaquinaall(
      "mermamp13",
      "tamp13",
      "tpmp13",
      "estadomp13",
      "rc13",
      "cc13",
      73,
      "tptBCM3",
      "velocidadBCM3",
    ),
  1000,
);
setInterval(
  () =>
    MonitorObj.datamaquinaall(
      "mermamp14",
      "tamp14",
      "tpmp14",
      "estadomp14",
      "rc14",
      "cc14",
      74,
      "tptPE10",
      "velocidadPE10",
    ),
  1000,
);
setInterval(
  () =>
    MonitorObj.datamaquinaall(
      "mermamp15",
      "tamp15",
      "tpmp15",
      "estadomp15",
      "rc15",
      "cc15",
      77,
      "tptBCM1",
      "velocidadBCM1",
    ),
  1000,
);
setInterval(
  () =>
    MonitorObj.datamaquinaall(
      "mermamp16",
      "tamp16",
      "tpmp16",
      "estadomp16",
      "rc16",
      "cc16",
      137,
      "tptMP16",
      "velocidadMP16",
    ),
  1000,
);

import { Monitor } from "../module/Monitor.js";
const MonitorObj = new Monitor();

MonitorObj.CreateGraf("GrafMaquina", 60, "numregmonitor1");
MonitorObj.CreateGraf("GrafMaquina2", 61, "numregmonitor2");
MonitorObj.CreateGraf("GrafMaquina3", 62, "numregmonitor3");
MonitorObj.CreateGraf("GrafMaquina4", 63, "numregmonitor4");
MonitorObj.CreateGraf("GrafMaquina5", 64, "numregmonitor5");
MonitorObj.CreateGraf("GrafMaquina6", 65, "numregmonitor6");

setInterval(
  () =>
    MonitorObj.datamaquinaall(
      "mermamp12",
      "tamp12",
      "tpmp12",
      "estadomp12",
      "rc12",
      "cc12",
      60,
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
      61,
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
      62,
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
      63,
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
      64,
      "tptMP22",
      "velocidadMP22",
    ),
  1000,
);
setInterval(
  () =>
    MonitorObj.datamaquinaall(
      "mermamp17",
      "tamp17",
      "tpmp17",
      "estadomp17",
      "rc17",
      "cc17",
      65,
      "tptMP25",
      "velocidadMP25",
    ),
  1000,
);

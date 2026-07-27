

import { Monitor } from '../module/Monitor.js';
const MonitorObj = new Monitor();

MonitorObj.CreateGraf('GrafMaquina', 81,'numregmonitor1');
MonitorObj.CreateGraf('GrafMaquina2', 82,'numregmonitor2');
MonitorObj.CreateGraf('GrafMaquina3', 83,'numregmonitor3');
MonitorObj.CreateGraf('GrafMaquina4', 84,'numregmonitor4');
MonitorObj.CreateGraf('GrafMaquina5', 97,'numregmonitor5');
setInterval(() => MonitorObj.datamaquinaall('mermamp12', 'tamp12', 'tpmp12', 'estadomp12','rc12','cc12', 81, 'tptpa01','velocidadpa01'), 1000);
setInterval(() => MonitorObj.datamaquinaall('mermamp13', 'tamp13', 'tpmp13', 'estadomp13','rc13','cc13', 82, 'tptpa02','velocidadpa02'), 1000);
setInterval(() => MonitorObj.datamaquinaall('mermamp14', 'tamp14', 'tpmp14', 'estadomp14','rc14','cc14', 83, 'tptpa03','velocidadpa03'), 1000);
setInterval(() => MonitorObj.datamaquinaall('mermamp15', 'tamp15', 'tpmp15', 'estadomp15','rc15','cc15', 84, 'tptpa04','velocidadpa04'), 1000);
setInterval(() => MonitorObj.datamaquinaall('mermamp16', 'tamp16', 'tpmp16', 'estadomp16','rc16','cc16', 97, 'tptpa05','velocidadpa05'), 1000);
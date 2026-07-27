import { Toolsjs } from "../../Tools/Tools.js";
const Tools = new Toolsjs();
class Platicasmaquina {
    reporte() {
        Tools.llnarslc('CatalogoPersonal',"GetSlcDeps", "departamento",0);
    }
  }
  document.getElementById('departamento').addEventListener('change',function(e){
    e.preventDefault();
    const departamento = document.getElementById('departamento').value;
    Tools.llnarslc('CatalogoPersonal',"GetSlcMaquinasxdep&departamento="+departamento, "maquinas",0);
  })
  Platicasmaquina = new Platicasmaquina();
  Platicasmaquina.reporte();
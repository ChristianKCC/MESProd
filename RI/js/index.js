class RI extends Herramientas {
    fillMunicipios() {
        let entidad = document.getElementById("IdClvEntidad").value;
        const herramientas = new Herramientas();
        herramientas.llnarslcCatalogo('CatalogoPersonal',"GetSlcMunicipio&entidad=" + entidad, "ClvMunicipioYDelegacion",0)
    }
    modalNewEmp(event) {
        event.preventDefault();
        document.getElementById("editarempleado").hidden = true;
        document.getElementById("guardar").hidden = false;
        document.getElementById("NoEmp").removeAttribute("readonly");
        document.getElementById("respuesta").innerHTML = '';
        document.getElementById("formnuevoemp").reset();
    }
    async consultEmp(event) {
        event.preventDefault();
        let form = document.getElementById("formconsultaemp");
        let body = "";
        let i = 0;
        const formdata = new FormData(form);
        formdata.append("data", form);
        const respuestaraw = await fetch("./php/empleados.php?consultaempleados", {
            method: "POST",
            body: formdata
        });
        const respuesta = await respuestaraw.json();
        respuesta.forEach(elemento => {
            body += `<tr><td>${++i}</td><td>${elemento.NoEmp}</td><td>${elemento.Nombres}</td><td>${elemento.ApellidoPaterno}</td><td>${elemento.ApellidoMaterno}</td>
        <td>${elemento.NomDep}</td><td>${elemento.NomDepreal}</td><td>${elemento.NomPuesto}</td><td>${elemento.Bajas == 1 ? '<span class="text-danger">Baja</span>' : '<span class="text-success">Activo</span>'}</td>
        <td><a href="./perfil.php?ibm=${elemento.NoEmp}" target="_blank" class="btn btn-sm btn-primary"><i class="fa-solid fa-id-badge"></i></a> 
        <button type="button" class="btn btn-sm btn-warning" onclick="RI.editarempleado(${elemento.NoEmp})"><i class="fa-solid fa-pen-to-square"></i></button></td></tr>`;
        })
        document.getElementById("bodytblcstempleados").innerHTML = body;
    }
    validacamposobligatorios() {
        let noemp = document.getElementById("NoEmp").value;
        let nombres = document.getElementById("Nombres").value;
        let app = document.getElementById("ApellidoPaterno").value;
        let apm = document.getElementById("ApellidoMaterno").value;
        let centrocostos = document.getElementById("IdCentroCosto").value;
        let departamento = document.getElementById("NombreDepartamento").value;
        let imss = document.getElementById("IMSS").value;
        let rfc = document.getElementById("RFC").value;
        let curp = document.getElementById("CURP").value;
        let nvlestudios = document.getElementById("IdClvNivelEstudios").value;
        let docprobatorio = document.getElementById("IdClvProbatorio").value;
        let entidad = document.getElementById("IdClvEntidad").value;
        let delegacion = document.getElementById("ClvMunicipioYDelegacion").value;
        let centrocostosreal = document.getElementById("IdCentroCostoReal").value;
        let nodepreal = document.getElementById("NoDeptoReal").value;
        if (noemp == "" || nombres == "" || app == "" || apm == "" || centrocostos == "" || departamento == "" || imss == "" ||
            rfc == "" || curp == "" || nvlestudios == "" || docprobatorio == "" || entidad == "" || delegacion == "" || centrocostosreal == "" ||
            nodepreal == "" || docprobatorio == "") {
            Swal.fire('LLena todos los campos', '', 'info');
            return false;
        }
    }
    async saveChangeEmp(event) {
        event.preventDefault();
        const ri = new RI();
        if (ri.validacamposobligatorios() === false) return false;
        const form = document.getElementById("formnuevoemp");
        const formdata = new FormData(form);
        formdata.append("data", form);
        const respuestaraw = await fetch("./php/empleados.php?actualizarempleado", {
            method: "POST",
            body: formdata
        });
        const respuesta = await respuestaraw.json();
        respuesta == "ok" ?
            Swal.fire('Información actualizada', '', 'success') :
            Swal.fire('Error', '', 'error');
    }
    async saveEmp(event) {
        event.preventDefault();
        const ri = new RI();
        if (ri.validacamposobligatorios() === false) return false;
        const form = document.getElementById("formnuevoemp");
        const formdata = new FormData(form);
        formdata.append("data", form);
        const respuestaraw = await fetch("./php/empleados.php?guardarempleado", {
            method: "POST",
            body: formdata
        });
        const respuesta = await respuestaraw.json();
        document.getElementById("respuesta").innerHTML = respuesta;
        respuesta == "ok" ?
            Swal.fire('Información actualizada', '', 'success') :
            respuesta == "error" ?
                Swal.fire('Error', '', 'error') :
                Swal.fire('El ususario ya existe', '', 'info');
    }
    async editarempleado($ibm) {
        var modalempleados = new bootstrap.Modal(document.getElementById("nuevoempleado"));
        modalempleados.show();
        document.getElementById("editarempleado").hidden = false;
        document.getElementById("guardar").hidden = true;
        document.getElementById("respuesta").innerHTML = "";
        document.getElementById("NoEmp").setAttribute("readonly", true);
        const data = new FormData();
        data.append("ibm", $ibm);
        const respuestaraw = await fetch("./php/empleados.php?consultaallinfempleados", {
            method: "POST",
            body: data
        });
        const respuesta = await respuestaraw.json();
        document.getElementById("NoEmp").value = respuesta[0].Noemp;
        document.getElementById("Nombres").value = respuesta[0].Nombres;
        document.getElementById("ApellidoPaterno").value = respuesta[0].ApellidoPaterno;
        document.getElementById("ApellidoMaterno").value = respuesta[0].ApellidoMaterno;
        document.getElementById("NombreDepartamento").value = respuesta[0].NombreDepartamento;
        document.getElementById("IMSS").value = respuesta[0].IMSS;
        document.getElementById("RFC").value = respuesta[0].RFC;
        document.getElementById("CURP").value = respuesta[0].CURP;
        document.getElementById("IdClvEstadoCivil").value = respuesta[0].IdClvEstadoCivil;
        document.getElementById("Puesto").value = respuesta[0].Puesto;
        document.getElementById("FechaIngreso").value = respuesta[0].FechaIngreso;
        document.getElementById("Telefono").value = respuesta[0].Telefono;
        document.getElementById("Telefono1").value = respuesta[0].Telefono1;
        document.getElementById("Telefono2").value = respuesta[0].Telefono2;
        document.getElementById("Domicilio").value = respuesta[0].Domicilio;
        document.getElementById("IdClvNivelEstudios").value = respuesta[0].IdClvNivelEstudios;
        document.getElementById("IdClvProbatorio").value = respuesta[0].IdClvDocProbatorio;
        document.getElementById("FechaVencimientoContrato").value = respuesta[0].FechaVencimientoContrato;
        document.getElementById("TipoTrabajador").value = respuesta[0].TipoTrabajador;
        document.getElementById("FechaAntiguedad").value = respuesta[0].FechaAntiguedad;
        document.getElementById("FechaBaja").value = respuesta[0].FechaBaja;
        document.getElementById("OtroMotivoBaja").value = respuesta[0].OtroMotivoBaja;
        document.getElementById("IdClvDiscapacidad").value = respuesta[0].IdClvDiscapacidad;
        document.getElementById("NoHijosDependientes").value = respuesta[0].NoHijosDependientes;
        document.getElementById("IdClvOcupaciones").value = respuesta[0].IdClvOcupaciones;
        document.getElementById("AnioEmisionDocto").value = respuesta[0].AnioEmisionDocto;
        document.getElementById("IdClvInstitucionEducativa").value = respuesta[0].IdClvTipoInstEducativa;
        document.getElementById("NombreEstudioCarrera").value = respuesta[0].NombreEstudioCarrera;
        document.getElementById("JefeInm").value = respuesta[0].JefeInm;
        document.getElementById("NoDeptoReal").value = respuesta[0].NoDeptoReal;
        document.getElementById("IdCentroCosto").value = respuesta[0].IdCentroCosto;
        document.getElementById("IdCentroCostoReal").value = respuesta[0].IdCentroCostoReal;
        document.getElementById("Sexo").value = respuesta[0].Sexo;
        document.getElementById("IdClvEntidad").value = respuesta[0].IdClvEntidad;
        document.getElementById("IdMotivoBaja").value = respuesta[0].IdMotivoBaja == 0 ? "" : respuesta[0].IdMotivoBaja;
        document.getElementById("EmpleadoSindicalizado").checked = respuesta[0].EmpleadoSindicalizado == 1 ? true : false;
        document.getElementById("Bajas").checked = respuesta[0].Bajas == 1 ? true : false;
        document.getElementById("RecibeOferta").checked = respuesta[0].RecibeOferta == 1 ? true : false;
        const herramientas = new Herramientas();
        herramientas.llnarslcCatalogo('CatalogoPersonal',"GetSlcMunicipio&entidad=" + respuesta[0].IdClvEntidad, "ClvMunicipioYDelegacion",0).then(() => {
            document.getElementById("ClvMunicipioYDelegacion").value = respuesta[0].ClvMunicipioYDelegacion;
        });
    }


    inicio() {
        const herramientas = new Herramientas();
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcDeps", "deps", 1);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcPuestos", "Puestos", 1);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcNvlEstudios", "nivestu", 1);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcCentroCostos", "IdCentroCosto",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcDeps", "NombreDepartamento",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcJefeInm", "JefeInm",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcPuestos", "Puesto",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcTipoTrabajador", "TipoTrabajador",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcEstadoCivil", "IdClvEstadoCivil",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcNvlEstudios", "IdClvNivelEstudios",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcDocAprobatorio", "IdClvProbatorio",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcEntidad", "IdClvEntidad",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcClaveInst", "IdClvInstitucionEducativa",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcDiscapacidad", "IdClvDiscapacidad",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcOcupacion", "IdClvOcupaciones",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcMotivoBaja", "IdMotivoBaja",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcCentroCostos", "IdCentroCostoReal",0);
        this.llnarslcCatalogo('CatalogoPersonal',"GetSlcDepsall", "NoDeptoReal",0);
        document.getElementById('IdClvEntidad').addEventListener('change', this.fillMunicipios);
        document.getElementById('mondalnuevoempleado').addEventListener('click', this.modalNewEmp);
        document.getElementById('cempleados').addEventListener('click', this.consultEmp);
        document.getElementById('editarempleado').addEventListener('click', this.saveChangeEmp);
        document.getElementById('guardar').addEventListener('click', this.saveEmp);
        document.getElementById('exportarexcel').addEventListener('click',()=> herramientas.exportartablaexcel('vistatabla'));
    }
    
}


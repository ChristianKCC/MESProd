let clasificacionCurso = document.querySelector(
  'input[name="clasificacionCurso"]:checked',
);
class Cursos extends Herramientas {
  async tblcursosall() {
    const libre = document.getElementById("buscarcurso").value;
    const checkautoriza = document.getElementById("fiteraut");
    const dataf = new FormData();
    dataf.append("libre", libre);
    dataf.append("checkautoriza", checkautoriza.checked ? 1 : 0);
    const respuestaraw = await fetch("./php/creadorcursos.php?tblcursosall", {
      method: "POST",
      body: dataf,
    });
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      body += `<tr><td>${element.id}</td><td>${element.nombre}</td><td>${element.duracion}</td><td>${element.areatem}</td>
            <td>${element.modalidad}</td><td>${element.objetivo}</td><td>${element.direccion}</td>
            <td><button type="button" class="btn btn-sm btn-warning" onclick="cursos.editcurso(${element.id});"><i class="fa-solid fa-pen-to-square"></i></button></td></tr>`;
    });
    document.getElementById("tablacursosautoriza").innerHTML = body;
  }
  async addorremoveitem(event, ruta) {
    event.preventDefault();
    const id = event.target.value;
    const folio = document.getElementById("folio").value;
    const datafecth = new FormData();
    datafecth.append("folio", folio);
    datafecth.append("id", id);
    if (folio === "") {
      Swal.fire("Ups!!!", "No hay un folio seleccionado", "warning");
      return false;
    }
    const dataraw = await fetch("./php/creadorcursos.php?" + ruta, {
      method: "POST",
      body: datafecth,
    });
    const data = await dataraw.json();
    data === "done"
      ? Swal.fire("Listo!!!", "Acción completada correctamente", "success")
      : Swal.fire("UPS!!!", "Hay problemas en la base de datos", "error");
    this.filldataxadd(
      "./php/creadorcursos.php?getInstructoresxadd",
      folio,
      "instructorslc",
    );
    this.filldataxadd(
      "./php/creadorcursos.php?getInstructorespile",
      folio,
      "instructor",
    );
    this.filldataxadd(
      "./php/creadorcursos.php?getPuestosxadd",
      folio,
      "puestosadd",
    );
    this.filldataxadd(
      "./php/creadorcursos.php?getPuestospile",
      folio,
      "puestos",
    );
  }
  async guardarCurso(event) {
    event.preventDefault();
    const dataform = new FormData();
    const folio = document.getElementById("folio").value;
    const nombre = document.getElementById("nombre").value;
    const duracion = document.getElementById("duracion").value;
    const areatem = document.getElementById("DescAreaTematica").value;
    const modalidad = document.getElementById("ModalidadCapacitacion").value;
    const objetivo = document.getElementById("ObjetivoCapacitacion").value;
    const direccion = document.getElementById("direccion").value;
    const clasificacion = document.getElementById("clasificacion").value;
    clasificacionCurso = document.querySelector(
      'input[name="clasificacionCurso"]:checked',
    ).value;
    if (
      nombre === "" ||
      duracion === "" ||
      areatem === "" ||
      modalidad === "" ||
      objetivo === "" ||
      clasificacion === ""
    ) {
      Swal.fire("Ups!!!", "No puede haber campos vacios", "warning");
      return false;
    }
    dataform.append("folio", folio);
    dataform.append("nombre", nombre);
    dataform.append("duracion", duracion);
    dataform.append("areatem", areatem);
    dataform.append("modalidad", modalidad);
    dataform.append("objetivo", objetivo);
    dataform.append("direccion", direccion);
    dataform.append("clasificacion", clasificacion);
    dataform.append("clasificacionCurso", clasificacionCurso);
    const dataraw = await fetch("./php/creadorcursos.php?saveDataCurso", {
      method: "POST",
      body: dataform,
    });
    const data = await dataraw.json();
    data === "done"
      ? Swal.fire("Listo!!!", "Informacion Actualizada con exito", "success")
      : ((document.getElementById("folio").value = data),
        Swal.fire(
          "Listo!!!",
          "Informacion Guardada con exito, nuevo curso con id: " + data,
          "success",
        ));
    const curso = new Cursos();
    curso.tblcursosall();
    curso.filldataxadd(
      "./php/creadorcursos.php?getInstructoresxadd",
      folio,
      "instructorslc",
    );
    curso.filldataxadd(
      "./php/creadorcursos.php?getInstructorespile",
      folio,
      "instructor",
    );
    curso.filldataxadd(
      "./php/creadorcursos.php?getPuestosxadd",
      folio,
      "puestosadd",
    );
    curso.filldataxadd(
      "./php/creadorcursos.php?getPuestospile",
      folio,
      "puestos",
    );
  }
  limpiar() {
    document.getElementById("folio").value = "";
    document.getElementById("nombre").value = "";
    document.getElementById("duracion").value = "";
    document.getElementById("DescAreaTematica").value = "";
    document.getElementById("ModalidadCapacitacion").value = "";
    document.getElementById("ObjetivoCapacitacion").value = "";
    document.getElementById("direccion").value = "";
    document.getElementById("clasificacion").value = "";
    document.getElementById("instructorslc").innerHTML = "";
    document.getElementById("instructor").innerHTML = "";
    document.getElementById("puestosadd").innerHTML = "";
    document.getElementById("puestos").innerHTML = "";
    document.getElementById("tblpreguntas").innerHTML = "";
    document.getElementById("archivo").value = "";
    cursos.emptypregunta();
  }
  eliminarCurso(event) {
    event.preventDefault();
    const folio = document.getElementById("folio").value;
    if (folio === "") {
      Swal.fire("UPS!!!", "No hay un folio seleccionado", "warning");
      return false;
    }
    Swal.fire({
      title: "¿Estás seguro?",
      text: "¡Este cambio ya no se puede revertir!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "¡Si, eliminar!",
    }).then(async (result) => {
      if (result.isConfirmed) {
        const curso = new Cursos();
        const dataf = new FormData();
        dataf.append("folio", folio);
        const dataraw = await fetch("./php/creadorcursos.php?deleteCurso", {
          method: "POST",
          body: dataf,
        });
        const data = await dataraw.json();
        data === "done"
          ? (Swal.fire("Listo!!!", "Curso eliminado", "success"),
            curso.tblcursosall(),
            curso.limpiar())
          : Swal.fire("Ups!!!", "Error al eliminar el curso", "error");
      }
    });
  }
  async autorizaCurso(event) {
    event.preventDefault();
    const folio = document.getElementById("folio").value;
    if (folio === "") {
      Swal.fire("UPS!!!", "No hay un folio seleccionado", "warning");
      return false;
    }
    const curso = new Cursos();
    const dataf = new FormData();
    dataf.append("folio", folio);
    const dataraw = await fetch("./php/creadorcursos.php?autorizacurso", {
      method: "POST",
      body: dataf,
    });
    const data = await dataraw.json();
    data === "done"
      ? (Swal.fire("Listo!!!", "Curso autorizado", "success"), curso.limpiar())
      : Swal.fire("Ups!!!", "Hay un problema", "error");
  }
  emptypregunta() {
    document.getElementById("pregunta").value = "";
    document.getElementById("respuesta1").value = "";
    document.getElementById("respuesta2").value = "";
    document.getElementById("respuesta3").value = "";
    document.getElementById("respuestac").value = "";
  }
  async savePregunta(event) {
    event.preventDefault();
    const folio = document.getElementById("folio").value;
    if (folio === "") {
      Swal.fire("UPS!!!", "No hay un folio seleccionado", "warning");
      return false;
    }
    const pregunta = document.getElementById("pregunta").value;
    const respuesta1 = document.getElementById("respuesta1").value;
    const respuesta2 = document.getElementById("respuesta2").value;
    const respuesta3 = document.getElementById("respuesta3").value;
    const respuestac = document.getElementById("respuestac").value;
    if (
      pregunta === "" ||
      respuesta1 === "" ||
      respuesta2 === "" ||
      respuesta3 === "" ||
      respuestac === ""
    ) {
      Swal.fire("UPS!!!", "No puede haber campos vacíos", "warning");
      return false;
    }
    const cursos = new Cursos();
    const dataf = new FormData();
    dataf.append("folio", folio);
    dataf.append("pregunta", pregunta);
    dataf.append("respuesta1", respuesta1);
    dataf.append("respuesta2", respuesta2);
    dataf.append("respuesta3", respuesta3);
    dataf.append("respuestac", respuestac);
    const dataraw = await fetch("./php/creadorcursos.php?savePregunta", {
      method: "POST",
      body: dataf,
    });
    const data = await dataraw.json();
    data === "done"
      ? (Swal.fire("Listo!!!", "Pregunta guardada", "success"),
        cursos.tblpreguntas(),
        cursos.emptypregunta())
      : Swal.fire("Ups!!!", "Hay un problema", "error");
  }
  async deletePregunta(id) {
    const dataf = new FormData();
    dataf.append("id", id);
    const dataraw = await fetch("./php/creadorcursos.php?deletePregunta", {
      method: "POST",
      body: dataf,
    });
    dataraw.ok
      ? Swal.fire("Listo!!!", "Pregunta eliminada", "success")
      : Swal.fire(
          "Error!!!",
          "Hay problemas contacta al administrador",
          "error",
        );
    this.tblpreguntas();
  }
  async tblpreguntas() {
    const folio = document.getElementById("folio").value;
    const dataf = new FormData();
    dataf.append("folio", folio);
    const dataraw = await fetch("./php/creadorcursos.php?getDataPreguntas", {
      method: "POST",
      body: dataf,
    });
    const data = await dataraw.json();
    if (data === "error") {
      Swal.fire("Ups!!!", "Hay un problema", "error");
      return false;
    }
    let body = "";
    data.forEach((element) => {
      body += `<tr><td>${element.folio}</td><td>${element.pregunta}</td><td>${element.respuesta1}</td><td>${element.respuesta2}</td>
            <td>${element.respuesta3}</td><td>${element.respuestac}</td><td><button class='btn btn-sm btn-danger' onclick='cursos.deletePregunta(${element.folio})'><i class="fa-solid fa-delete-left"></i></button></td></tr>`;
    });
    document.getElementById("tblpreguntas").innerHTML = body;
  }
  async uploadCurso(event) {
    event.preventDefault();
    const folio = document.getElementById("folio").value;
    const fileInput = document.getElementById("archivo");
    const file = fileInput.files[0];
    if (folio === "") {
      Swal.fire("Ups!!!", "No hay un folio seleccionado", "warning");
      return false;
    } else if (file) {
      const formData = new FormData();
      formData.append("folio", folio);
      formData.append("filec", file);
      const response = await fetch("./php/creadorcursos.php?uploadFile", {
        method: "POST",
        body: formData,
      });
      response.ok
        ? Swal.fire("Listo!!!", "Archivo cargado", "success")
        : Swal.fire(
            "Error!!!",
            "Hay problemas al cargar la información",
            "error",
          );
    } else {
      Swal.fire("Ups!!!", "Debes cargar un archivo", "warning");
      return false;
    }
  }
  start() {
    this.llnarslcCatalogo(
      "CatalogoCursos",
      "GetSlcAreatematica",
      "DescAreaTematica",
      0,
    );
    this.llnarslcCatalogo(
      "CatalogoCursos",
      "GetSlcModalidadcap",
      "ModalidadCapacitacion",
      0,
    );
    this.llnarslcCatalogo(
      "CatalogoCursos",
      "GetSlcObjcapasitacion",
      "ObjetivoCapacitacion",
      0,
    );
    this.llnarslcCatalogo(
      "CatalogoCursos",
      "GetSlcClasificacion",
      "clasificacion",
      0,
    );
    this.tblcursosall();
    document
      .getElementById("instructorslc")
      .addEventListener("dblclick", (e) =>
        this.addorremoveitem(e, "addInstructor"),
      );
    document
      .getElementById("instructor")
      .addEventListener("dblclick", (e) =>
        this.addorremoveitem(e, "removeInstructor"),
      );
    document
      .getElementById("puestos")
      .addEventListener("dblclick", (e) =>
        this.addorremoveitem(e, "addPuestos"),
      );
    document
      .getElementById("puestosadd")
      .addEventListener("dblclick", (e) =>
        this.addorremoveitem(e, "removePuestos"),
      );
    document
      .getElementById("guardarcurso")
      .addEventListener("click", this.guardarCurso);
    document
      .getElementById("eliminar")
      .addEventListener("click", this.eliminarCurso);
    document.getElementById("limpiar").addEventListener("click", this.limpiar);
    document
      .getElementById("autoriza")
      .addEventListener("click", this.autorizaCurso);
    document
      .getElementById("crearpregunta")
      .addEventListener("click", this.savePregunta);
    document
      .getElementById("fitertblcurso")
      .addEventListener("click", this.tblcursosall);
    document
      .getElementById("uploadcurso")
      .addEventListener("click", this.uploadCurso);
  }
  async editcurso($idcurso) {
    const truck_modal = document.querySelector("#cursosmodal");
    const modal = bootstrap.Modal.getInstance(truck_modal);
    modal.hide();
    const idcurso = new FormData();
    idcurso.append("idcurso", $idcurso);
    const respuestaraw = await fetch("./php/creadorcursos.php?consultarcurso", {
      method: "POST",
      body: idcurso,
    });
    const data = await respuestaraw.json();
    document.getElementById("folio").value = data[0].idcurso;
    document.getElementById("nombre").value = data[0].nombre;
    document.getElementById("duracion").value = data[0].duracion;
    document.getElementById("DescAreaTematica").value = data[0].idareatematica;
    document.getElementById("ModalidadCapacitacion").value =
      data[0].idmodcapacitacion;
    document.getElementById("ObjetivoCapacitacion").value =
      data[0].idobjcapacitacion;
    document.getElementById("direccion").value = data[0].direcccioncurso;
    document.getElementById("clasificacion").value = data[0].clasificacion;
    const val = data[0].clasificacionCurso;
    if (val != null) {
      const radio = document.querySelector(
        `input[name="clasificacionCurso"][value="${val}"]`,
      );
      if (radio) radio.checked = true;
    } else {
      document
        .querySelectorAll('input[name="clasificacionCurso"]')
        .forEach((r) => (r.checked = false));
    }
    this.filldataxadd(
      "./php/creadorcursos.php?getInstructoresxadd",
      data[0].idcurso,
      "instructorslc",
    );
    this.filldataxadd(
      "./php/creadorcursos.php?getInstructorespile",
      data[0].idcurso,
      "instructor",
    );
    this.filldataxadd(
      "./php/creadorcursos.php?getPuestosxadd",
      data[0].idcurso,
      "puestosadd",
    );
    this.filldataxadd(
      "./php/creadorcursos.php?getPuestospile",
      data[0].idcurso,
      "puestos",
    );
    this.tblpreguntas();
  }
}

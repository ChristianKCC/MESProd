import { Toolsjs } from "../../Tools/Tools.js";

const SIGWEB_URL = "http://localhost:47289/SigWeb/";

class FirmaModule {
  constructor() {
    this.firmaFinalizada = false;
    this.tmr = null;
    this.ctx = null;
    this.tblenc();
  }
  
  // Funcion de llenado de datos
  async tblenc() {
    const respuetaraw = await fetch("php/index.php?tblenc");
    const respuesta = await respuetaraw.json();
    let body = "";

    respuesta.forEach(elemento => {
      body += `
        <tr>
          <td>${elemento.FD_id}</td>
          <td>${elemento.FD_noemp}</td>
          <td>${elemento.FD_nombre}</td>
          <td>${elemento.FD_departamento}</td>
          <td>${elemento.FD_puesto}</td>
          <td>${elemento.FD_fechaRegistro}</td>
          <td>`;

      if (elemento.FD_imgSign) {
        body += `
        <button class="btn btn-sm btn-primary" onclick="verFirma('${elemento.FD_imgSign}', '${elemento.FD_nombre}')">
          <i class="fa-solid fa-file-image"></i> Ver firma
        </button>`;
        
        // Validaciones para que el solo el usuario en la sesion pueda ver su propia firma
        // if(elemento.FD_usuario === elemento.FD_noemp) {
        //   body += `
        //   <button class="btn btn-sm btn-primary" onclick="verFirma('${elemento.FD_imgSign}', '${elemento.FD_nombre}')">
        //     <i class="fa-solid fa-file-image"></i> Ver firma
        //   </button>`;
        // } else {
        //   body += `
        //   <span class="text-danger small">
        //     Solo puedes ver tu propia firma
        //   </span>`;
        // }        
      }
      
      if (elemento.FD_permisoEdicion) {
        if (elemento.FD_imgSign) {          
          body += `
            <button class="btn btn-sm btn-warning" onclick="editFirma(${elemento.FD_id}, '${elemento.FD_imgSign}', '${elemento.FD_noemp}')">
              <i class="fa-solid fa-pen-to-square"></i> Capturar/Editar
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarFirma(${elemento.FD_id})">
              <i class="fa-solid fa-trash"></i> Eliminar
            </button>`;
        } else {
          body += `
            <span class="text-danger small">
              No tienes una firma registrada, contacta al departamento de sistemas.
            </span>`;
        }
      }

      body += `</td></tr>`;
    });

    document.getElementById("tblFD").innerHTML = body;
  }

  async init() {
    this.detectarDispositivo();
    

    // Listener para buscar info del empleado
    document.getElementById("noemp").addEventListener("keyup", () => {
      const noemp = document.getElementById("noemp").value;
      this.getinfoemp(noemp);
    });
  }

  async getinfoemp(noemp) {
    const respuetaraw = await fetch("../Components/CatalogoSeguridad.php?datosemp&noemp=" + noemp);
    const respuesta = await respuetaraw.json();
    if (respuesta.length === 0) {
      document.getElementById("nombre").value = "";
      document.getElementById("departamento").value = "";
      document.getElementById("puesto").value = "";
      return;
    }
    document.getElementById("nombre").value = respuesta[0].nombre;
    document.getElementById("departamento").value = respuesta[0].departamento;
    document.getElementById("puesto").value = respuesta[0].puesto;
  }

  async detectarDispositivo() {
    try {
      const version = await fetch(SIGWEB_URL + "SigWebVersion").then(r => r.text());
      const conectado = await fetch(SIGWEB_URL + "TabletConnectQuery").then(r => r.text());

      const el = document.getElementById("dispositivo");
      const el1 = document.getElementById("dispositivo-edit");

      if (el && el1) {
        if (parseInt(conectado) === 1) {
          el.innerHTML  = "<code>SigWeb instalado — versión " + version + " | Tableta detectada y conectada</code>";
          el1.innerHTML = "<code>SigWeb instalado — versión " + version + " | Tableta detectada y conectada</code>";
        } else {
          el.innerHTML  = "<code>SigWeb instalado — versión " + version + " | Tableta NO DETECTADA O CONECTADA</code>";
          el1.innerHTML = "<code>SigWeb instalado — versión " + version + " | Tableta NO DETECTADA O CONECTADA</code>";
        }
      }
    } catch {
      const el = document.getElementById("dispositivo");
      const el1 = document.getElementById("dispositivo-edit");
      if (el && el1) {
        el.innerHTML = "SigWeb no instalado. <a href='https://www.topazsystems.com/sdks/sigweb.html' target='_blank'>Instalar SigWeb</a>";
        el1.innerHTML = "SigWeb no instalado. <a href='https://www.topazsystems.com/sdks/sigweb.html' target='_blank'>Instalar SigWeb</a>";
      }
    }
  }

  async iniciarCaptura() {
    const canvas = document.getElementById("sig-canvas");
    this.ctx = canvas.getContext("2d");

    SetTabletState(0, this.tmr);
    ClearTablet();
    SetDisplayXSize(canvas.width);
    SetDisplayYSize(canvas.height);
    SetJustifyMode(0);

    this.tmr = SetTabletState(1, this.ctx, 50);
    this.firmaFinalizada = false;
    this.mostrarStatus("Firmando... use el lápiz sobre la tableta.", "alert-info");
  }

  async finalizarCaptura() {
    if (this.tmr != null) {
      SetTabletState(0, this.tmr);
      this.tmr = null;
    }
    this.firmaFinalizada = true;
    this.mostrarStatus("Firma finalizada. Ahora puede guardarla.", "alert-warning");
  }

  async limpiarFirma() {
    const canvas = document.getElementById("sig-canvas");
    if (!canvas) return;

    const ctxLocal = canvas.getContext("2d");
    const status = document.getElementById("status");

    if (this.tmr != null) {
      SetTabletState(0, this.tmr);
      this.tmr = null;
    }

    ClearTablet();
    // ctxLocal.clearRect(0, 0, canvas.width, canvas.height);
    this.firmaFinalizada = false;

    const imgPreview = document.getElementById("firma-preview");
    if (imgPreview) imgPreview.classList.add("d-none");

    // this.mostrarStatus("Canvas limpiado. Listo para nueva firma.", "alert-secondary");
    if (canvas) {                  
      ctxLocal.clearRect(0, 0, canvas.width, canvas.height);    
    }    
    if (status) {
      status.textContent = "";
      status.classList.add("d-none");
    }
  }  

  async guardarFirma() {
    const noemp = document.getElementById("noemp").value.trim();
    const nombre = document.getElementById("nombre").value.trim();
    const departamento = document.getElementById("departamento").value.trim();
    const puesto = document.getElementById("puesto").value.trim();

    // 
    if (!noemp || !nombre || !departamento || !puesto) {
      Swal.fire({ icon: 'warning', title: 'Datos incompletos',
        text: 'Verifica que los campos de No. Emp, Nombre, Departamento y Puesto esten completos antes de guardar.', confirmButtonText: 'Entendido' });
      return;
    }

    if (!this.firmaFinalizada) {
      Swal.fire({ icon: 'warning', title: 'Finaliza / Detén la captura antes',
        text: 'Debes presionar "Detener captura" antes de guardar.', confirmButtonText: 'Entendido' });
      return;
    }

    if (this.tmr != null) {
      SetTabletState(0, this.tmr);
      this.tmr = null;
    }

    if (NumberOfTabletPoints() == 0) {
       Swal.fire({ 
        icon: 'warning', 
        title: 'Firma vacía', 
        text: 'No se a dibujado ninguna firma. Intenta nuevamente.', 
        confirmButtonText: 'Entendido' 
      });
      return;
    }

    SetImageXSize(400);
    SetImageYSize(150);
    SetImagePenWidth(5);

    GetSigImageB64(b64 => {
      if (!b64 || b64.length === 0) {
        this.mostrarStatus("No hay firma capturada.", "alert-danger");
        return;
      }

      const payload = { 
            imagen_b64: b64, 
            usuario_id: noemp, 
            nombre_usuario: nombre, 
            departamento_usuario: departamento,  
            puesto_usuario: puesto
          };

      fetch("php/index.php?guardarFirma", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      })
      .then(r => r.json())
      .then(resp => {
        if (resp.ok) {                    
          ClearTablet();
          this.firmaFinalizada = false;          

          const imgPreview = document.getElementById("firma-preview");
          if (imgPreview) {
            imgPreview.src = "data:image/png;base64," + b64;
            imgPreview.classList.remove("d-none");
          }

          Swal.fire({ 
            icon: 'success', 
            title: 'Firma guardada correctamente', 
            text: 'Firma guardada correctamente, con el nombre: ' + resp.ruta, 
            confirmButtonText: 'Salir' 
          });

          const sigCanvas = document.getElementById("sig-canvas");                    
          const status = document.getElementById("status");

          if (sigCanvas) {            
            const ctx = sigCanvas.getContext("2d");
            ctx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
            document.getElementById("noemp").value = "";
            document.getElementById("nombre").value = "";
            document.getElementById("departamento").value = "";
            document.getElementById("puesto").value = "";
          }
          if (imgPreview) {
            imgPreview.src = "";
            imgPreview.classList.add("d-none");
          }
          if (status) {
            status.textContent = "";
            status.classList.add("d-none");
          }
          this.tblenc();
        } else {
          Swal.fire({ 
            icon: 'error', 
            title: 'No se pudo guardar', 
            text: resp.error, 
            confirmButtonText: 'Entendido' 
          });
        }
      })
      .catch(e =>         
        Swal.fire({ 
          icon: 'error', 
          title: 'Error en la conexion a la BD',
          text: resp.error, 
          confirmButtonText: 'Entendido' 
        })
      );
    });
  }

  async mostrarStatus(msg, tipo) {
    const el = document.getElementById("status");
    el.textContent = msg;
    el.className = "alert " + tipo;
    el.classList.remove("d-none");
  }

  async mostrarStatusEdicion(msg, tipo) {
    const el = document.getElementById("status-edit");
    el.textContent = msg;
    el.className = "alert " + tipo;
    el.classList.remove("d-none");
  }

  async verFirma(ruta, nombre) {
    const img = document.getElementById("firma-preview-modal");    
    img.src = `./firmas/${ruta}`;

    const modal = new bootstrap.Modal(document.getElementById("modalFirma"));
    document.getElementById("autorFirma").textContent = nombre;
    modal.show();
  }
  
  // ----------------------------------------------------------------------------------------------
  // Funciones de edicion de datos
  // ----------------------------------------------------------------------------------------------
  async editFirma(id, rutaExistente, noemp) {
    window.currentFirmaId = id;
    window.currentNoemp = noemp;

    const img = document.getElementById("firma-preview-edit");  
    if (rutaExistente) {
      img.src = `./firmas/${rutaExistente}`;
      img.classList.remove("d-none");
    } else {
      img.src = "";
      img.classList.add("d-none");
    }

    const modal = new bootstrap.Modal(document.getElementById("modalEditFirma"));
    modal.show();
  }

  // Métodos específicos para el modal de edición
  async iniciarCapturaEdit() {
    const canvas = document.getElementById("sig-canvas-edit");
    const ctx = canvas.getContext("2d");

    // detener capturas previas
    if (window.tmrEdit) {
      SetTabletState(0, window.tmrEdit);
      window.tmrEdit = null;
    } else {
      SetTabletState(0, null);
    }

    ClearTablet();
    SetDisplayXSize(canvas.width);
    SetDisplayYSize(canvas.height);
    SetJustifyMode(0);

    window.tmrEdit = SetTabletState(1, ctx, 50);
    this.mostrarStatusEdicion("Firmando... use el lápiz sobre la tableta.", "alert-info");
  }


  async finalizarCapturaEdit() {
    if (window.tmrEdit) {
      SetTabletState(0, window.tmrEdit);
      window.tmrEdit = null;
    }    
    this.firmaFinalizada = true;
    this.mostrarStatusEdicion("Firma finalizada. Ahora puede guardarla.", "alert-warning");    
  }

  async limpiarFirmaEdit() {
    const canvas = document.getElementById("sig-canvas-edit");
    const imgPreview = document.getElementById("firma-preview-edit");
    const status = document.getElementById("status-edit");

    const ctx = canvas.getContext("2d");
    ClearTablet();
    ctx.clearRect(0, 0, canvas.width, canvas.height);    
    // this.mostrarStatusEdicion("Canvas limpiado. Listo para nueva firma.", "alert-secondary");
    if (canvas) {            
      
      ctx.clearRect(0, 0, canvas.width, canvas.height);    
    }    
    if (status) {
      status.textContent = "";
      status.classList.add("d-none");
    }
  }

  // Funcion de guardar edicion
  async guardarFirmaEdit() {
    if (!window.currentFirmaId) {
      Swal.fire({ icon: 'error', title: 'Error', text: 'No se encontró el ID de la firma.' });
      return;
    }

    // Validar que la captura esté finalizada
    if (!this.firmaFinalizada) {
      Swal.fire({
        icon: 'warning',
        title: 'Finaliza / Detén la captura antes',
        text: 'Debes presionar "Detener captura" antes de guardar.',
        confirmButtonText: 'Entendido'
      });
      return;
    }

    // Detener captura si aún hay un timer activo
    if (this.tmrEdit != null) {
      SetTabletState(0, this.tmrEdit);
      this.tmrEdit = null;
    }

    // Validar que haya firma
    if (NumberOfTabletPoints() == 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Firma vacía',
        text: 'No se ha dibujado ninguna firma. Intenta nuevamente.',
        confirmButtonText: 'Entendido'
      });
      return;
    }

    // Configuración de imagen
    SetImageXSize(400);
    SetImageYSize(150);
    SetImagePenWidth(5);

    GetSigImageB64(b64 => {
      if (!b64 || b64.length === 0) {
        Swal.fire({ icon: 'warning', title: 'No hay firma capturada' });
        return;
      }

      const formData = new FormData();
      formData.append("id_usuario", window.currentFirmaId);
      formData.append("nuevaFirma", b64);
      formData.append("ibm_usuario", window.currentNoemp);

      fetch("./php/index.php?actualizarFirma", {
        method: "POST",
        body: formData
      })
      .then(r => r.json())
      .then(resp => {
        if (resp.success) {
          Swal.fire({ icon: 'success', title: 'Firma actualizada correctamente' });

          const imgPreview = document.getElementById("firma-preview-edit");
          if (imgPreview) {
            imgPreview.src = `./firmas/${resp.ruta}`;
            imgPreview.classList.remove("d-none");
          }

          // limpiar estado de captura
          this.firmaFinalizada = false;
          ClearTablet();          
          this.tblenc();
        } else {
          Swal.fire({ icon: 'error', title: 'Error al actualizar', text: resp.error });
        }
      })
      .catch(e => Swal.fire({ icon: 'error', title: 'Error de conexión', text: e }));
    });
  }

  // Funcion de eliminacion de datos
  async eliminarFirma(id){
    window.idToEliminar = id;

    const formData = new FormData();
    formData.append("id_usuario", window.idToEliminar);

  fetch("./php/index.php?eliminarFirma", {
      method: "POST",
      body: formData
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {        
        Swal.fire({ icon: 'success', title: 'Firma eliminada correctamente' });        
        this.tblenc();
      } else {
        Swal.fire({ icon: 'error', title: 'Error al eliminar', text: resp.error });
      }
    })
    .catch(e => Swal.fire({ icon: 'error', title: 'Error de conexión', text: e }));        
  }

}

// Instancia y carga inicial
const firmaModule = new FirmaModule();
firmaModule.init();

// Exposicion de funciones principales
window.iniciarCaptura = () => firmaModule.iniciarCaptura();
window.finalizarCaptura = () => firmaModule.finalizarCaptura();
window.limpiarFirma = () => firmaModule.limpiarFirma();
window.guardarFirma = () => firmaModule.guardarFirma();

// Exposicion de funciones a modal
window.iniciarCapturaEdit = () => firmaModule.iniciarCapturaEdit();
window.finalizarCapturaEdit = () => firmaModule.finalizarCapturaEdit();
window.limpiarFirmaEdit = () => firmaModule.limpiarFirmaEdit();
window.guardarFirmaEdit = () => firmaModule.guardarFirmaEdit();

// Funciones para consulta, edicion y eliminacion de firma en tabla
window.verFirma = (ruta, nombre) => firmaModule.verFirma(ruta, nombre);
window.editFirma = (id, imgSign, noemp) => firmaModule.editFirma(id, imgSign, noemp);
window.eliminarFirma = (id) => firmaModule.eliminarFirma(id);

// Acciones al cierre del modal
document.getElementById("modalEditFirma").addEventListener("hidden.bs.modal", () => {  
  const img = document.getElementById("firma-preview-edit");
  img.src = "";
  img.classList.add("d-none");

  const status = document.getElementById("status-edit");
  status.textContent = "";
  status.classList.add("d-none");

  window.currentFirmaId = null;
  window.currentNoemp = null;

  const canvas = document.getElementById("sig-canvas-edit");
  if (canvas) {
    const ctx = canvas.getContext("2d");
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ClearTablet();
  }
});


// ── Tutorial Firma Digital ────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    const driver = window.driver.js.driver;

    const steps = [
        { 
            element: ".tittlecont", 
            popover: { 
                title: "Captura de firma", 
                description: "Aquí registras tu firma digital.", 
                side: "bottom" 
            } 
        },       
        { 
            element: "#dispositivo", 
            popover: { 
                title: "Verificación de SigWeb", 
                description: "Aquí se valida si SigWeb está instalado. Si no lo está, se mostrará un enlace para descargarlo.", 
                side: "bottom", 
                popoverClass: "popover-importante" 
            } 
        },
        { 
            element: "#noemp", 
            popover: { 
                title: "Número de empleado", 
                description: "Ingresa tu número de empleado.", 
                side: "top" 
            } 
        },       
        { 
            element: "#sig-canvas", 
            popover: { 
                title: "Área de firma", 
                description: "Dibuja tu firma en este espacio utilizando la tableta Topaz.", 
                side: "top" 
            } 
        },
        { 
            element: ".btn-warning", 
            popover: { 
                title: "Iniciar captura", 
                description: "Presiona este botón para comenzar a escribir tu firma en la tableta.", 
                side: "top" 
            } 
        },
        { 
            element: ".btn-danger", 
            popover: { 
                title: "Detener captura", 
                description: "Debes detener la captura antes de poder guardar o limpiar la firma.", 
                side: "top" 
            } 
        },
        { 
            element: ".btn-primary", 
            popover: { 
                title: "Limpiar firma", 
                description: "Si necesitas rehacer tu firma, primero detén la captura y luego usa este botón para limpiar el área.", 
                side: "top" 
            } 
        },
        { 
            element: ".btn-success", 
            popover: { 
                title: "Guardar firma", 
                description: "Cuando estés satisfecho con tu firma y hayas detenido la captura, guarda tu firma aquí.", 
                side: "top" 
            } 
        },
        { 
            element: "#tblFD", 
            popover: { 
                title: "Registros de firmas", 
                description: "En esta tabla se muestran las firmas registradas previamente junto con sus datos.", 
                side: "top" 
            } 
        },
        { 
            element: "#btnAyuda",                    
            popover: { 
                title: "Tutorial",                  
                description: "Si quieres volver a ver el tutorial presiona aqui.", 
                side: "bottom" 
            } 
        }  
    ];

    const driverObj = driver({
        showProgress: true,
        allowClose: false,
        disableInteraction: true,
        progressText: "Paso {{current}} de {{total}}",
        doneBtnText: "Finalizar",
        nextBtnText: "Siguiente",
        prevBtnText: "Atrás",
        steps
    });

    const tk = "tutorial_firmaDigital";
    if (!localStorage.getItem(tk)) {
        driverObj.drive();
        localStorage.setItem(tk, "true");
    }

    // Botón de ayuda para repetir el tutorial
    document.getElementById("btnAyuda")?.addEventListener("click", () => driverObj.drive());
});

class Proact extends Herramientas {
  inicio() {
    document.getElementById("observacion").addEventListener("change", () => {
      let observacion = document.getElementById("observacion").value;
      observacion == ""
        ? (document.getElementById("observacionreal").innerHTML = "")
        : this.llnarslcruta(
            "php/index.php?Observacionreal&id=" + observacion,
            "observacionreal"
          );
    });
    this.tblpro();
    setInterval(this.mostrarHora, 1000);
    this.llnarslcruta("php/index.php?Usuarios", "nombres");
    this.llnarslcruta("php/index.php?Areas", "areas");
    this.llnarslcruta("php/index.php?Observacion", "observacion");
    this.llnarslcruta("php/index.php?proactComportamientos", "comportamiento");
    function guardar() {
      Swal.fire({
        title: "¿Estas seguro?",
        text: "Ya no se podrá cambiar el registro una vez guardado!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, seguro!",
      }).then((result) => {
        if (result.isConfirmed) {
          (async () => {
            const proact = new Proact();
            const form = document.getElementById("formproact");
            const data = new FormData(form);
            data.append("data", form);
            const respuestaraw = await fetch("php/index.php?guardar", {
              method: "POST",
              body: data,
            });
            const respuesta = await respuestaraw.json();
            if (respuesta == "Vacios") {
              Swal.fire("UPS!!!", "Hay campos vacios", "info");
            } else if (respuesta == "Errorsql") {
              Swal.fire(
                "UPS!!!",
                "Hay problemas al insertar a la base de datos",
                "error"
              );
            } else {
              Swal.fire(
                "LISTO!!!",
                "Información guardada con exito",
                "success"
              );
              form.reset();
              proact.tblpro();
            }
          })();
        }
      });
    }
    document.getElementById("areas").addEventListener("change", function () {
      const areas = document.getElementById("areas").value;
      const herramientas = new Herramientas();
      herramientas.llnarslcruta(
        "php/index.php?Maquinas&areas=" + areas,
        "maquinas"
      );
    });
    document
      .getElementById("guardar")
      .addEventListener("click", function (event) {
        event.preventDefault();
        guardar();
      });
    document.getElementById("observado").addEventListener("blur", function () {
      const observado = document.getElementById("observado").value;
      document.getElementById("nombres").value = observado;
    });

    document.getElementById("nombres").addEventListener("blur", function () {
      const nombres = document.getElementById("nombres").value;
      document.getElementById("observado").value = nombres;
    });

    document.getElementById("cumplesi").addEventListener("change", function () {
      if (this.checked) {
        document.getElementById('textcumple').innerHTML = "El comportamiento observado evitó:" 
      }
    });
    document.getElementById("cumpleno").addEventListener("change", function () {
      if (this.checked) {
        document.getElementById('textcumple').innerHTML = "El comportamiento observado puede originar:" 
      }
    });
  }
  async llnarobservaciones() {
    const [obst1, obst2] = await Promise.all([
      fetch("php/index.php?getobservacionest1"),
      fetch("php/index.php?getobservacionest2"),
    ]);
    const obst1raw = await obst1.json();
    const obst2raw = await obst2.json();
    let body1 = "",
      body2 = "";
    obst1raw.forEach((elemnto) => {
      body1 += `<tr class="align-middle border fw-bold"><td>${elemnto.nombre}</td>
            <td> <input class="form-check-input" type="checkbox" name="obs1si[]" value="${elemnto.id}" aria-label="..."></td>
            <td> <input class="form-check-input" type="checkbox" name="obs1no[]" value="${elemnto.id}" aria-label="..."></td></tr>`;
    });
    obst2raw.forEach((elemnto) => {
      body2 += `<tr class="align-middle border fw-bold"><td>${elemnto.nombre}</td>
            <td> <input class="form-check-input" type="checkbox" name="obs2si[]" value="${elemnto.id}" aria-label="..."></td>
            <td> <input class="form-check-input" type="checkbox" name="obs2no[]" value="${elemnto.id}" aria-label="..."></td></tr>`;
    });
    document.getElementById("observacionestip1").innerHTML = body1;
    document.getElementById("observacionestip2").innerHTML = body2;
  }
  async tblpro() {
    const resultadoraw = await fetch("php/index.php?tablaproact");
    const resultado = await resultadoraw.json();
    let body = "";
    resultado.forEach((elemento) => {
      body += `<tr><td>${elemento.id}</td><td>${elemento.Observador}</td><td>${elemento.Observado}</td><td>${elemento.Area}</td>
                <td>${elemento.Maquina}</td><td>${elemento.Fecha}</td></tr>`;
      // <td><button class="btn btn-warning btn-sm" onclick="proact.editarinteracciones(${elemento.id})"><i class="fa-solid fa-pen-to-square"></i></button></td>
    });
    document.getElementById("tbl").innerHTML = body;
  }
  async editarinteracciones(folio) {
    Swal.fire("LO SIENTO!!!", "Esta funcion no esta disponible", "error");
    // const data = new FormData();
    // const respuestaraw = await fetch("php/index.php?consultardatos&folio=" + folio);
    // const respuesta = await respuestaraw.json();
    // document.getElementById("folio").innerHTML = respuesta[0].id
    // document.getElementById("observado").value = respuesta[0].Observado;
    // document.getElementById("nombres").value = respuesta[0].Observado;
    // document.getElementById("areas").value = respuesta[0].Area;
    // document.getElementById("otrainteraccion").value = respuesta[0].Otrainteraccion;
    // document.getElementById("deacuerdo").checked = respuesta[0].Deacuerdo == 1 ? true : false;
    // data.append("areas", respuesta[0].Area);
    // const maquinas = await fetch("php/index.php?Maquinas", {
    //     method: "POST",
    //     body: data
    // });
    // const respuestamaquinas = await maquinas.json();
    // herramientas.llenarslc(respuestamaquinas, "maquinas");
    // document.getElementById("maquinas").value = respuesta[0].Maquina;
    // document.getElementById("fecha").value = respuesta[0].Fecha;
    // document.getElementById("hora").value = respuesta[0].Hora;
    // document.getElementById("comentarios").value = respuesta[0].Comentario;
    // const observaciones = await fetch("php/index.php?Observaciones&folio=" + folio)
    // const respuestaobservaciones = await observaciones.json();
    // const Observacionesagregadas = await fetch("php/index.php?Observacionesagregadas&folio=" + folio)
    // const respuestaObservacionesagregadas = await Observacionesagregadas.json();
    // herramientas.llenarcheck(respuestaobservaciones, "observaciones", respuestaObservacionesagregadas);
    // const proact = new Proact();
    // proact.tblpro();
  }

  reporte() {
    this.llnarslcruta("php/index.php?Areas", "areas");
    document.getElementById("areas").addEventListener("change", function () {
      let areas = document.getElementById("areas").value;
      if (areas == "") {
        document.getElementById("maquinas").innerHTML = "";
        return false;
      }
      (async function () {
        const maquinas = await fetch("php/index.php?Maquinas&areas=" + areas);
        const respuestamaquinas = await maquinas.json();
        const herramientas = new Herramientas();
        herramientas.llnarslcruta(
          "php/index.php?Maquinas&areas=" + areas,
          "maquinas"
        );
      })();
    });
    document
      .getElementById("consulta")
      .addEventListener("click", function (event) {
        event.preventDefault();
        const form = document.getElementById("reporteLSW");
        const datei = document.getElementById("fechai").value;
        const datef = document.getElementById("fechai").value;
        if (datei == "" || datef == "") {
          Swal.fire("UPS!!!", "El intervalo de fechas es obligatorio", "info");
          return false;
        }
        document.getElementById("resultado").innerHTML = "";
        const data = new FormData(form);
        data.append("data", form);
        (async () => {
          const [
            respuestaraw,
            respuestaraw2,
            respuestaraw3,
            respuestaraw4,
            respuestaraw5,
            datos,
          ] = await Promise.all([
            fetch("php/index.php?consultarxfechamaq", {
              method: "POST",
              body: data,
            }),
            fetch("php/index.php?consultarxfechaobs", {
              method: "POST",
              body: data,
            }),
            fetch("php/index.php?consultarxfechalwsenc", {
              method: "POST",
              body: data,
            }),
            fetch("php/index.php?consultarxfechatop5", {
              method: "POST",
              body: data,
            }),
            fetch("php/index.php?consultarxfechaobs2", {
              method: "POST",
              body: data,
            }),
            fetch("php/index.php?consultardatosrep", {
              method: "POST",
              body: data,
            }),
          ]);
          const respuesta = await respuestaraw.json();
          if (respuesta == "fechas") alert("Selecciona las fechas");
          const respuesta2 = await respuestaraw2.json();
          const respuesta3 = await respuestaraw3.json();
          const respuesta4 = await respuestaraw4.json();
          const respuesta5 = await respuestaraw5.json();
          const grafica = document.getElementById("myChart");
          const Objproact = new Proact();
          Objproact.grafica("bar", respuesta, "myChart");
          Objproact.grafica("bar", respuesta2, "myChart2");
          Objproact.grafica("pie", respuesta3, "myChart3");
          Objproact.grafica("bar", respuesta5, "myChart5");
          Objproact.grafica("bar", respuesta4, "myChart4");
          const respuestadatos = await datos.json();
          let body = "";
          respuestadatos.forEach((elemento) => {
            body += `<tr><td>${elemento.id}</td><td>${
              elemento.Observador
            }</td><td>${elemento.Observacion}</td><td>${elemento.Observado}</td>
                    <td>${elemento.Obsnombre}</td><td>${
              elemento.opcion == 1 ? "SI" : "NO"
            }</td>
                    <td>${elemento.Area}</td><td>${elemento.Maquina}</td><td>${
              elemento.Fecha
            }</td><td>${elemento.Hora}</td><td>${elemento.Comentario}</td>
                    <td>${elemento.Otra}</td><td>${
              elemento.Deacuerdo == 1 ? "SI" : "NO"
            }</td>
                    </tr>`;
          });
          document.getElementById("table").innerHTML = body;
        })();
      });
  }
  reportexavance() {
    (async () => {
      const areas = await fetch("php/index.php?Areas");
      const respuestaareas = await areas.json();
      herramientas.llenarslc(respuestaareas, "areas");
    })();
    document
      .getElementById("consulta")
      .addEventListener("click", function (event) {
        //total numero de registros cargados -> los que lleva y los restantes
        // Filtro en avance, del restante / faltantes 25 - 50 - 75 - 100
        event.preventDefault();
        const datei = document.getElementById("fechai").value;
        const datef = document.getElementById("fechai").value;
        if (datei == "" || datef == "") {
          document.getElementById("resultado").innerHTML =
            "<div class='alert alert-danger'>Debes seleccionar las fechas</div>";
          return false;
        }
        (async () => {
          const form = document.getElementById("reporteLSW");
          const data = new FormData(form);
          data.append("data", form);
          const meta = await fetch("php/index.php?reporteavancemeta", {
            method: "POST",
            body: data,
          });
          const respuestameta = await meta.json();
          console.log(respuestameta);
          let chartStatus = Chart.getChart("myChart2");
          let f = true;
          if (chartStatus != undefined) chartStatus.destroy();
          const etiquetas = respuestameta.etiquetas;
          const datos2 = {
            label: "Restantes",
            data: respuestameta.datos,
            backgroundColor: respuestameta.colores,
            borderColor: "rgba(0, 0, 255, .2)",
            borderWidth: 2,
          };
          const datos1 = {
            label: "Creadas",
            data: respuestameta.numreal,
            backgroundColor: respuestameta.colores2,
            borderColor: "rgba(0, 0, 255, .2)",
            borderWidth: 2,
          };
          const informacion = {
            type: "bar",
            data: {
              labels: etiquetas,
              datasets: [datos2, datos1],
            },
            options: {
              scales: {
                x: {
                  stacked: true,
                },
                y: {
                  stacked: true,
                },
              },
              indexAxis: "y",
              // plugins: {
              // tooltip: {
              //     callbacks: {
              //         label: function(tooltipItem, data) {
              //             let label =tooltipItem.dataset.label || '';
              //             if (label) {
              //                 label += ': ';
              //             }
              //             let inf= Number(tooltipItem.raw);
              //             inf =  Number(inf.toFixed(2));
              //             let percent = "";
              //             console.log(tooltipItem.index);
              //             return "Meta: "+inf+"";
              //         },
              //         afterLabel: function(tooltipItem){
              //             console.log(tooltipItem);
              //             return "Interacciones cargadas: "+respuestameta.numreal[tooltipItem.dataIndex];
              //         }
              //     }
              // }
              // },
            },
            plugins: [ChartDataLabels],
          };
          const grafica = document.getElementById("myChart2");
          new Chart(grafica, informacion);
        })();
      });
  }

  grafica(tipo, respuesta, dom) {
    let chartStatus = Chart.getChart(dom);
    let f = true;
    if (tipo == "doughnut" || tipo == "radar" || tipo == "polarArea") f = false;
    if (chartStatus != undefined) chartStatus.destroy();
    const etiquetas = respuesta.etiquetas;
    const datos = {
      label: "Datos",
      data: respuesta.datos,
      backgroundColor: respuesta.colores,
      borderColor: "rgba(0, 0, 0, .2)",
      borderWidth: 2,
    };
    const informacion = {
      type: tipo,
      data: {
        labels: etiquetas,
        datasets: [datos],
      },
      options: {
        legend: {
          display: false,
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              display: f,
            },
          },
          x: {
            ticks: {
              callback: function (value, index, ticks_array) {
                let characterLimit = 20;
                let label = this.getLabelForValue(value);
                if (label.length >= characterLimit) {
                  return (
                    label
                      .slice(0, label.length)
                      .substring(0, characterLimit - 1)
                      .trim() + "..."
                  );
                }
                return label;
              },
              display: f,
              maxRotation: 80,
              minRotation: 80,
            },
          },
        },
      },
    };
    const grafica = document.getElementById(dom);
    new Chart(grafica, informacion);
  }
}

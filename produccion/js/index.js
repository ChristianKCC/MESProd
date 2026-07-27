class Monitoreo{
        async Graficamaquina(maquina,idcanvas){
        let numreg = document.getElementById("numhrs"+idcanvas).value;
        const respuestaraw = await fetch("php/calls.php?"+maquina+"&numhrs="+numreg);
        const respuesta= await respuestaraw.json();
        var ctx = document.getElementById(idcanvas).getContext('2d');
        var idcanvas = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: respuesta.hora, 
                    datasets: [{
                        label: 'Operacion',
                        data: respuesta.datos, 
                        backgroundColor: 'rgba(255, 51, 51, .8)',
                        borderColor: 'rgba(255, 51, 51, .8)',
                        borderWidth: 3,
                        tension:0.4,
                        pointRadius: 0,
                        pointHoverRadius:0
                    },{
                        label: 'Merma',
                        data: respuesta.merma, 
                        backgroundColor: 'rgba(0, 111, 204, .8)',
                        borderColor: 'rgba(0, 112, 204, 0.8)',
                        borderWidth: 3,
                        tension:0.4,
                        pointRadius: 0,
                        pointHoverRadius:0
                    },{
                        label: 'Velocidad',
                        data: respuesta.velocidad, 
                        backgroundColor: 'rgba(178, 190, 2, 0.8)',
                        borderColor: 'rgba(177, 226, 0, 0.8)',
                        borderWidth: 3,
                        tension:0.4,
                        pointRadius: 0,
                        pointHoverRadius:0
                    }]
                },
                options: {
                  responsive: true,
                  plugins: {
                    tooltip: {
                      enabled: true,
                      mode: 'nearest',
                      intersect: false,
                      callbacks: {
                        label: function(context) {
                          var label = context.label;
                          var value = context.parsed.y;
                          return label + ': ' + value;
                        }
                      }
                    }
                    },
                    title: {
                        display: true
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                              color: 'white'
                           }
                        },
                        x: {
                          ticks: {
                              color: 'white' 
                          }
                      }
                       
                    }
                }
            });
            }
            async Obtienedatosmaquina(maquina,idgrafica){
              let numreg = document.getElementById("numhrs"+idgrafica).value;
              const respuestaraw = await fetch("php/calls.php?"+maquina+"&numhrs="+numreg);
              const respuesta= await respuestaraw.json();
              return respuesta;
            }
            actualizainformacion(datos,idgrafica){
              const Monitor= new Monitoreo();
              Monitor.Obtienedatosmaquina(datos,idgrafica).then(respuesta =>{
                var chart = Chart.getChart(idgrafica)
                chart.data.labels = respuesta.hora;
                chart.data.datasets[0].data = respuesta.datos;
                chart.data.datasets[1].data = respuesta.merma;
                chart.update();
              })
            }
            async informacionmaquina(call,cortesid,rechazosid,velocidadid,estadoid,mermaid){
              const respuestaraw = await fetch("php/calls.php?"+call);
              const respuesta= await respuestaraw.json();
              document.getElementById(cortesid).innerHTML = respuesta.cortes;
              document.getElementById(rechazosid).innerHTML = respuesta.rechazos;
              document.getElementById(velocidadid).innerHTML = respuesta.velocidad;
              document.getElementById(estadoid).innerHTML = respuesta.estado == 0 ? "<i class='text-danger fa-solid fa-power-off'></i>" : "<i class='text-success fa-solid fa-power-off'></i>";
              document.getElementById(mermaid).innerHTML = respuesta.merma;

            }

      
    cargarattrpanal(){
      const Monitor= new Monitoreo();
      function creargraf(){
        Monitor.Graficamaquina("datosmaquinabcm4","bcm4");
        Monitor.Graficamaquina("datosmaquinabcm3","bcm3");
        Monitor.Graficamaquina("datosmaquinabcm1","bcm1");
        Monitor.Graficamaquina("datosmaquinamp25","mp25");
        Monitor.Graficamaquina("datosmaquinape10","pe10");
        Monitor.Graficamaquina("datosmaquinamp22","mp22");
        }creargraf();
        document.getElementById("numhrsbcm4").addEventListener("change",function(){
          Monitor.actualizainformacion("datosmaquinabcm4","bcm4");
        })  
        document.getElementById("numhrsbcm3").addEventListener("change",function(){
          Monitor.actualizainformacion("datosmaquinabcm3","bcm3");
        })  
        document.getElementById("numhrsbcm1").addEventListener("change",function(){
          Monitor.actualizainformacion("datosmaquinabcm1","bcm1");
        })  
        document.getElementById("numhrsmp25").addEventListener("change",function(){
          Monitor.actualizainformacion("datosmaquinamp25","mp25");
        })  
        document.getElementById("numhrspe10").addEventListener("change",function(){
          Monitor.actualizainformacion("datosmaquinape10","pe10");
        })  
        document.getElementById("numhrsmp22").addEventListener("change",function(){
          Monitor.actualizainformacion("datosmaquinamp22","mp22");
        })  
        setInterval(() => {
          Monitor.actualizainformacion("datosmaquinabcm4","bcm4");
          Monitor.actualizainformacion("datosmaquinabcm3","bcm3");
          Monitor.actualizainformacion("datosmaquinabcm1","bcm1");
          Monitor.actualizainformacion("datosmaquinamp25","mp25");
          Monitor.actualizainformacion("datosmaquinape10","pe10");
          Monitor.actualizainformacion("datosmaquinamp22","mp22");
        }, 10000);
        setInterval(() => {
          Monitor.informacionmaquina("infobcm4","cortesbcm4","rechazosbcm4","velocidadbcm4","estadobcm4","mermabcm4");
          Monitor.informacionmaquina("infobcm3","cortesbcm3","rechazosbcm3","velocidadbcm3","estadobcm3","mermabcm3");
          // Monitor.informacionmaquina("infobcm1","cortesbcm1","rechazosbcm1","velocidadbcm1","estadobcm1","mermabcm1");
          // Monitor.informacionmaquina("infomp25","cortesmp25","rechazosmp25","velocidadmp25","estadomp25","mermamp25");
          // Monitor.informacionmaquina("infope10","cortespe10","rechazospe10","velocidadpe10","estadope10","mermape10");
          Monitor.informacionmaquina("infomp22","cortesmp22","rechazosmp22","velocidadmp22","estadomp22","mermamp22");
        }, 2000);
    }
    cargarattrinco(){
      const Monitor= new Monitoreo();
      function creargraf(){
        Monitor.Graficamaquina("datosmaquinapa01","pa01");
        Monitor.Graficamaquina("datosmaquinapa02","pa02");
        Monitor.Graficamaquina("datosmaquinapa03","pa03");
        Monitor.Graficamaquina("datosmaquinapa04","pa04");
        Monitor.Graficamaquina("datosmaquinapa05","pa05");
        }creargraf();
        document.getElementById("numhrspa01").addEventListener("change",function(){
          Monitor.actualizainformacion("datosmaquinapa01","pa01");
        });
        document.getElementById("numhrspa02").addEventListener("change",function(){
          Monitor.actualizainformacion("datosmaquinapa02","pa02");
        });
        document.getElementById("numhrspa03").addEventListener("change",function(){
          Monitor.actualizainformacion("datosmaquinapa03","pa03");
        });  
        document.getElementById("numhrspa04").addEventListener("change",function(){
          Monitor.actualizainformacion("datosmaquinapa04","pa04");
        });  
        document.getElementById("numhrspa05").addEventListener("change",function(){
          Monitor.actualizainformacion("datosmaquinapa05","pa05");
        });  
        setInterval(() => {
          Monitor.actualizainformacion("datosmaquinapa01","pa01");
          Monitor.actualizainformacion("datosmaquinapa02","pa02");
          Monitor.actualizainformacion("datosmaquinapa03","pa03");
          Monitor.actualizainformacion("datosmaquinapa04","pa04");
          Monitor.actualizainformacion("datosmaquinapa05","pa05");
        }, 10000);
        setInterval(() => {
          Monitor.informacionmaquina("infopa01","cortespa01","rechazospa01","velocidadpa01","estadopa01","mermapa01");
          Monitor.informacionmaquina("infopa02","cortespa02","rechazospa02","velocidadpa02","estadopa02","mermapa02");
          Monitor.informacionmaquina("infopa03","cortespa03","rechazospa03","velocidadpa03","estadopa03","mermapa03");
          Monitor.informacionmaquina("infopa04","cortespa04","rechazospa04","velocidadpa04","estadopa04","mermapa04");
          Monitor.informacionmaquina("infopa05","cortespa05","rechazospa05","velocidadpa05","estadopa05","mermapa05");
        }, 2000);
    }
     
}
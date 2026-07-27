import { Toolsjs } from "../../Tools/Tools.js";
const Tools = new Toolsjs();

async function rechazos(){
    const respuestaraw = await fetch('php/aris.php?tblRechazos');
    const respuesta = await respuestaraw.json();
    console.log(respuesta);
    return respuesta;
}


rechazos().then((e)=>{
    let body = "";
    e.forEach(element => {
        body += `<tr><td>${element.id_inser}</td><td>${element.codigo}</td><td>${element.descripcion}</td>
        <td>${element.merma}</td><td>${element.fecha}</td><td>${element.turno}</td><td>${element.categoria}</td></tr>`
    });
    document.getElementById('tblRechazos').innerHTML = body
})





Chart.register(ChartDataLabels);
rechazos().then((e) => {
    const descripciones = e.map(item => item.descripcion);
    const mermas = e.map(item => parseFloat(item.merma));
    const totalMerma = mermas.reduce((a, b) => a + b, 0);

    const ctx = document.getElementById('graficaMerma').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: descripciones,
            datasets: [{
                label: 'Merma',
                data: mermas,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                title: {
                    display: true,
                    text: 'Merma por Descripción'
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    formatter: (value) => {
                        const porcentaje = ((value / totalMerma) * 100).toFixed(1);
                        return `${value} (${porcentaje}%)`;
                    },
                    color: '#000',
                    font: {
                        weight: 'bold'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        },
        plugins: [ChartDataLabels]
    });
});

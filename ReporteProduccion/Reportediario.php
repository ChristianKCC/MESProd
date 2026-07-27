<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container">
    <h5 class="tittlecont">Reporte de Diario</h5>
    <div class="row justify-content-center m-2">
        <div class="col-2">
            <h5 class="text-center">MP12</h5>
            <canvas id="myChart" width="400" height="200"></canvas>
        </div>
        <div class="col-2">
            <h5 class="text-center">MP13</h5>
            <canvas id="myChart2" width="400" height="200"></canvas>
        </div>
        <div class="col-2">
            <h5 class="text-center">MP14</h5>
            <canvas id="myChart3" width="400" height="200"></canvas>
        </div>
        <div class="col-2">
            <h5 class="text-center">MP15</h5>
            <canvas id="myChart4" width="400" height="200"></canvas>
        </div>
        
    </div>
    <div class="row justify-content-center m-2">
        <div class="col-2">
            <canvas id="myChart11" width="400" height="200"></canvas>
        </div>
        <div class="col-2">
            <canvas id="myChart22" width="400" height="200"></canvas>
        </div>
        <div class="col-2">
            <canvas id="myChart33" width="400" height="200"></canvas>
        </div>
        <div class="col-2">
            <canvas id="myChart44" width="400" height="200"></canvas>
        </div>
        
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
    async function fetchData(maq) {
        try {
            const response = await fetch('php/producciones.php?datagraficasdiario&maq=' + maq);
            if (!response.ok) {
                throw new Error('Error al obtener los datos');
            }
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
        }
    }
    async function fetchData2(maq) {
        try {
            const response = await fetch('php/producciones.php?datagraficasdiario2&maq=' + maq);
            if (!response.ok) {
                throw new Error('Error al obtener los datos');
            }
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
        }
    }
    async function createChart(maq, ubi) {
        const data = await fetchData(maq);
        if (data) {
            const ctx = document.getElementById(ubi).getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'MERMA'
                        },
                        datalabels: {
                            color: '#000',
                            formatter: (value, context) => {
                                return context.chart.data.labels[context.dataIndex] + ': ' + value;
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }
    }
    async function createChart2(maq, ubi) {
        const data = await fetchData2(maq);
        if (data) {
            const ctx = document.getElementById(ubi).getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'TP'
                        },
                        datalabels: {
                            color: '#000',
                            formatter: (value, context) => {
                                return context.chart.data.labels[context.dataIndex] + ': ' + value;
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }
    }
    createChart(76, 'myChart');
    createChart(73, 'myChart2');
    createChart(74, 'myChart3');
    createChart(77, 'myChart4');

    createChart2(76, 'myChart11');
    createChart2(73, 'myChart22');
    createChart2(74, 'myChart33');
    createChart2(77, 'myChart44');
</script>
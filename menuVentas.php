<?php
// Incluir la conexión a la base de datos
include('config.php');


// Función para obtener los ingresos por mes
function obtenerIngresosPorMes($conn, $fecha_inicio, $fecha_fin) {
    $query = "SELECT SUM(monto) AS total_ingresos FROM ingresos WHERE fecha BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_ingresos'] ? $row['total_ingresos'] : 0;
}

// Función para obtener los ingresos por estado
function obtenerIngresosPorEstado($conn) {
    $query = "SELECT estado, SUM(monto) AS total_ingresos FROM ingresos GROUP BY estado";
    $result = $conn->query($query);
    $ingresos_por_estado = [];
    
    while ($row = $result->fetch_assoc()) {
        $ingresos_por_estado[$row['estado']] = $row['total_ingresos'];
    }
    
    return $ingresos_por_estado;
}

// Obtener los ingresos del mes actual y el mes anterior
$fecha_actual = date('Y-m-d');
$fecha_mes_anterior = date('Y-m-d', strtotime('-1 month', strtotime($fecha_actual)));

// Definir las fechas de inicio y fin del mes actual
$inicio_mes_actual = date('Y-m-01', strtotime($fecha_actual));
$fin_mes_actual = date('Y-m-t', strtotime($fecha_actual));

// Obtener ingresos del mes actual y mes anterior
$ingresos_mes_actual = obtenerIngresosPorMes($conn, $inicio_mes_actual, $fin_mes_actual);
$ingresos_mes_anterior = obtenerIngresosPorMes($conn, date('Y-m-01', strtotime($fecha_mes_anterior)), date('Y-m-t', strtotime($fecha_mes_anterior)));

// Calcular el porcentaje de cambio entre el mes actual y el mes anterior
$porcentaje_cambio = 0;
if ($ingresos_mes_anterior > 0) {
    $porcentaje_cambio = (($ingresos_mes_actual - $ingresos_mes_anterior) / $ingresos_mes_anterior) * 100;
}

// Obtener los ingresos por estado
$ingresos_estado = obtenerIngresosPorEstado($conn);
?>
<style>
      .container.mt-4 {
        width:1200px;  
        margin-left: 220px;
        padding: 70px;
    }
    .canvas-container {
        width: 400px;  /* Ajusta el tamaño del gráfico */
        height: 400px;
        margin: 0 auto;
    }
</style>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ingresos</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <header>
        <?php include('headerventas.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Dashboard de Ingresos</h2>

        <!-- Sección de gráficos -->
        <div class="row">
            <div class="col-md-6">
                <h3>Relación de Ingresos (Mes Actual vs Mes Anterior)</h3>
                <canvas id="graficoBarra"></canvas>
            </div>

            <div class="col-md-6">
                <h3>Porcentaje de Cambio</h3>
                <div class="alert alert-info">
                    <?php
                    if ($porcentaje_cambio > 0) {
                        echo "Los ingresos del mes actual son un {$porcentaje_cambio}% mayores que los del mes anterior.";
                    } elseif ($porcentaje_cambio < 0) {
                        echo "Los ingresos del mes actual son un " . abs($porcentaje_cambio) . "% menores que los del mes anterior.";
                    } else {
                        echo "Los ingresos del mes actual son iguales a los del mes anterior.";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <h3>Ingresos por Estado</h3>
                <div class="canvas-container">
                    <canvas id="graficoDona"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para los gráficos -->
    <script>
        // Configuración del gráfico de barras
        var ctxBarra = document.getElementById('graficoBarra').getContext('2d');
        var graficoBarra = new Chart(ctxBarra, {
            type: 'bar',
            data: {
                labels: ['Mes Anterior', 'Mes Actual'],
                datasets: [{
                    label: 'Ingresos',
                    data: [<?php echo $ingresos_mes_anterior; ?>, <?php echo $ingresos_mes_actual; ?>],
                    backgroundColor: ['#FF5733', '#33FF57'],
                    borderColor: ['#C70039', '#00FF00'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Configuración del gráfico de dona para los ingresos por estado
        var ctxDona = document.getElementById('graficoDona').getContext('2d');
        var graficoDona = new Chart(ctxDona, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($ingresos_estado)); ?>, // Estados (pendiente, vencido, etc.)
                datasets: [{
                    label: 'Ingresos por Estado',
                    data: <?php echo json_encode(array_values($ingresos_estado)); ?>, // Ingresos correspondientes a cada estado
                    backgroundColor: ['#FF5733', '#33FF57', '#FFC300', '#C70039'], // Colores para cada estado
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': $' + tooltipItem.raw.toFixed(2);
                            }
                        }
                    },
                    // Mostrar porcentajes en cada segmento del gráfico de dona
                    datalabels: {
                        display: true,
                        color: 'white',
                        formatter: function(value, context) {
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = (value / total * 100).toFixed(2) + '%';
                            return percentage;
                        }
                    }
                }
            }
        });
    </script>

    <!-- Librería de Data Labels para mostrar los porcentajes en el gráfico de dona -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

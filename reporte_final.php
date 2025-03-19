<?php

// Incluir la conexión a la base de datos
include('config.php');

// Establecer la configuración regional para que los nombres de los meses estén en español
$conn->query("SET lc_time_names = 'es_ES'");

// Obtener el primer y último día del mes actual
$primer_dia_mes = date('Y-m-01');
$ultimo_dia_mes = date('Y-m-t');

// Consultas SQL para obtener los totales
$consultas = [
    'ingresos' => "
        SELECT SUM(monto) AS total_ingresos
        FROM ingresos
        WHERE estado = 'pagado' AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ",
    'egresos' => "
        SELECT SUM(monto) AS total_egresos
        FROM egresos
        WHERE estado = 'pagado' AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ",
    'compras_a_cobrar' => "
        SELECT SUM(monto) AS total_compras_a_cobrar
        FROM compras
        WHERE estado = 'pagado' AND estado_compra = 'a_cobrar' AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ",
    'compras_a_pagar' => "
        SELECT SUM(monto) AS total_compras_a_pagar
        FROM compras
        WHERE estado = 'pagado' AND estado_compra = 'a_pagar' AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ",
    'gastos_a_cobrar' => "
        SELECT * FROM gastos
        WHERE estado = 'a_cobrar' AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ",
    'gastos_a_pagar' => "
        SELECT * FROM gastos
        WHERE estado = 'a_pagar' AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ",
    'ingresos_lista' => "
        SELECT * FROM ingresos
        WHERE estado = 'pagado' AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ",
    'egresos_lista' => "
        SELECT * FROM egresos
        WHERE estado = 'pagado' AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    "
];

// Ejecutar las consultas y almacenar los resultados
$resultados = [];
foreach ($consultas as $clave => $query) {
    if ($result = $conn->query($query)) {
        if (strpos($clave, 'lista') !== false) {
            // Si es una lista, almacenamos los resultados como arrays
            $resultados[$clave] = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $row = $result->fetch_assoc();
            $resultados[$clave] = $row ? number_format($row['total_' . $clave], 2) : '0.00';
        }
        $result->free();
    } else {
        $resultados[$clave] = '0.00';
    }
}

// Verificar si el resultado de 'gastos' existe
if (!isset($resultados['gastos_a_cobrar'])) {
    $resultados['gastos_a_cobrar'] = [];
}
if (!isset($resultados['gastos_a_pagar'])) {
    $resultados['gastos_a_pagar'] = [];
}
if (!isset($resultados['ingresos_lista'])) {
    $resultados['ingresos_lista'] = [];
}
if (!isset($resultados['egresos_lista'])) {
    $resultados['egresos_lista'] = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Final</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            width: 1200px;
            margin-left:320px;
            padding: 0px;
            margin-top:-420px;
        }
        table {
            width: 100%;
        }
        th, td {
            text-align: center;
        }
        .hidden {
            display: none;
        }
    </style>
    <script>
        function toggleVisibility(id) {
            var element = document.getElementById(id);
            if (element.style.display === "none") {
                element.style.display = "block";
            } else {
                element.style.display = "none";
            }
        }
    </script>
</head>
<body>
<?php
include('headeradmin.php');
?>
    <div class="container">
        <h2>Reporte Final del Mes</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Ingresos</td>
                    <td>$<?php echo $resultados['ingresos']; ?></td>
                </tr>
                <tr>
                    <td>Egresos</td>
                    <td>$<?php echo $resultados['egresos']; ?></td>
                </tr>
                <tr>
                    <td>Compras a Cobrar</td>
                    <td>$<?php echo $resultados['compras_a_cobrar']; ?></td>
                </tr>
                <tr>
                    <td>Compras a Pagar</td>
                    <td>$<?php echo $resultados['compras_a_pagar']; ?></td>
                </tr>
                <tr>
                    <td>Gastos</td>
                    <td>$<?php echo isset($resultados['gastos']) ? $resultados['gastos'] : '0.00'; ?></td>
                </tr>
            </tbody>
        </table>

        <button class="btn btn-info" onclick="toggleVisibility('gastosACobrar')">Ver Gastos a Cobrar</button>
        <button class="btn btn-info" onclick="toggleVisibility('gastosAPagar')">Ver Gastos a Pagar</button>
        <button class="btn btn-info" onclick="toggleVisibility('ingresosLista')">Ver Ingresos</button>
        <button class="btn btn-info" onclick="toggleVisibility('egresosLista')">Ver Egresos</button>

        <!-- Listas detalladas (ocultas por defecto) -->
        <div id="gastosACobrar" class="hidden">
            <h3>Gastos a Cobrar</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados['gastos_a_cobrar'] as $gasto): ?>
                        <tr>
                            <td><?php echo $gasto['id']; ?></td>
                            <td><?php echo $gasto['fecha']; ?></td>
                            <td>$<?php echo number_format($gasto['monto'], 2); ?></td>
                            <td><?php echo $gasto['descripcion']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="gastosAPagar" class="hidden">
            <h3>Gastos a Pagar</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados['gastos_a_pagar'] as $gasto): ?>
                        <tr>
                            <td><?php echo $gasto['id']; ?></td>
                            <td><?php echo $gasto['fecha']; ?></td>
                            <td>$<?php echo number_format($gasto['monto'], 2); ?></td>
                            <td><?php echo $gasto['descripcion']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="ingresosLista" class="hidden">
            <h3>Ingresos</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados['ingresos_lista'] as $ingreso): ?>
                        <tr>
                            <td><?php echo $ingreso['id']; ?></td>
                            <td><?php echo $ingreso['fecha']; ?></td>
                            <td>$<?php echo number_format($ingreso['monto'], 2); ?></td>
                            <td><?php echo $ingreso['descripcion']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="egresosLista" class="hidden">
            <h3>Egresos</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados['egresos_lista'] as $egreso): ?>
                        <tr>
                            <td><?php echo $egreso['id']; ?></td>
                            <td><?php echo $egreso['fecha']; ?></td>
                            <td>$<?php echo number_format($egreso['monto'], 2); ?></td>
                            <td><?php echo $egreso['descripcion']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

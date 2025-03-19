<?php
// Incluir el encabezado y la configuración de la base de datos
include('headeradmin.php');
include('config.php');

// Establecer la configuración regional para que las fechas se muestren en español
$conn->query("SET lc_time_names = 'es_ES'");

// Obtener la fecha actual
$fecha_actual = date('Y-m-d');

// Consulta SQL para obtener los proveedores y sus saldos pendientes y vencidos
$query = "
    SELECT p.id, p.nombre, 
           (p.saldo_inicial - IFNULL(SUM(pa.monto_pago), 0)) AS saldo_pendiente
    FROM proveedores p
    LEFT JOIN pagos pa ON p.id = pa.id_proveedor
    GROUP BY p.id, p.nombre, p.saldo_inicial
    HAVING saldo_pendiente > 0
    ORDER BY saldo_pendiente DESC
";

// Ejecutar la consulta
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Corriente de Proveedores</title>
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
        .vencido {
            background-color: #f8d7da;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Cuenta Corriente de Proveedores</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Proveedor</th>
                    <th>Saldo Pendiente</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Determinar si el saldo está vencido
                        $estado = 'Pendiente';
                        $class = '';
                        // Aquí puedes agregar lógica adicional para determinar si está vencido
                        // por ejemplo, comparando fechas de facturación y fecha actual

                        echo "<tr class='$class'>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                        echo "<td>$" . number_format($row['saldo_pendiente'], 2) . "</td>";
                        echo "<td>$estado</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No hay proveedores con saldos pendientes.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>

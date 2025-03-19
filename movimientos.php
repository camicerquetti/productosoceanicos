<?php
// Incluir la conexión a la base de datos
include('config.php');  // Asegúrate de que la conexión a la base de datos esté configurada correctamente

// Consulta para obtener los movimientos de la tabla 'movimientos' y los detalles de la cuenta
$query = "SELECT m.Id_movimiento, m.Tipo, m.Monto, m.Fecha, m.Descripcion, m.Metodo_pago, c.Cuenta
          FROM movimientos m
          JOIN cuentas c ON m.Id_cuenta = c.Id_cuenta
          ORDER BY m.Fecha DESC";  // Ordenar los movimientos por fecha

$result = $conn->query($query);

// Comprobar si hay resultados
if ($result->num_rows > 0) {
    $movimientos = $result->fetch_all(MYSQLI_ASSOC);  // Obtener todos los resultados como un array asociativo
} else {
    $movimientos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos de Cuentas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container.mt-4 {
            width: 1200px;
            margin-left: 220px;
            padding: 70px;
        }

        .movimientos-table {
            margin-top: 20px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .movimientos-table th, .movimientos-table td {
            padding: 10px;
            text-align: center;
        }

        .movimientos-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .movimientos-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .monto-ingreso {
            color: green;
            font-weight: bold;
        }

        .monto-egreso {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <?php include('headeradmin.php'); // Si tienes un header común ?>
    </header>

    <div class="container mt-4">
        <h2>Movimientos de Cuentas</h2>

        <!-- Tabla de movimientos -->
        <table class="table movimientos-table">
            <thead>
                <tr>
                    <th>ID Movimiento</th>
                    <th>Cuenta</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Método de Pago</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($movimientos) > 0): ?>
                    <?php foreach ($movimientos as $movimiento): ?>
                        <tr>
                            <td><?php echo $movimiento['Id_movimiento']; ?></td>
                            <td><?php echo $movimiento['Cuenta']; ?></td>
                            <td><?php echo $movimiento['Tipo']; ?></td>
                            <td class="<?php echo ($movimiento['Tipo'] == 'Ingreso') ? 'monto-ingreso' : 'monto-egreso'; ?>">
                                <?php echo '$' . number_format($movimiento['Monto'], 2); ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($movimiento['Fecha'])); ?></td>
                            <td><?php echo $movimiento['Descripcion']; ?></td>
                            <td><?php echo $movimiento['Metodo_pago']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron movimientos</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

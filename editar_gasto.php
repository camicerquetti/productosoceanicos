<?php
// Incluir archivo de configuración de la base de datos
include('config.php');

// Verificar si se ha pasado un ID válido por la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $gasto_id = $_GET['id'];

    // Consulta SQL para obtener el gasto por ID
    $query = "SELECT * FROM gastos WHERE id = ?";
    
    // Preparar la consulta
    if ($stmt = $conn->prepare($query)) {
        // Vincular el parámetro de la consulta
        $stmt->bind_param('i', $gasto_id); // 'i' es para enteros

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener el resultado de la consulta
        $result = $stmt->get_result();

        // Verificar si se encontraron resultados
        if ($result->num_rows > 0) {
            // Si se encuentra el gasto, lo cargamos
            $gasto = $result->fetch_assoc();
        } else {
            die("Gasto no encontrado.");
        }

        // Cerrar la sentencia preparada
        $stmt->close();
    } else {
        die("Error en la consulta SQL: " . $conn->error);
    }

    // Si el formulario se envía, actualizar el gasto en la base de datos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitizar los datos recibidos del formulario
        $fecha = $conn->real_escape_string($_POST['fecha']);
        $descripcion = $conn->real_escape_string($_POST['descripcion']);
        $categoria = $conn->real_escape_string($_POST['categoria']);
        $metodo_pago = $conn->real_escape_string($_POST['metodo_pago']);
        $monto = $conn->real_escape_string($_POST['monto']);
        $estado = $conn->real_escape_string($_POST['estado']);

        // Consulta preparada para actualizar el gasto
        $query_update = $conn->prepare("UPDATE gastos SET
            fecha = ?, descripcion = ?, categoria = ?, metodo_pago = ?, monto = ?, estado = ?
            WHERE id = ?");

        // Vincular los parámetros a la consulta preparada
        $query_update->bind_param('ssssds', 
            $fecha, $descripcion, $categoria, $metodo_pago, $monto, $estado, $gasto_id);

        // Ejecutar la consulta
        if ($query_update->execute()) {
            $mensaje = "Gasto actualizado exitosamente.";
        } else {
            $mensaje = "Error al actualizar el gasto: " . $query_update->error;
        }
    }
} else {
    die("ID de gasto no válido.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Gasto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1000px;
            margin-left: 300px;
            margin-right: auto;
            padding: 40px;
        }

        body {
            padding-top: 60px;
        }

        .form-container {
            margin-top: 40px;
        }

        .btn-green {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-green:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container">
        <h2>Editar Gasto</h2>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-info"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- Formulario para editar el gasto -->
        <div class="form-container">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" value="<?php echo $gasto['fecha']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <input type="text" id="descripcion" name="descripcion" class="form-control" value="<?php echo $gasto['descripcion']; ?>">
                </div>

                <div class="mb-3">
                    <label for="categoria" class="form-label">Categoría</label>
                    <input type="text" id="categoria" name="categoria" class="form-control" value="<?php echo $gasto['categoria']; ?>">
                </div>

                <div class="mb-3">
                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                    <input type="text" id="metodo_pago" name="metodo_pago" class="form-control" value="<?php echo $gasto['metodo_pago']; ?>">
                </div>

                <div class="mb-3">
                    <label for="monto" class="form-label">Monto</label>
                    <input type="number" id="monto" name="monto" class="form-control" value="<?php echo $gasto['monto']; ?>" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select id="estado" name="estado" class="form-control" required>
                        <option value="pendiente" <?php echo $gasto['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="confirmado" <?php echo $gasto['estado'] == 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Actualizar Gasto</button>
                <a href="gastos.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

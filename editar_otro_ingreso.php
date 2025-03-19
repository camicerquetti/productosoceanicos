<?php
// Incluir archivo de configuración de la base de datos
include('config.php');

// Verificar si se ha pasado un ID válido por la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ingreso_id = $_GET['id'];

    // Consulta SQL para obtener el ingreso por ID
    $query = "SELECT * FROM otros_ingresos WHERE id = ?";
    
    // Preparar la consulta
    if ($stmt = $conn->prepare($query)) {
        // Vincular el parámetro de la consulta
        $stmt->bind_param('i', $ingreso_id); // 'i' es para enteros

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener el resultado de la consulta
        $result = $stmt->get_result();

        // Verificar si se encontraron resultados
        if ($result->num_rows > 0) {
            // Si se encuentra el ingreso, lo cargamos
            $ingreso = $result->fetch_assoc();
        } else {
            die("Ingreso no encontrado.");
        }

        // Cerrar la sentencia preparada
        $stmt->close();
    } else {
        die("Error en la consulta SQL: " . $conn->error);
    }

    // Si el formulario se envía, actualizar el ingreso en la base de datos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitizar los datos recibidos del formulario
        $ingreso_valor = $conn->real_escape_string($_POST['ingreso']);
        $categoria = $conn->real_escape_string($_POST['categoria']);
        $cuenta = $conn->real_escape_string($_POST['cuenta']);
        $vendedor = $conn->real_escape_string($_POST['vendedor']);
        $total = $conn->real_escape_string($_POST['total']);
        $descripcion = $conn->real_escape_string($_POST['descripcion']);

        // Consulta preparada para actualizar el ingreso
        $query_update = $conn->prepare("UPDATE otros_ingresos SET
            ingreso = ?, categoria = ?, cuenta = ?, vendedor = ?, total = ?, descripcion = ?
            WHERE id = ?");

        // Vincular los parámetros a la consulta preparada
        $query_update->bind_param('ssssdsi', 
            $ingreso_valor, $categoria, $cuenta, $vendedor, $total, $descripcion, $ingreso_id);

        // Ejecutar la consulta
        if ($query_update->execute()) {
            $mensaje = "Ingreso actualizado exitosamente.";
        } else {
            $mensaje = "Error al actualizar el ingreso: " . $query_update->error;
        }
    }
} else {
    die("ID de ingreso no válido.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ingreso</title>
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
        <h2>Editar Ingreso</h2>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-info"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- Formulario para editar el ingreso -->
        <div class="form-container">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="ingreso" class="form-label">Ingreso</label>
                    <input type="text" id="ingreso" name="ingreso" class="form-control" value="<?php echo $ingreso['ingreso']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="categoria" class="form-label">Categoría</label>
                    <input type="text" id="categoria" name="categoria" class="form-control" value="<?php echo $ingreso['categoria']; ?>" required>
                </div>

                <div class="mb-3">
    <label for="cuenta" class="form-label">Cuenta</label>
    <select name="cuenta_id" class="form-control" required>
        <?php
        // Consulta para obtener las cuentas desde la tabla "cuentas"
        $query_cuentas = "SELECT Id_cuenta, Cuenta FROM cuentas";
        $resultado_cuentas = $conn->query($query_cuentas);

        // Mostrar las cuentas en el select
        while ($cuenta = $resultado_cuentas->fetch_assoc()) :
        ?>
            <option value="<?php echo $cuenta['Id_cuenta']; ?>">
                <?php echo $cuenta['Cuenta']; ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>


                <div class="mb-3">
                    <label for="vendedor" class="form-label">Vendedor</label>
                    <input type="text" id="vendedor" name="vendedor" class="form-control" value="<?php echo $ingreso['vendedor']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="total" class="form-label">Total</label>
                    <input type="number" id="total" name="total" class="form-control" value="<?php echo $ingreso['total']; ?>" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="4" required><?php echo $ingreso['descripcion']; ?></textarea>
                </div>

                <button type="submit" class="btn btn-success">Actualizar Ingreso</button>
                <a href="lista_otros_ingresos.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

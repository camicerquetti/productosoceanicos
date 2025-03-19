<?php
// Incluir el archivo de configuración de la base de datos
include('config.php');

// Verificar si se ha pasado un ID válido por la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $compra_id = $_GET['id'];

    // Consulta SQL para obtener la compra por ID
    $query = "SELECT * FROM compras WHERE id = ?";
    
    // Preparar la consulta
    if ($stmt = $conn->prepare($query)) {
        // Vincular el parámetro de la consulta
        $stmt->bind_param('i', $compra_id); // 'i' es para enteros

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener el resultado de la consulta
        $result = $stmt->get_result();

        // Verificar si se encontraron resultados
        if ($result->num_rows > 0) {
            // Si se encuentra una compra, la cargamos
            $compra = $result->fetch_assoc();
        } else {
            die("Compra no encontrada.");
        }

        // Cerrar la sentencia preparada
        $stmt->close();
    } else {
        die("Error en la consulta SQL: " . $conn->error);
    }

    // Si el formulario se envía, actualizar la compra en la base de datos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitizar los datos recibidos del formulario para evitar inyecciones SQL
        $emision = $conn->real_escape_string($_POST['emision']);
        $vencimiento = $conn->real_escape_string($_POST['vencimiento']);
        $proveedor = $conn->real_escape_string($_POST['proveedor']);
        $categoria = $conn->real_escape_string($_POST['categoria']);
        $subtotal = $conn->real_escape_string($_POST['subtotal']);
        $descuento = $conn->real_escape_string($_POST['descuento']);
        $cantidad = $conn->real_escape_string($_POST['cantidad']);
        $total = $conn->real_escape_string($_POST['total']);
        $vencimientoPago = $conn->real_escape_string($_POST['vencimientoPago']);
        $tipoCompra = $conn->real_escape_string($_POST['tipoCompra']);
        $producto = $conn->real_escape_string($_POST['producto']);
        $precio = $conn->real_escape_string($_POST['precio']);
        $iva = $conn->real_escape_string($_POST['iva']);
        $notaInterna = $conn->real_escape_string($_POST['notaInterna']);
        $contador = $conn->real_escape_string($_POST['contador']);

        // Consulta preparada para actualizar la compra
        $query_update = $conn->prepare("UPDATE compras SET
            emision = ?, vencimiento = ?, proveedor = ?, categoria = ?, subtotal = ?, descuento = ?, cantidad = ?, total = ?, vencimientoPago = ?, tipoCompra = ?, producto = ?, precio = ?, iva = ?, notaInterna = ?, contador = ?
            WHERE id = ?");

        // Vincular los parámetros a la consulta preparada
        $query_update->bind_param('ssssdddssssssss', 
            $emision, $vencimiento, $proveedor, $categoria, $subtotal, $descuento, $cantidad, $total, $vencimientoPago, $tipoCompra, $producto, $precio, $iva, $notaInterna, $contador, $compra_id);

        // Ejecutar la consulta
        if ($query_update->execute()) {
            $mensaje = "Compra actualizada exitosamente.";
        } else {
            $mensaje = "Error al actualizar la compra: " . $query_update->error;
        }
    }
} else {
    die("ID de compra no válido.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Compra</title>
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
        <h2>Editar Compra</h2>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-info"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- Formulario para editar la compra -->
        <div class="form-container">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="emision" class="form-label">Fecha de Emisión</label>
                    <input type="date" id="emision" name="emision" class="form-control" value="<?php echo $compra['emision']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="vencimiento" class="form-label">Fecha de Vencimiento</label>
                    <input type="date" id="vencimiento" name="vencimiento" class="form-control" value="<?php echo $compra['vencimiento']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="proveedor" class="form-label">Proveedor</label>
                    <input type="text" id="proveedor" name="proveedor" class="form-control" value="<?php echo $compra['proveedor']; ?>">
                </div>

                <div class="mb-3">
                    <label for="categoria" class="form-label">Categoría</label>
                    <input type="text" id="categoria" name="categoria" class="form-control" value="<?php echo $compra['categoria']; ?>">
                </div>

                <div class="mb-3">
                    <label for="subtotal" class="form-label">Subtotal</label>
                    <input type="number" id="subtotal" name="subtotal" class="form-control" value="<?php echo $compra['subtotal']; ?>" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="descuento" class="form-label">Descuento</label>
                    <input type="number" id="descuento" name="descuento" class="form-control" value="<?php echo $compra['descuento']; ?>" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="cantidad" class="form-label">Cantidad</label>
                    <input type="number" id="cantidad" name="cantidad" class="form-control" value="<?php echo $compra['cantidad']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="total" class="form-label">Total</label>
                    <input type="number" id="total" name="total" class="form-control" value="<?php echo $compra['total']; ?>" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label for="vencimientoPago" class="form-label">Fecha de Vencimiento de Pago</label>
                    <input type="date" id="vencimientoPago" name="vencimientoPago" class="form-control" value="<?php echo $compra['vencimientoPago']; ?>">
                </div>

                <div class="mb-3">
                    <label for="tipoCompra" class="form-label">Tipo de Compra</label>
                    <input type="text" id="tipoCompra" name="tipoCompra" class="form-control" value="<?php echo $compra['tipoCompra']; ?>">
                </div>

                <div class="mb-3">
                    <label for="producto" class="form-label">Producto</label>
                    <input type="text" id="producto" name="producto" class="form-control" value="<?php echo $compra['producto']; ?>">
                </div>

                <div class="mb-3">
                    <label for="precio" class="form-label">Precio</label>
                    <input type="number" id="precio" name="precio" class="form-control" value="<?php echo $compra['precio']; ?>" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="iva" class="form-label">IVA</label>
                    <input type="number" id="iva" name="iva" class="form-control" value="<?php echo $compra['iva']; ?>" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="notaInterna" class="form-label">Nota Interna</label>
                    <textarea id="notaInterna" name="notaInterna" class="form-control"><?php echo $compra['notaInterna']; ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="contador" class="form-label">Contador</label>
                    <input type="text" id="contador" name="contador" class="form-control" value="<?php echo $compra['contador']; ?>">
                </div>

                <button type="submit" class="btn btn-success">Actualizar Compra</button>
                <a href="lista_compras.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

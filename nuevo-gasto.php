<?php
ob_start();
include('config.php');
include('clase/Gasto.php');

// Obtener lista de productos y proveedores
$productosQuery = $conn->query("SELECT * FROM producto");
$proveedores = $conn->query("SELECT * FROM proveedores");

// Guardar los productos en un array para reutilizarlos
$productosArray = [];
while ($row = $productosQuery->fetch_assoc()) {
    $productosArray[] = $row;
}

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $fecha = $_POST['fecha'];
    $proveedor = $_POST['proveedor'];
    $estado = $_POST['estado']; // Estado (Por Pagar o Pagado)
    $metodo_pago = $_POST['metodo_pago'];
    $categoria_pago = $_POST['categoria_pago'];
    $descripcion = $_POST['descripcion'];

    // Calcular el subtotal, IVA y total
    $subtotal = 0;
    $iva = 0;
    $productos_seleccionados = [];

    // Recorremos el array de productos seleccionados
    // Aquí se asume que el índice del array $_POST['productos'] es el id del producto
    foreach ($_POST['productos'] as $producto_id => $cantidad) {
        // Consultamos el producto (puedes optimizar obteniendo todos los datos del array $productosArray si fuera necesario)
        $producto = $conn->query("SELECT * FROM producto WHERE id = $producto_id")->fetch_assoc();
        $precio_unitario = $producto['precio'];
        $subtotal += $precio_unitario * $cantidad;
        $productos_seleccionados[] = [
            'producto' => $producto['nombre'],
            'cantidad' => $cantidad,
            'precio' => $precio_unitario,
            'total' => $precio_unitario * $cantidad
        ];
    }
    
    // Calcular IVA (21%)
    $iva = $subtotal * 0.21;
    
    // Total con IVA
    $total = $subtotal + $iva;

    // Insertar el gasto en la base de datos (suponiendo que existe un método en Gasto para ello)
    $gasto = new Gasto($conn);
    $gasto->insertarGasto($fecha, $proveedor, $estado, $metodo_pago, $categoria_pago, $descripcion, $subtotal, $iva, $total, $productos_seleccionados);

    // Redirigir a la lista de gastos
    header('Location: gastos.php');
    exit();
}
?>

<script>
    function actualizarPrecio(index) {
        const productoSelect = document.getElementById('producto_' + index);
        const cantidadInput = document.getElementById('cantidad_' + index);
        const precioInput = document.getElementById('precio_' + index);

        const selectedOption = productoSelect.options[productoSelect.selectedIndex];
        const precioUnitario = parseFloat(selectedOption.getAttribute('data-precio')) || 0;
        const cantidad = parseInt(cantidadInput.value) || 0;

        const precioTotal = precioUnitario * cantidad;

        if (precioTotal > 0) {
            precioInput.value = '$' + precioTotal.toFixed(2);
        } else {
            precioInput.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const cantidadInputs = document.querySelectorAll('input[name^="cantidad"]');
        cantidadInputs.forEach(function(input) {
            const index = input.name.match(/\d+/)[0];
            actualizarPrecio(index);
        });
    });

    let productCount = 1;

    document.getElementById('agregar-producto').addEventListener('click', function() {
        productCount++;
        const productItem = document.createElement('div');
        productItem.classList.add('producto-item');
        // Usamos backticks para la plantilla y recorremos el array de productos usando PHP embebido
        productItem.innerHTML = `
            <select name="productos[${productCount}]" class="form-control" id="producto_${productCount}" required onchange="actualizarPrecio(${productCount})">
                <option value="">Seleccione un Producto</option>
                <?php foreach($productosArray as $producto): ?>
                    <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['Costo
                    ']; ?>">
                        <?php echo $producto['Nombre']; ?> - $<?php echo number_format($producto['Costo'], 2); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="cantidad[${productCount}]" class="form-control mt-2" id="cantidad_${productCount}" placeholder="Cantidad" min="1" required oninput="actualizarPrecio(${productCount})">
            <input type="text" name="precio[${productCount}]" class="form-control mt-2" id="precio_${productCount}" placeholder="Precio" readonly>
        `;
        document.getElementById('productos-container').appendChild(productItem);
    });
</script>

<style>
    .container.mt-4 {
        width: 1200px;
        margin-left: 220px;
        padding: 80px;
    }

    .producto-item {
        margin-top: 10px;
    }
</style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Gasto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Nuevo Gasto</h2>

        <form method="POST">
            <!-- Fecha -->
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha de Emisión</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>

            <!-- Proveedor -->
            <div class="mb-3">
                <label for="proveedor" class="form-label">Proveedor</label>
                <select name="proveedor" class="form-control" required>
                    <option value="">Seleccione un Proveedor</option>
                    <?php while ($proveedor = $proveedores->fetch_assoc()) : ?>
                        <option value="<?php echo $proveedor['id']; ?>">
                            <?php echo $proveedor['proveedor']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Estado -->
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" class="form-control" required>
                    <option value="por_pagar">Por Pagar</option>
                    <option value="pagado">Pagado</option>
                </select>
            </div>

            <!-- Método de Pago -->
            <div class="mb-3">
                <label for="metodo_pago" class="form-label">Método de Pago</label>
                <select name="metodo_pago" class="form-control" required>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>

            <!-- Categoría de Pago -->
            <div class="mb-3">
                <label for="categoria_pago" class="form-label">Categoría de Pago</label>
                <select name="categoria_pago" class="form-control" required>
                    <option value="contado">Contado</option>
                    <option value="credito">Crédito</option>
                </select>
            </div>

            <!-- Descripción -->
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" required>
            </div>

            <!-- Productos -->
            <div id="productos-container">
           
            </div>

            <button type="button" id="agregar-producto" class="btn btn-secondary mt-2">+ Agregar Producto</button>

            <button type="submit" class="btn btn-primary mt-3">Guardar Gasto</button>
        </form>
    </div>
    <script>
        // Convertir el array de productos a JSON y pasarlo a JavaScript
        const productos = <?php echo json_encode($productosArray); ?>;

        // Función para agregar un producto al formulario
        function agregarProducto(index) {
            const productItem = document.createElement('div');
            productItem.classList.add('producto-item');

            // Crear las opciones para el select de productos
            let options = '<option value="">Seleccione un Producto</option>';
            productos.forEach(producto => {
                options += `<option value="${producto.id}" data-precio="${producto.Costo}" data-nombre="${producto.Nombre}">${producto.Nombre} - $${parseFloat(producto.Costo).toFixed(2)}</option>`;
            });

            productItem.innerHTML = `
                <select name="productos[${index}]" class="form-control" id="producto_${index}" required onchange="actualizarPrecio(${index})">
                    ${options}
                </select>
                <input type="number" name="cantidad[${index}]" class="form-control mt-2" id="cantidad_${index}" placeholder="Cantidad" min="1" required oninput="actualizarPrecio(${index})">
                <input type="text" name="precio[${index}]" class="form-control mt-2" id="precio_${index}" placeholder="Precio" readonly>
            `;
            document.getElementById('productos-container').appendChild(productItem);
        }

        // Función para actualizar el precio total del producto
        function actualizarPrecio(index) {
            const productoSelect = document.getElementById('producto_' + index);
            const cantidadInput = document.getElementById('cantidad_' + index);
            const precioInput = document.getElementById('precio_' + index);

            const selectedOption = productoSelect.options[productoSelect.selectedIndex];
            const costoUnitario = parseFloat(selectedOption.getAttribute('data-precio')); // Usamos el costo
            const cantidad = parseInt(cantidadInput.value) || 0;

            const precioTotal = costoUnitario * cantidad;

            if (precioTotal > 0) {
                precioInput.value = '$' + precioTotal.toFixed(2);
            } else {
                precioInput.value = '';
            }
        }

        // Agregar el primer producto al cargar la página
        document.addEventListener('DOMContentLoaded', function () {
            agregarProducto(1);
        });

        let productCount = 1;

        // Agregar más productos al hacer clic en el botón
        document.getElementById('agregar-producto').addEventListener('click', function () {
            productCount++;
            agregarProducto(productCount);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

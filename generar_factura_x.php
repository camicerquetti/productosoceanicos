<?php

// Incluir las dependencias necesarias
include('config.php');
include('clase/Ingresosx.php');

// Crear una instancia de la clase Ingreso
$ingreso = new Ingreso($conn);

// Verificar que se haya pasado un ID válido
if (isset($_GET['id'])) {
    $ingreso_id = (int)$_GET['id'];
    $detalle_ingreso = $ingreso->obtenerIngresoPorId($ingreso_id);
    if (!$detalle_ingreso) {
        die("Ingreso no encontrado.");
    }
} else {
    die("No se ha proporcionado un ID de ingreso.");
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .container { width: 80%; margin: auto; padding: 20px; border: 1px solid #000; }
        .header { font-size: 20px; font-weight: bold; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: left; }
        .total { font-weight: bold; font-size: 18px; margin-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <img src="img/LOGO.png" alt="Logo" width="100"><br>
    <div class="header">Producto Oceanico SRL</div>
    <p>Santa Magdalena 309, CABA 1277 - Buenos Aires</p>
    <p>Tel: 7546-2063 | productooceanicopatricio@gmail.com</p>
    <p>www.productooceanico.com.ar</p>
    <hr>
    
    <h2>Factura X</h2>
    <p>Fecha de Emisión: <?php echo isset($detalle_ingreso['fecha']) ? $detalle_ingreso['fecha'] : 'No disponible'; ?></p>
    <p>Factura AFIP: <?php echo isset($detalle_ingreso['factura_afip']) ? $detalle_ingreso['factura_afip'] : 'No disponible'; ?></p>
    <p>Estado: <?php echo isset($detalle_ingreso['estado']) ? $detalle_ingreso['estado'] : 'No disponible'; ?></p>
    <p>Razón Social: <?php echo isset($detalle_ingreso['razon_social']) ? $detalle_ingreso['razon_social'] : 'No disponible'; ?></p>
    <p>CUIT: <?php echo isset($detalle_ingreso['cuit']) ? $detalle_ingreso['cuit'] : 'No disponible'; ?></p>
    <p>Condición IVA: <?php echo isset($detalle_ingreso['condicion_iva']) ? $detalle_ingreso['condicion_iva'] : 'No disponible'; ?></p>
    <p>Vendedor: <?php echo isset($detalle_ingreso['empleado_responsable']) ? $detalle_ingreso['empleado_responsable'] : 'No disponible'; ?></p>
    
    <table>
        <tr>
            <th>Código</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>IVA</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach ($detalle_ingreso['productos'] as $producto): ?>
        <tr>
            <td><?php echo isset($producto['codigo']) ? $producto['codigo'] : 'No disponible'; ?></td>
            <td><?php echo isset($producto['Nombre']) ? $producto['Nombre'] : 'No disponible'; ?></td>
            <td><?php echo isset($producto['cantidad']) ? $producto['cantidad'] : 'No disponible'; ?></td>
            <td>$<?php echo isset($producto['precio']) ? number_format($producto['precio'], 2, ',', '.') : 'No disponible'; ?></td>
            <td><?php echo isset($producto['iva']) ? $producto['iva'] : '0'; ?>%</td>
            <td>$<?php echo isset($producto['subtotal']) ? number_format($producto['subtotal'], 2, ',', '.') : 'No disponible'; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p class="total">Neto No Gravado: $<?php echo isset($detalle_ingreso['neto_no_gravado']) ? number_format($detalle_ingreso['neto_no_gravado'], 2, ',', '.') : '0,00'; ?></p>
    <p class="total">Total Venta: $<?php echo isset($detalle_ingreso['total_venta']) ? number_format($detalle_ingreso['total_venta'], 2, ',', '.') : '0,00'; ?></p>
    <p class="total">Impuesto: <?php echo isset($detalle_ingreso['iva_total']) ? $detalle_ingreso['iva_total'] : '0'; ?>%</p>
    <p class="total">Total a Cobrar: $<?php echo isset($detalle_ingreso['total_cobrar']) ? number_format($detalle_ingreso['total_cobrar'], 2, ',', '.') : '0,00'; ?></p>
    <p class="total">Total Cobrado: $<?php echo isset($detalle_ingreso['total_cobrado']) ? number_format($detalle_ingreso['total_cobrado'], 2, ',', '.') : '0,00'; ?></p>
    
    <button onclick="window.print()">Imprimir / Guardar PDF</button>
</div>

</body>
</html>

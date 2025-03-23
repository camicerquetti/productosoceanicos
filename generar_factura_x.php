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
    
    <h2>Factura <span style="font-size: 30px; font-weight: bold;"> <?php echo $detalle_ingreso['tipo_factura']; ?> </span></h2>
    <p>Fecha de Emisión: <?php echo $detalle_ingreso['fecha']; ?></p>
    <p>Factura AFIP: <?php echo $detalle_ingreso['factura_afip']; ?></p>
    <p>Estado: <?php echo $detalle_ingreso['estado']; ?></p>
    <p>Razón Social: <?php echo $detalle_ingreso['razon_social']; ?></p>
    <p>CUIT: <?php echo $detalle_ingreso['cuit']; ?></p>
    <p>Condición IVA: <?php echo $detalle_ingreso['condicion_iva']; ?></p>
    <p>Vendedor: <?php echo $detalle_ingreso['empleado_responsable']; ?></p>
    
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
            <td><?php echo $producto['codigo']; ?></td>
            <td><?php echo $producto['Nombre']; ?></td>
            <td><?php echo $producto['cantidad']; ?></td>
            <td>$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
            <td><?php echo $producto['iva']; ?>%</td>
            <td>$<?php echo number_format($producto['subtotal'], 2, ',', '.'); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p class="total">Neto No Gravado: $<?php echo number_format($detalle_ingreso['neto_no_gravado'], 2, ',', '.'); ?></p>
    <p class="total">Total Venta: $<?php echo number_format($detalle_ingreso['total_venta'], 2, ',', '.'); ?></p>
    <p class="total">Impuesto: <?php echo $detalle_ingreso['iva_total']; ?>%</p>
    <p class="total">Total a Cobrar: $<?php echo number_format($detalle_ingreso['total_cobrar'], 2, ',', '.'); ?></p>
    <p class="total">Total Cobrado: $<?php echo number_format($detalle_ingreso['total_cobrado'], 2, ',', '.'); ?></p>
    
    <button onclick="window.print()">Imprimir / Guardar PDF</button>
</div>

</body>
</html>
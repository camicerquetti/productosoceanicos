<?php
// Incluir la clase Ingreso y la conexión
include('config.php');
include('clase/Ingreso.php');

// Crear una instancia de la clase Ingreso
$ingreso = new Ingreso($conn);

// Manejar filtro
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';
$estado_filtro = isset($_POST['estado']) ? $_POST['estado'] : '%%';

// Obtener el número de ingresos por página
$ingresos_por_pagina = 10;

// Calcular la página actual
$paginacion = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginacion - 1) * $ingresos_por_pagina;

// Obtener los ingresos filtrados con paginación, solo ventas con los estados deseados
$ingresos = $ingreso->obtenerIngresosVentas($filtro, $estado_filtro, $ingresos_por_pagina, $offset);

// Obtener el total de ingresos para la paginación
$total_ingresos = $ingreso->contarIngresosVentas($filtro, $estado_filtro);
$total_paginas = ceil($total_ingresos / $ingresos_por_pagina);
?>
    <style>
        .pagination {
            width: 100%;
            justify-content: center;  /* Centra la paginación */
        }
        .container.mt-4 {
            width: 1200px;  
            margin-left: 220px;
            padding: 70px;
        }
        .estado-vencido {
            color: red;
        }
        .estado-pendiente {
            color: yellow;
        }
        .estado-facturado {
            color: green;
        }
        
    </style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Lista de Ingresos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Lista de Ventas</h2>
        
        <!-- Botón "+ INGRESO" -->
        <a href="nuevo_ingreso.php" class="btn btn-success mb-3">
            <strong>+ INGRESO</strong>
        </a>

        <!-- Formulario de filtro -->
        <form method="POST">
            <input type="text" name="filtro" class="form-control" placeholder="Buscar por tipo o cliente" value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
            
            <!-- Filtro de estado -->
            <select name="estado" class="form-control mt-2">
                <option value="%%" <?php echo $estado_filtro == '%%' ? 'selected' : ''; ?>>Todos los estados</option>
                <option value="vencido" <?php echo $estado_filtro == 'vencido' ? 'selected' : ''; ?>>Vencidos</option>
                <option value="pendiente" <?php echo $estado_filtro == 'pendiente' ? 'selected' : ''; ?>>Pendientes</option>
                <option value="facturado" <?php echo $estado_filtro == 'facturado' ? 'selected' : ''; ?>>Facturado</option>
            </select>

            <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
        </form>

        <!-- Tabla de ingresos -->
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Tipo Ingreso</th>
                    <th>Descripción</th>
                    <th>Monto</th>
                    <th>Cliente</th>
                    <th>Factura AFIP</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $ingresos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['fecha']; ?></td>
                        <td><?php echo $row['tipo_ingreso']; ?></td>
                        <td><?php echo $row['descripcion']; ?></td>
                        <td><?php echo '$' . number_format($row['monto'], 2); ?></td>
                        <td><?php echo $row['cliente']; ?></td>
                        <td><?php echo $row['factura_afip']; ?></td>
                        <td class="<?php echo 'estado-' . $row['estado']; ?>">
                            <?php echo ucfirst($row['estado']); ?>
                        </td>
                        <td>
                            <!-- Enlace para ver más detalles o generar factura -->
                            <a href="detalles_venta.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Ver Detalles</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <nav aria-label="Paginación">
            <ul class="pagination mt-3">
                <li class="page-item <?php echo $paginacion <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $paginacion - 1; ?>">Anterior</a>
                </li>

                <?php
                // Mostrar las páginas
                $rango = 2;
                $inicio = max(1, $paginacion - $rango);
                $fin = min($total_paginas, $paginacion + $rango);
                for ($i = $inicio; $i <= $fin; $i++) :
                ?>
                    <li class="page-item <?php echo $i == $paginacion ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php echo $paginacion >= $total_paginas ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $paginacion + 1; ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

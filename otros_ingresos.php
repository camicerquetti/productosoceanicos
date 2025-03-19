<?php
// Incluir la clase OtrosIngreso y la conexión
include('config.php');
include('clase/otrosIngresos.php');

// Crear una instancia de la clase OtrosIngreso
$otros_ingreso = new OtrosIngreso($conn);

// Manejar filtro
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';

// Obtener el número de otros ingresos por página
$otros_ingresos_por_pagina = 10;

// Calcular la página actual
$paginacion = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginacion - 1) * $otros_ingresos_por_pagina;

// Obtener los otros ingresos filtrados con paginación
$otros_ingresos = $otros_ingreso->obtenerOtrosIngresos($filtro, $otros_ingresos_por_pagina, $offset);

// Obtener el total de otros ingresos para la paginación
$total_otros_ingresos = $otros_ingreso->contarOtrosIngresos($filtro);
$total_paginas = ceil($total_otros_ingresos / $otros_ingresos_por_pagina);
?>
<style>
    .pagination {
        width: 100%;
        justify-content: center;  /* Centra la paginación */
    }
    .container.mt-4 {
        width:1200px;  
        margin-left: 220px;
        padding: 70px;
    }
</style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Otros Ingresos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Lista de Otros Ingresos</h2>
        <!-- Botón "+ OTRO INGRESO" -->
        <a href="nuevo_otro_ingreso.php" class="btn btn-success mb-3">
            <strong>+ OTRO INGRESO</strong>
        </a>

        <!-- Formulario de filtro -->
        <form method="POST">
            <input type="text" name="filtro" class="form-control" placeholder="Buscar por tipo o cuenta" value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
            <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
        </form>

        <!-- Tabla de otros ingresos -->
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ingreso</th>
                    <th>Categoría</th>
                    <th>Cuenta</th>
                    <th>Vendedor</th>
                    <th>Total</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $otros_ingresos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['ingreso']; ?></td>
                        <td><?php echo $row['categoria']; ?></td>
                        <td><?php echo $row['cuenta']; ?></td>
                        <td><?php echo $row['vendedor']; ?></td>
                        <td><?php echo '$' . number_format($row['total'], 2); ?></td>
                        <td><?php echo $row['descripcion']; ?>
                          <!-- Botón de editar -->
                          <td>
                <a href="editar_otro_ingreso.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                <!-- Botón de eliminar -->
                <a href="lista_otros_ingresos.php?id_eliminar=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este ingreso?')">Eliminar</a>
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

<?php
// Incluir la clase Usuario y la conexión a la base de datos
include('config.php');
include('clase/usuarios.php');

// Crear una instancia de la clase Usuario
$usuario = new Usuario($conn);

// Verificar si se ha pasado un 'id' en la URL
if (isset($_GET['id'])) {
    // Obtener el id del usuario
    $usuario->id = $_GET['id'];
    
    // Obtener los datos del usuario
    $usuario->obtenerDatos(); // Esto carga los datos del usuario en la instancia de la clase
    
    // Verificar si el formulario ha sido enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Asignar los nuevos datos recibidos del formulario
        $usuario->usuario = $_POST['usuario'];
        $usuario->nombre = $_POST['nombre'];
        $usuario->apellido = $_POST['apellido'];
        $usuario->email = $_POST['email'];
        $usuario->cargo = $_POST['cargo'];
        $usuario->rol = $_POST['rol'];
      // Si estás manejando un campo de avatar
      if (!empty($_POST['contraseña'])) {
        $usuario->contraseña = password_hash($_POST['contraseña'], PASSWORD_BCRYPT); // Hashear la nueva contraseña
    } else {
        $usuario->contraseña = $usuario->obtenerContraseñaActual(); // Mantener la contraseña actual si el campo está vacío
    }
    
    
        // Intentar actualizar los datos del usuario
        if ($usuario->actualizarDatos()) {
            echo "<div class='alert alert-success'>Usuario actualizado con éxito</div>";
        } else {
            echo "<div class='alert alert-danger'>Error al actualizar el usuario.</div>";
        }
    }
} else {
    echo "<div class='alert alert-danger'>No se ha especificado el ID del usuario.</div>";
}
?>
<style>
    /* Ajuste para que el contenido quede fuera del encabezado */
    body {
        padding-top: 80px; /* Agrega espacio debajo del header (ajusta según el tamaño del header) */
    }

    .container {
        margin-left: 140px; /* Ajusta el margen a la izquierda según lo necesites */
    }

    /* Si deseas un margen solo en el formulario, puedes hacerlo así */
    form {
        height:40%;
        margin-left: 320px; /* Asegúrate de ajustar esto según sea necesario */
    }

    /* Opcional: Si deseas un mayor margen a nivel de los campos de formulario */
    .form-control {
        margin-left:5px; /* Ajusta según lo necesites */
    }
    .container.mt-4 {
        width: 1200px;
        margin-left: 220px;
        padding: 80px;

        }

    </style>

    

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<header>
<?php
include('headeradmin.php');?></header>
<body>
    <div class="container">
        <h2 style="margin-left:500px">Editar Usuario</h2>
        <form action="editar_usuario.php?id=<?php echo $usuario->id; ?>" method="POST">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" name="usuario" class="form-control" value="<?php echo $usuario->usuario; ?>" required>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?php echo $usuario->nombre; ?>" required>
            </div>

            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" name="apellido" class="form-control" value="<?php echo $usuario->apellido; ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo $usuario->email; ?>" required>
            </div>

            <div class="mb-3">
                <label for="cargo" class="form-label">Cargo</label>
                <input type="text" name="cargo" class="form-control" value="<?php echo $usuario->cargo; ?>" required>
            </div>

            <div class="mb-3">
                <label for="rol" class="form-label">Rol</label>
                <select name="rol" class="form-control" required>
                    <option value="admin" <?php echo $usuario->rol == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="empleado" <?php echo $usuario->rol == 'empleado' ? 'selected' : ''; ?>>Empleado</option>
                    <option value="usuario" <?php echo $usuario->rol == 'usuario' ? 'selected' : ''; ?>>Usuario</option>
                </select>
            </div>

        
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña (opcional)</label>
                <input type="password" name="contraseña" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

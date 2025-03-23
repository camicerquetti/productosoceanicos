<?php
class Gasto {
    private $conn;

    // Constructor de la clase
    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Método para insertar un gasto en la base de datos
    public function insertarGasto($fecha, $proveedor, $estado, $metodo_pago, $categoria, $descripcion, $subtotal, $iva, $total, $productos_seleccionados, $vendedor) {
        // Insertar el gasto en la tabla "gastosx"
        $query = "INSERT INTO gastosx (fecha, proveedor, estado, metodo_pago, categoria, descripcion, subtotal, iva, total, productos, vendedor) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Error en la consulta: " . $this->conn->error);
        }

        // Convertir los productos seleccionados a JSON
        $productos_json = json_encode($productos_seleccionados);

        // Asociar parámetros
        $stmt->bind_param("sssssssssss", $fecha, $proveedor, $estado, $metodo_pago, $categoria, $descripcion, $subtotal, $iva, $total, $productos_json, $vendedor);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Obtener el ID del gasto insertado
            $gasto_id = $stmt->insert_id;

            // Insertar los productos relacionados
            foreach ($productos_seleccionados as $producto) {
                $query_producto = "INSERT INTO gasto_productos (gasto_id, producto, cantidad, precio, monto) 
                                   VALUES (?, ?, ?, ?, ?)";
                $stmt_producto = $this->conn->prepare($query_producto);
                if (!$stmt_producto) {
                    die("Error en la consulta de productos: " . $this->conn->error);
                }

                // Insertar los productos en la tabla gasto_productos
                $stmt_producto->bind_param("isidd", $gasto_id, $producto['producto'], $producto['cantidad'], $producto['precio'], $producto['total']);
                $stmt_producto->execute();
            }

            $stmt->close();
            return true;
        } else {
            die("Error al insertar gasto: " . $stmt->error);
            return false;
        }
    }

    // Método para obtener todos los gastos con filtros y paginación
    public function obtenerGastos($filtro, $limit, $offset) {
        $query = "SELECT * FROM gastosx WHERE 
                  (descripcion LIKE ? OR categoria LIKE ? OR estado LIKE ?)
                  LIMIT ?, ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssi", $filtro, $filtro, $filtro, $offset, $limit);

        $stmt->execute();
        return $stmt->get_result();
    }

    // Método para contar el total de gastos (para la paginación)
    public function contarGastos($filtro) {
        $query = "SELECT COUNT(*) AS total FROM gastosx WHERE 
                  (descripcion LIKE ? OR categoria LIKE ? OR estado LIKE ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $filtro, $filtro, $filtro);

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    // Método para obtener un gasto por su ID
    public function obtenerGastoPorId($id) {
        $query = "SELECT * FROM gastosx WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Método para actualizar un gasto
    public function actualizarGasto($id, $fecha, $descripcion, $categoria, $metodo_pago, $monto, $estado) {
        $query = "UPDATE gastosx SET fecha = ?, descripcion = ?, categoria = ?, metodo_pago = ?, subtotal = ?, iva = ?, total = ?, estado = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssdddi", $fecha, $descripcion, $categoria, $metodo_pago, $monto['subtotal'], $monto['iva'], $monto['total'], $estado, $id);

        return $stmt->execute();
    }

    // Método para eliminar un gasto
    public function eliminarGasto($id) {
        $query = "DELETE FROM gastosx WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
?>

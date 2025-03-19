<?php
class Gasto {
    private $conn;

    // Constructor de la clase
    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Método para insertar un gasto en la base de datos
    public function insertarGasto($fecha, $descripcion, $categoria, $metodo_pago, $monto, $estado) {
        $query = "INSERT INTO gastos (fecha, descripcion, categoria, metodo_pago, monto, estado)
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssds", $fecha, $descripcion, $categoria, $metodo_pago, $monto, $estado);

        // Ejecutar la consulta y verificar si fue exitosa
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Método para obtener todos los gastos con filtros y paginación
    public function obtenerGastos($filtro, $limit, $offset) {
        $query = "SELECT * FROM gastos WHERE 
                  (descripcion LIKE ? OR categoria LIKE ? OR estado LIKE ?)
                  LIMIT ?, ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssi", $filtro, $filtro, $filtro, $offset, $limit);

        $stmt->execute();
        return $stmt->get_result();
    }

    // Método para contar el total de gastos (para la paginación)
    public function contarGastos($filtro) {
        $query = "SELECT COUNT(*) AS total FROM gastos WHERE 
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
        $query = "SELECT * FROM gastos WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Método para actualizar un gasto
    public function actualizarGasto($id, $fecha, $descripcion, $categoria, $metodo_pago, $monto, $estado) {
        $query = "UPDATE gastos SET fecha = ?, descripcion = ?, categoria = ?, metodo_pago = ?, monto = ?, estado = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssdsi", $fecha, $descripcion, $categoria, $metodo_pago, $monto, $estado, $id);

        return $stmt->execute();
    }

    // Método para eliminar un gasto
    public function eliminarGasto($id) {
        $query = "DELETE FROM gastos WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
?>

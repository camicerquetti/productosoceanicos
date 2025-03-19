<?php
class OtrosIngreso {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Método para obtener los otros ingresos con filtro y paginación
    public function obtenerOtrosIngresos($filtro, $limit, $offset) {
        $sql = "SELECT id, ingreso, categoria, cuenta, vendedor, total, descripcion 
                FROM otros_ingresos 
                WHERE ingreso LIKE ? OR cuenta LIKE ? 
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssii', $filtro, $filtro, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Método para contar los otros ingresos
    public function contarOtrosIngresos($filtro) {
        $sql = "SELECT COUNT(*) 
                FROM otros_ingresos 
                WHERE ingreso LIKE ? OR cuenta LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $filtro, $filtro);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        return $total;
    }
     // Método para insertar un nuevo ingreso
     public function insertarIngreso($ingreso, $categoria, $cuenta, $vendedor, $total, $descripcion) {
        $sql = "INSERT INTO otros_ingresos (ingreso, categoria, cuenta, vendedor, total, descripcion) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        // Verificar si la preparación fue exitosa
        if ($stmt === false) {
            die('Error en la preparación de la consulta: ' . $this->conn->error);
        }

        // Vincular los parámetros a la consulta preparada
        $stmt->bind_param('ssssds', $ingreso, $categoria, $cuenta, $vendedor, $total, $descripcion);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            return true;  // Éxito
        } else {
            return false; // Error al insertar
        }
    }
}
?>

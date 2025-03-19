<?php
class Compra {
    private $conn;

    // Constructor para establecer la conexión
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Método para insertar una compra
    public function insertarCompra($emision, $vencimiento, $proveedor, $categoria, $subtotal, $descuento, $cantidad, $total, $vencimientoPago, $tipoCompra, $producto, $precio, $iva, $notaInterna, $contador) {
        $query = "INSERT INTO compras (emision, vencimiento, proveedor, categoria, subtotal, descuento, cantidad, total, vencimientoPago, tipoCompra, producto, precio, iva, notaInterna, contador) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssssdiidsssssss', $emision, $vencimiento, $proveedor, $categoria, $subtotal, $descuento, $cantidad, $total, $vencimientoPago, $tipoCompra, $producto, $precio, $iva, $notaInterna, $contador);
        $stmt->execute();
        $stmt->close();
    }

    // Método para obtener las compras con paginación
    public function obtenerCompras($filtro, $limit, $offset) {
        $query = "SELECT * FROM compras WHERE proveedor LIKE ? OR producto LIKE ? LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssii', $filtro, $filtro, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Método para contar el total de compras
    public function contarCompras($filtro) {
        $query = "SELECT COUNT(*) AS total FROM compras WHERE proveedor LIKE ? OR producto LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $filtro, $filtro);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }
}
?>

<?php
class Compra {
    private $conn;

    // Constructor para establecer la conexión
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function insertarCompra($fecha, $proveedor, $estado, $metodo_pago, $categoria_pago, $descripcion, $subtotal, $iva, $total, $productos_seleccionados, $vendedor) {
        // 1. Insertar la compra principal en la tabla 'compras'
        $query_compra = "INSERT INTO compras (emision, proveedor, categoria, subtotal, descuento, cantidad, total, vencimientoPago, tipoCompra, producto, precio, iva, notaInterna, contador, estado, vendedor)
                         VALUES ('$fecha', '$proveedor', '$categoria_pago', '$subtotal', 0, 0, '$total', NULL, '$metodo_pago', '$descripcion', '$precio_unitario', '$iva', '$descripcion', '$vendedor', '$estado', '$vendedor')";
    
        if ($this->conn->query($query_compra) === TRUE) {
            // Obtener el ID de la compra recién insertada
            $compra_id = $this->conn->insert_id;
            
            // 2. Insertar los productos en la tabla 'detalle_compra'
            foreach ($productos_seleccionados as $producto) {
                $producto_id = $producto['producto_id']; // ID del producto
                $cantidad = $producto['cantidad']; // Cantidad
                $precio_total = $producto['total']; // Precio total por producto
    
                // Inserción en la tabla detalle_compra
                $query_detalle = "INSERT INTO detalle_compra (compra_id, producto_id, cantidad, precio_total)
                                  VALUES ('$compra_id', '$producto_id', '$cantidad', '$precio_total')";
    
                if (!$this->conn->query($query_detalle)) {
                    echo "Error al insertar detalle del producto: " . $this->conn->error;
                }
            }
    
            echo "Compra insertada correctamente.";
        } else {
            echo "Error al insertar la compra: " . $this->conn->error;
        }
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

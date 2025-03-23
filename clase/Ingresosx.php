<?php

class Ingreso{
    private $conn;

    // Constructor con la conexión
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function insertarIngreso(
        $fecha, 
        $vencimiento, 
        $tipo_ingreso, 
        $descripcion, 
        $monto, 
        $estado, 
        $metodo_pago, 
        $empleado_responsable, 
        $metodo_transporte, 
        $subtotal, 
        $iva, 
        $total, 
        $proveedor, 
        $tipo_factura, 
        $cliente, 
       
        $productos_json
    ) {
        // Consulta SQL para la tabla ingresosx
        $query = "INSERT INTO ingresosx (fecha, vencimiento, tipo_ingreso, descripcion, monto, estado, empleado_responsable, metodo_pago, metodo_transporte, subtotal, iva, total, proveedor, tipo_factura, cliente, producto) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
        // Preparamos la consulta
        $stmt = $this->conn->prepare($query);
    
        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }
    
        // Vinculamos los parámetros
        if (!$stmt->bind_param("ssssddssdddsds", 
            $fecha, 
            $vencimiento, 
            $tipo_ingreso, 
            $descripcion, 
            $monto,         // double
            $estado, 
            $empleado_responsable, 
            $metodo_pago, 
            $metodo_transporte, 
            $subtotal,      // double
            $iva,           // double
            $total,         // double
            $proveedor,  // Nombre del proveedor (varchar)
            $tipo_factura,  // tipo de factura (varchar)
            $cliente,    // Nombre del cliente (varchar)

            $productos_json // JSON de productos (varchar o text)
        )) {
            throw new Exception("Error al enlazar parámetros: " . $stmt->error);
        }
    
        // Ejecutamos la consulta
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }
    
        return true;
    }
    

    // Función para verificar y actualizar el estado de las facturas a "vencida"
    public function actualizarEstadoVencido() {
        $query = "UPDATE ingresosx SET estado = 'vencida' WHERE estado = 'pendiente' AND TIMESTAMPDIFF(HOUR, fecha, NOW()) > 24";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    // Obtener ingresos con filtro (puede ser por cliente, tipo, etc.)
    public function obtenerIngresos($filtro = '%%', $limit = 10, $offset = 0) {
        $query = "SELECT * FROM ingresosx WHERE tipo_ingreso LIKE ? LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sii", $filtro, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Contar el total de ingresos para la paginación
    public function contarIngresos($filtro = '%%') {
        $query = "SELECT COUNT(*) as total FROM ingresosx WHERE tipo_ingreso LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $filtro);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    }

    // Generar la factura en PDF
    public function generarFacturaPDF($id) {
        // Aquí se implementará la generación del PDF de la factura (con FPDF o TCPDF)
    }
    
    // Método para obtener ingresos filtrados por tipo (venta) y estado
    public function obtenerIngresosVentas($filtro, $estado_filtro, $limit, $offset) {
        // Crear la consulta SQL
        $sql = "SELECT * FROM ingresosx 
                WHERE tipo_ingreso = 'venta' 
                AND (estado LIKE ? OR ? = '%%') 
                AND (descripcion LIKE ? OR cliente LIKE ?) 
                LIMIT ? OFFSET ?";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($sql);
        
        // Verifica si la consulta se preparó correctamente
        if (!$stmt) {
            die("Error al preparar la consulta: " . $this->conn->error);
        }
        
        // Vincular los parámetros
        $stmt->bind_param('ssssii', $estado_filtro, $estado_filtro, $filtro, $filtro, $limit, $offset);
        
        // Ejecutar la consulta
        $stmt->execute();
        
        // Obtener y retornar los resultados
        return $stmt->get_result();
    }
   
    // Método para contar los ingresos filtrados por tipo (venta) y estado
    public function contarIngresosVentas($filtro, $estado_filtro) {
        $sql = "SELECT COUNT(*) FROM ingresosx 
                WHERE tipo_ingreso = 'venta' 
                AND (estado LIKE ? OR ? = '%%') 
                AND (descripcion LIKE ? OR cliente LIKE ?)";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssss', $estado_filtro, $estado_filtro, $filtro, $filtro);
        
        // Ejecutar la consulta
        $stmt->execute();
        
        // Obtener el resultado
        $stmt->get_result();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        
        // Retornar el número total de registros
        return $row[0];
    }

    public function obtenerIngresoPorId($id) {
        // Consulta principal para obtener los detalles del ingreso
        $sql = "SELECT ingresosx.*, clientes.cuit, clientes.razon_social, clientes.condicion_iva, ingresosx.empleado_responsable
        FROM ingresosx 
        LEFT JOIN clientes ON ingresosx.cliente = clientes.id
        WHERE ingresosx.id = ?";

        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            die("Error en la consulta SQL: " . $this->conn->error); // Mostrar error real
        }
    
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $ingreso = $resultado->fetch_assoc();
    
        if (!$ingreso) {
            return null;
        }
    
        // Obtener productos asociados a la factura
        $sql_productos = "SELECT p.Codigo, p.Nombre, i.cantidad, i.precio, i.iva, 
        (i.cantidad * i.precio) as subtotal
        FROM ingreso_productos i
        INNER JOIN producto p ON i.producto_id = p.id
        WHERE i.ingreso_id = ?";

        $stmt_prod = $this->conn->prepare($sql_productos);
        
        if (!$stmt_prod) {
            die("Error en la consulta de productos: " . $this->conn->error);
        }
    
        $stmt_prod->bind_param("i", $id);
        $stmt_prod->execute();
        $resultado_prod = $stmt_prod->get_result();
        $ingreso['productos'] = $resultado_prod->fetch_all(MYSQLI_ASSOC);
    
        // Calcular totales
        $ingreso['total_venta'] = array_sum(array_column($ingreso['productos'], 'subtotal'));
        $ingreso['iva'] = 21;
        $ingreso['total_cobrar'] = $ingreso['total_venta'] * (1 + ($ingreso['iva'] / 100));
    
        return $ingreso;
    }
}
?>

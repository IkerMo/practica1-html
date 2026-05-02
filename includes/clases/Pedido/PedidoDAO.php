<?php
namespace es\ucm\fdi\aw\Pedido;
use es\ucm\fdi\aw\Aplicacion;
class PedidoDAO {
    /** Obtiene el siguiente número de pedido para hoy */
    public function obtenerSiguienteNumero() {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            "SELECT COALESCE(MAX(numero_pedido), 0) + 1 AS siguiente 
             FROM Pedidos 
             WHERE DATE(fecha_creacion) = CURDATE()"
        );
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$result['siguiente'];
    }
    /** Crea un pedido */
    public function crear(PedidoDTO $p) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            "INSERT INTO Pedidos (numero_pedido, cliente_id, tipo, estado, fecha_creacion, total_sin_iva, total_con_iva, total_sin_descuento, total_descuento, observaciones) 
             VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iissdddds',
            $p->numero_pedido, $p->cliente_id, $p->tipo, $p->estado,
            $p->total_sin_iva, $p->total_con_iva, $p->total_sin_descuento, $p->total_descuento, $p->observaciones
        );
        
        if ($stmt->execute()) {
            $p->id = $conn->insert_id;
            $stmt->close();
            return $p;
        }
        $stmt->close();
        return false;
    }
    /** Guarda las líneas de un pedido */
    public function guardarLineas($pedidoId, array $lineas) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            "INSERT INTO LineasPedido (pedido_id, producto_id, cantidad, precio_unitario_sin_iva, iva, subtotal_sin_iva, subtotal_con_iva, oferta_id, subtotal_descuento, observaciones, estado_cocina) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        foreach ($lineas as $l) {
            $estadoCocina = $l->estado_cocina ?? 'pendiente';
            $stmt->bind_param('iiididdidss',
                $pedidoId, $l->producto_id, $l->cantidad,
                $l->precio_unitario_sin_iva, $l->iva,
                $l->subtotal_sin_iva, $l->subtotal_con_iva,
                $l->oferta_id,
                $l->subtotal_descuento,
                $l->observaciones,
                $estadoCocina
            );
            $stmt->execute();
        }
        $stmt->close();
    }
    /** Obtener líneas de un pedido */
    public function obtenerLineas($pedidoId) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            "SELECT lp.*, pr.nombre AS nombre_producto, pr.requiere_cocina, u.nombre AS nombre_cocinero
             FROM LineasPedido lp 
             JOIN Productos pr ON lp.producto_id = pr.id 
             LEFT JOIN Usuarios u ON lp.cocinero_id = u.id
             WHERE lp.pedido_id = ?"
        );
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $lineas = [];
        while ($fila = $result->fetch_assoc()) {
            $l = new LineaPedidoDTO();
            $l->id = (int)$fila['id'];
            $l->pedido_id = (int)$fila['pedido_id'];
            $l->producto_id = (int)$fila['producto_id'];
            $l->cantidad = (int)$fila['cantidad'];
            $l->precio_unitario_sin_iva = (float)$fila['precio_unitario_sin_iva'];
            $l->iva = (int)$fila['iva'];
            $l->subtotal_sin_iva = (float)$fila['subtotal_sin_iva'];
            $l->subtotal_con_iva = (float)$fila['subtotal_con_iva'];
            $l->oferta_id = isset($fila['oferta_id']) ? (int)$fila['oferta_id'] : null;
            $l->subtotal_descuento = isset($fila['subtotal_descuento']) ? (float)$fila['subtotal_descuento'] : 0.0;
            $l->observaciones = $fila['observaciones'];
            $l->nombre_producto = $fila['nombre_producto'];
            $l->estado_cocina = $fila['estado_cocina'] ?? 'pendiente';
            $l->requiere_cocina = isset($fila['requiere_cocina']) ? (bool)$fila['requiere_cocina'] : true;
            $l->cocinero_id = $fila['cocinero_id'] ? (int)$fila['cocinero_id'] : null;
            $l->nombre_cocinero = $fila['nombre_cocinero'] ?? '';
            $l->fecha_listo_cocina = $fila['fecha_listo_cocina'] ?? null;
            $lineas[] = $l;
        }
        $stmt->close();
        return $lineas;
    }
    /** Busca un pedido por ID */
    public function buscarPorId($id) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            "SELECT p.*, u.nombre AS nombre_cliente 
             FROM Pedidos p 
             JOIN Usuarios u ON p.cliente_id = u.id 
             WHERE p.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $fila = $result->fetch_assoc();
        $stmt->close();
        
        if (!$fila) return null;
        
        $dto = $this->filaADto($fila);
        $dto->lineas = $this->obtenerLineas($dto->id);
        return $dto;
    }
    /** Lista pedidos de un cliente */
    public function listarPorCliente($clienteId) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            "SELECT p.*, u.nombre AS nombre_cliente 
             FROM Pedidos p 
             JOIN Usuarios u ON p.cliente_id = u.id 
             WHERE p.cliente_id = ? 
             ORDER BY p.fecha_creacion DESC"
        );
        $stmt->bind_param('i', $clienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $lista = [];
        while ($fila = $result->fetch_assoc()) {
            $lista[] = $this->filaADto($fila);
        }
        $stmt->close();
        return $lista;
    }
    /** Lista pedidos que están en alguno de los estados dados */
    public function listarPorEstados(array $estados) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $placeholders = implode(',', array_fill(0, count($estados), '?'));
        $types = str_repeat('s', count($estados));
        
        $stmt = $conn->prepare(
            "SELECT p.*, u.nombre AS nombre_cliente 
             FROM Pedidos p 
             JOIN Usuarios u ON p.cliente_id = u.id 
             WHERE p.estado IN ($placeholders) 
             ORDER BY p.fecha_creacion ASC"
        );
        $stmt->bind_param($types, ...$estados);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $lista = [];
        while ($fila = $result->fetch_assoc()) {
            $dto = $this->filaADto($fila);
            $dto->lineas = $this->obtenerLineas($dto->id);
            $lista[] = $dto;
        }
        $stmt->close();
        return $lista;
    }
    /** Cambia el estado de un pedido y registra en historial */
    public function cambiarEstado($pedidoId, $nuevoEstado, $usuarioId = null) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        // Obtener estado actual
        $stmt = $conn->prepare("SELECT estado FROM Pedidos WHERE id = ?");
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$result) return false;
        $estadoAnterior = $result['estado'];
        
        // Preparar campos adicionales según el estado
        $campoExtra = '';
        if ($nuevoEstado === 'recibido') {
            $campoExtra = ', fecha_confirmacion = NOW()';
        } elseif ($nuevoEstado === 'en_preparacion') {
            $campoExtra = ', fecha_pago = NOW()';
        } elseif ($nuevoEstado === 'cocinando' && $usuarioId) {
            $campoExtra = ", cocinero_id = " . (int)$usuarioId;
        } elseif ($nuevoEstado === 'terminado' && $usuarioId) {
            $campoExtra = ", camarero_id = " . (int)$usuarioId;
        } elseif ($nuevoEstado === 'entregado') {
            $campoExtra = ', fecha_entrega = NOW()';
        }
        
        // Actualizar estado
        $stmt = $conn->prepare("UPDATE Pedidos SET estado = ? $campoExtra WHERE id = ?");
        $stmt->bind_param('si', $nuevoEstado, $pedidoId);
        $ok = $stmt->execute();
        $stmt->close();
        
        if ($ok) {
            // Registrar en historial
            $stmt = $conn->prepare(
                "INSERT INTO PedidoHistorial (pedido_id, estado_anterior, estado_nuevo, usuario_id) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param('issi', $pedidoId, $estadoAnterior, $nuevoEstado, $usuarioId);
            $stmt->execute();
            $stmt->close();
        }
        
        return $ok;
    }

    public function marcarLineaListaCocina($lineaId, $cocineroId) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            "UPDATE LineasPedido lp
             JOIN Productos pr ON lp.producto_id = pr.id
             SET lp.estado_cocina = 'listo_cocina', lp.cocinero_id = ?, lp.fecha_listo_cocina = NOW()
             WHERE lp.id = ? AND pr.requiere_cocina = 1"
        );
        $stmt->bind_param('ii', $cocineroId, $lineaId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function buscarPedidoIdPorLinea($lineaId) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT pedido_id FROM LineasPedido WHERE id = ?");
        $stmt->bind_param('i', $lineaId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ? (int)$result['pedido_id'] : null;
    }

    public function tieneLineasPendientesCocina($pedidoId) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS total
             FROM LineasPedido
             WHERE pedido_id = ? AND estado_cocina = 'pendiente'"
        );
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return ((int)$result['total']) > 0;
    }
    private function filaADto(array $fila) {
        $p = new PedidoDTO();
        $p->id = (int)$fila['id'];
        $p->numero_pedido = (int)$fila['numero_pedido'];
        $p->cliente_id = (int)$fila['cliente_id'];
        $p->tipo = $fila['tipo'];
        $p->estado = $fila['estado'];
        $p->fecha_creacion = $fila['fecha_creacion'];
        $p->fecha_confirmacion = $fila['fecha_confirmacion'];
        $p->fecha_pago = $fila['fecha_pago'];
        $p->fecha_entrega = $fila['fecha_entrega'];
        $p->cocinero_id = $fila['cocinero_id'] ? (int)$fila['cocinero_id'] : null;
        $p->camarero_id = $fila['camarero_id'] ? (int)$fila['camarero_id'] : null;
        $p->total_sin_iva = (float)$fila['total_sin_iva'];
        $p->total_con_iva = (float)$fila['total_con_iva'];
        $p->total_sin_descuento = isset($fila['total_sin_descuento']) ? (float)$fila['total_sin_descuento'] : (float)$fila['total_con_iva'];
        $p->total_descuento = isset($fila['total_descuento']) ? (float)$fila['total_descuento'] : 0.0;
        $p->observaciones = $fila['observaciones'];
        $p->nombre_cliente = $fila['nombre_cliente'] ?? '';
        return $p;
    }
}

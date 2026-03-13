<?php
namespace es\ucm\fdi\aw\Pedido;

use es\ucm\fdi\aw\Producto\ProductoDAO;

class PedidoAppService {
    private $dao;

    public function __construct() {
        $this->dao = new PedidoDAO();
    }

    /** Crea un pedido completo desde el carrito de la sesión */
    public function crearPedidoDesdeCarrito($clienteId, $tipo, $items, $observaciones = '') {
        $productoDAO = new ProductoDAO();
        
        // Construir líneas y calcular totales
        $lineas = [];
        $totalSinIva = 0;
        $totalConIva = 0;
        
        foreach ($items as $item) {
            $producto = $productoDAO->buscarPorId($item['producto_id']);
            if (!$producto || !$producto->disponible) continue;
            
            $l = new LineaPedidoDTO();
            $l->producto_id = $producto->id;
            $l->cantidad = (int)$item['cantidad'];
            $l->precio_unitario_sin_iva = $producto->precio_base;
            $l->iva = $producto->iva;
            $l->subtotal_sin_iva = $producto->precio_base * $l->cantidad;
            $l->subtotal_con_iva = $l->subtotal_sin_iva * (1 + ($producto->iva / 100));
            $l->observaciones = $item['observaciones'] ?? null;
            
            $totalSinIva += $l->subtotal_sin_iva;
            $totalConIva += $l->subtotal_con_iva;
            
            $lineas[] = $l;
        }
        
        if (empty($lineas)) return false;
        
        // Crear pedido
        $pedido = new PedidoDTO();
        $pedido->numero_pedido = $this->dao->obtenerSiguienteNumero();
        $pedido->cliente_id = $clienteId;
        $pedido->tipo = $tipo;
        $pedido->estado = 'recibido';
        $pedido->total_sin_iva = round($totalSinIva, 2);
        $pedido->total_con_iva = round($totalConIva, 2);
        $pedido->observaciones = $observaciones;
        
        $pedido = $this->dao->crear($pedido);
        
        if ($pedido) {
            $this->dao->guardarLineas($pedido->id, $lineas);
            $pedido->lineas = $lineas;
            return $pedido;
        }
        return false;
    }

    /** Confirma pago online (recibido → en_preparacion) */
    public function pagarPedido($pedidoId) {
        return $this->dao->cambiarEstado($pedidoId, 'en_preparacion');
    }

    /** Camarero cobra pedido (recibido → en_preparacion) */
    public function cobrarPedido($pedidoId, $camareroId) {
        return $this->dao->cambiarEstado($pedidoId, 'en_preparacion', $camareroId);
    }

    /** Cocinero toma pedido (en_preparacion → cocinando) */
    public function tomarPedido($pedidoId, $cocineroId) {
        return $this->dao->cambiarEstado($pedidoId, 'cocinando', $cocineroId);
    }

    /** Cocinero completa pedido (cocinando → listo_cocina) */
    public function completarCocina($pedidoId, $cocineroId) {
        return $this->dao->cambiarEstado($pedidoId, 'listo_cocina', $cocineroId);
    }

    /** Camarero prepara entrega (listo_cocina → terminado) */
    public function prepararEntrega($pedidoId, $camareroId) {
        return $this->dao->cambiarEstado($pedidoId, 'terminado', $camareroId);
    }

    /** Camarero entrega (terminado → entregado) */
    public function entregarPedido($pedidoId, $camareroId) {
        return $this->dao->cambiarEstado($pedidoId, 'entregado', $camareroId);
    }

    /** Cancela un pedido (solo si nuevo o recibido) */
    public function cancelarPedido($pedidoId) {
        $pedido = $this->dao->buscarPorId($pedidoId);
        if ($pedido && in_array($pedido->estado, ['nuevo', 'recibido'])) {
            return $this->dao->cambiarEstado($pedidoId, 'cancelado');
        }
        return false;
    }

    public function getPedido($id) {
        return $this->dao->buscarPorId($id);
    }

    public function getPedidosCliente($clienteId) {
        return $this->dao->listarPorCliente($clienteId);
    }

    public function getPedidosPorEstados(array $estados) {
        return $this->dao->listarPorEstados($estados);
    }
}

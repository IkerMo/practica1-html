<?php
namespace es\ucm\fdi\aw\Pedido;

use es\ucm\fdi\aw\Producto\ProductoDAO;

class PedidoAppService {
    private $dao;

    public function __construct() {
        $this->dao = new PedidoDAO();
    }

    /** Crea un pedido completo desde el carrito de la sesión y aplica ofertas opcionales */
    public function crearPedidoDesdeCarrito($clienteId, $tipo, $items, $ofertaIds = [], $observaciones = '') {
        $productoDAO = new ProductoDAO();
        $ofertaService = new \es\ucm\fdi\aw\Oferta\OfertaAppService();

        // Cantidades iniciales por producto (cart)
        $cantidadesDisponibles = [];
        foreach ($items as $item) {
            $pid = (int)$item['producto_id'];
            $cantidad = max(0, (int)$item['cantidad']);
            if ($cantidad <= 0) continue;
            $cantidadesDisponibles[$pid] = ($cantidadesDisponibles[$pid] ?? 0) + $cantidad;
        }

        $lineas = [];
        $totalSinIva = 0;
        $totalConIva = 0;
        $totalSinDesc = 0;
        $totalDescuento = 0;

        // Aplicamos ofertas seleccionadas (una por producto como restriccion de uso)
        foreach ($ofertaIds as $ofertaId) {
            $oferta = $ofertaService->getOferta((int)$ofertaId);
            if (!$oferta || !$oferta->estaActiva()) {
                continue;
            }

            // determinar maxVeces aplicable con cantidades disponibles
            $maxVeces = PHP_INT_MAX;
            foreach ($oferta->productos as $pid => $cantRequerida) {
                $dispo = $cantidadesDisponibles[$pid] ?? 0;
                if ($cantRequerida <= 0 || $dispo <= 0) {
                    $maxVeces = 0;
                    break;
                }
                $maxVeces = min($maxVeces, (int)floor($dispo / $cantRequerida));
            }
            if ($maxVeces <= 0) continue;

            $impacto = $ofertaService->calcularImpactoOferta($oferta);
            $packTotal = $impacto['total_sin_descuento'];
            $packDescuento = $impacto['descuento'];
            $packTotalConDesc = $impacto['total_con_descuento'];

            for ($veces = 0; $veces < $maxVeces; $veces++) {
                $lineasPaquete = [];
                $packSubtotalConIva = 0;
                $packDescSubtotal = 0;

                foreach ($oferta->productos as $pid => $cantRequerida) {
                    $producto = $productoDAO->buscarPorId($pid);
                    if (!$producto || !$producto->disponible) {
                        // deveria haber sido uncentin 
                        continue;
                    }

                    $cantidad = $cantRequerida;
                    $linea = new LineaPedidoDTO();
                    $linea->producto_id = $producto->id;
                    $linea->cantidad = $cantidad;
                    $linea->precio_unitario_sin_iva = $producto->precio_base;
                    $linea->iva = $producto->iva;
                    $linea->subtotal_sin_iva = $producto->precio_base * $cantidad;
                    $linea->subtotal_con_iva = round($linea->subtotal_sin_iva * (1 + ($producto->iva / 100)), 2);
                    $linea->oferta_id = $oferta->id;
                    $linea->subtotal_descuento = 0; // se asignara despues
                    $linea->observaciones = 'Oferta ' . $oferta->nombre;
                    $linea->estado_cocina = $producto->requiere_cocina ? 'pendiente' : 'no_requiere_cocina';

                    $lineasPaquete[] = $linea;
                    $packSubtotalConIva += $linea->subtotal_con_iva;

                    // restar cantidad disponible
                    $cantidadesDisponibles[$pid] -= $cantidad;
                }

                // Asignar descuentos proporcionales en el paquete
                $descuentoMaximo = round($packSubtotalConIva - ($packTotalConDesc), 2);
                if ($descuentoMaximo < 0) {
                    $descuentoMaximo = 0;
                }

                $restoAsignado = 0;
                foreach ($lineasPaquete as $i => $linea) {
                    if ($packSubtotalConIva > 0) {
                        $lineasPaquete[$i]->subtotal_descuento = round(($linea->subtotal_con_iva / $packSubtotalConIva) * $descuentoMaximo, 2);
                    } else {
                        $lineasPaquete[$i]->subtotal_descuento = 0;
                    }
                    $restoAsignado += $lineasPaquete[$i]->subtotal_descuento;
                }
                // Ajustar por redondeo en última línea
                if (!empty($lineasPaquete)) {
                    $diff = round($descuentoMaximo - $restoAsignado, 2);
                    $lineasPaquete[count($lineasPaquete)-1]->subtotal_descuento += $diff;
                }

                foreach ($lineasPaquete as $linea) {
                    $lineas[] = $linea;
                    $totalSinIva += $linea->subtotal_sin_iva;
                    $totalConIva += $linea->subtotal_con_iva;
                    $totalSinDesc += $linea->subtotal_con_iva;
                    $totalDescuento += $linea->subtotal_descuento;
                }
            }
        }

        // Lineas restantes (no aplicadas a ofertas)
        foreach ($items as $item) {
            $producto = $productoDAO->buscarPorId($item['producto_id']);
            if (!$producto || !$producto->disponible) continue;
            $pid = (int)$item['producto_id'];
            $cantidad = max(0, (int)$item['cantidad']);
            $restante = $cantidadesDisponibles[$pid] ?? 0;
            if ($restante <= 0) continue;

            $linea = new LineaPedidoDTO();
            $linea->producto_id = $producto->id;
            $linea->cantidad = $restante;
            $linea->precio_unitario_sin_iva = $producto->precio_base;
            $linea->iva = $producto->iva;
            $linea->subtotal_sin_iva = $producto->precio_base * $restante;
            $linea->subtotal_con_iva = round($linea->subtotal_sin_iva * (1 + ($producto->iva / 100)), 2);
            $linea->oferta_id = null;
            $linea->subtotal_descuento = 0;
            $linea->observaciones = $item['observaciones'] ?? null;
            $linea->estado_cocina = $producto->requiere_cocina ? 'pendiente' : 'no_requiere_cocina';

            $lineas[] = $linea;
            $totalSinIva += $linea->subtotal_sin_iva;
            $totalConIva += $linea->subtotal_con_iva;
            $totalSinDesc += $linea->subtotal_con_iva;
        }

        if (empty($lineas)) {
            return false;
        }

        $pedido = new PedidoDTO();
        $pedido->numero_pedido = $this->dao->obtenerSiguienteNumero();
        $pedido->cliente_id = $clienteId;
        $pedido->tipo = $tipo;
        $pedido->estado = 'recibido';
        $pedido->total_sin_iva = round($totalSinIva, 2);
        $pedido->total_con_iva = round(max(0, $totalConIva - $totalDescuento), 2);
        $pedido->total_sin_descuento = round($totalSinDesc, 2);
        $pedido->total_descuento = round($totalDescuento, 2);
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
         $ok = $this->dao->cambiarEstado($pedidoId, 'en_preparacion');
        if ($ok) {
            $this->añadirBistroCoins($pedidoId); 
            $this->completarAutomaticamenteSiNoHayCocina($pedidoId);
        }
        return $ok;
    }

    /** Camarero cobra pedido (recibido → en_preparacion) */
    public function cobrarPedido($pedidoId, $camareroId) {
        $ok = $this->dao->cambiarEstado($pedidoId, 'en_preparacion', $camareroId);
        if ($ok) {
            $this->añadirBistroCoins($pedidoId); 
            $this->completarAutomaticamenteSiNoHayCocina($pedidoId);
        }
        return $ok; 
    }

    /** Cocinero toma pedido (en_preparacion → cocinando) */
    public function tomarPedido($pedidoId, $cocineroId) {
        if (!$this->dao->tieneLineasPendientesCocina($pedidoId)) {
            return $this->dao->cambiarEstado($pedidoId, 'listo_cocina', $cocineroId);
        }
        return $this->dao->cambiarEstado($pedidoId, 'cocinando', $cocineroId);
    }

    /** Cocinero completa pedido (cocinando → listo_cocina) */
    public function completarCocina($pedidoId, $cocineroId) {
        return $this->dao->cambiarEstado($pedidoId, 'listo_cocina', $cocineroId);
    }

    /** Cocinero marca una linea concreta como lista */
    public function marcarLineaListaCocina($lineaId, $cocineroId) {
        $pedidoId = $this->dao->buscarPedidoIdPorLinea($lineaId);
        if (!$pedidoId) {
            return false;
        }

        $ok = $this->dao->marcarLineaListaCocina($lineaId, $cocineroId);
        if ($ok && !$this->dao->tieneLineasPendientesCocina($pedidoId)) {
            $this->completarCocina($pedidoId, $cocineroId);
        }
        return $ok;
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

    public function getPedidosActivos() {
        return $this->dao->listarPorEstados(['recibido', 'en_preparacion', 'cocinando', 'listo_cocina', 'terminado']);
    }

    private function completarAutomaticamenteSiNoHayCocina($pedidoId) {
        if (!$this->dao->tieneLineasPendientesCocina($pedidoId)) {
            $this->dao->cambiarEstado($pedidoId, 'listo_cocina');
        }
    }

        /**
     * Añade BistroCoins al cliente después de pagar un pedido
     * (1 BistroCoin por cada euro gastado, redondeado hacia abajo)
     */
    private function añadirBistroCoins($pedidoId) {
        $pedido = $this->dao->buscarPorId($pedidoId);
        if (!$pedido) return false;
        
        // Calcular BistroCoins (1 por cada euro redondeado hacia abajo)
        $bistroCoins = floor($pedido->total_con_iva);
        
        if ($bistroCoins <= 0) return true;
        
        // Obtener usuario y actualizar su saldo
        $usuario = \es\ucm\fdi\aw\Usuarios\Usuario::buscaUsuarioPorId($pedido->cliente_id);
        if (!$usuario) return false;
        
        $saldoActual = $usuario->getBistroCoins() ?? 0;
        return $usuario->actualiza(['bistro_coins' => $saldoActual + $bistroCoins]);
    }
}


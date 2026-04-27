<?php
namespace es\ucm\fdi\aw\Recompensa;
use es\ucm\fdi\aw\Recompensa\RecompensaDAO;
use es\ucm\fdi\aw\Producto\ProductoAppService; // Asumiendo que existe para validar productos

class RecompensaAppService {
    private $dao;

    public function __construct() {
        $this->dao = new RecompensaDAO();
    }

    public function listarRecompensas() {
        return $this->dao->listarRecompensas();
    }

    public function obtenerRecompensaPorId($id) {
        return $this->dao->buscaPorId($id);
    }

    public function crearRecompensa($productoId, $bistrocoinsRequeridos) {
        // Validar que el producto existe
        $productoService = new ProductoAppService();
        $producto = $productoService->getProducto($productoId);
        if (!$producto) {
            throw new \Exception("Producto no encontrado.");
        }
        if ($bistrocoinsRequeridos <= 0) {
            throw new \Exception("Los BistroCoins requeridos deben ser positivos.");
        }
        $dto = new RecompensaDTO($productoId, $bistrocoinsRequeridos);
        return $this->dao->crear($dto);
    }

    public function actualizarRecompensa($id, $productoId, $bistrocoinsRequeridos) {
        $recompensa = $this->dao->buscaPorId($id);
        if (!$recompensa) {
            throw new \Exception("Recompensa no encontrada.");
        }
        // Validar producto
        $productoService = new ProductoAppService();
        $producto = $productoService->getProducto($productoId);
        if (!$producto) {
            throw new \Exception("Producto no encontrado.");
        }
        if ($bistrocoinsRequeridos <= 0) {
            throw new \Exception("Los BistroCoins requeridos deben ser positivos.");
        }
        $recompensa->producto_id = $productoId;
        $recompensa->bistrocoins_requeridos = $bistrocoinsRequeridos;
        return $this->dao->actualizar($recompensa);
    }

    public function eliminarRecompensa($id) {
        $recompensa = $this->dao->buscaPorId($id);
        if (!$recompensa) {
            throw new \Exception("Recompensa no encontrada.");
        }
        return $this->dao->eliminar($id);
    }

    // Método adicional para obtener recompensas disponibles para un cliente (basado en su saldo)
    public function obtenerRecompensasDisponibles($clienteId, $saldoBistroCoins) {
        $todas = $this->dao->listarRecompensas();
        $disponibles = [];
        foreach ($todas as $recompensa) {
            if ($saldoBistroCoins >= $recompensa->bistrocoins_requeridos) {
                $disponibles[] = $recompensa;
            }
        }
        return $disponibles;
    }
}
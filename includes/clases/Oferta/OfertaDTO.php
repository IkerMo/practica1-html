<?php

namespace es\ucm\fdi\aw\Oferta;

class OfertaDTO {
    public $id;
    public $nombre;
    public $descripcion;
    public $fecha_inicio;
    public $fecha_fin;
    public $porcentaje_descuento;
    public $activo;
    public $productos = []; // producto_id => cantidad

    public function estaActiva() {
        $hoy = date('Y-m-d');
        return $this->activo == 1 && $this->fecha_inicio <= $hoy && $hoy <= $this->fecha_fin;
    }

    public function calcularPrecioSinDescuento($productService) {
        $total = 0;
        foreach ($this->productos as $productoId => $cantidad) {
            $producto = $productService->getProducto($productoId);
            if (!$producto) continue;
            $total += $producto->getPrecioFinal() * $cantidad;
        }
        return round($total, 2);
    }

    public function calcularDescuento() {
        return round($this->calcularPrecioSinDescuento(new \es\ucm\fdi\aw\Producto\ProductoAppService()) * ($this->porcentaje_descuento / 100), 2);
    }
}


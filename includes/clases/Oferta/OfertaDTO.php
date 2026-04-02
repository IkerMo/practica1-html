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
        return $this->activo && $this->fecha_inicio <= $hoy && $hoy <= $this->fecha_fin;
    }
}

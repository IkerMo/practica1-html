<?php
namespace es\ucm\fdi\aw\Recompensa;

class RecompensaDTO {
    public $id;
    public $producto_id;
    public $bistrocoins_requeridos;

    public function __construct($producto_id, $bistrocoins_requeridos, $id = null) {
        $this->id = $id;
        $this->producto_id = $producto_id;
        $this->bistrocoins_requeridos = $bistrocoins_requeridos;
    }
}
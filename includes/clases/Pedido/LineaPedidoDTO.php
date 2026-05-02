<?php
namespace es\ucm\fdi\aw\Pedido;

class LineaPedidoDTO {
    public $id;
    public $pedido_id;
    public $producto_id;
    public $cantidad;
    public $precio_unitario_sin_iva;
    public $iva;
    public $subtotal_sin_iva;
    public $subtotal_con_iva;
    public $oferta_id;
    public $subtotal_descuento;
    public $observaciones;
    public $nombre_producto;   // Para display
    public $estado_cocina;     // pendiente, listo_cocina, no_requiere_cocina
    public $requiere_cocina;   // Para display
    public $cocinero_id;
    public $nombre_cocinero;   // Para display
    public $fecha_listo_cocina;
}

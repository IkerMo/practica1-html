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
    public $observaciones;
    public $nombre_producto;   // Para display
}

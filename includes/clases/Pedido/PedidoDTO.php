<?php
namespace es\ucm\fdi\aw\Pedido;

class PedidoDTO {
    public $id;
    public $numero_pedido;
    public $cliente_id;
    public $tipo;              // 'local' o 'llevar'
    public $estado;            // nuevo, recibido, en_preparacion, cocinando, listo_cocina, terminado, entregado, cancelado
    public $fecha_creacion;
    public $fecha_confirmacion;
    public $fecha_pago;
    public $fecha_entrega;
    public $cocinero_id;
    public $camarero_id;
    public $total_sin_iva;
    public $total_con_iva;
    public $observaciones;
    public $lineas = [];       // Array de LineaPedidoDTO
    public $nombre_cliente;    // Para display
}

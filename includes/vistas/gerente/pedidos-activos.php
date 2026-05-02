<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Pedido\PedidoAppService;

if (!estaLogueado() || !($_SESSION['esAdmin'] ?? false)) {
    header('Location: ' . RUTA_RAIZ . 'inicio.php');
    exit();
}

$service = new PedidoAppService();
$pedidos = $service->getPedidosActivos();
$tituloPagina = 'Pedidos Activos';

$estadoLabels = [
    'recibido' => 'Recibido',
    'en_preparacion' => 'En preparacion',
    'cocinando' => 'Cocinando',
    'listo_cocina' => 'Listo cocina',
    'terminado' => 'Terminado',
];

function estadoLineaGerente($linea) {
    if ($linea->estado_cocina === 'no_requiere_cocina') {
        return 'No cocina';
    }
    if ($linea->estado_cocina === 'listo_cocina') {
        return 'Listo cocina';
    }
    return 'Pendiente';
}

$pedidosHtml = '';
if (empty($pedidos)) {
    $pedidosHtml = '<p>No hay pedidos activos.</p>';
} else {
    foreach ($pedidos as $p) {
        $fecha = date('d/m/Y H:i', strtotime($p->fecha_creacion));
        $total = number_format($p->total_con_iva, 2);
        $tipo = $p->tipo === 'local' ? 'Local' : 'Llevar';
        $estado = $estadoLabels[$p->estado] ?? $p->estado;
        $cocineroPedido = $p->cocinero_id ? "Cocinero #{$p->cocinero_id}" : 'Sin asignar';

        $lineasHtml = '';
        foreach ($p->lineas as $l) {
            $estadoLinea = estadoLineaGerente($l);
            $cocineroLinea = $l->nombre_cocinero ?: ($l->cocinero_id ? "Cocinero #{$l->cocinero_id}" : $cocineroPedido);
            $lineasHtml .= "<tr><td>{$l->cantidad}x {$l->nombre_producto}</td><td>{$estadoLinea}</td><td>{$cocineroLinea}</td></tr>";
        }

        $pedidosHtml .= <<<HTML
        <div class="pedido-card">
            <div class="pedido-card-header">
                <strong class="pedido-card-numero color-maroon">#{$p->numero_pedido}</strong>
                <span class="status-badge estado-{$p->estado}">{$estado}</span>
            </div>
            <div class="pedido-card-info-extra">
                Cliente: {$p->nombre_cliente} | {$tipo} | {$fecha} | {$total} &euro; | {$cocineroPedido}
            </div>
            <table class="tabla-pedidos mt-10">
                <thead>
                    <tr><th>Producto</th><th>Estado cocina</th><th>Cocinero</th></tr>
                </thead>
                <tbody>
                    {$lineasHtml}
                </tbody>
            </table>
        </div>
HTML;
    }
}

$contenidoPrincipal = <<<HTML
<div class="pedidos-dashboard-header">
    <h1>Pedidos activos</h1>
</div>

<div class="lista-pedidos">
    {$pedidosHtml}
</div>
HTML;

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

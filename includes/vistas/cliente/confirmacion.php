<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Pedido\PedidoAppService;

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

$pedidoId = (int)($_GET['id'] ?? 0);
$service = new PedidoAppService();
$pedido = $service->getPedido($pedidoId);

if (!$pedido || $pedido->cliente_id != $_SESSION['idUsuario']) {
    header('Location: ' . RUTA_RAIZ . 'inicio.php');
    exit();
}

$tituloPagina = 'Pedido Confirmado';

$estadoLabels = [
    'recibido' => '⏳ Pendiente de pago (espera al camarero)',
    'en_preparacion' => '✅ Pagado - Esperando cocinero',
    'cocinando' => '🔥 En preparación',
    'listo_cocina' => '🍽️ Listo en cocina',
    'terminado' => '📦 Listo para recoger',
    'entregado' => '✔️ Entregado',
];

$estadoActual = $estadoLabels[$pedido->estado] ?? $pedido->estado;
$tipoLabel = $pedido->tipo === 'local' ? '🍽️ Para Local' : '🥡 Para Llevar';
$total = number_format($pedido->total_con_iva, 2);
$total_sin_descuento = number_format($pedido->total_sin_descuento, 2);
$descuento = number_format($pedido->total_descuento, 2);

$rutaRaiz = RUTA_RAIZ;

// Líneas del pedido
$lineasHtml = '';
foreach ($pedido->lineas as $l) {
    $subtotal = number_format($l->subtotal_con_iva, 2);
    $lineasHtml .= "<tr><td>{$l->nombre_producto}</td><td style='text-align:center;'>{$l->cantidad}</td><td style='text-align:right;'>{$subtotal} €</td></tr>";
}

if ($pedido->total_descuento > 0) {
    $tfootHtml = <<<HTML
                <tr>
                    <td colspan="2" style="padding:5px;">Subtotal</td>
                    <td style="padding:5px;text-align:right;">{$total_sin_descuento} €</td>
                </tr>
                <tr style="color:#8b0000;">
                    <td colspan="2" style="padding:5px;">Descuento</td>
                    <td style="padding:5px;text-align:right;">-{$descuento} €</td>
                </tr>
                <tr style="font-weight:bold;border-top:2px solid #333;">
                    <td colspan="2" style="padding:10px;">TOTAL</td>
                    <td style="padding:10px;text-align:right;">{$total} €</td>
                </tr>
HTML;
} else {
    $tfootHtml = <<<HTML
                <tr style="font-weight:bold;border-top:2px solid #333;">
                    <td colspan="2" style="padding:10px;">TOTAL</td>
                    <td style="padding:10px;text-align:right;">{$total} €</td>
                </tr>
HTML;
}

$contenidoPrincipal = <<<HTML
<div style="text-align:center;margin-top:30px;">
    <div style="font-size:4em;">✅</div>
    <h1>¡Pedido Confirmado!</h1>
    
    <div style="background:white;padding:30px;border-radius:10px;border:1px solid #ddd;max-width:500px;margin:20px auto;">
        <div style="font-size:3em;color:#8b0000;font-weight:bold;">Nº {$pedido->numero_pedido}</div>
        <p style="color:#888;">Este es tu número de pedido de hoy</p>
        
        <div style="background:#f9f9f9;padding:15px;border-radius:5px;margin:15px 0;">
            <p style="margin:0;"><strong>Estado:</strong> {$estadoActual}</p>
            <p style="margin:5px 0 0;"><strong>Tipo:</strong> {$tipoLabel}</p>
        </div>
        
        <table style="width:100%;border-collapse:collapse;margin:15px 0;">
            <thead>
                <tr style="border-bottom:2px solid #8b0000;">
                    <th style="padding:8px;text-align:left;">Producto</th>
                    <th style="padding:8px;text-align:center;">Cant.</th>
                    <th style="padding:8px;text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                {$lineasHtml}
            </tbody>
            <tfoot>
{$tfootHtml}
            </tfoot>
        </table>
    </div>
    
    <a href="{$rutaRaiz}inicio.php" style="background:#8b0000;color:white;padding:12px 30px;border-radius:5px;text-decoration:none;font-size:1.1em;display:inline-block;margin-top:15px;">
        Volver al Inicio
    </a>
    
    <div style="margin-top:10px;">
        <a href="mis-pedidos.php" style="color:#8b0000;text-decoration:none;">Ver todos mis pedidos →</a>
    </div>
</div>
HTML;

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

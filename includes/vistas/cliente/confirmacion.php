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
    $lineasHtml .= "<tr><td>{$l->nombre_producto}</td><td class='text-center'>{$l->cantidad}</td><td class='text-right'>{$subtotal} €</td></tr>";
}

if ($pedido->total_descuento > 0) {
    $tfootHtml = <<<HTML
                <tr>
                    <td colspan="2" class="p-5">Subtotal</td>
                    <td class="p-5 text-right">{$total_sin_descuento} €</td>
                </tr>
                <tr class="color-maroon">
                    <td colspan="2" class="p-5">Descuento</td>
                    <td class="p-5 text-right">-{$descuento} €</td>
                </tr>
                <tr class="font-bold border-top">
                    <td colspan="2" class="p-10">TOTAL</td>
                    <td class="p-10 text-right">{$total} €</td>
                </tr>
HTML;
} else {
    $tfootHtml = <<<HTML
                <tr class="font-bold border-top">
                    <td colspan="2" class="p-10">TOTAL</td>
                    <td class="p-10 text-right">{$total} €</td>
                </tr>
HTML;
}

$contenidoPrincipal = <<<HTML
<div class="text-center mt-30">
    <div class="font-xl">✅</div>
    <h1>¡Pedido Confirmado!</h1>
    
    <div class="bg-white p-30 rounded-8 border-light max-w-500 mx-auto mt-20 mb-20">
        <div class="font-xl color-maroon font-bold">Nº {$pedido->numero_pedido}</div>
        <p class="color-gray">Este es tu número de pedido de hoy</p>
        
        <div class="bg-light p-15 rounded-5 mb-15 mt-15">
            <p class="mb-0"><strong>Estado:</strong> {$estadoActual}</p>
            <p class="mt-5 mb-0"><strong>Tipo:</strong> {$tipoLabel}</p>
        </div>
        
        <table class="tabla-pedidos mb-15 mt-15">
            <thead>
                <tr class="border-bottom-bold">
                    <th class="p-8 text-left">Producto</th>
                    <th class="p-8 text-center">Cant.</th>
                    <th class="p-8 text-right">Subtotal</th>
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
    
    <a href="{$rutaRaiz}inicio.php" class="btn-checkout mt-15">
        Volver al Inicio
    </a>
    
    <div class="mt-10">
        <a href="mis-pedidos.php" class="color-maroon no-decoration">Ver todos mis pedidos →</a>
    </div>
</div>
HTML;

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Pedido\PedidoAppService;

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

// Procesar cancelación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_pedido'])) {
    $service = new PedidoAppService();
    $service->cancelarPedido((int)$_POST['cancelar_pedido']);
    header('Location: mis-pedidos.php');
    exit();
}

$service = new PedidoAppService();
$clienteId = $_SESSION['idUsuario'];
$pedidos = $service->getPedidosCliente($clienteId);

$tituloPagina = 'Mis Pedidos';

$estadoLabels = [
    'nuevo' => '🆕 Nuevo',
    'recibido' => '⏳ Pendiente de pago',
    'en_preparacion' => '🔄 En preparación',
    'cocinando' => '🔥 Cocinando',
    'listo_cocina' => '🍽️ Listo cocina',
    'terminado' => '📦 Listo para recoger',
    'entregado' => '✔️ Entregado',
    'cancelado' => '❌ Cancelado',
];

$estadoClases = [
    'nuevo' => 'estado-nuevo',
    'recibido' => 'estado-recibido',
    'en_preparacion' => 'estado-preparacion',
    'cocinando' => 'estado-cocinando',
    'listo_cocina' => 'estado-listo-cocina',
    'terminado' => 'estado-terminado',
    'entregado' => 'estado-entregado',
    'cancelado' => 'estado-cancelado',
];

if (empty($pedidos)) {
    $contenidoPrincipal = <<<HTML
    <h1>Mis Pedidos</h1>
    <div class="text-center" style="padding: 50px 0;">
        <div class="font-xl">📦</div>
        <p class="font-lg color-gray">No tienes pedidos todavía</p>
        <a href="nuevo-pedido.php" class="btn-pedido btn-primary" style="text-decoration:none;">Hacer un pedido</a>
    </div>
HTML;
} else {
    $pedidosHtml = '';
    foreach ($pedidos as $p) {
        $estado = $estadoLabels[$p->estado] ?? $p->estado;
        $claseEstado = $estadoClases[$p->estado] ?? '';
        $fecha = date('d/m/Y H:i', strtotime($p->fecha_creacion));
        $total = number_format($p->total_con_iva, 2);
        $tipo = $p->tipo === 'local' ? '🍽️ Local' : '🥡 Llevar';
        
        $accionesHtml = '';
        if (in_array($p->estado, ['nuevo', 'recibido'])) {
            $accionesHtml = <<<HTML
            <form method="POST" class="inline">
                <input type="hidden" name="cancelar_pedido" value="{$p->id}">
                <button type="submit" class="btn-pedido btn-danger" style="padding: 5px 10px; font-size: 0.85em;" onclick="return confirm('¿Cancelar este pedido?')">Cancelar</button>
            </form>
HTML;
        }
        
        $pedidosHtml .= <<<HTML
        <div class="pedido-lista-card {$claseEstado}">
            <div class="flex-between">
                <div>
                    <strong class="font-lg">Pedido #{$p->numero_pedido}</strong>
                    <span class="status-badge {$claseEstado}" style="margin-left: 10px;">{$estado}</span>
                    <span class="icon-small color-gray" style="margin-left: 10px;">{$tipo}</span>
                </div>
                <div class="text-right">
                    <strong class="font-bold">{$total} €</strong>
                    <div class="icon-small color-gray">{$fecha}</div>
                </div>
            </div>
            <div class="mt-8">
                {$accionesHtml}
            </div>
        </div>
HTML;
    }

    $contenidoPrincipal = <<<HTML
    <h1>Mis Pedidos</h1>
    {$pedidosHtml}
HTML;
}

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

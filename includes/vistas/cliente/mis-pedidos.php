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

$estadoColores = [
    'nuevo' => '#3498db',
    'recibido' => '#f39c12',
    'en_preparacion' => '#e67e22',
    'cocinando' => '#e74c3c',
    'listo_cocina' => '#2ecc71',
    'terminado' => '#27ae60',
    'entregado' => '#95a5a6',
    'cancelado' => '#bdc3c7',
];

if (empty($pedidos)) {
    $contenidoPrincipal = <<<HTML
    <h1>Mis Pedidos</h1>
    <div style="text-align:center;padding:50px 0;">
        <div style="font-size:4em;">📦</div>
        <p style="font-size:1.2em;color:#888;">No tienes pedidos todavía</p>
        <a href="nuevo-pedido.php" style="background:#8b0000;color:white;padding:10px 25px;border-radius:5px;text-decoration:none;">Hacer un pedido</a>
    </div>
HTML;
} else {
    $pedidosHtml = '';
    foreach ($pedidos as $p) {
        $estado = $estadoLabels[$p->estado] ?? $p->estado;
        $color = $estadoColores[$p->estado] ?? '#888';
        $fecha = date('d/m/Y H:i', strtotime($p->fecha_creacion));
        $total = number_format($p->total_con_iva, 2);
        $tipo = $p->tipo === 'local' ? '🍽️ Local' : '🥡 Llevar';
        
        $accionesHtml = '';
        if (in_array($p->estado, ['nuevo', 'recibido'])) {
            $accionesHtml = <<<HTML
            <form method="POST" style="display:inline;">
                <input type="hidden" name="cancelar_pedido" value="{$p->id}">
                <button type="submit" style="background:#e74c3c;color:white;border:none;padding:5px 10px;border-radius:3px;cursor:pointer;font-size:0.85em;" onclick="return confirm('¿Cancelar este pedido?')">Cancelar</button>
            </form>
HTML;
        }
        
        $pedidosHtml .= <<<HTML
        <div style="background:white;padding:15px;border-radius:8px;border:1px solid #ddd;border-left:4px solid {$color};margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <strong style="font-size:1.2em;">Pedido #{$p->numero_pedido}</strong>
                    <span style="margin-left:10px;background:{$color};color:white;padding:3px 10px;border-radius:10px;font-size:0.8em;">{$estado}</span>
                    <span style="margin-left:10px;font-size:0.85em;color:#888;">{$tipo}</span>
                </div>
                <div style="text-align:right;">
                    <strong>{$total} €</strong>
                    <div style="font-size:0.8em;color:#888;">{$fecha}</div>
                </div>
            </div>
            <div style="margin-top:8px;">
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

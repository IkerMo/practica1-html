<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Pedido\PedidoAppService;

if (!estaLogueado() || !($_SESSION['esCocinero'] ?? false) && !($_SESSION['esAdmin'] ?? false)) {
    header('Location: ' . RUTA_RAIZ . 'inicio.php');
    exit();
}

$service = new PedidoAppService();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedidoId = (int)($_POST['pedido_id'] ?? 0);
    $accion = $_POST['accion'] ?? '';
    $cocineroId = $_SESSION['idUsuario'];
    
    switch ($accion) {
        case 'tomar':
            $service->tomarPedido($pedidoId, $cocineroId);
            break;
        case 'listo':
            $service->completarCocina($pedidoId, $cocineroId);
            break;
    }
    header('Location: pedidos.php');
    exit();
}

$enEspera = $service->getPedidosPorEstados(['en_preparacion']);
$cocinando = $service->getPedidosPorEstados(['cocinando']);

$tituloPagina = 'Panel Cocinero';
$nombreCocinero = $_SESSION['nombre'] ?? 'Cocinero';

// Función para tarjeta de pedido
function generarTarjetaCocina($pedido, $accion, $botonTexto, $botonColor) {
    $tipo = $pedido->tipo === 'local' ? '🍽️' : '🥡';
    $hora = date('H:i', strtotime($pedido->fecha_creacion));
    
    $lineasHtml = '';
    foreach ($pedido->lineas as $l) {
        $lineasHtml .= "<div style='padding:4px 0;border-bottom:1px solid #eee;'><strong>{$l->cantidad}x</strong> {$l->nombre_producto}</div>";
    }
    
    return <<<HTML
    <div style="background:white;padding:20px;border-radius:10px;border:1px solid #ddd;min-width:280px;max-width:350px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <strong style="font-size:2em;color:#8b0000;">#{$pedido->numero_pedido}</strong>
            <span style="font-size:0.9em;">{$tipo} {$hora}</span>
        </div>
        <div style="background:#f9f9f9;padding:12px;border-radius:5px;margin-bottom:12px;">
            {$lineasHtml}
        </div>
        <form method="POST">
            <input type="hidden" name="pedido_id" value="{$pedido->id}">
            <input type="hidden" name="accion" value="{$accion}">
            <button type="submit" style="width:100%;background:{$botonColor};color:white;border:none;padding:12px;border-radius:5px;cursor:pointer;font-size:1.1em;">
                {$botonTexto}
            </button>
        </form>
    </div>
HTML;
}

$esperaHtml = '';
foreach ($enEspera as $p) {
    $esperaHtml .= generarTarjetaCocina($p, 'tomar', '🍳 Empezar a Cocinar', '#e67e22');
}

$cocinandoHtml = '';
foreach ($cocinando as $p) {
    $cocinandoHtml .= generarTarjetaCocina($p, 'listo', '✅ Marcar Listo', '#2ecc71');
}

$numEspera = count($enEspera);
$numCocinando = count($cocinando);

$contenidoPrincipal = <<<HTML
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h1 style="margin:0;">Panel Cocinero</h1>
    <div style="display:flex;align-items:center;gap:10px;background:#f0e9e2;padding:8px 15px;border-radius:20px;">
        <span style="font-size:1.5em;">👨‍🍳</span>
        <strong>{$nombreCocinero}</strong>
    </div>
</div>

<h2 style="color:#e67e22;">⏳ En Cola ({$numEspera})</h2>
<div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:30px;">
    {$esperaHtml}
</div>

<h2 style="color:#e74c3c;">🔥 Cocinando ({$numCocinando})</h2>
<div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:30px;">
    {$cocinandoHtml}
</div>
HTML;

if (empty($esperaHtml) && empty($cocinandoHtml)) {
    $contenidoPrincipal .= '<p style="text-align:center;color:#888;font-size:1.2em;margin-top:30px;">No hay pedidos pendientes en cocina 🎉</p>';
}

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

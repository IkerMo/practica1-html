<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Pedido\PedidoAppService;

if (!estaLogueado() || !($_SESSION['esCamarero'] ?? false) && !($_SESSION['esAdmin'] ?? false)) {
    header('Location: ' . RUTA_RAIZ . 'inicio.php');
    exit();
}

$service = new PedidoAppService();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedidoId = (int)($_POST['pedido_id'] ?? 0);
    $accion = $_POST['accion'] ?? '';
    $camareroId = $_SESSION['idUsuario'];
    
    switch ($accion) {
        case 'cobrar':
            $service->cobrarPedido($pedidoId, $camareroId);
            break;
        case 'preparar_entrega':
            $service->prepararEntrega($pedidoId, $camareroId);
            break;
        case 'entregar':
            $service->entregarPedido($pedidoId, $camareroId);
            break;
    }
    header('Location: pedidos-pendientes.php');
    exit();
}

// Obtener pedidos relevantes para el camarero
$recibidos = $service->getPedidosPorEstados(['recibido']);
$listosCocina = $service->getPedidosPorEstados(['listo_cocina']);
$terminados = $service->getPedidosPorEstados(['terminado']);

$rutaImgs = RUTA_IMGS;
$avatarCamarero = $_SESSION['nombre'] ?? 'Camarero';
$tituloPagina = 'Panel Camarero';

// Función auxiliar para generar tarjetas de pedido
function generarTarjetaPedido($pedido, $accion, $botonTexto, $botonColor) {
    $tipo = $pedido->tipo === 'local' ? '🍽️' : '🥡';
    $total = number_format($pedido->total_con_iva, 2);
    $hora = date('H:i', strtotime($pedido->fecha_creacion));
    
    // Líneas del pedido
    $lineasHtml = '';
    foreach ($pedido->lineas as $l) {
        $lineasHtml .= "<div>{$l->cantidad}x {$l->nombre_producto}</div>";
    }
    
    return <<<HTML
    <div style="background:white;padding:15px;border-radius:10px;border:1px solid #ddd;min-width:250px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <strong style="font-size:1.5em;">#{$pedido->numero_pedido}</strong>
            <span>{$tipo} {$hora}</span>
        </div>
        <div style="font-size:0.85em;color:#666;margin-bottom:8px;">
            Cliente: {$pedido->nombre_cliente}
        </div>
        <div style="background:#f9f9f9;padding:10px;border-radius:5px;margin-bottom:10px;font-size:0.9em;">
            {$lineasHtml}
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <strong>{$total} €</strong>
            <form method="POST">
                <input type="hidden" name="pedido_id" value="{$pedido->id}">
                <input type="hidden" name="accion" value="{$accion}">
                <button type="submit" style="background:{$botonColor};color:white;border:none;padding:10px 20px;border-radius:5px;cursor:pointer;font-size:1em;">
                    {$botonTexto}
                </button>
            </form>
        </div>
    </div>
HTML;
}

// Generar HTML para cada sección
$recibidosHtml = '';
foreach ($recibidos as $p) {
    $recibidosHtml .= generarTarjetaPedido($p, 'cobrar', '💰 Cobrar', '#f39c12');
}

$listosHtml = '';
foreach ($listosCocina as $p) {
    $listosHtml .= generarTarjetaPedido($p, 'preparar_entrega', '📦 Preparar', '#3498db');
}

$terminadosHtml = '';
foreach ($terminados as $p) {
    $terminadosHtml .= generarTarjetaPedido($p, 'entregar', '✅ Entregar', '#2ecc71');
}

$numRecibidos = count($recibidos);
$numListos = count($listosCocina);
$numTerminados = count($terminados);

$contenidoPrincipal = <<<HTML
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h1 style="margin:0;">Panel Camarero</h1>
    <div style="display:flex;align-items:center;gap:10px;background:#f0e9e2;padding:8px 15px;border-radius:20px;">
        <span style="font-size:1.5em;">👤</span>
        <strong>{$avatarCamarero}</strong>
    </div>
</div>

<h2 style="color:#f39c12;">💰 Pendientes de Cobro ({$numRecibidos})</h2>
<div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:30px;">
    {$recibidosHtml}
</div>

<h2 style="color:#3498db;">📦 Listos desde Cocina ({$numListos})</h2>
<div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:30px;">
    {$listosHtml}
</div>

<h2 style="color:#2ecc71;">✅ Listos para Entregar ({$numTerminados})</h2>
<div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:30px;">
    {$terminadosHtml}
</div>
HTML;

if (empty($recibidosHtml) && empty($listosHtml) && empty($terminadosHtml)) {
    $contenidoPrincipal .= '<p style="text-align:center;color:#888;font-size:1.2em;margin-top:30px;">No hay pedidos pendientes 🎉</p>';
}

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

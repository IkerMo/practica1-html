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
function generarTarjetaPedido($pedido, $accion, $botonTexto, $botonColorClass) {
    $tipo = $pedido->tipo === 'local' ? '🍽️' : '🥡';
    $total = number_format($pedido->total_con_iva, 2);
    $hora = date('H:i', strtotime($pedido->fecha_creacion));
    
    // Líneas del pedido
    $lineasHtml = '';
    foreach ($pedido->lineas as $l) {
        $lineasHtml .= "<div>{$l->cantidad}x {$l->nombre_producto}</div>";
    }
    
    return <<<HTML
    <div class="pedido-card">
        <div class="pedido-card-header">
            <strong class="pedido-card-numero">#{$pedido->numero_pedido}</strong>
            <span>{$tipo} {$hora}</span>
        </div>
        <div class="pedido-card-info-extra">
            Cliente: {$pedido->nombre_cliente}
        </div>
        <div class="pedido-card-detalles">
            {$lineasHtml}
        </div>
        <div class="pedido-card-footer">
            <strong class="pedido-card-total">{$total} €</strong>
            <form method="POST">
                <input type="hidden" name="pedido_id" value="{$pedido->id}">
                <input type="hidden" name="accion" value="{$accion}">
                <button type="submit" class="btn-pedido {$botonColorClass}">
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
    $recibidosHtml .= generarTarjetaPedido($p, 'cobrar', '💰 Cobrar', 'btn-cobrar');
}

$listosHtml = '';
foreach ($listosCocina as $p) {
    $listosHtml .= generarTarjetaPedido($p, 'preparar_entrega', '📦 Preparar', 'btn-preparar');
}

$terminadosHtml = '';
foreach ($terminados as $p) {
    $terminadosHtml .= generarTarjetaPedido($p, 'entregar', '✅ Entregar', 'btn-entregar');
}

$numRecibidos = count($recibidos);
$numListos = count($listosCocina);
$numTerminados = count($terminados);

$contenidoPrincipal = <<<HTML
<div class="pedidos-dashboard-header">
    <h1>Panel Camarero</h1>
    <div class="usuario-info-badge">
        <span class="icon-large">👤</span>
        <strong>{$avatarCamarero}</strong>
    </div>
</div>

<h2 class="texto-pendientes">💰 Pendientes de Cobro ({$numRecibidos})</h2>
<div class="lista-pedidos">
    {$recibidosHtml}
</div>

<h2 class="texto-cocina">📦 Listos desde Cocina ({$numListos})</h2>
<div class="lista-pedidos">
    {$listosHtml}
</div>

<h2 class="texto-entregar">✅ Listos para Entregar ({$numTerminados})</h2>
<div class="lista-pedidos">
    {$terminadosHtml}
</div>
HTML;

if (empty($recibidosHtml) && empty($listosHtml) && empty($terminadosHtml)) {
    $contenidoPrincipal .= '<p class="aviso-vacio">No hay pedidos pendientes 🎉</p>';
}

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

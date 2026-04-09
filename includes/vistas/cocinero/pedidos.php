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
function generarTarjetaCocina($pedido, $accion, $botonTexto, $claseBoton) {
    $tipo = $pedido->tipo === 'local' ? '🍽️' : '🥡';
    $hora = date('H:i', strtotime($pedido->fecha_creacion));
    
    $lineasHtml = '';
    foreach ($pedido->lineas as $l) {
        $lineasHtml .= "<div class='pedido-card-detalles-linea'><strong>{$l->cantidad}x</strong> {$l->nombre_producto}</div>";
    }
    
    return <<<HTML
    <div class="pedido-card">
        <div class="pedido-card-header">
            <strong class="pedido-card-numero color-maroon">#{$pedido->numero_pedido}</strong>
            <span class="icon-small">{$tipo} {$hora}</span>
        </div>
        <div class="pedido-card-detalles">
            {$lineasHtml}
        </div>
        <form method="POST">
            <input type="hidden" name="pedido_id" value="{$pedido->id}">
            <input type="hidden" name="accion" value="{$accion}">
            <button type="submit" class="btn-pedido {$claseBoton} w-100">
                {$botonTexto}
            </button>
        </form>
    </div>
HTML;
}

$esperaHtml = '';
foreach ($enEspera as $p) {
    $esperaHtml .= generarTarjetaCocina($p, 'tomar', 'Empezar a Cocinar', 'btn-cocina-tomar');
}

$cocinandoHtml = '';
foreach ($cocinando as $p) {
    $cocinandoHtml .= generarTarjetaCocina($p, 'listo', 'Marcar Listo', 'btn-cocina-listo');
}

$numEspera = count($enEspera);
$numCocinando = count($cocinando);

$contenidoPrincipal = <<<HTML
<div class="pedidos-dashboard-header">
    <h1>Panel Cocinero</h1>
    <div class="usuario-info-badge">
        <span class="icon-large">👨‍🍳</span>
        <strong>{$nombreCocinero}</strong>
    </div>
</div>

<h2 class="texto-espera">En Cola ({$numEspera})</h2>
<div class="lista-pedidos">
    {$esperaHtml}
</div>

<h2 class="texto-preparando">Cocinando ({$numCocinando})</h2>
<div class="lista-pedidos">
    {$cocinandoHtml}
</div>
HTML;

if (empty($esperaHtml) && empty($cocinandoHtml)) {
    $contenidoPrincipal .= '<p class="text-gray-center">No hay pedidos pendientes en cocina 🎉</p>';
}

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

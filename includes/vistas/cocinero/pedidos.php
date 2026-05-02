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
    $lineaId = (int)($_POST['linea_id'] ?? 0);
    $accion = $_POST['accion'] ?? '';
    $cocineroId = $_SESSION['idUsuario'];

    switch ($accion) {
        case 'tomar':
            $service->tomarPedido($pedidoId, $cocineroId);
            break;
        case 'linea_lista':
            $service->marcarLineaListaCocina($lineaId, $cocineroId);
            break;
    }
    header('Location: pedidos.php');
    exit();
}

$enEspera = $service->getPedidosPorEstados(['en_preparacion']);
$cocinando = $service->getPedidosPorEstados(['cocinando']);

$tituloPagina = 'Panel Cocinero';
$nombreCocinero = $_SESSION['nombre'] ?? 'Cocinero';

function generarEstadoLinea($linea) {
    if ($linea->estado_cocina === 'no_requiere_cocina') {
        return '<span class="status-badge estado-entregado">No cocina</span>';
    }
    if ($linea->estado_cocina === 'listo_cocina') {
        return '<span class="status-badge estado-listo-cocina">Listo cocina</span>';
    }
    return '<span class="status-badge estado-preparacion">Pendiente</span>';
}

function generarTarjetaCocina($pedido, $accion, $botonTexto, $claseBoton, $permitirLineas = false) {
    $tipo = $pedido->tipo === 'local' ? 'Local' : 'Llevar';
    $hora = date('H:i', strtotime($pedido->fecha_creacion));

    $lineasHtml = '';
    foreach ($pedido->lineas as $l) {
        $estado = generarEstadoLinea($l);
        $accionLinea = '';

        if ($permitirLineas && $l->requiere_cocina && $l->estado_cocina === 'pendiente') {
            $accionLinea = <<<HTML
            <form method="POST" class="mt-8">
                <input type="hidden" name="pedido_id" value="{$pedido->id}">
                <input type="hidden" name="linea_id" value="{$l->id}">
                <input type="hidden" name="accion" value="linea_lista">
                <button type="submit" class="btn-pedido btn-cocina-listo btn-sm">Marcar producto listo</button>
            </form>
HTML;
        }

        $lineasHtml .= <<<HTML
        <div class="pedido-card-detalles-linea">
            <div><strong>{$l->cantidad}x</strong> {$l->nombre_producto} {$estado}</div>
            {$accionLinea}
        </div>
HTML;
    }

    $formPedido = '';
    if ($accion) {
        $formPedido = <<<HTML
        <form method="POST">
            <input type="hidden" name="pedido_id" value="{$pedido->id}">
            <input type="hidden" name="accion" value="{$accion}">
            <button type="submit" class="btn-pedido {$claseBoton} w-100">
                {$botonTexto}
            </button>
        </form>
HTML;
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
        {$formPedido}
    </div>
HTML;
}

$esperaHtml = '';
foreach ($enEspera as $p) {
    $esperaHtml .= generarTarjetaCocina($p, 'tomar', 'Empezar a cocinar', 'btn-cocina-tomar');
}

$cocinandoHtml = '';
foreach ($cocinando as $p) {
    $cocinandoHtml .= generarTarjetaCocina($p, '', '', '', true);
}

$numEspera = count($enEspera);
$numCocinando = count($cocinando);

$contenidoPrincipal = <<<HTML
<div class="pedidos-dashboard-header">
    <h1>Panel Cocinero</h1>
    <div class="usuario-info-badge">
        <strong>{$nombreCocinero}</strong>
    </div>
</div>

<h2 class="texto-espera">En cola ({$numEspera})</h2>
<div class="lista-pedidos">
    {$esperaHtml}
</div>

<h2 class="texto-preparando">Cocinando ({$numCocinando})</h2>
<div class="lista-pedidos">
    {$cocinandoHtml}
</div>
HTML;

if (empty($esperaHtml) && empty($cocinandoHtml)) {
    $contenidoPrincipal .= '<p class="text-gray-center">No hay pedidos pendientes en cocina.</p>';
}

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

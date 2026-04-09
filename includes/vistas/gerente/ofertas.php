<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Oferta\OfertaAppService;

if (!estaLogueado() || !($_SESSION['esAdmin'] ?? false)) {
    header('Location: ' . RUTA_RAIZ . 'inicio.php');
    exit();
}

$service = new OfertaAppService();
$ofertas = $service->listarOfertas(true); // Todas: actuales y pasadas

$lista = '';
if (empty($ofertas)) {
    $lista = '<p>No hay ofertas registradas.</p>';
} else {
    $lista .= '<table class="tabla-pedidos">';
    $lista .= '<thead><tr class="header-maroon"><th>Nombre</th><th>Fechas</th><th>%</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>';
    foreach ($ofertas as $o) {
        $estado = $o->estaActiva() ? 'Activa' : 'Caducada';
        $lista .= '<tr class="border-bottom">';
        $lista .= '<td>' . htmlspecialchars($o->nombre) . '</td>';
        $lista .= '<td>' . htmlspecialchars($o->fecha_inicio . ' - ' . $o->fecha_fin) . '</td>';
        $lista .= '<td>' . number_format($o->porcentaje_descuento, 2) . '%</td>';
        $lista .= '<td>' . $estado . '</td>';
        $lista .= '<td>' .
            "<a href='formularioOferta.php?id={$o->id}' class='mr-10 no-decoration color-blue'>Editar</a>" .
            "<a href='borrarOferta.php?id={$o->id}' class='no-decoration color-red' onclick='return confirm(\"¿Borrar esta oferta?\")'>Borrar</a>" .
            '</td>';
        $lista .= '</tr>';
    }
    $lista .= '</tbody></table>';
}

$tituloPagina = 'Ofertas';
$contenidoPrincipal = <<<HTML
<h1>Gestión de Ofertas</h1>
<div class="mb-20">
    <a href="formularioOferta.php" class="btn-pedido btn-nuevo-verde">+ Crear oferta</a>
</div>
{$lista}
HTML;

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

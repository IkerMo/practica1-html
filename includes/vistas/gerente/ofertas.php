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
    $lista .= '<table style="width:100%;border-collapse:collapse;background:white;">';
    $lista .= '<thead><tr style="background:#8b0000;color:white;"><th>Nombre</th><th>Fechas</th><th>%</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>';
    foreach ($ofertas as $o) {
        $estado = $o->estaActiva() ? 'Activa' : 'Caducada';
        $lista .= '<tr style="border-bottom:1px solid #ddd;">';
        $lista .= '<td>' . htmlspecialchars($o->nombre) . '</td>';
        $lista .= '<td>' . htmlspecialchars($o->fecha_inicio . ' - ' . $o->fecha_fin) . '</td>';
        $lista .= '<td>' . number_format($o->porcentaje_descuento, 2) . '%</td>';
        $lista .= '<td>' . $estado . '</td>';
        $lista .= '<td>' .
            "<a href='formularioOferta.php?id={$o->id}' style='margin-right:10px;'>Editar</a>" .
            "<a href='borrarOferta.php?id={$o->id}' onclick='return confirm(\'¿Borrar esta oferta?\')'>Borrar</a>" .
            '</td>';
        $lista .= '</tr>';
    }
    $lista .= '</tbody></table>';
}

$tituloPagina = 'Ofertas';
$contenidoPrincipal = <<<HTML
<h1>Gestión de Ofertas</h1>
<div style="margin-bottom: 20px;">
    <a href="formularioOferta.php" style="background:#2ecc71;color:white;padding:10px 16px;text-decoration:none;border-radius:4px;">+ Crear oferta</a>
</div>
{$lista}
HTML;

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

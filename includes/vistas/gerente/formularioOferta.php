<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Formularios\FormularioOferta;

if (!estaLogueado() || !($_SESSION['esAdmin'] ?? false)) {
    header('Location: ' . RUTA_RAIZ . 'inicio.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$formulario = new FormularioOferta($id);
$contenidoFormulario = $formulario->gestiona();

$tituloPagina = $id ? 'Editar Oferta' : 'Crear Oferta';
$contenidoPrincipal = <<<HTML
<h1>{$tituloPagina}</h1>
<div class="bg-white p-20 rounded-8 border-light">{$contenidoFormulario}</div>
HTML;

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

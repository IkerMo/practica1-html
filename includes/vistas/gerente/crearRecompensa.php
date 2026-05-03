<?php
require_once __DIR__ . '/../../config.php';
require_once RUTA_CLASES . '/Formularios/FormularioRecompensa.php';

if (!estaLogueado() || !esAdmin()) {
    header('Location: ' . RUTA_RAIZ . 'index.php');
    exit();
}

$form = new \es\ucm\fdi\aw\Formularios\FormularioRecompensa();
$htmlForm = $form->gestiona();

$tituloPagina = 'Crear Recompensa';
$contenidoPrincipal = "<h1>Nueva Recompensa</h1>$htmlForm";
require_once __DIR__ . '/../comun/plantilla.php';
?>
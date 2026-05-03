<?php
require_once __DIR__ . '/../../config.php';
require_once RUTA_CLASES . '/Formularios/FormularioRecompensa.php';

if (!estaLogueado() || !esAdmin()) {
    header('Location: ' . RUTA_RAIZ . 'index.php');
    exit();
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: listarRecompensas.php');
    exit();
}

$form = new \es\ucm\fdi\aw\Formularios\FormularioRecompensa($id);
$htmlForm = $form->gestiona();

$tituloPagina = 'Editar Recompensa';
$contenidoPrincipal = "<h1>Editar Recompensa</h1>$htmlForm";
require_once __DIR__ . '/../comun/plantilla.php';
?>
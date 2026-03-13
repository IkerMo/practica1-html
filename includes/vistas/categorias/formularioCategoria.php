<?php
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\Formularios\FormularioCategoria;

// SEGURIDAD: Solo Gerente
if (!$_SESSION['usuario']->tieneRol('Gerente')) {
    header('Location: listarCategorias.php');
    exit();
}

$id = $_GET['id'] ?? null;
$form = new FormularioCategoria($id);
$htmlForm = $form->gestiona();

$tituloPagina = $id ? 'Editar Categoría' : 'Nueva Categoría';
$contenidoPrincipal = "<h1>$tituloPagina</h1>" . $htmlForm;

require __DIR__.'/../plantillas/layout.php';
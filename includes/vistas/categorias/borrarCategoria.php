<?php
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\Categoria\CategoriaAppService;

if (!$_SESSION['usuario']->tieneRol('Gerente')) {
    exit("No permitido");
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $service = new CategoriaAppService();
    $service->borrarCategoria($id);
}

header('Location: listarCategorias.php');
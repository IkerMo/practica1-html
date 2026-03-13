<?php
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\Formularios\FormularioCategoria;

// SEGURIDAD: Usamos la función de config.php que es segura y no usa objetos
if (!esAdmin()) {
    header('Location: listarCategorias.php');
    exit();
}

$id = $_GET['id'] ?? null;

// Creamos el formulario. La clase FormularioCategoria debe estar en el namespace correcto
$form = new FormularioCategoria($id);
$htmlForm = $form->gestiona();

$tituloPagina = $id ? 'Editar Categoría' : 'Nueva Categoría';

// Construimos el contenido
$contenidoPrincipal = <<<HTML
    <h1>$tituloPagina</h1>
    <div class="contenedor-formulario">
        $htmlForm
    </div>
HTML;

// Usamos la ruta que te funcionó en los otros archivos
require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
<?php
require_once __DIR__.'/../../config.php';
require_once RAIZ_APP . '/includes/vistas/helpers/vistaCategorias.php'; // Cargamos el apoyo

use es\ucm\fdi\aw\Categoria\CategoriaAppService;

// 1. SEGURIDAD
if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

// 2. DATOS
$esGerente = $_SESSION['esAdmin'] ?? false;
$service = new CategoriaAppService();
$categorias = $service->getTodasCategorias();

// 3. CONSTRUCCIÓN DE LA VISTA
$tituloPagina = 'Categorías del Restaurante';
$contenidoPrincipal = "<h1>Categorías</h1>";

if ($esGerente) {
    $contenidoPrincipal .= '
        <div class="mb-20">
            <a href="formularioCategoria.php" class="btn-pedido btn-nuevo-verde">+ Nueva Categoría</a>
        </div>';
}

// Llamamos a la función de apoyo que contiene el FOREACH
$contenidoPrincipal .= renderizaListaCategorias($categorias, $esGerente);

// 4. PLANTILLA
require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
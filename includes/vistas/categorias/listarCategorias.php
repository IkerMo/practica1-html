<?php
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\Categoria\CategoriaAppService;

// 1. SEGURIDAD: Usamos la función de config.php para evitar errores de objeto
if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

// 2. RECUPERAR DATOS DE ROL (Variables simples de sesión)
$esGerente = $_SESSION['esAdmin'] ?? false;
$idRol = $_SESSION['rol'] ?? null;

$service = new CategoriaAppService();
$categorias = $service->getTodasCategorias();

$tituloPagina = 'Categorías del Restaurante';
$contenidoPrincipal = "<h1>Categorías</h1>";

// Botón para Gerentes (Rol 4)
if ($esGerente) {
    $contenidoPrincipal .= <<<HTML
        <div class="mb-20">
            <a href="formularioCategoria.php" class="btn-pedido btn-nuevo-verde">+ Nueva Categoría</a>
        </div>
HTML;
}

$contenidoPrincipal .= '<div class="categorias-grid">';

foreach ($categorias as $cat) {
    // Usamos RUTA_BASE en lugar de resuelve()
    $img = !empty($cat->imagen) ? $cat->imagen : 'default_cat.png';
    $urlImagen = RUTA_BASE . '/img/categorias/' . $img;
    
    $contenidoPrincipal .= "
    <div class='pedido-card text-center'>
        <img src='{$urlImagen}' class='img-card'>
        <h3 class='mt-15 mb-15'>{$cat->nombre}</h3>
        <p class='color-gray font-small'>{$cat->descripcion}</p>
        
        <a href='../productos/ListarProductos.php?categoria={$cat->id}' class='btn-detalle font-small'>Ver productos</a>";

    if ($esGerente) {
        $contenidoPrincipal .= "
        <div class='mt-15 pt-10 border-top font-small'>
            <a href='formularioCategoria.php?id={$cat->id}' class='color-blue mr-10 no-decoration'>Editar</a>
            <a href='borrarCategoria.php?id={$cat->id}' class='color-red no-decoration' onclick='return confirm(\"¿Borrar categoría?\")'>Borrar</a>
        </div>";
    }

    $contenidoPrincipal .= "</div>";
}

$contenidoPrincipal .= '</div>';

// 3. CARGAR PLANTILLA (Asegúrate de que la ruta sea correcta)
require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
<?php
require_once __DIR__.'/../../config.php';

use es\ucm\fdi\aw\Producto\ProductoAppService;
use es\ucm\fdi\aw\Categoria\CategoriaAppService; 

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

$esGerente = $_SESSION['esAdmin'] ?? false;

$service = new ProductoAppService();
$serviceCat = new CategoriaAppService();

$productos = $service->getCarta();

$objetosCategorias = $serviceCat->getTodasCategorias();
$mapaCategorias = [];
foreach ($objetosCategorias as $cat) {
    $mapaCategorias[$cat->id] = $cat->nombre;
}

$catSeleccionada = $_GET['categoria'] ?? 'todas';

if ($catSeleccionada !== 'todas') {
    $productos = array_filter($productos, fn($p) => $p->categoria_id == $catSeleccionada);
}

$tituloPagina = 'Carta - Bistro';
$contenidoPrincipal = "<h1>Nuestra Carta</h1>";

if ($esGerente) {
    $contenidoPrincipal .= '
        <div class="mb-20">
            <a href="formularioProducto.php" class="btn-pedido btn-nuevo-verde">+ Nuevo Producto</a>
        </div>';
}

$contenidoPrincipal .= '<div class="filtros mb-20">';
$contenidoPrincipal .= '<a href="ListarProductos.php?categoria=todas" class="btn">Todas</a> ';

foreach ($objetosCategorias as $cat) {
    $contenidoPrincipal .= "<a href='ListarProductos.php?categoria={$cat->id}' class='btn'>{$cat->nombre}</a> ";
}
$contenidoPrincipal .= '</div>';

$contenidoPrincipal .= '<div class="productos-grid">';

if (empty($productos)) {
    $contenidoPrincipal .= "<p>No hay productos disponibles en esta categoría.</p>";
} else {
    foreach ($productos as $p) {
        $nombreImagen = !empty($p->imagen_principal) ? $p->imagen_principal : 'default.jpg';
        $urlImg = RUTA_BASE . "/IMG/productos/" . $nombreImagen;
        $nombreCat = $mapaCategorias[$p->categoria_id] ?? "Sin categoría";

        $contenidoPrincipal .= "
        <div class='pedido-card'>
            <img src='$urlImg' class='img-card' alt='$p->nombre'>
            <small class='color-gray'>$nombreCat</small>
            <h3 class='mb-5'>$p->nombre</h3>
            <p><strong class='font-bold'>".number_format($p->getPrecioFinal(), 2)." €</strong></p>
            <a href='detalleProducto.php?id={$p->id}' class='btn-detalle'>Ver detalles</a>";
            
            if ($esGerente) {
                $contenidoPrincipal .= "
                <div class='mt-8 pt-10 border-top font-small'>
                    <a href='formularioProducto.php?id={$p->id}' class='color-blue mr-10 no-decoration'>Editar</a>
                    <a href='borrarProductos.php?id={$p->id}' class='color-red no-decoration' onclick='return confirm(\"¿Borrar producto?\")'>Borrar</a>
                </div>";
            }

        $contenidoPrincipal .= "</div>";
    }
}
$contenidoPrincipal .= '</div>';

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
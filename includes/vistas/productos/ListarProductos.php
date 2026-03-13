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
        <div style="margin-bottom: 20px;">
            <a href="formularioProducto.php" class="btn btn-primario" style="background:#2ecc71; color:white; padding:10px; text-decoration:none; border-radius:5px; display:inline-block;">+ Nuevo Producto</a>
        </div>';
}

$contenidoPrincipal .= '<div class="filtros" style="margin-bottom: 20px;">';
$contenidoPrincipal .= '<a href="ListarProductos.php?categoria=todas" class="btn">Todas</a> ';

foreach ($objetosCategorias as $cat) {
    $contenidoPrincipal .= "<a href='ListarProductos.php?categoria={$cat->id}' class='btn'>{$cat->nombre}</a> ";
}
$contenidoPrincipal .= '</div>';

$contenidoPrincipal .= '<div class="productos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">';

if (empty($productos)) {
    $contenidoPrincipal .= "<p>No hay productos disponibles en esta categoría.</p>";
} else {
    foreach ($productos as $p) {
        $nombreImagen = !empty($p->imagen_principal) ? $p->imagen_principal : 'default.jpg';
        $urlImg = RUTA_BASE . "/IMG/productos/" . $nombreImagen;
        $nombreCat = $mapaCategorias[$p->categoria_id] ?? "Sin categoría";

        $contenidoPrincipal .= "
        <div class='card' style='border: 1px solid #ddd; padding: 15px; border-radius: 10px; background: white;'>
            <img src='$urlImg' style='width: 100%; height: 150px; object-fit: cover; border-radius: 5px;' alt='$p->nombre'>
            <small style='color: #888;'>$nombreCat</small>
            <h3 style='margin: 5px 0;'>$p->nombre</h3>
            <p><strong>".number_format($p->getPrecioFinal(), 2)." €</strong></p>
            <a href='detalleProducto.php?id={$p->id}' class='btn-detalle'>Ver detalles</a>";
            
            if ($esGerente) {
                $contenidoPrincipal .= "
                <div style='margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee; font-size: 0.8em;'>
                    <a href='formularioProducto.php?id={$p->id}' style='color:#3498db; margin-right:10px; text-decoration:none;'>Editar</a>
                    <a href='borrarProductos.php?id={$p->id}' style='color:#e74c3c; text-decoration:none;' onclick='return confirm(\"¿Borrar producto?\")'>Borrar</a>
                </div>";
            }

        $contenidoPrincipal .= "</div>";
    }
}
$contenidoPrincipal .= '</div>';

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
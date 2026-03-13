<?php
require_once __DIR__.'/../../config.php';

use es\ucm\fdi\aw\Producto\ProductoAppService;
use es\ucm\fdi\aw\Categoria\CategoriaAppService; // Importamos el servicio de categorías

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

// Inicializamos servicios
$service = new ProductoAppService();
$serviceCat = new CategoriaAppService();

$productos = $service->getCarta();

// 1. Obtener todas las categorías para poder mapear ID -> Nombre
$objetosCategorias = $serviceCat->getTodasCategorias();
$mapaCategorias = [];
foreach ($objetosCategorias as $cat) {
    // Asumo que tu CategoriaDTO tiene propiedades 'id' y 'nombre'
    $mapaCategorias[$cat->id] = $cat->nombre;
}

// --- FILTRO POR ID DE CATEGORÍA ---
$catSeleccionada = $_GET['categoria'] ?? 'todas';

if ($catSeleccionada !== 'todas') {
    $productos = array_filter($productos, fn($p) => $p->categoria_id == $catSeleccionada);
}

// --- VISTA ---
$tituloPagina = 'Carta - Bistro';
$contenidoPrincipal = "<h1>Nuestra Carta</h1>";

// Botones de filtro dinámicos con NOMBRES reales
$contenidoPrincipal .= '<div class="filtros" style="margin-bottom: 20px;">';
$contenidoPrincipal .= '<a href="ListarProductos.php?categoria=todas" class="btn">Todas</a> ';

// Solo mostramos botones para las categorías que tienen productos o para todas las existentes
foreach ($objetosCategorias as $cat) {
    $contenidoPrincipal .= "<a href='ListarProductos.php?categoria={$cat->id}' class='btn'>{$cat->nombre}</a> ";
}
$contenidoPrincipal .= '</div>';

// --- LISTADO DE PRODUCTOS ---
$contenidoPrincipal .= '<div class="productos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">';

if (empty($productos)) {
    $contenidoPrincipal .= "<p>No hay productos disponibles en esta categoría.</p>";
} else {
    foreach ($productos as $p) {
        $imagen = !empty($p->imagenes) ? $p->imagenes[0] : 'default.jpg';
        // Buscamos el nombre de la categoría en nuestro mapa para mostrarlo en la tarjeta
        $nombreCat = $mapaCategorias[$p->categoria_id] ?? "Sin categoría";

        $contenidoPrincipal .= "
        <div class='card' style='border: 1px solid #ddd; padding: 15px; border-radius: 10px; background: white;'>
            <img src='".RUTA_BASE."/img/productos/$imagen' style='width: 100%; height: 150px; object-fit: cover; border-radius: 5px;'>
            <small style='color: #888;'>$nombreCat</small>
            <h3 style='margin: 5px 0;'>$p->nombre</h3>
            <p><strong>".number_format($p->getPrecioFinal(), 2)." €</strong></p>
            <a href='detalleProducto.php?id={$p->id}' class='btn-detalle'>Ver detalles</a>
        </div>";
    }
}
$contenidoPrincipal .= '</div>';

// He actualizado la ruta según tu último mensaje
require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
<?php
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\Producto\ProductoAppService;

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

$service = new ProductoAppService();
$productos = $service->getCarta();

// --- FILTRO POR ID DE CATEGORÍA ---
// Ahora recibimos un ID (ej: ListarProductos.php?categoria=1)
$catSeleccionada = $_GET['categoria'] ?? 'todas';

if ($catSeleccionada !== 'todas') {
    // Filtramos comparando con categoria_id
    $productos = array_filter($productos, fn($p) => $p->categoria_id == $catSeleccionada);
}

// Para los botones, necesitamos los IDs únicos de las categorías presentes
$idsCategorias = array_unique(array_map(fn($p) => $p->categoria_id, $service->getCarta()));

// --- VISTA ---
$tituloPagina = 'Carta - Bistro';
$contenidoPrincipal = "<h1>Nuestra Carta</h1>";

// Botones de filtro
$contenidoPrincipal .= '<div class="filtros" style="margin-bottom: 20px;">';
$contenidoPrincipal .= '<a href="ListarProductos.php?categoria=todas" class="btn">Todas</a> ';
foreach ($idsCategorias as $idCat) {
    // Aquí podrías buscar el nombre de la categoría si tienes el servicio de categorías
    $contenidoPrincipal .= "<a href='ListarProductos.php?categoria=$idCat' class='btn'>Categoría $idCat</a> ";
}
$contenidoPrincipal .= '</div>';

// ... resto del código de las tarjetas (el que ya teníamos) ...
// (Asegúrate de que en el bucle uses $p->categoria_id si necesitas el ID)

$contenidoPrincipal .= '<div class="productos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">';
foreach ($productos as $p) {
    $imagen = !empty($p->imagenes) ? $p->imagenes[0] : 'default.jpg';
    $contenidoPrincipal .= "
    <div class='card' style='border: 1px solid #ddd; padding: 15px; border-radius: 10px;'>
        <img src='".RUTA_BASE."/img/productos/$imagen' style='width: 100%; height: 150px; object-fit: cover;'>
        <h3>$p->nombre</h3>
        <p><strong>".number_format($p->getPrecioFinal(), 2)." €</strong></p>
        <a href='detalleProducto.php?id={$p->id}'>Ver detalles</a>
    </div>";
}
$contenidoPrincipal .= '</div>';

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
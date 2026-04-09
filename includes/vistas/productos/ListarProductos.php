<?php
require_once __DIR__.'/../../config.php';
require_once RAIZ_APP . '/includes/vistas/helpers/vistaProductos.php'; // Cargamos el apoyo

use es\ucm\fdi\aw\Producto\ProductoAppService;
use es\ucm\fdi\aw\Categoria\CategoriaAppService; 

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

$esGerente = $_SESSION['esAdmin'] ?? false;
$service = new ProductoAppService();
$serviceCat = new CategoriaAppService();

// --- OBTENCIÓN DE DATOS ---
$productos = $service->getCarta();
$objetosCategorias = $serviceCat->getTodasCategorias();

// Creamos el mapa para saber el nombre de la categoría por su ID
$mapaCategorias = [];
foreach ($objetosCategorias as $cat) {
    $mapaCategorias[$cat->id] = $cat->nombre;
}

// --- LÓGICA DE FILTRADO ---
$catSeleccionada = $_GET['categoria'] ?? 'todas';
if ($catSeleccionada !== 'todas') {
    $productos = array_filter($productos, fn($p) => $p->categoria_id == $catSeleccionada);
}

// --- CONSTRUCCIÓN DE LA PÁGINA ---
$tituloPagina = 'Carta - Bistro';
$contenidoPrincipal = "<h1>Nuestra Carta</h1>";

if ($esGerente) {
    $contenidoPrincipal .= '
        <div class="mb-20">
            <a href="formularioProducto.php" class="btn-pedido btn-nuevo-verde">+ Nuevo Producto</a>
        </div>';
}

// 1. Usamos el script de apoyo para los botones de FILTROS
$contenidoPrincipal .= renderizaFiltrosCategorias($objetosCategorias);

// 2. Usamos el script de apoyo para el GRID de productos
$contenidoPrincipal .= renderizaListaProductos($productos, $mapaCategorias, $esGerente);

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
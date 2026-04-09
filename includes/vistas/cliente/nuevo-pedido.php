<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Pedido\Carrito;
use es\ucm\fdi\aw\Producto\ProductoAppService;
use es\ucm\fdi\aw\Categoria\CategoriaAppService;
use es\ucm\fdi\aw\Oferta\OfertaAppService;

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

// Procesar selección de tipo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_pedido'])) {
    Carrito::setTipo($_POST['tipo_pedido']);
}

$tipo = Carrito::getTipo();

// Si no ha elegido tipo, mostrar selección
if (!$tipo) {
    $tituloPagina = 'Nuevo Pedido';
    $contenidoPrincipal = <<<HTML
    <h1>Nuevo Pedido</h1>
    <p class="text-center font-lg">¿Cómo quieres tu pedido?</p>
    <div class="flex-gap-30 justify-center mt-30">
        <form method="POST">
            <input type="hidden" name="tipo_pedido" value="local">
            <button type="submit" class="btn-selector">
                <div class="font-xl">🍽️</div>
                <strong>Para Local</strong>
                <p class="color-gray font-small">Consumir en Bistro FDI</p>
            </button>
        </form>
        <form method="POST">
            <input type="hidden" name="tipo_pedido" value="llevar">
            <button type="submit" class="btn-selector">
                <div class="font-xl">🥡</div>
                <strong>Para Llevar</strong>
                <p class="color-gray font-small">Recoger y consumir fuera</p>
            </button>
        </form>
    </div>
HTML;
    require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
    exit();
}

// Mostrar carta para añadir productos
$service = new ProductoAppService();
$serviceCat = new CategoriaAppService();
$serviceOferta = new OfertaAppService();
$productos = $service->getCarta();
$categorias = $serviceCat->getTodasCategorias();
$ofertas = $serviceOferta->getOfertasActivas();

$catSeleccionada = $_GET['categoria'] ?? 'todas';
if ($catSeleccionada !== 'todas') {
    $productos = array_filter($productos, fn($p) => $p->categoria_id == $catSeleccionada);
}

$mapaCategorias = [];
foreach ($categorias as $cat) {
    $mapaCategorias[$cat->id] = $cat->nombre;
}

$tipoLabel = $tipo === 'local' ? '🍽️ Para Local' : '🥡 Para Llevar';
$itemsCarrito = Carrito::getTotalUnidades();

$tituloPagina = 'Nuevo Pedido - Carta';

// Filtros de categoría
$filtrosHtml = '<a href="nuevo-pedido.php?categoria=todas" class="btn-cat ' . ($catSeleccionada == 'todas' ? 'btn-cat-active' : '') . '">Todas</a> ';
foreach ($categorias as $cat) {
    $claseActiva = ($catSeleccionada == $cat->id) ? 'btn-cat-active' : '';
    $filtrosHtml .= "<a href='nuevo-pedido.php?categoria={$cat->id}' class='btn-cat {$claseActiva}'>{$cat->nombre}</a> ";
}

// Productos
$productosHtml = '';
if (empty($productos)) {
    $productosHtml = '<p>No hay productos disponibles en esta categoría.</p>';
} else {
    $productosHtml = '<div class="grid-carta">';
    foreach ($productos as $p) {
        if (!$p->disponible) continue;
        $img = !empty($p->imagen_principal) ? $p->imagen_principal : 'default.jpg';
        $urlImg = RUTA_BASE . '/IMG/productos/' . $img;
        $precio = number_format($p->getPrecioFinal(), 2);
        $nombreCat = $mapaCategorias[$p->categoria_id] ?? '';
        
        $productosHtml .= <<<HTML
        <div class="card-producto-mini">
            <img src="$urlImg" class="img-carta" alt="{$p->nombre}">
            <small class="color-gray">{$nombreCat}</small>
            <h3 class="mt-5 mb-5 font-bold">{$p->nombre}</h3>
            <p class="mb-0"><strong>{$precio} €</strong></p>
            <form method="POST" action="ajax-carrito.php" class="mt-8 flex-gap-5 align-center">
                <input type="hidden" name="accion" value="agregar">
                <input type="hidden" name="producto_id" value="{$p->id}">
                <input type="number" name="cantidad" value="1" min="1" max="20" class="p-5 text-center border-light rounded-8 w-50">
                <button type="submit" class="btn-pedido btn-nuevo-verde flex-1">🛒 Añadir</button>
            </form>
        </div>
HTML;
    }
    $productosHtml .= '</div>';
}

// Ofertas disponibles
$ofertasHtml = '';
if (!empty($ofertas)) {
    $ofertasHtml .= '<h2 class="mb-15 mt-30 font-lg">📦 Ofertas Especiales</h2>';
    $ofertasHtml .= '<div class="grid-carta">';
    foreach ($ofertas as $oferta) {
        $descuentoTexto = number_format($oferta->porcentaje_descuento, 1);
        $productosTexto = '';
        foreach ($oferta->productos as $pidOferta => $cantOferta) {
            $prodOferta = $service->getProducto($pidOferta);
            if ($prodOferta) {
                $productosTexto .= "{$prodOferta->nombre} (x{$cantOferta}), ";
            }
        }
        $productosTexto = rtrim($productosTexto, ', ');
        
        $ofertasHtml .= <<<HTML
        <div class="card-producto-mini">
            <div class="badge-descuento">-{$descuentoTexto}% DESCUENTO</div>
            <h3 class="mt-5 mb-5 font-bold">{$oferta->nombre}</h3>
            <small class="color-gray">{$oferta->descripcion}</small>
            <p class="mt-8 mb-0 font-small color-gray">{$productosTexto}</p>
            <form method="POST" action="ajax-carrito.php" class="mt-8">
                <input type="hidden" name="accion" value="agregar_oferta">
                <input type="hidden" name="oferta_id" value="{$oferta->id}">
                <button type="submit" class="btn-pedido btn-nuevo-verde w-100 font-small">✓ Aplicar</button>
            </form>
        </div>
HTML;
    }
    $ofertasHtml .= '</div>';
}

$contenidoPrincipal = <<<HTML
<div class="flex-between mb-20 align-center">
    <h1 class="mb-0">Nuevo Pedido</h1>
    <div class="flex-gap-10 align-center">
        <span class="badge-tipo">{$tipoLabel}</span>
        <a href="carrito.php" class="btn-pedido btn-primary no-decoration">
            🛒 Carrito ({$itemsCarrito})
        </a>
    </div>
</div>

<div class="nav-categorias">
    {$filtrosHtml}
</div>

{$productosHtml}

{$ofertasHtml}
HTML;

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

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
    <p style="text-align:center;font-size:1.1em;">¿Cómo quieres tu pedido?</p>
    <div style="display:flex;gap:30px;justify-content:center;margin-top:30px;">
        <form method="POST">
            <input type="hidden" name="tipo_pedido" value="local">
            <button type="submit" style="width:200px;height:200px;border:2px solid #8b0000;border-radius:15px;background:white;cursor:pointer;font-size:1.2em;transition:all 0.3s;">
                <div style="font-size:3em;">🍽️</div>
                <strong>Para Local</strong>
                <p style="color:#888;font-size:0.8em;">Consumir en Bistro FDI</p>
            </button>
        </form>
        <form method="POST">
            <input type="hidden" name="tipo_pedido" value="llevar">
            <button type="submit" style="width:200px;height:200px;border:2px solid #8b0000;border-radius:15px;background:white;cursor:pointer;font-size:1.2em;transition:all 0.3s;">
                <div style="font-size:3em;">🥡</div>
                <strong>Para Llevar</strong>
                <p style="color:#888;font-size:0.8em;">Recoger y consumir fuera</p>
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
$filtrosHtml = '<a href="nuevo-pedido.php?categoria=todas" class="btn" style="margin:2px;padding:5px 10px;text-decoration:none;background:#eee;border-radius:5px;">Todas</a> ';
foreach ($categorias as $cat) {
    $activa = ($catSeleccionada == $cat->id) ? 'background:#8b0000;color:white;' : 'background:#eee;';
    $filtrosHtml .= "<a href='nuevo-pedido.php?categoria={$cat->id}' class='btn' style='margin:2px;padding:5px 10px;text-decoration:none;border-radius:5px;{$activa}'>{$cat->nombre}</a> ";
}

// Productos
$productosHtml = '';
if (empty($productos)) {
    $productosHtml = '<p>No hay productos disponibles en esta categoría.</p>';
} else {
    $productosHtml = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:15px;">';
    foreach ($productos as $p) {
        if (!$p->disponible) continue;
        $img = !empty($p->imagen_principal) ? $p->imagen_principal : 'default.jpg';
        $urlImg = RUTA_BASE . '/IMG/productos/' . $img;
        $precio = number_format($p->getPrecioFinal(), 2);
        $nombreCat = $mapaCategorias[$p->categoria_id] ?? '';
        
        $productosHtml .= <<<HTML
        <div style="border:1px solid #ddd;padding:12px;border-radius:10px;background:white;">
            <img src="$urlImg" style="width:100%;height:120px;object-fit:cover;border-radius:5px;" alt="{$p->nombre}">
            <small style="color:#888;">{$nombreCat}</small>
            <h3 style="margin:5px 0;font-size:1em;">{$p->nombre}</h3>
            <p style="margin:0;"><strong>{$precio} €</strong></p>
            <form method="POST" action="ajax-carrito.php" style="margin-top:8px;display:flex;gap:5px;align-items:center;">
                <input type="hidden" name="accion" value="agregar">
                <input type="hidden" name="producto_id" value="{$p->id}">
                <input type="number" name="cantidad" value="1" min="1" max="20" style="width:50px;padding:5px;text-align:center;border:1px solid #ddd;border-radius:3px;">
                <button type="submit" style="background:#2ecc71;color:white;border:none;padding:8px 12px;border-radius:5px;cursor:pointer;flex:1;">🛒 Añadir</button>
            </form>
        </div>
HTML;
    }
    $productosHtml .= '</div>';
}

// Ofertas disponibles
$ofertasHtml = '';
if (!empty($ofertas)) {
    $ofertasHtml .= '<h2 style="margin:30px 0 15px 0;font-size:1.3em;">📦 Ofertas Especiales</h2>';
    $ofertasHtml .= '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:15px;">';
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
        <div style="border:1px solid #ddd;padding:12px;border-radius:10px;background:white;">
            <div style="background:#28a745;color:white;padding:4px 8px;border-radius:5px;margin-bottom:8px;text-align:center;font-weight:bold;font-size:0.85em;">-{$descuentoTexto}% DESCUENTO</div>
            <h3 style="margin:5px 0;font-size:1em;">{$oferta->nombre}</h3>
            <small style="color:#888;">{$oferta->descripcion}</small>
            <p style="margin:8px 0 0;font-size:0.85em;color:#666;">{$productosTexto}</p>
            <form method="POST" action="ajax-carrito.php" style="margin-top:8px;">
                <input type="hidden" name="accion" value="agregar_oferta">
                <input type="hidden" name="oferta_id" value="{$oferta->id}">
                <button type="submit" style="background:#2ecc71;color:white;border:none;padding:8px 12px;border-radius:5px;cursor:pointer;width:100%;font-size:0.9em;">✓ Aplicar</button>
            </form>
        </div>
HTML;
    }
    $ofertasHtml .= '</div>';
}

$contenidoPrincipal = <<<HTML
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h1 style="margin:0;">Nuevo Pedido</h1>
    <div>
        <span style="background:#f0e9e2;padding:5px 12px;border-radius:15px;font-size:0.9em;">{$tipoLabel}</span>
        <a href="carrito.php" style="background:#8b0000;color:white;padding:8px 15px;border-radius:5px;text-decoration:none;margin-left:10px;">
            🛒 Carrito ({$itemsCarrito})
        </a>
    </div>
</div>

<div style="margin-bottom:15px;">
    {$filtrosHtml}
</div>

{$productosHtml}

{$ofertasHtml}
HTML;

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

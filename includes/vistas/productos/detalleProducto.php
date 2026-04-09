<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Producto\ProductoAppService;
use es\ucm\fdi\aw\Categoria\CategoriaAppService;

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

$esGerente = $_SESSION['esAdmin'] ?? false;
$esCliente = $_SESSION['esCliente'] ?? false;

$idProducto = $_GET['id'] ?? null;
$service = new ProductoAppService();
$serviceCat = new CategoriaAppService();
$p = $service->getProducto($idProducto);


if (!$p) {
    die("Producto no encontrado.");
}

// Obtener nombre de categoría
$cat = $serviceCat->getCategoria($p->categoria_id);
$nombreCategoria = $cat ? $cat->nombre : 'Sin categoría';

$tituloPagina = $p->nombre . ' - Detalle';
$precioFinal = number_format($p->getPrecioFinal(), 2);
$precioBase = number_format($p->precio_base, 2);

$iva = $p->iva;

// Imagen principal
$imgPrincipal = !empty($p->imagen_principal) ? $p->imagen_principal : 'default.jpg';
$urlImgPrincipal = RUTA_BASE . '/IMG/productos/' . $imgPrincipal;

// Galería de imágenes
$todasImgs = $p->getTodasImagenes();
$galeriaHtml = '';
if (count($todasImgs) > 1) {
    $galeriaHtml = '<div class="flex-gap-10 mt-8 flex-wrap">';
    foreach ($todasImgs as $img) {
        $url = RUTA_BASE . '/IMG/productos/' . $img;
        $galeriaHtml .= "<img src='$url' class='img-miniatura' onclick=\"document.getElementById('img-principal').src='$url'\">";
    }
    $galeriaHtml .= '</div>';
}

$contenidoPrincipal = <<<HTML
$contenidoPrincipal = <<<HTML
<div class="detalle-contenedor">
    
    <div class="detalle-seccion">
        <img id="img-principal" src="{$urlImgPrincipal}" class="img-detalle">
        {$galeriaHtml}
    </div>

    <div class="detalle-info">
        <a href="ListarProductos.php" class="color-gray no-decoration">← Volver al listado</a>
        <h1 class="mt-8 mb-10">{$p->nombre}</h1>
        <span class="badge-categoria">
            Categoría: {$nombreCategoria}
        </span>
        
        <p class="font-lg color-gray mb-20 mt-20">{$p->descripcion}</p>

        <div class="precio-caja">
            <span class="precio-destacado">{$precioFinal} €</span>
            <p class="mb-0 color-gray">IVA incluido ({$iva}%)</p>
        </div>
HTML;

// --- ACCIONES POR ROL ---
if ($esGerente) {
    $contenidoPrincipal .= <<<HTML
    $contenidoPrincipal .= <<<HTML
        <div class="acciones-admin flex-gap-10 mt-20">
            <a href="formularioProducto.php?id={$p->id}" class="btn-pedido btn-preparar">Modificar Datos</a>
            <a href="borrarProductos.php?id={$p->id}" class="btn-pedido btn-danger" 
               onclick="return confirm('¿Seguro que quieres retirar este producto de la carta?')">Retirar de la Carta</a>
        </div>
        <div class="mt-8 font-small color-gray">
            Precio Base: {$precioBase} € | ID Producto: {$p->id}
        </div>
HTML;
}

elseif ($esCliente) {
    if ($p->disponible) {
        $contenidoPrincipal .= <<<HTML
        $contenidoPrincipal .= <<<HTML
            <div class="mt-20">
                <form method="POST" action="../cliente/ajax-carrito.php" class="inline">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="producto_id" value="{$p->id}">
                    <input type="number" name="cantidad" value="1" min="1" max="20" class="p-10 text-center border-light rounded-8 w-100">
                    <button type="submit" class="btn-pedido btn-cocina-listo font-lg ml-10">
                        🛒 Añadir al Pedido
                    </button>
                </form>
            </div>
HTML;
    }
    else {
        $contenidoPrincipal .= "<p class='color-red mt-20'><strong>Temporalmente no disponible</strong></p>";
    }
}

$contenidoPrincipal .= "</div></div>";

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
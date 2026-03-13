<?php
require_once __DIR__.'/../../config.php';

use es\ucm\fdi\aw\Producto\ProductoAppService;
use es\ucm\fdi\aw\Categoria\CategoriaAppService;

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

<<<<<<< HEAD
// 1. RECUPERAR DATOS DE SESIÓN
$esGerente = $_SESSION['esAdmin'] ?? false;
$esCliente = $_SESSION['esCliente'] ?? false;

// 2. RECUPERAR EL PRODUCTO
$idProducto = $_GET['id'] ?? null;
$service = new ProductoAppService();
=======
$esGerente = $_SESSION['esAdmin'] ?? false;
$esCliente = $_SESSION['esCliente'] ?? false;

$idProducto = $_GET['id'] ?? null;
$service = new ProductoAppService();
$serviceCat = new CategoriaAppService();
>>>>>>> e64342e (version definitiva de la funcionalidad 2 y correción de algunas funciones 0 y 1. Todo implementado, falta ponerlo bonito y cierdas redirecciones a web que estan mal)
$p = $service->getProducto($idProducto); 

if (!$p) {
    die("Producto no encontrado.");
}

<<<<<<< HEAD
// 3. PREPARACIÓN DE DATOS
$nombreCategoria = $p->categoria_id; 
$tituloPagina = $p->nombre . ' - Detalle';

=======
// Obtener nombre de categoría
$cat = $serviceCat->getCategoria($p->categoria_id);
$nombreCategoria = $cat ? $cat->nombre : 'Sin categoría';

$tituloPagina = $p->nombre . ' - Detalle';
>>>>>>> e64342e (version definitiva de la funcionalidad 2 y correción de algunas funciones 0 y 1. Todo implementado, falta ponerlo bonito y cierdas redirecciones a web que estan mal)
$precioFinal = number_format($p->getPrecioFinal(), 2);
$precioBase = number_format($p->precio_base, 2); 
$iva = $p->iva;

<<<<<<< HEAD
// CORRECCIÓN: Usamos imagen_principal (que es un array en tu DTO)
$nombreImagen = (!empty($p->imagen_principal) && isset($p->imagen_principal[0])) 
                ? $p->imagen_principal[0] 
                : 'default.jpg';

// Usamos IMG en mayúsculas como en el listado
$urlImagen = RUTA_BASE . '/IMG/productos/' . $nombreImagen;

// 4. CONSTRUCCIÓN DEL CONTENIDO
=======
// Imagen principal
$imgPrincipal = !empty($p->imagen_principal) ? $p->imagen_principal : 'default.jpg';
$urlImgPrincipal = RUTA_BASE . '/IMG/productos/' . $imgPrincipal;

// Galería de imágenes
$todasImgs = $p->getTodasImagenes();
$galeriaHtml = '';
if (count($todasImgs) > 1) {
    $galeriaHtml = '<div style="display:flex;gap:10px;margin-top:15px;flex-wrap:wrap;">';
    foreach ($todasImgs as $img) {
        $url = RUTA_BASE . '/IMG/productos/' . $img;
        $galeriaHtml .= "<img src='$url' style='width:80px;height:80px;object-fit:cover;border-radius:5px;border:2px solid transparent;cursor:pointer;' onclick=\"document.getElementById('img-principal').src='$url'\" onmouseover=\"this.style.borderColor='#8b0000'\" onmouseout=\"this.style.borderColor='transparent'\">";
    }
    $galeriaHtml .= '</div>';
}

>>>>>>> e64342e (version definitiva de la funcionalidad 2 y correción de algunas funciones 0 y 1. Todo implementado, falta ponerlo bonito y cierdas redirecciones a web que estan mal)
$contenidoPrincipal = <<<HTML
<div class="detalle-contenedor" style="display: flex; gap: 30px; margin-top: 20px; padding: 20px;">
    
    <div class="detalle-imagen" style="flex: 1;">
<<<<<<< HEAD
        <img src="{$urlImagen}" 
             style="width: 100%; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
=======
        <img id="img-principal" src="{$urlImgPrincipal}" 
             style="width: 100%; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        {$galeriaHtml}
>>>>>>> e64342e (version definitiva de la funcionalidad 2 y correción de algunas funciones 0 y 1. Todo implementado, falta ponerlo bonito y cierdas redirecciones a web que estan mal)
    </div>

    <div class="detalle-info" style="flex: 1.5;">
        <a href="ListarProductos.php" style="text-decoration: none; color: #666;">← Volver al listado</a>
        <h1 style="margin: 10px 0;">{$p->nombre}</h1>
        <span class="badge-categoria" style="background: #e0e0e0; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;">
            Categoría: {$nombreCategoria}
        </span>
        
        <p style="font-size: 1.2em; color: #444; margin: 20px 0;">{$p->descripcion}</p>

        <div class="precio-box" style="background: #f9f9f9; padding: 20px; border-radius: 8px; border-left: 5px solid #2ecc71;">
            <span style="font-size: 2em; font-weight: bold;">{$precioFinal} €</span>
            <p style="margin: 0; color: #888;">IVA incluido ({$iva}%)</p>
        </div>
HTML;

// --- ACCIONES POR ROL ---
if ($esGerente) {
<<<<<<< HEAD
    // CORRECCIÓN: El enlace ahora apunta a borrarProductos.php (plural) para que haga el borrado lógico
=======
>>>>>>> e64342e (version definitiva de la funcionalidad 2 y correción de algunas funciones 0 y 1. Todo implementado, falta ponerlo bonito y cierdas redirecciones a web que estan mal)
    $contenidoPrincipal .= <<<HTML
        <div class="acciones-admin" style="margin-top: 30px; display: flex; gap: 10px;">
            <a href="formularioProducto.php?id={$p->id}" class="btn" style="background:#3498db; color:white; padding:10px; border-radius:5px; text-decoration:none;">Modificar Datos</a>
            <a href="borrarProductos.php?id={$p->id}" class="btn" style="background:#e74c3c; color:white; padding:10px; border-radius:5px; text-decoration:none;" 
               onclick="return confirm('¿Seguro que quieres retirar este producto de la carta?')">Retirar de la Carta</a>
        </div>
        <div style="margin-top: 10px; font-size: 0.9em; color: #999;">
            Precio Base: {$precioBase} € | ID Producto: {$p->id}
        </div>
HTML;
} 
elseif ($esCliente) {
    if ($p->disponible) {
        $contenidoPrincipal .= <<<HTML
            <div style="margin-top: 30px;">
<<<<<<< HEAD
                <button class="btn-primario" style="background:#2ecc71; color:white; padding: 15px 30px; font-size: 1.1em; border:none; border-radius:5px; cursor:pointer;">
                    🛒 Añadir al Pedido
                </button>
=======
                <form method="POST" action="../cliente/ajax-carrito.php" style="display:inline;">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="producto_id" value="{$p->id}">
                    <input type="number" name="cantidad" value="1" min="1" max="20" style="width:60px;padding:10px;text-align:center;border:1px solid #ddd;border-radius:5px;">
                    <button type="submit" style="background:#2ecc71; color:white; padding: 12px 25px; font-size: 1.1em; border:none; border-radius:5px; cursor:pointer;">
                        🛒 Añadir al Pedido
                    </button>
                </form>
>>>>>>> e64342e (version definitiva de la funcionalidad 2 y correción de algunas funciones 0 y 1. Todo implementado, falta ponerlo bonito y cierdas redirecciones a web que estan mal)
            </div>
HTML;
    } else {
        $contenidoPrincipal .= "<p style='color: red; margin-top: 20px;'><strong>Temporalmente no disponible</strong></p>";
    }
}

$contenidoPrincipal .= "</div></div>";

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
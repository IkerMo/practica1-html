<?php
require_once __DIR__.'/../../config.php';

use es\ucm\fdi\aw\Producto\ProductoAppService;
use es\ucm\fdi\aw\Categoria\CategoriaAppService;

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

// 1. RECUPERAR DATOS DE SESIÓN
$esGerente = $_SESSION['esAdmin'] ?? false;
$esCliente = $_SESSION['esCliente'] ?? false;

// 2. RECUPERAR EL PRODUCTO
$idProducto = $_GET['id'] ?? null;
$service = new ProductoAppService();
$p = $service->getProducto($idProducto); // Lo llamamos $p para que coincida con tu HTML

if (!$p) {
    die("Producto no encontrado.");
}

// 3. PREPARACIÓN DE DATOS
$nombreCategoria = $p->categoria_id; 
$tituloPagina = $p->nombre . ' - Detalle';

$precioFinal = number_format($p->getPrecioFinal(), 2);
$precioBase = number_format($p->precio_base, 2); // Ojo: en tu DTO es precioBase, no precio_base
$iva = $p->iva;
$imagen = !empty($p->imagenes) ? $p->imagenes[0] : 'default.jpg';
$urlImagen = RUTA_BASE . '/img/productos/' . $imagen;

// 4. CONSTRUCCIÓN DEL CONTENIDO
$contenidoPrincipal = <<<HTML
<div class="detalle-contenedor" style="display: flex; gap: 30px; margin-top: 20px; padding: 20px;">
    
    <div class="detalle-imagen" style="flex: 1;">
        <img src="{$urlImagen}" 
             style="width: 100%; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
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
    $contenidoPrincipal .= <<<HTML
        <div class="acciones-admin" style="margin-top: 30px; display: flex; gap: 10px;">
            <a href="formularioProducto.php?id={$p->id}" class="btn" style="background:#3498db; color:white; padding:10px; border-radius:5px; text-decoration:none;">Modificar Datos</a>
            <a href="borrarProducto.php?id={$p->id}" class="btn" style="background:#e74c3c; color:white; padding:10px; border-radius:5px; text-decoration:none;" 
               onclick="return confirm('¿Seguro?')">Retirar de la Carta</a>
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
                <button class="btn-primario" style="background:#2ecc71; color:white; padding: 15px 30px; font-size: 1.1em; border:none; border-radius:5px; cursor:pointer;">
                    🛒 Añadir al Pedido
                </button>
            </div>
HTML;
    } else {
        $contenidoPrincipal .= "<p style='color: red; margin-top: 20px;'><strong>Temporalmente no disponible</strong></p>";
    }
}

$contenidoPrincipal .= "</div></div>";

// Cargar plantilla (Usa el nombre que tengas: layout.php o plantilla.php)
require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
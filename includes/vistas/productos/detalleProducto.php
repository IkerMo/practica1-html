<?php
require_once __DIR__.'/../../config.php';

use es\ucm\fdi\aw\Producto\ProductoAppService;
use es\ucm\fdi\aw\Categoria\CategoriaAppService;

// --- REGLA DE ORO 2: SEGURIDAD ---
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    header('Location: ' . $app->resuelve('/login.php'));
    exit();
}

/** @var es\ucm\fdi\aw\Usuarios\Usuario $usuario */
$usuario = $_SESSION['usuario'];
$rol = $usuario->getRolActual();

// --- OBTENCIÓN DE DATOS ---
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: listarProductos.php');
    exit();
}

$serviceProd = new ProductoAppService();
$serviceCat = new CategoriaAppService();

$p = $serviceProd->getProducto($id);

if (!$p) {
    header('Location: listarProductos.php');
    exit();
}

// Obtenemos el nombre de la categoría para mostrarlo
// Nota: Tu ProductoDTO tiene el atributo 'categoria' (nombre), 
// pero si fuera un ID, usaríamos $serviceCat->getCategoria($p->idCategoria)
$nombreCategoria = $p->categoria; 

// --- PREPARACIÓN DE LA VISTA ---
$tituloPagina = $p->nombre . ' - Detalle';

$precioFinal = number_format($p->getPrecioFinal(), 2);
$precioBase = number_format($p->precioBase, 2);
$iva = $p->iva;
$imagen = !empty($p->imagenes) ? $p->imagenes[0] : 'default.jpg';

// Construcción del contenido
$contenidoPrincipal = <<<HTML
<div class="detalle-contenedor" style="display: flex; gap: 30px; margin-top: 20px;">
    
    <div class="detalle-imagen" style="flex: 1;">
        <img src="{$app->resuelve('/img/productos/'.$imagen)}" 
             style="width: 100%; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
    </div>

    <div class="detalle-info" style="flex: 1.5;">
        <a href="listarProductos.php" style="text-decoration: none; color: #666;">← Volver al listado</a>
        <h1 style="margin: 10px 0;">{$p->nombre}</h1>
        <span class="badge-categoria" style="background: #e0e0e0; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;">
            {$nombreCategoria}
        </span>
        
        <p style="font-size: 1.2em; color: #444; margin: 20px 0;">{$p->descripcion}</p>

        <div class="precio-box" style="background: #f9f9f9; padding: 20px; border-radius: 8px; border-left: 5px solid #2ecc71;">
            <span style="font-size: 2em; font-weight: bold;">{$precioFinal} €</span>
            <p style="margin: 0; color: #888;">IVA incluido ({$iva}%)</p>
        </div>
HTML;

// --- ACCIONES POR ROL ---

if ($rol === 'Gerente') {
    $contenidoPrincipal .= <<<HTML
        <div class="acciones-admin" style="margin-top: 30px; display: flex; gap: 10px;">
            <a href="formularioProducto.php?id={$p->id}" class="btn btn-edit">Modificar Datos</a>
            <a href="borrarProducto.php?id={$p->id}" class="btn btn-delete" 
               onclick="return confirm('¿Seguro que quieres retirar este producto?')">Retirar de la Carta</a>
        </div>
        <div style="margin-top: 10px; font-size: 0.9em; color: #999;">
            Precio Base: {$precioBase} € | ID: {$p->id}
        </div>
HTML;
} 
elseif ($rol === 'Cliente') {
    if ($p->disponible) {
        $contenidoPrincipal .= <<<HTML
            <div style="margin-top: 30px;">
                <button class="btn-primario" style="padding: 15px 30px; font-size: 1.1em;">
                    🛒 Añadir al Pedido
                </button>
            </div>
HTML;
    } else {
        $contenidoPrincipal .= "<p style='color: red; margin-top: 20px;'><strong>Temporalmente no disponible</strong></p>";
    }
}

$contenidoPrincipal .= <<<HTML
    </div>
</div>
HTML;

// Cargar plantilla
require __DIR__.'/../plantillas/layout.php';
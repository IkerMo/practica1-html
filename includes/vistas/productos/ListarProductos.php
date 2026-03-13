<?php
require_once __DIR__.'/../../config.php';

use es\ucm\fdi\aw\Producto\ProductoAppService;

// --- REGLA DE ORO 2: SEGURIDAD ---
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    header('Location: ' . $app->resuelve('/login.php'));
    exit();
}

/** @var es\ucm\fdi\aw\Usuarios\Usuario $usuario */
$usuario = $_SESSION['usuario'];
$rol = $usuario->getRolActual();

// --- LÓGICA DE DATOS ---
$service = new ProductoAppService();
$productos = $service->getCarta();

// Filtro de categoría
$catSeleccionada = $_GET['categoria'] ?? 'todas';
if ($catSeleccionada !== 'todas') {
    $productos = array_filter($productos, fn($p) => $p->categoria == $catSeleccionada);
}

$todasCategorias = array_unique(array_map(fn($p) => $p->categoria, $service->getCarta()));

// --- CONSTRUCCIÓN DE LA VISTA ---
$tituloPagina = 'Carta de Productos - Bistro';

$contenidoPrincipal = "<h1>Menú y Gestión de Productos</h1>";

// 1. Selector de Categorías
$contenidoPrincipal .= '<div class="filtros-categorias" style="margin-bottom: 20px;">';
$contenidoPrincipal .= '<a href="listarProductos.php?categoria=todas" class="btn">Todas</a> ';
foreach ($todasCategorias as $cat) {
    $clase = ($catSeleccionada == $cat) ? 'btn-active' : 'btn';
    $contenidoPrincipal .= "<a href='listarProductos.php?categoria=$cat' class='$clase'>$cat</a> ";
}
$contenidoPrincipal .= '</div>';

// 2. Acción especial: Añadir Producto (Solo Gerente)
if ($rol === 'Gerente') {
    $contenidoPrincipal .= <<<HTML
        <div class="zona-admin" style="background: #f4f4f4; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
            <a href="formularioProducto.php" class="btn btn-primario"> + Registrar Nuevo Producto</a>
        </div>
    HTML;
}

// 3. Rejilla de Productos
$contenidoPrincipal .= '<div class="productos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">';

if (empty($productos)) {
    $contenidoPrincipal .= "<p>No hay productos que mostrar en esta categoría.</p>";
}

foreach ($productos as $p) {
    $precioFinal = number_format($p->getPrecioFinal(), 2);
    $imagen = !empty($p->imagenes) ? $p->imagenes[0] : 'default.jpg';
    $urlDetalle = "detalleProducto.php?id={$p->id}"; // URL hacia el nuevo script de detalles

    $contenidoPrincipal .= "
    <div class='card-producto' style='border: 1px solid #ddd; border-radius: 10px; padding: 15px; background: white; display: flex; flex-direction: column;'>
        
        <a href='$urlDetalle'>
            <img src='{$app->resuelve('/img/productos/'.$imagen)}' style='width: 100%; height: 180px; object-fit: cover; border-radius: 5px;'>
        </a>

        <h3 style='margin: 10px 0;'>
            <a href='$urlDetalle' style='text-decoration: none; color: black;'>{$p->nombre}</a>
        </h3>
        
        <p style='font-size: 0.9em; color: #666; flex-grow: 1;'>{$p->descripcion}</p>
        
        <div class='info-precios' style='background: #f9f9f9; padding: 8px; border-radius: 5px; margin-bottom: 10px;'>
            <strong>Precio Final: $precioFinal €</strong>
        </div>

        <div style='margin-bottom: 10px;'>
            <a href='$urlDetalle' style='font-size: 0.85em; color: #007bff; text-decoration: none;'>🔍 Ver más detalles</a>
        </div>

        <hr style='border: 0; border-top: 1px solid #eee; margin: 10px 0;'>";

    // --- ACCIONES ESPECÍFICAS POR ROL ---
    
    if ($rol === 'Gerente') {
        $contenidoPrincipal .= "
            <div class='acciones' style='display: flex; gap: 5px;'>
                <a href='formularioProducto.php?id={$p->id}' class='btn-edit' style='flex: 1; text-align: center; font-size: 0.8em;'>Editar</a>
                <a href='borrarProducto.php?id={$p->id}' class='btn-delete' style='flex: 1; text-align: center; font-size: 0.8em;' onclick='return confirm(\"¿Retirar?\")'>Retirar</a>
            </div>";
    } 
    elseif ($rol === 'Cliente') {
        if ($p->disponible) {
            $contenidoPrincipal .= "<button class='btn-add' onclick='añadirPedido({$p->id})'>Añadir al Pedido</button>";
        } else {
            $contenidoPrincipal .= "<button disabled style='background: #ccc;'>Agotado</button>";
        }
    } 
    elseif ($rol === 'Cocinero') {
        $contenidoPrincipal .= "<p style='margin:0; font-size:0.8em;'><em>Estado: Preparación</em></p>";
    }

    $contenidoPrincipal .= "</div>";
}

$contenidoPrincipal .= '</div>';

require __DIR__.'/../plantillas/layout.php';
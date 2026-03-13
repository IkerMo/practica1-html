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
$rol = $usuario->getRolActual(); // 'Gerente', 'Cliente', 'Cocinero', 'Camarero'...

// --- LÓGICA DE DATOS ---
$service = new ProductoAppService();
$productos = $service->getCarta(); // Devuelve todos los ofertados

// Filtro de categoría por URL (Usabilidad requerida)
$catSeleccionada = $_GET['categoria'] ?? 'todas';
if ($catSeleccionada !== 'todas') {
    $productos = array_filter($productos, fn($p) => $p->categoria == $catSeleccionada);
}

// Obtener categorías únicas para el selector
$todasCategorias = array_unique(array_map(fn($p) => $p->categoria, $service->getCarta()));

// --- CONSTRUCCIÓN DE LA VISTA ---
$tituloPagina = 'Gestión de Productos - Bistro';

$contenidoPrincipal = "<h1>Menú y Gestión de Productos</h1>";

// 1. Selector de Categorías (Botones de filtro)
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
            <p><strong>Panel de Control:</strong></p>
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
    $precioBase = number_format($p->precioBase, 2);
    $iva = $p->iva;
    $imagen = !empty($p->imagenes) ? $p->imagenes[0] : 'default.jpg';
    $badgeStock = $p->disponible 
        ? '<span style="color: green;">✔ Disponible</span>' 
        : '<span style="color: red;">✘ Sin existencias</span>';

    $contenidoPrincipal .= "
    <div class='card-producto' style='border: 1px solid #ddd; border-radius: 10px; padding: 15px; background: white;'>
        <img src='{$app->resuelve('/img/productos/'.$imagen)}' style='width: 100%; height: 180px; object-fit: cover; border-radius: 5px;'>
        <h3 style='margin: 10px 0;'>{$p->nombre}</h3>
        <p style='font-size: 0.9em; color: #666;'>{$p->descripcion}</p>
        
        <div class='info-precios' style='background: #f9f9f9; padding: 8px; border-radius: 5px;'>
            <strong>Precio Final: $precioFinal €</strong> <br>";
            
            // Usabilidad Gerente: Ver desglose de IVA
            if ($rol === 'Gerente') {
                $contenidoPrincipal .= "<small>Base: $precioBase € | IVA: $iva%</small><br>";
            }
            
    $contenidoPrincipal .= "</div>
        <p>$badgeStock</p>
        <hr>";

    // --- ACCIONES CONDICIONALES SEGÚN ROL ---
    
    if ($rol === 'Gerente') {
        $contenidoPrincipal .= "
            <div class='acciones'>
                <a href='formularioProducto.php?id={$p->id}' class='btn-edit'>Modificar</a>
                <a href='borrarProducto.php?id={$p->id}' class='btn-delete' onclick='return confirm(\"¿Retirar de la carta?\")'>Retirar</a>
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
        // El cocinero simplemente los muestra con sus cantidades (si el DTO tuviera stock_num)
        $contenidoPrincipal .= "<p><em>Vista de preparación</em></p>";
    }
    // Si es Camarero, no se añade ningún botón según tu instrucción

    $contenidoPrincipal .= "</div>";
}

$contenidoPrincipal .= '</div>';

// Cargar plantilla pasándole las variables
require __DIR__.'/../plantillas/layout.php';
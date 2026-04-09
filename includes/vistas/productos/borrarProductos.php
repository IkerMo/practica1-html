<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Producto\ProductoAppService;

// 1. SEGURIDAD: Comprobar si está logueado
if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

// 2. SEGURIDAD: Comprobar si es Gerente (usando tu lógica de $_SESSION['esAdmin'])
$esGerente = $_SESSION['esAdmin'] ?? false;

if (!$esGerente) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Error 403</h1><p>No tienes permiso para borrar productos.</p>";
    require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
    exit();
}

// --- LÓGICA DE BORRADO ---
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$resultado = false;

if ($id) {
    $service = new ProductoAppService();
    // Este método pone ofertado = 0 en la BD
    $resultado = $service->retirarDeLaCarta($id);
}

if ($resultado) {
    // Si todo ha ido bien, volvemos al listado
    header('Location: ListarProductos.php');
    exit();
} else {
    $tituloPagina = 'Error al borrar';
    $contenidoPrincipal = <<<HTML
        <h1>Hubo un problema</h1>
        <p>No se pudo retirar el producto de la carta. Es posible que el producto no exista.</p>
        <a href="ListarProductos.php" class="color-blue">Volver al listado</a>
    HTML;
    
    require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
}
<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Producto\ProductoAppService;

// --- REGLA DE ORO 2: SEGURIDAD ---
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    header('Location: ' . $app->resuelve('/login.php'));
    exit();
}

/** @var es\ucm\fdi\aw\Usuarios\Usuario $usuario */
$usuario = $_SESSION['usuario'];

// Solo el Gerente puede retirar productos de la carta
if ($usuario->getRolActual() !== 'Gerente') {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Error 403</h1><p>No tienes permiso para borrar productos.</p>";
    require __DIR__.'/../plantillas/layout.php';
    exit();
}

// --- LÓGICA DE BORRADO LÓGICO ---
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$resultado = false;

if ($id) {
    $service = new ProductoAppService();
    // Usamos tu método que pone ofertado = false
    $resultado = $service->retirarDeLaCarta($id);
}

if ($resultado) {
    // Si todo ha ido bien, volvemos al listado
    header('Location: ' . $app->resuelve('/vistas/productos/listarProductos.php'));
    exit();
} else {
    // Si hay error (ej: el ID no existe), mostramos el error con tu Sidebar
    $tituloPagina = 'Error al borrar';
    $contenidoPrincipal = <<<HTML
        <h1>Hubo un problema</h1>
        <p>No se pudo retirar el producto de la carta. Es posible que el producto no exista.</p>
        <a href="listarProductos.php" class="btn">Volver al listado</a>
    HTML;
    
    require __DIR__.'/../plantillas/layout.php';
}
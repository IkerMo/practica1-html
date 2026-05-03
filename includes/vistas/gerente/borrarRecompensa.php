<?php
require_once __DIR__ . '/../../config.php';
require_once RUTA_CLASES . '/Recompensa/RecompensaAppService.php';

if (!estaLogueado() || !esAdmin()) {
    header('Location: ' . RUTA_RAIZ . 'index.php');
    exit();
}

$id = (int)($_GET['id'] ?? 0);
$service = new \es\ucm\fdi\aw\Recompensa\RecompensaAppService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $service->eliminarRecompensa($id);
        header('Location: listarRecompensas.php?mensaje=eliminada');
        exit();
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
}

$recompensa = $service->obtenerRecompensaPorId($id);
if (!$recompensa) {
    header('Location: listarRecompensas.php');
    exit();
}

$productoService = new \es\ucm\fdi\aw\Producto\ProductoAppService();
$producto = $productoService->getProducto($recompensa->producto_id);

$tituloPagina = 'Eliminar Recompensa';
$contenidoPrincipal = <<<EOS
    <h1>Eliminar Recompensa</h1>
    <p>¿Eliminar recompensa para <strong>{$producto->nombre}</strong>? ({$recompensa->bistrocoins_requeridos}🪙)</p>
    <form method="post"><button type="submit" class="btn-borrar">Sí, eliminar</button>
    <a href="listarRecompensas.php" class="btn-secondary">Cancelar</a></form>
EOS;
require_once __DIR__ . '/../comun/plantilla.php';
?>
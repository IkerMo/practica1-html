<?php
require_once __DIR__ . '/../../config.php';
require_once RUTA_CLASES . '/Recompensa/RecompensaAppService.php';
require_once RUTA_CLASES . '/Producto/ProductoAppService.php';

if (!estaLogueado() || !esAdmin()) {
    header('Location: ' . RUTA_RAIZ . 'index.php');
    exit();
}

$service = new \es\ucm\fdi\aw\Recompensa\RecompensaAppService();
$productoService = new \es\ucm\fdi\aw\Producto\ProductoAppService();
$recompensas = $service->listarRecompensas();

$mensaje = '';
if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'creada') $mensaje = '<div class="mensaje-exito">Recompensa creada correctamente</div>';
    if ($_GET['mensaje'] === 'actualizada') $mensaje = '<div class="mensaje-exito">Recompensa actualizada correctamente</div>';
    if ($_GET['mensaje'] === 'eliminada') $mensaje = '<div class="mensaje-exito">Recompensa eliminada correctamente</div>';
} elseif (isset($_GET['error'])) {
    $mensaje = '<div class="mensaje-error">Error al procesar la recompensa</div>';
}

$tabla = '';
foreach ($recompensas as $recompensa) {
    $producto = $productoService->getProducto($recompensa->producto_id);
    $nombreProducto = $producto ? $producto->nombre : 'Producto no encontrado';
    
    $tabla .= <<<EOS
    <tr>
        <td>{$recompensa->id}</td>
        <td>{$nombreProducto} (ID: {$recompensa->producto_id})</td>
        <td>{$recompensa->bistrocoins_requeridos} 🪙</td>
        <td class="acciones">
            <a href="editarRecompensa.php?id={$recompensa->id}" class="btn-editar">Editar</a>
            <a href="borrarRecompensa.php?id={$recompensa->id}" class="btn-borrar" onclick="return confirm('¿Eliminar esta recompensa?')">Eliminar</a>
        </td>
    </tr>
EOS;
}

$tituloPagina = 'Gestionar Recompensas';
$contenidoPrincipal = <<<EOS
    <h1>Gestión de Recompensas</h1>
    $mensaje
    <div class="acciones-globales">
        <a href="crearRecompensa.php" class="btn-primary">+ Nueva Recompensa</a>
    </div>
    <table class="tabla-recompensas">
        <thead><tr><th>ID</th><th>Producto</th><th>BistroCoins</th><th>Acciones</th></tr></thead>
        <tbody>$tabla</tbody>
    </table>
EOS;
require_once __DIR__ . '/../comun/plantilla.php';
?>
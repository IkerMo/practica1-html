<?php
require_once __DIR__ . '/../../config.php';
require_once RUTA_CLASES . '/Recompensa/RecompensaAppService.php';

if (!estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$usuario = Usuario::buscaUsuarioPorId($_SESSION['idUsuario']);
$saldo = $usuario->getBistroCoins();

$service = new \es\ucm\fdi\aw\Recompensa\RecompensaAppService();
$recompensas = $service->obtenerRecompensasDisponibles($_SESSION['idUsuario'], $saldo);

$html = "<h2>Canjear BistroCoins</h2><p>Tu saldo: <strong>{$saldo} 🪙</strong></p>";
if (empty($recompensas)) {
    $html .= "<p>No hay recompensas disponibles para tu saldo actual.</p>";
} else {
    $html .= "<ul class='recompensas-lista'>";
    foreach ($recompensas as $r) {
        $producto = (new ProductoAppService())->getProducto($r->producto_id);
        $html .= "<li><strong>{$producto->nombre}</strong> - {$r->bistrocoins_requeridos} 🪙</li>";
    }
    $html .= "</ul>";
}
$tituloPagina = 'Recompensas';
$contenidoPrincipal = $html;
require_once __DIR__ . '/../comun/plantilla.php';
?>
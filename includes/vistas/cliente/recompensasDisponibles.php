<?php
require_once __DIR__ . '/../../config.php';
require_once RUTA_CLASES . '/Usuarios/Usuario.php';
require_once RUTA_CLASES . '/Recompensa/RecompensaAppService.php';
require_once RUTA_CLASES . '/Producto/ProductoAppService.php';

if (!estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$usuario = \es\ucm\fdi\aw\Usuarios\Usuario::buscaUsuarioPorId($_SESSION['idUsuario']);
if (!$usuario) {
    header('Location: ' . RUTA_RAIZ . 'index.php');
    exit();
}

$saldo = $usuario->getBistroCoins();

$service = new \es\ucm\fdi\aw\Recompensa\RecompensaAppService();
$productoService = new \es\ucm\fdi\aw\Producto\ProductoAppService();

$todasRecompensas = $service->listarRecompensas();
$recompensasDisponibles = array();
$recompensasNoDisponibles = array();

foreach ($todasRecompensas as $recompensa) {
    if ($saldo >= $recompensa->bistrocoins_requeridos) {
        $recompensasDisponibles[] = $recompensa;
    } else {
        $recompensasNoDisponibles[] = $recompensa;
    }
}

$urlPerfil = RUTA_VISTAS . '/usuarios/perfil.php';

$tituloPagina = 'Canjear BistroCoins';

$html = <<<EOS
    <div class="recompensas-container">
        <h1>💰 Canjear BistroCoins</h1>
        
        <div class="saldo-usuario">
            <p>Tu saldo actual: <strong>{$saldo} 🪙</strong></p>
        </div>
        
        <h2>Recompensas disponibles para ti</h2>
EOS;

if (empty($recompensasDisponibles)) {
    $html .= '<p class="mensaje-info">No tienes suficientes BistroCoins para canjear ninguna recompensa. ¡Sigue comprando!</p>';
} else {
    $html .= '<div class="recompensas-grid">';
    foreach ($recompensasDisponibles as $recompensa) {
        $producto = $productoService->getProducto($recompensa->producto_id);
        $nombreProducto = $producto ? $producto->nombre : 'Producto no encontrado';
        
        $html .= <<<EOS
        <div class="recompensa-card disponible">
            <h3>{$nombreProducto}</h3>
            <p class="coins">{$recompensa->bistrocoins_requeridos} 🪙</p>
            <form method="post" action="canjearRecompensa.php">
                <input type="hidden" name="recompensa_id" value="{$recompensa->id}">
                <button type="submit" class="btn-canjes">Canjear</button>
            </form>
        </div>
EOS;
    }
    $html .= '</div>';
}

$html .= <<<EOS
        <h2>Recompensas que necesitan más BistroCoins</h2>
EOS;

if (empty($recompensasNoDisponibles)) {
    $html .= '<p class="mensaje-info">¡Has desbloqueado todas las recompensas!</p>';
} else {
    $html .= '<div class="recompensas-grid no-disponibles">';
    foreach ($recompensasNoDisponibles as $recompensa) {
        $producto = $productoService->getProducto($recompensa->producto_id);
        $nombreProducto = $producto ? $producto->nombre : 'Producto no encontrado';
        $faltan = $recompensa->bistrocoins_requeridos - $saldo;
        
        $html .= <<<EOS
        <div class="recompensa-card no-disponible">
            <h3>{$nombreProducto}</h3>
            <p class="coins">{$recompensa->bistrocoins_requeridos} 🪙</p>
            <p class="faltan">Te faltan {$faltan} 🪙</p>
        </div>
EOS;
    }
    $html .= '</div>';
}

$html .= <<<EOS
        <div class="volver">
            <a href="{$urlPerfil}" class="btn-secondary">← Volver a mi perfil</a>
        </div>
    </div>
EOS;

$contenidoPrincipal = $html;
require_once __DIR__ . '/../comun/plantilla.php';
?>
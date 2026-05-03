<?php
require_once __DIR__ . '/../../config.php';
require_once RUTA_CLASES . '/Usuarios/Usuario.php';
require_once RUTA_CLASES . '/Recompensa/RecompensaAppService.php';
require_once RUTA_CLASES . '/Producto/ProductoAppService.php';

if (!estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$recompensaId = isset($_POST['recompensa_id']) ? (int)$_POST['recompensa_id'] : 0;
if ($recompensaId <= 0) {
    header('Location: recompensasDisponibles.php?error=recompensa_invalida');
    exit();
}

$usuario = \es\ucm\fdi\aw\Usuarios\Usuario::buscaUsuarioPorId($_SESSION['idUsuario']);
if (!$usuario) {
    header('Location: ' . RUTA_RAIZ . 'index.php');
    exit();
}

$recompensaService = new \es\ucm\fdi\aw\Recompensa\RecompensaAppService();
$recompensa = $recompensaService->obtenerRecompensaPorId($recompensaId);

if (!$recompensa) {
    header('Location: recompensasDisponibles.php?error=recompensa_no_encontrada');
    exit();
}

$saldo = $usuario->getBistroCoins();
if ($saldo < $recompensa->bistrocoins_requeridos) {
    header('Location: recompensasDisponibles.php?error=saldo_insuficiente');
    exit();
}

// Aquí deberías añadir la lógica para canjear la recompensa
// Por ahora solo actualizamos el saldo (falta añadir el producto al carrito/pedido)

$nuevoSaldo = $saldo - $recompensa->bistrocoins_requeridos;
$usuario->actualiza(['bistro_coins' => $nuevoSaldo]);

// Redirigir con mensaje de éxito
header('Location: recompensasDisponibles.php?mensaje=canje_exitoso');
exit();
?>
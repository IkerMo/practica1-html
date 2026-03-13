<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_RAIZ . 'login.php');
    exit();
}

$tituloPagina = 'Panel de Control';
$nombre = $_SESSION['nombre'];
$esAdmin = $_SESSION['esAdmin'] ?? false;
$esCamarero = $_SESSION['esCamarero'] ?? false;
$esCocinero = $_SESSION['esCocinero'] ?? false;
$esCliente = $_SESSION['esCliente'] ?? false;

$contenidoTarjetas = '<div class="panel-tarjetas">';

if ($esAdmin) {
    $contenidoTarjetas .= <<<EOS
        <a href="gerente/usuarios.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">👥</span></div>
            <h3>Usuarios</h3>
            <p>Gestionar personal y roles</p>
        </a>
        <a href="productos/ListarProductos.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">🍔</span></div>
            <h3>Productos</h3>
            <p>Administrar el menú</p>
        </a>
        <a href="gerente/estadisticas.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">📈</span></div>
            <h3>Estadísticas</h3>
            <p>Ver balance de ventas</p>
        </a>
EOS;
}

if ($esCamarero) {
    $contenidoTarjetas .= <<<EOS
        <a href="camarero/pedidos-pendientes.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">🍽️</span></div>
            <h3>Servir</h3>
            <p>Pedidos listos para mesa</p>
        </a>
EOS;
}

if ($esCocinero) {
    $contenidoTarjetas .= <<<EOS
        <a href="cocinero/pedidos.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">🍳</span></div>
            <h3>Comandas</h3>
            <p>Platos por cocinar</p>
        </a>
EOS;
}

if ($esCliente) {
    $contenidoTarjetas .= <<<EOS
        <a href="cliente/carta.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">📜</span></div>
            <h3>Ver Carta</h3>
            <p>Explora nuestros platos</p>
        </a>
        <a href="cliente/mis-pedidos.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">📦</span></div>
            <h3>Mis Pedidos</h3>
            <p>Estado de tu compra</p>
        </a>
EOS;
}

$contenidoTarjetas .= '</div>';

$contenidoPrincipal = <<<EOS
    <div class="inicio-paneles">
        <h1>Bienvenido, $nombre</h1>
        <p>Selecciona una gestión para continuar:</p>
        $contenidoTarjetas
    </div>
EOS;

require("includes/vistas/comun/plantilla.php");
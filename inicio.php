<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_RAIZ . 'login.php');
    exit();
}

$rutaVistas = RUTA_VISTAS;

$tituloPagina = 'Panel de Control';
$nombre = $_SESSION['nombre'];
$esAdmin = $_SESSION['esAdmin'] ?? false;
$esCamarero = $_SESSION['esCamarero'] ?? false;
$esCocinero = $_SESSION['esCocinero'] ?? false;
$esCliente = $_SESSION['esCliente'] ?? false;

$contenidoTarjetas = '<div class="panel-tarjetas">';

// --- BLOQUE GERENTE / ADMIN (Ahora con 5 tarjetas) ---
if ($esAdmin) {
    $contenidoTarjetas .= <<<EOS
        <a href="{$rutaVistas}/usuarios/admin/listar.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">👥</span></div>
            <h3>Usuarios</h3>
            <p>Gestionar personal y roles</p>
        </a>
        <a href="{$rutaVistas}/productos/ListarProductos.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">🍔</span></div>
            <h3>Productos</h3>
            <p>Administrar el menú</p>
        </a>
        <a href="{$rutaVistas}/categorias/listarCategorias.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">📂</span></div>
            <h3>Categorías</h3>
            <p>Organizar platos y bebidas</p>
        </a>
        <!-- <a href="{$rutaVistas}/gerente/ofertas.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">🏷️</span></div>
            <h3>Ofertas</h3>
            <p>Gestionar promociones</p>
        </a>
        <a href="{$rutaVistas}/gerente/estadisticas.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">📈</span></div>
            <h3>Estadísticas</h3>
            <p>Ver balance de ventas</p>
        </a>-->
EOS;
}

// --- BLOQUE CAMARERO ---
if ($esCamarero) {
    $contenidoTarjetas .= <<<EOS
        <a href="{$rutaVistas}/camarero/pedidos-pendientes.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">🍽️</span></div>
            <h3>Servir</h3>
            <p>Pedidos listos para mesa</p>
        </a>
EOS;
}

// --- BLOQUE COCINERO ---
if ($esCocinero) {
    $contenidoTarjetas .= <<<EOS
        <a href="{$rutaVistas}/cocinero/pedidos.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">🍳</span></div>
            <h3>Comandas</h3>
            <p>Platos por cocinar</p>
        </a>
EOS;
}

// --- BLOQUE CLIENTE ---
if ($esCliente) {
    $contenidoTarjetas .= <<<EOS
        <a href="{$rutaVistas}/productos/ListarProductos.php" class="tarjeta">
            <div class="icono-contenedor"><span class="icono">📜</span></div>
            <h3>Ver Carta</h3>
            <p>Explora nuestros platos</p>
        </a>
        <a href="{$rutaVistas}/cliente/mis-pedidos.php" class="tarjeta">
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
        <p class="text-center">Selecciona una gestión para continuar:</p>
        $contenidoTarjetas
    </div>
EOS;

require("includes/vistas/comun/plantilla.php");
<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Pedido\Carrito;

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

// Procesar acciones del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $productoId = (int)($_POST['producto_id'] ?? 0);
    
    switch ($accion) {
        case 'agregar':
            $cantidad = max(1, (int)($_POST['cantidad'] ?? 1));
            Carrito::agregar($productoId, $cantidad);
            break;
        case 'actualizar':
            $cantidad = (int)($_POST['cantidad'] ?? 0);
            Carrito::modificarCantidad($productoId, $cantidad);
            break;
        case 'eliminar':
            Carrito::eliminar($productoId);
            break;
        case 'vaciar':
            Carrito::vaciar();
            break;
        case 'agregar_oferta':
            $ofertaId = (int)($_POST['oferta_id'] ?? 0);
            Carrito::agregarOferta($ofertaId);
            break;
        case 'eliminar_oferta':
            $ofertaId = (int)($_POST['oferta_id'] ?? 0);
            Carrito::eliminarOferta($ofertaId);
            break;
    }
    
    // Si viene de la carta, volver ahí
    $referer = $_POST['referer'] ?? 'nuevo-pedido.php';
    header('Location: ' . $referer);
    exit();
}

// Redirigir GETs al carrito
header('Location: carrito.php');
exit();

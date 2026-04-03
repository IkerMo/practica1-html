<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Oferta\OfertaAppService;

if (!estaLogueado() || !($_SESSION['esAdmin'] ?? false)) {
    header('Location: ' . RUTA_RAIZ . 'inicio.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = new OfertaAppService();

if ($id > 0) {
    $service->borrarOferta($id);
}

header('Location: ofertas.php');
exit();

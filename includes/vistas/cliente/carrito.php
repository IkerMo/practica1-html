<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Pedido\Carrito;
use es\ucm\fdi\aw\Oferta\OfertaAppService;

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

// Procesar actualizaciones de cantidad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $productoId = (int)($_POST['producto_id'] ?? 0);
    
    if ($accion === 'actualizar' && $productoId) {
        $cantidad = (int)($_POST['cantidad'] ?? 0);
        Carrito::modificarCantidad($productoId, $cantidad);
    } elseif ($accion === 'eliminar' && $productoId) {
        Carrito::eliminar($productoId);
    } elseif ($accion === 'vaciar') {
        Carrito::vaciar();
    }
    
    header('Location: carrito.php');
    exit();
}

$items = Carrito::getItems();
$total = Carrito::getTotal();
$tipo = Carrito::getTipo();
$tipoLabel = $tipo === 'local' ? '🍽️ Para Local' : '🥡 Para Llevar';

$ofertaService = new OfertaAppService();
$ofertasActivas = $ofertaService->getOfertasActivas();


$tituloPagina = 'Mi Carrito';

if (Carrito::estaVacio()) {
    $contenidoPrincipal = <<<HTML
    <h1>Mi Carrito</h1>
    <div style="text-align:center;padding:50px 0;">
        <div style="font-size:4em;">🛒</div>
        <p style="font-size:1.2em;color:#888;">Tu carrito está vacío</p>
        <a href="nuevo-pedido.php" style="background:#8b0000;color:white;padding:10px 25px;border-radius:5px;text-decoration:none;">Ver la Carta</a>
    </div>
HTML;
} else {
    $lineasHtml = '';
    foreach ($items as $item) {
        $img = RUTA_BASE . '/IMG/productos/' . ($item['imagen'] ?: 'default.jpg');
        $subtotal = number_format($item['subtotal'], 2);
        
        $lineasHtml .= <<<HTML
        <tr>
            <td><img src="{$img}" style="width:50px;height:50px;object-fit:cover;border-radius:5px;"></td>
            <td>{$item['nombre']}</td>
            <td>{$item['iva']}%</td>
            <td style="text-align:right;">{$subtotal} €</td>
            <td>
                <form method="POST" style="display:flex;gap:5px;align-items:center;">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="producto_id" value="{$item['producto_id']}">
                    <input type="number" name="cantidad" value="{$item['cantidad']}" min="1" max="20" 
                           style="width:50px;padding:4px;text-align:center;border:1px solid #ddd;border-radius:3px;"
                           onchange="this.form.submit()">
                </form>
            </td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="producto_id" value="{$item['producto_id']}">
                    <button type="submit" style="background:#e74c3c;color:white;border:none;padding:5px 10px;border-radius:3px;cursor:pointer;">✕</button>
                </form>
            </td>
        </tr>
HTML;
    }

    $totalFormateado = number_format($total, 2);
    
    $contenidoPrincipal = <<<HTML
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <h1>Mi Carrito</h1>
        <span style="background:#f0e9e2;padding:5px 12px;border-radius:15px;font-size:0.9em;">{$tipoLabel}</span>
    </div>
    
    <table style="width:100%;border-collapse:collapse;margin:20px 0;background:white;">
        <thead>
            <tr style="background:#8b0000;color:white;">
                <th style="padding:10px;">Imagen</th>
                <th style="padding:10px;text-align:left;">Producto</th>
                <th style="padding:10px;">IVA</th>
                <th style="padding:10px;text-align:right;">Subtotal</th>
                <th style="padding:10px;">Cantidad</th>
                <th style="padding:10px;">Quitar</th>
            </tr>
        </thead>
        <tbody>
            {$lineasHtml}
        </tbody>
        <tfoot>
            <tr style="font-weight:bold;font-size:1.2em;">
                <td colspan="3" style="padding:15px;text-align:right;">TOTAL (IVA incluido):</td>
                <td style="padding:15px;text-align:right;">{$totalFormateado} €</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    
    <div style="display:flex;gap:10px;justify-content:space-between;align-items:center;margin-top:20px;">
        <div>
            <a href="nuevo-pedido.php" style="color:#8b0000;text-decoration:none;">← Seguir comprando</a>
            <form method="POST" style="display:inline;margin-left:15px;">
                <input type="hidden" name="accion" value="vaciar">
                <button type="submit" style="background:#e74c3c;color:white;border:none;padding:8px 15px;border-radius:5px;cursor:pointer;" onclick="return confirm('¿Vaciar todo el carrito?')">Vaciar carrito</button>
            </form>
        </div>
        <a href="pago.php" style="background:#2ecc71;color:white;padding:12px 30px;border-radius:5px;text-decoration:none;font-size:1.1em;font-weight:bold;">
            Confirmar y Pagar →
        </a>
    </div>
HTML;
}

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

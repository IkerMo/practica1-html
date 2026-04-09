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
    } elseif ($accion === 'eliminar_oferta') {
        $ofertaId = (int)($_POST['oferta_id'] ?? 0);
        Carrito::eliminarOferta($ofertaId);
    } elseif ($accion === 'vaciar') {
        Carrito::vaciar();
        Carrito::limpiarOfertas();
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
$ofertasAplicadas = Carrito::getOfertas();

// Calcular descuentos de las ofertas aplicadas
$descuentoTotal = 0;
$ofertasAplicadasInfo = [];
foreach ($ofertasAplicadas as $ofertaId) {
    $oferta = $ofertaService->getOferta($ofertaId);
    if ($oferta && $oferta->estaActiva()) {
        $impacto = $ofertaService->calcularImpactoOferta($oferta);
        $descuentoTotal += $impacto['descuento'] ?? 0;
        $ofertasAplicadasInfo[] = [
            'id' => $oferta->id,
            'nombre' => $oferta->nombre,
            'descuento' => $impacto['descuento'] ?? 0
        ];
    }
}

$totalConDescuento = $total - $descuentoTotal;


$tituloPagina = 'Mi Carrito';

if (Carrito::estaVacio()) {
    $contenidoPrincipal = <<<HTML
    <h1>Mi Carrito</h1>
    <div class="text-center py-50">
        <div class="font-xl">🛒</div>
        <p class="font-lg color-gray">Tu carrito está vacío</p>
        <a href="nuevo-pedido.php" class="btn-pedido btn-primary no-decoration">Ver la Carta</a>
    </div>
HTML;
} else {
    $lineasHtml = '';
    foreach ($items as $item) {
        $img = RUTA_BASE . '/IMG/productos/' . ($item['imagen'] ?: 'default.jpg');
        $subtotal = number_format($item['subtotal'], 2);
        
        $lineasHtml .= <<<HTML
        <tr>
            <td><img src="{$img}" class="img-miniatura"></td>
            <td>{$item['nombre']}</td>
            <td>{$item['iva']}%</td>
            <td class="text-right">{$subtotal} €</td>
            <td>
                <form method="POST" class="flex-gap-5 align-center">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="producto_id" value="{$item['producto_id']}">
                    <input type="number" name="cantidad" value="{$item['cantidad']}" min="1" max="20" 
                           class="p-5 text-center border-light rounded-8 w-50"
                           onchange="this.form.submit()">
                </form>
            </td>
            <td>
                <form method="POST" class="inline">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="producto_id" value="{$item['producto_id']}">
                    <button type="submit" class="btn-pedido btn-danger btn-sm">✕</button>
                </form>
            </td>
        </tr>
HTML;
    }

    $totalFormateado = number_format($total, 2);
    $totalDescuentoFormateado = number_format($descuentoTotal, 2);
    $totalConDescuentoFormateado = number_format($totalConDescuento, 2);
    
    // HTML de ofertas aplicadas
    $ofertasAplicadasHtml = '';
    if (!empty($ofertasAplicadasInfo)) {
        $ofertasAplicadasHtml = '<hr class="mb-20 mt-20"><h2 class="font-lg mb-15 mt-15">🎉 Descuentos Aplicados</h2><div class="offers-box">';
        foreach ($ofertasAplicadasInfo as $of) {
            $descuentoFormato = number_format($of['descuento'], 2);
            $ofertasAplicadasHtml .= <<<HTML
            <div class="offer-item">
                <div>
                    <strong class="color-green">{$of['nombre']}</strong><br>
                    <small class="color-gray">Descuento: -{$descuentoFormato} €</small>
                </div>
                <form method="POST" class="inline">
                    <input type="hidden" name="accion" value="eliminar_oferta">
                    <input type="hidden" name="oferta_id" value="{$of['id']}">
                    <button type="submit" class="btn-pedido btn-danger btn-sm">Quitar</button>
                </form>
            </div>
HTML;
        }
        $ofertasAplicadasHtml .= '</div>';
    }
    
    $contenidoPrincipal = <<<HTML
    <div class="flex-between">
        <h1>Mi Carrito</h1>
        <span class="badge-tipo">{$tipoLabel}</span>
    </div>
    
    <table class="tabla-pedidos mb-20 mt-20">
        <thead>
            <tr class="header-maroon">
                <th class="p-10">Imagen</th>
                <th class="p-10 text-left">Producto</th>
                <th class="p-10">IVA</th>
                <th class="p-10 text-right">Subtotal</th>
                <th class="p-10">Cantidad</th>
                <th class="p-10">Quitar</th>
            </tr>
        </thead>
        <tbody>
            {$lineasHtml}
        </tbody>
        <tfoot>
            <tr class="font-bold font-lg">
                <td colspan="3" class="p-15 text-right">TOTAL (IVA incluido):</td>
                <td class="p-15 text-right">{$totalFormateado} €</td>
                <td colspan="2"></td>
            </tr>
HTML;
    if ($descuentoTotal > 0) {
        $contenidoPrincipal .= <<<HTML
            <tr class="color-green font-bold">
                <td colspan="3" class="p-15 text-right">Descuento Aplicado:</td>
                <td class="p-15 text-right">-{$totalDescuentoFormateado} €</td>
                <td colspan="2"></td>
            </tr>
            <tr class="font-bold font-lg bg-light-green">
                <td colspan="3" class="p-15 text-right">TOTAL A PAGAR:</td>
                <td class="p-15 text-right color-green">{$totalConDescuentoFormateado} €</td>
                <td colspan="2"></td>
            </tr>
HTML;
    }
    $contenidoPrincipal .= <<<HTML
        </tfoot>
    </table>

    {$ofertasAplicadasHtml}
    
    <div class="flex-between mt-20">
        <div>
            <a href="nuevo-pedido.php" class="color-maroon no-decoration">← Seguir comprando</a>
            <form method="POST" class="inline ml-15">
                <input type="hidden" name="accion" value="vaciar">
                <button type="submit" class="btn-pedido btn-danger btn-sm" onclick="return confirm('¿Vaciar todo el carrito?')">Vaciar carrito</button>
            </form>
        </div>
        <a href="pago.php" class="btn-checkout">
            Confirmar y Pagar →
        </a>
    </div>
HTML;
}

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Pedido\Carrito;
use es\ucm\fdi\aw\Pedido\PedidoAppService;
use es\ucm\fdi\aw\Oferta\OfertaAppService;

if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

if (Carrito::estaVacio()) {
    header('Location: carrito.php');
    exit();
}

$tituloPagina = 'Pago';
$items = Carrito::getItems();
$total = Carrito::getTotal();
$totalFormateado = number_format($total, 2);

// Obtener descuentos de ofertas
$ofertaService = new OfertaAppService();
$ofertasAplicadas = Carrito::getOfertas();
$descuentoTotal = 0;

foreach ($ofertasAplicadas as $ofertaId) {
    $oferta = $ofertaService->getOferta($ofertaId);
    if ($oferta && $oferta->estaActiva()) {
        $impacto = $ofertaService->calcularImpactoOferta($oferta);
        $descuentoTotal += $impacto['descuento'] ?? 0;
    }
}

$totalConDescuento = $total - $descuentoTotal;
$totalDescuentoFormateado = number_format($descuentoTotal, 2);
$totalConDescuentoFormateado = number_format($totalConDescuento, 2);

// Procesar pago
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodoPago = $_POST['metodo_pago'] ?? '';
    $errores = [];
    
    if ($metodoPago === 'tarjeta') {
        $numTarjeta = preg_replace('/\s+/', '', $_POST['num_tarjeta'] ?? '');
        $fechaExp = $_POST['fecha_exp'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        
        if (strlen($numTarjeta) < 13 || strlen($numTarjeta) > 19 || !ctype_digit($numTarjeta)) {
            $errores[] = 'Número de tarjeta no válido (13-19 dígitos).';
        }
        if (!preg_match('/^\d{2}\/\d{2}$/', $fechaExp)) {
            $errores[] = 'Fecha de expiración no válida (MM/AA).';
        }
        if (strlen($cvv) < 3 || strlen($cvv) > 4 || !ctype_digit($cvv)) {
            $errores[] = 'CVV no válido (3-4 dígitos).';
        }
    } elseif ($metodoPago !== 'camarero') {
        $errores[] = 'Selecciona un método de pago.';
    }
    
    if (empty($errores)) {
        $service = new PedidoAppService();
        $clienteId = $_SESSION['idUsuario'];
        $tipo = Carrito::getTipo() ?: 'local';
        $ofertas = Carrito::getOfertas();
        
        $pedido = $service->crearPedidoDesdeCarrito($clienteId, $tipo, Carrito::getItemsRaw(), $ofertas);
        
        if ($pedido) {
            // Si paga con tarjeta, marcar como pagado directamente
            if ($metodoPago === 'tarjeta') {
                $service->pagarPedido($pedido->id);
            }
            // Si paga al camarero, queda en estado 'recibido'
            
            Carrito::vaciar();
            Carrito::limpiarOfertas();
            header('Location: confirmacion.php?id=' . $pedido->id);
            exit();
        } else {
            $errores[] = 'Error al crear el pedido. Inténtalo de nuevo.';
        }
    }
}

$erroresHtml = '';
if (!empty($errores)) {
    $erroresHtml = '<div class="errores"><ul>';
    foreach ($errores as $e) {
        $erroresHtml .= "<li>$e</li>";
    }
    $erroresHtml .= '</ul></div>';
}

// HTML del resumen con descuentos
$resumenDescuentoHtml = '';
if ($descuentoTotal > 0) {
    $resumenDescuentoHtml = <<<HTML
    <p style="color:#28a745;"><strong>Descuento aplicado: -{$totalDescuentoFormateado} €</strong></p>
    <p style="font-size:1.2em;color:#28a745;margin-top:10px;"><strong>Total a pagar: {$totalConDescuentoFormateado} €</strong></p>
HTML;
} else {
    $resumenDescuentoHtml = <<<HTML
    <p style="font-size:1.2em;"><strong>Total a pagar: {$totalFormateado} €</strong></p>
HTML;
}

$contenidoPrincipal = <<<HTML
<h1>Pago del Pedido</h1>

{$erroresHtml}

<div style="background:white;padding:20px;border-radius:8px;border:1px solid #ddd;margin-bottom:20px;">
    <h3>Resumen del pedido</h3>
    <p><strong>Subtotal: {$totalFormateado} €</strong></p>
    {$resumenDescuentoHtml}
</div>

<form method="POST">
    <fieldset>
        <legend>Método de Pago</legend>
        
        <div class="opciones-radio" style="margin-bottom:20px;">
            <label class="radio-option">
                <input type="radio" name="metodo_pago" value="tarjeta" id="radio-tarjeta" checked onchange="togglePago()"> 
                💳 Pagar con Tarjeta
            </label>
            <label class="radio-option">
                <input type="radio" name="metodo_pago" value="camarero" id="radio-camarero" onchange="togglePago()"> 
                🧑‍🍳 Pagar al Camarero
            </label>
        </div>
        
        <div id="form-tarjeta">
            <div class="bloque-entrada">
                <label>Número de tarjeta:</label>
                <input type="text" name="num_tarjeta" placeholder="1234 5678 9012 3456" maxlength="19" 
                       oninput="this.value=this.value.replace(/[^\d\s]/g,'')">
            </div>
            <div style="display:flex;gap:15px;">
                <div class="bloque-entrada" style="flex:1;">
                    <label>Fecha expiración:</label>
                    <input type="text" name="fecha_exp" placeholder="MM/AA" maxlength="5"
                           oninput="if(this.value.length===2&&!this.value.includes('/'))this.value+='/'">
                </div>
                <div class="bloque-entrada" style="flex:1;">
                    <label>CVV:</label>
                    <input type="text" name="cvv" placeholder="123" maxlength="4"
                           oninput="this.value=this.value.replace(/\D/g,'')">
                </div>
            </div>
        </div>
        
        <div id="msg-camarero" style="display:none;background:#fff3cd;padding:15px;border-radius:5px;border:1px solid #ffc107;margin:15px 0;">
            <p style="margin:0;">💡 Al confirmar, tu pedido quedará pendiente de pago. Un camarero pasará a cobrarte.</p>
        </div>
        
        <div style="display:flex;gap:10px;margin-top:20px;">
            <a href="carrito.php" style="color:#666;text-decoration:none;padding:10px;">← Volver al carrito</a>
            <button type="submit" style="background:#2ecc71;color:white;border:none;padding:12px 30px;border-radius:5px;cursor:pointer;font-size:1.1em;margin-left:auto;">
                Confirmar Pedido →
            </button>
        </div>
    </fieldset>
</form>

<script>
function togglePago() {
    const tarjeta = document.getElementById('radio-tarjeta').checked;
    document.getElementById('form-tarjeta').style.display = tarjeta ? 'block' : 'none';
    document.getElementById('msg-camarero').style.display = tarjeta ? 'none' : 'block';
}
</script>
HTML;

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

<?php
require_once __DIR__ . '/../../config.php';

use es\ucm\fdi\aw\Pedido\PedidoAppService;
use es\ucm\fdi\aw\Usuarios\UsuarioDAO;

if (!estaLogueado() || !($_SESSION['esAdmin'] ?? false)) {
    header('Location: ' . RUTA_RAIZ . 'inicio.php');
    exit();
}

$service = new PedidoAppService();
$dao = new UsuarioDAO();

// Si se seleccionó un cliente
$clienteId = (int)($_GET['cliente_id'] ?? 0);
$pedidosHtml = '';

if ($clienteId) {
    $cliente = $dao->buscarPorId($clienteId);
    $pedidos = $service->getPedidosCliente($clienteId);
    
    $nombreCliente = $cliente ? "{$cliente->nombre} {$cliente->apellidos}" : 'Desconocido';
    
    $estadoLabels = [
        'nuevo' => '🆕', 'recibido' => '⏳', 'en_preparacion' => '🔄',
        'cocinando' => '🔥', 'listo_cocina' => '🍽️', 'terminado' => '📦',
        'entregado' => '✔️', 'cancelado' => '❌',
    ];
    
    if (empty($pedidos)) {
        $pedidosHtml = "<p>No hay pedidos para este cliente.</p>";
    } else {
        $pedidosHtml = "<h2>Pedidos de {$nombreCliente}</h2><table class='tabla-pedidos'>";
        $pedidosHtml .= "<thead><tr><th>Nº</th><th>Fecha</th><th>Tipo</th><th>Estado</th><th class='text-right'>Total</th></tr></thead><tbody>";
        
        foreach ($pedidos as $p) {
            $icono = $estadoLabels[$p->estado] ?? '';
            $fecha = date('d/m/Y H:i', strtotime($p->fecha_creacion));
            $total = number_format($p->total_con_iva, 2);
            $tipo = $p->tipo === 'local' ? 'Local' : 'Llevar';
            $pedidosHtml .= "<tr><td class='text-center'>#{$p->numero_pedido}</td><td>{$fecha}</td><td>{$tipo}</td><td>{$icono} {$p->estado}</td><td class='text-right'>{$total} €</td></tr>";
        }
        
        $pedidosHtml .= "</tbody></table>";
    }
}

// Lista de clientes
$usuarios = $dao->listarTodos();
$selectClientes = '<option value="">Selecciona un cliente...</option>';
foreach ($usuarios as $u) {
    $sel = ($u->id == $clienteId) ? 'selected' : '';
    $selectClientes .= "<option value='{$u->id}' {$sel}>{$u->nombreUsuario} ({$u->nombre} {$u->apellidos})</option>";
}

$tituloPagina = 'Pedidos por Cliente';

$contenidoPrincipal = <<<HTML
<h1>Consultar Pedidos por Cliente</h1>

<form method="GET" class="bg-white p-20 rounded-8 border-light mb-20">
    <label class="font-bold">Seleccionar cliente:</label>
    <div class="flex-gap-10 mt-8">
        <select name="cliente_id" style="flex:1;">
            {$selectClientes}
        </select>
        <button type="submit" class="btn-pedido btn-primary">Ver Pedidos</button>
    </div>
</form>

{$pedidosHtml}
HTML;

require RAIZ_APP . '/includes/vistas/comun/plantilla.php';

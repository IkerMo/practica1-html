<?php
// Función para obtener el menú según el rol del usuario
function getMenuPorRol() {
    $menu = [
        'titulo' => 'Menú de navegación',
        'items' => []
    ];
    
    // Si no está logueado, menú básico
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        $menu['items'] = [
            ['url' => RUTA_RAIZ . 'index.php', 'texto' => 'Portada'], // Añadimos portada para todos
            ['url' => RUTA_VISTAS . '/login.php', 'texto' => 'Iniciar sesión'],
            ['url' => RUTA_VISTAS . '/registro.php', 'texto' => 'Registrarse']
        ];
        return $menu;
    }
    
    // Usuario logueado - INICIO COMÚN (Plan de Aaron)
    // Usamos RUTA_RAIZ porque inicio.php está en la carpeta principal
    $menu['items'][] = ['url' => RUTA_RAIZ . 'inicio.php', 'texto' => 'Inicio'];
    $menu['items'][] = ['url' => RUTA_VISTAS . '/usuarios/perfil.php', 'texto' => 'Mi perfil'];
    
    // Obtenemos información de sesión
    $esCliente = $_SESSION['esCliente'] ?? false;
    $esCamarero = $_SESSION['esCamarero'] ?? false;
    $esCocinero = $_SESSION['esCocinero'] ?? false;
    $esAdmin = $_SESSION['esAdmin'] ?? false;
    
    // Menú para clientes
    if ($esCliente) {
        $menu['titulo'] = 'Menú Cliente';
        $menu['items'][] = ['url' => RUTA_VISTAS . '/productos/ListarProductos.php', 'texto' => 'Ver carta'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/cliente/nuevo-pedido.php', 'texto' => 'Nuevo pedido'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/cliente/mis-pedidos.php', 'texto' => 'Mis pedidos'];
    }
    
    // Menú para camareros
    if ($esCamarero) {
        $menu['titulo'] = 'Menú Camarero';
        $menu['items'][] = ['url' => RUTA_VISTAS . '/camarero/pedidos-pendientes.php', 'texto' => 'Gestionar Pedidos'];
    }
    
    // Menú para cocineros
    if ($esCocinero) {
        $menu['titulo'] = 'Menú Cocinero';
        $menu['items'][] = ['url' => RUTA_VISTAS . '/cocinero/pedidos.php', 'texto' => 'Pedidos en Cocina'];
    }
    
    // Menú para administradores/gerentes
    if ($esAdmin) {
        $menu['titulo'] = 'Menú Gerente';
        $menu['items'][] = ['url' => RUTA_VISTAS . '/productos/ListarProductos.php', 'texto' => 'Gestionar productos'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/categorias/listarCategorias.php', 'texto' => 'Gestionar categorías'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/usuarios/admin/listar.php', 'texto' => 'Gestionar usuarios'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/gerente/pedidos-cliente.php', 'texto' => 'Pedidos por cliente'];
    }
    
    return $menu;
}

$menu = getMenuPorRol();
?>

<nav id="sidebarIzq">
    <h3><?= htmlspecialchars($menu['titulo']) ?></h3>
    <ul>
        <?php foreach ($menu['items'] as $item): ?>
            <li>
                <a href="<?= htmlspecialchars($item['url']) ?>">
                    <?= htmlspecialchars($item['texto']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
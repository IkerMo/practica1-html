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
            ['url' => RUTA_VISTAS . '/login.php', 'texto' => 'Iniciar sesión'],
            ['url' => RUTA_VISTAS . '/registro.php', 'texto' => 'Registrarse']
        ];
        return $menu;
    }
    
    // Usuario logueado - obtenemos información de sesión
    $esCliente = $_SESSION['esCliente'] ?? false;
    $esCamarero = $_SESSION['esCamarero'] ?? false;
    $esCocinero = $_SESSION['esCocinero'] ?? false;
    $esAdmin = $_SESSION['esAdmin'] ?? false;
    
    // Menú común para todos los usuarios logueados
    $menu['items'][] = ['url' => RUTA_VISTAS . '/usuarios/perfil.php', 'texto' => 'Mi perfil'];
    
    // Menú para clientes
    if ($esCliente) {
        $menu['titulo'] = 'Menú Cliente';
        $menu['items'][] = ['url' => RUTA_VISTAS . '/cliente/carta.php', 'texto' => 'Ver carta'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/cliente/nuevo-pedido.php', 'texto' => 'Nuevo pedido'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/cliente/mis-pedidos.php', 'texto' => 'Mis pedidos'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/cliente/ofertas.php', 'texto' => 'Ofertas'];
    }
    
    // Menú para camareros
    if ($esCamarero) {
        $menu['titulo'] = 'Menú Camarero';
        $menu['items'][] = ['url' => RUTA_VISTAS . '/camarero/pedidos-pendientes.php', 'texto' => 'Pedidos pendientes'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/camarero/cobrar.php', 'texto' => 'Cobrar pedidos'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/camarero/entregar.php', 'texto' => 'Entregar pedidos'];
    }
    
    // Menú para cocineros
    if ($esCocinero) {
        $menu['titulo'] = 'Menú Cocinero';
        $menu['items'][] = ['url' => RUTA_VISTAS . '/cocinero/pedidos.php', 'texto' => 'Pedidos en cola'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/cocinero/en-preparacion.php', 'texto' => 'En preparación'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/cocinero/historial.php', 'texto' => 'Historial'];
    }
    
    // Menú para administradores/gerentes
    if ($esAdmin) {
        $menu['titulo'] = 'Menú Gerente';
        $menu['items'][] = ['url' => RUTA_VISTAS . '/productos/ListarProductos.php', 'texto' => 'Gestionar productos'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/categorias/listarCategorias.php', 'texto' => 'Gestionar categorías'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/gerente/usuarios.php', 'texto' => 'Gestionar usuarios'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/gerente/ofertas.php', 'texto' => 'Gestionar ofertas'];
        $menu['items'][] = ['url' => RUTA_VISTAS . '/gerente/estadisticas.php', 'texto' => 'Estadísticas'];
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
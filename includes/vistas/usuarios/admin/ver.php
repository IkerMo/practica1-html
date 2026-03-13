<?php
require_once __DIR__ . '/../../../config.php';
require_once RUTA_CLASES . '/Usuarios/Usuario.php';
require_once RUTA_CLASES . '/Usuarios/UsuarioDAO.php';

// Función auxiliar (debe estar definida antes de usarla)
function getIdRolPorPrioridad($prioridad) {
    $mapa = [1 => 1, 2 => 2, 3 => 3, 4 => 4];
    return $mapa[$prioridad] ?? 1;
}

// Verificar que el usuario es gerente
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || !($_SESSION['esAdmin'] ?? false)) {
    header('Location: ' . RUTA_RAIZ . 'index.php');
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: listar.php');
    exit();
}

$dao = new \es\ucm\fdi\aw\Usuarios\UsuarioDAO();
$usuario = $dao->buscarPorId($id); // Devuelve objeto UsuarioDTO

if (!$usuario) {
    header('Location: listar.php?error=usuario_no_encontrado');
    exit();
}

// Obtener roles
$roles = $dao->obtenerRoles($usuario->id);
$rolActual = 1;
$prioridadMax = -1;
foreach ($roles as $rol) {
    if ($rol['prioridad'] > $prioridadMax) {
        $prioridadMax = $rol['prioridad'];
        $rolActual = getIdRolPorPrioridad($prioridadMax); // 👈 SIN $this
    }
}
$rutaImgs = RUTA_IMGS;

$tituloPagina = 'Ver Usuario - Bistro FDI';

$rolNombres = [
    1 => 'Cliente',
    2 => 'Camarero',
    3 => 'Cocinero',
    4 => 'Gerente'
];
$rolActualNombre = $rolNombres[$rolActual] ?? 'Cliente';
$activo = isset($usuario->activo) ? ($usuario->activo ? 'Sí' : 'No') : 'Sí';
$fechaRegistro = isset($usuario->fechaRegistro) ? date('d/m/Y H:i', strtotime($usuario->fechaRegistro)) : 'Desconocida';

$contenidoPrincipal = <<<EOS
    <h1>Detalles del Usuario</h1>
    
    <div class="usuario-detalle">
        <div class="detalle-avatar">
            <img src="{$rutaImgs}/avatares/{$usuario->avatar}" alt="Avatar" class="avatar-grande">
        </div>
        
        <div class="detalle-info">
            <p><strong>ID:</strong> {$usuario->id}</p>
            <p><strong>Usuario:</strong> {$usuario->nombreUsuario}</p>
            <p><strong>Email:</strong> {$usuario->email}</p>
            <p><strong>Nombre completo:</strong> {$usuario->nombre} {$usuario->apellidos}</p>
            <p><strong>Rol actual:</strong> <span class="rol-{$rolActual}">$rolActualNombre</span></p>
            <p><strong>Activo:</strong> $activo</p>
            <p><strong>Fecha registro:</strong> $fechaRegistro</p>
        </div>
    </div>
    
    <div class="detalle-acciones">
        <a href="editar.php?id={$usuario->id}" class="btn-editar">Editar</a>
        <a href="cambiar-rol.php?id={$usuario->id}" class="btn-rol">Cambiar Rol</a>
        <a href="listar.php" class="btn-secondary">Volver al listado</a>
    </div>
EOS;

require_once __DIR__ . '/../../comun/plantilla.php';
?>
<?php
require_once __DIR__ . '/../../../config.php';
require_once RUTA_CLASES . '/Usuarios/Usuario.php';
require_once RUTA_CLASES . '/Usuarios/UsuarioDAO.php';

// Verificar que el usuario es gerente
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || !($_SESSION['esAdmin'] ?? false)) {
    header('Location: ' . RUTA_RAIZ . 'index.php');
    exit();
}

$dao = new \es\ucm\fdi\aw\Usuarios\UsuarioDAO();
$usuarios = $dao->listarTodos(); // Esto devuelve array de objetos UsuarioDTO

$tituloPagina = 'Gestionar Usuarios - Bistro FDI';

// Función auxiliar para obtener ID del rol por prioridad
function getIdRolPorPrioridad($prioridad) {
    $mapa = [
        1 => 1, // Cliente (prioridad 1)
        2 => 2, // Camarero (prioridad 2)
        3 => 3, // Cocinero (prioridad 3)
        4 => 4  // Gerente (prioridad 4)
    ];
    return $mapa[$prioridad] ?? 1;
}

$mensaje = '';
if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'usuario_borrado') {
        $mensaje = '<div class="mensaje-exito">Usuario desactivado correctamente</div>';
    }
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] === 'no_auto_borrado') {
        $mensaje = '<div class="mensaje-error">No puedes desactivar tu propia cuenta</div>';
    } elseif ($_GET['error'] === 'usuario_no_encontrado') {
        $mensaje = '<div class="mensaje-error">Usuario no encontrado</div>';
    }
}

// Generar tabla de usuarios
$tablaUsuarios = '';
foreach ($usuarios as $usuario) { // $usuario es un objeto UsuarioDTO
    // Obtener roles del usuario
    $roles = $dao->obtenerRoles($usuario->id);
    
    // Determinar el rol con mayor prioridad
    $rolActual = 1; // Cliente por defecto
    $prioridadMax = -1;
    foreach ($roles as $rol) {
        if ($rol['prioridad'] > $prioridadMax) {
            $prioridadMax = $rol['prioridad'];
            $rolActual = getIdRolPorPrioridad($prioridadMax);
        }
    }
    
    $rolNombres = [
        1 => 'Cliente',
        2 => 'Camarero',
        3 => 'Cocinero',
        4 => 'Gerente'
    ];
    
    $rolNombre = $rolNombres[$rolActual] ?? 'Cliente';
    
    // 👇 MEJORA: Manejar el estado activo/inactivo
    $activo = isset($usuario->activo) ? $usuario->activo : true;
    $activoTexto = $activo ? 'Sí' : '<span class="inactivo">No</span>';
    $filaClase = $activo ? '' : 'usuario-inactivo';
    
    // Construir acciones según estado
    $acciones = '<a href="ver.php?id=' . $usuario->id . '" class="btn-ver">Ver</a>';
    $acciones .= '<a href="editar.php?id=' . $usuario->id . '" class="btn-editar">Editar</a>';
    $acciones .= '<a href="cambiar-rol.php?id=' . $usuario->id . '" class="btn-rol">Cambiar Rol</a>';
    
    // 👇 Solo mostrar botón de desactivar si está activo
    if ($activo) {
        $acciones .= '<a href="borrar.php?id=' . $usuario->id . '" class="btn-borrar" onclick="return confirm(\'¿Estás seguro de que quieres desactivar este usuario?\')">Desactivar</a>';
    } else {
        $acciones .= '<span class="btn-desactivado">Desactivado</span>';
    }
    
    $tablaUsuarios .= <<<EOS
    <tr class="$filaClase">
        <td>{$usuario->id}</td>
        <td>{$usuario->nombreUsuario}</td>
        <td>{$usuario->email}</td>
        <td>{$usuario->nombre} {$usuario->apellidos}</td>
        <td><span class="rol-{$rolActual}">$rolNombre</span></td>
        <td>$activoTexto</td>
        <td class="acciones">$acciones</td>
    </tr>
EOS;
}

$contenidoPrincipal = <<<EOS
    <h1>Gestión de Usuarios</h1>
    
    $mensaje

    <div class="acciones-globales">
        <a href="crear.php" class="btn-primary">+ Nuevo Usuario</a>
        <a href="../../../index.php" class="btn-secondary">Volver al inicio</a>
    </div>
    
    <table class="tabla-usuarios">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Nombre</th>
                <th>Rol</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            $tablaUsuarios
        </tbody>
    </table>
EOS;

require_once __DIR__ . '/../../comun/plantilla.php';
?>
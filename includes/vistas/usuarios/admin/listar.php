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
            // 👇 AQUÍ: llamar a la función SIN $this
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
    $activo = isset($usuario->activo) ? ($usuario->activo ? 'Sí' : 'No') : 'Sí';
    
    $tablaUsuarios .= <<<EOS
    <tr>
        <td>{$usuario->id}</td>
        <td>{$usuario->nombreUsuario}</td>
        <td>{$usuario->email}</td>
        <td>{$usuario->nombre} {$usuario->apellidos}</td>
        <td><span class="rol-{$rolActual}">$rolNombre</span></td>
        <td>$activo</td>
        <td class="acciones">
            <a href="ver.php?id={$usuario->id}" class="btn-ver">Ver</a>
            <a href="editar.php?id={$usuario->id}" class="btn-editar">Editar</a>
            <a href="cambiar-rol.php?id={$usuario->id}" class="btn-rol">Cambiar Rol</a>
            <a href="borrar.php?id={$usuario->id}" class="btn-borrar" onclick="return confirm('¿Estás seguro de que quieres borrar este usuario?')">Borrar</a>
        </td>
    </tr>
EOS;
}

$contenidoPrincipal = <<<EOS
    <h1>Gestión de Usuarios</h1>
    
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
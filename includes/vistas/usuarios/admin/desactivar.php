<?php
require_once __DIR__ . '/../../../config.php';
require_once RUTA_CLASES . '/Usuarios/Usuario.php';
require_once RUTA_CLASES . '/Usuarios/UsuarioDAO.php';

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

// No permitir desactivarse a sí mismo
if ($id == $_SESSION['idUsuario']) {
    header('Location: listar.php?error=no_auto_desactivado');
    exit();
}

// Obtener el usuario
$usuario = \es\ucm\fdi\aw\Usuarios\Usuario::buscaUsuarioPorId($id);

if (!$usuario) {
    header('Location: listar.php?error=usuario_no_encontrado');
    exit();
}

// Procesar confirmación
$mensajeError = '';
if (isset($_POST['confirmar']) && $_POST['confirmar'] === 'si') {
    // Desactivar usuario: establecer activo = 0
    if ($usuario->actualiza(['activo' => 0])) {
        header('Location: listar.php?mensaje=usuario_desactivado');
        exit();
    } else {
        $mensajeError = 'Error al desactivar el usuario';
    }
}

$tituloPagina = 'Desactivar Usuario - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <h1>Confirmar desactivación</h1>
    
    <div class="confirmar-borrado">
        $mensajeError
        <p>¿Estás seguro de que quieres desactivar al usuario <strong>{$usuario->getNombreUsuario()}</strong>?</p>
        <p>Email: {$usuario->getEmail()}</p>
        <p>Nombre: {$usuario->getNombreCompleto()}</p>
        <p>Estado actual: <strong>Activo</strong></p>
        
        <form method="post" class="inline">
            <input type="hidden" name="confirmar" value="si">
            <button type="submit" class="btn-borrar">Sí, desactivar</button>
            <a href="listar.php" class="btn-secondary">Cancelar</a>
        </form>
        <p><small>Nota: El usuario se desactivará pero sus datos permanecerán en el sistema.</small></p>
    </div>
EOS;

require_once __DIR__ . '/../../comun/plantilla.php';
?>
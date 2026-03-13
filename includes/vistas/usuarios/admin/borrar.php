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

// No permitir borrarse a sí mismo
if ($id == $_SESSION['idUsuario']) {
    header('Location: listar.php?error=no_auto_borrado');
    exit();
}

$dao = new \es\ucm\fdi\aw\Usuarios\UsuarioDAO();
$usuario = $dao->getPorId($id);

if (!$usuario) {
    header('Location: listar.php?error=usuario_no_encontrado');
    exit();
}

// Procesar confirmación
if (isset($_POST['confirmar']) && $_POST['confirmar'] === 'si') {
    // Borrado lógico (opcional - puedes poner activo = 0)
    $sql = "UPDATE Usuarios SET activo = 0 WHERE id = ?";
    $stmt = $dao->conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        header('Location: listar.php?mensaje=usuario_borrado');
        exit();
    } else {
        $error = 'Error al borrar el usuario';
    }
}

$tituloPagina = 'Borrar Usuario - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <h1>Confirmar borrado</h1>
    
    <div class="confirmar-borrado">
        <p>¿Estás seguro de que quieres borrar al usuario <strong>{$usuario['nombreUsuario']}</strong>?</p>
        <p>Email: {$usuario['email']}</p>
        <p>Nombre: {$usuario['nombre']} {$usuario['apellidos']}</p>
        
        <form method="post" class="inline">
            <input type="hidden" name="confirmar" value="si">
            <button type="submit" class="btn-borrar">Sí, borrar</button>
            <a href="listar.php" class="btn-secondary">Cancelar</a>
        </form>
    </div>
EOS;

require_once __DIR__ . '/../../comun/plantilla.php';
?>
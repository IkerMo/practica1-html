<?php
require_once __DIR__ . '/../../config.php';
require_once RUTA_CLASES . '/Usuarios/Usuario.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

// DEPURACIÓN: Mostrar lo que hay en sesión
error_log("=== DEPURACIÓN PERFIL ===");
error_log("Sesión completa: " . print_r($_SESSION, true));

$idUsuario = $_SESSION['idUsuario'] ?? 0;
error_log("ID Usuario de sesión: " . $idUsuario);

$usuario = \es\ucm\fdi\aw\Usuarios\Usuario::buscaUsuarioPorId($idUsuario);

// Obtener datos del usuario de la sesión
if (!$usuario) {
    error_log("ERROR: Usuario no encontrado con ID: " . $idUsuario);
    // Redirigir a logout para limpiar sesión corrupta
    header('Location: ' . RUTA_VISTAS . '/logout.php');
    exit();
}

$rutaImgs = RUTA_IMGS;
$tituloPagina = 'Mi Perfil - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <h1>Mi Perfil</h1>
    
    <div class="perfil-container">
        <div class="perfil-avatar">
            <img src="{$rutaImgs}/avatares/{$usuario->getAvatar()}" alt="Avatar" class="avatar-grande">
        </div>
        
        <div class="perfil-datos">
            <p><strong>Nombre de usuario:</strong> {$usuario->getNombreUsuario()}</p>
            <p><strong>Email:</strong> {$usuario->getEmail()}</p>
            <p><strong>Nombre completo:</strong> {$usuario->getNombreCompleto()}</p>
            <p><strong>Rol:</strong> {$_SESSION['rolNombre']}</p>
        </div>
        
        <div class="perfil-acciones">
            <a href="editar-perfil.php" class="btn-primary">Editar perfil</a>
            <a href="cambiar-password.php" class="btn-secondary">Cambiar contraseña</a>
        </div>
    </div>
EOS;

require_once __DIR__ . '/../comun/plantilla.php';
?>
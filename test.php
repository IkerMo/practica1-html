<?php
require_once 'includes/config.php';
require_once RUTA_CLASES . '/Usuarios/Usuario.php';

echo "<h1>Depuración de Usuario</h1>";

echo "<h2>Contenido de SESIÓN:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['idUsuario'])) {
    $id = $_SESSION['idUsuario'];
    echo "<h2>Buscando usuario con ID: $id</h2>";
    
    $usuario = \es\ucm\fdi\aw\Usuarios\Usuario::buscaUsuarioPorId($id);
    
    if ($usuario) {
        echo "<p style='color:green'>✅ Usuario encontrado</p>";
        echo "<p>Nombre: " . $usuario->getNombre() . "</p>";
        echo "<p>Usuario: " . $usuario->getNombreUsuario() . "</p>";
        echo "<p>Email: " . $usuario->getEmail() . "</p>";
    } else {
        echo "<p style='color:red'>❌ Usuario NO encontrado</p>";
    }
} else {
    echo "<p style='color:red'>❌ No hay idUsuario en sesión</p>";
}
?>
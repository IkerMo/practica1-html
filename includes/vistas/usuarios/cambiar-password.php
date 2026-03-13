<?php
require_once __DIR__ . '/../../config.php';
require_once RUTA_CLASES . '/Usuarios/Usuario.php';
require_once RUTA_CLASES . '/Formularios/FormularioCambiarPassword.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$form = new \es\ucm\fdi\aw\Formularios\FormularioCambiarPassword();
$htmlForm = $form->gestiona();

$tituloPagina = 'Cambiar Contraseña - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <h1>Cambiar Contraseña</h1>
    $htmlForm
EOS;

require_once __DIR__ . '/../comun/plantilla.php';
?>
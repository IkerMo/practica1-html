<?php
require_once __DIR__ . '/../../config.php';
require_once RUTA_CLASES . '/Usuarios/Usuario.php';
require_once RUTA_CLASES . '/Formularios/FormularioEditarPerfil.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$form = new \es\ucm\fdi\aw\Formularios\FormularioEditarPerfil();
$htmlForm = $form->gestiona();

$tituloPagina = 'Editar Perfil - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <h1>Editar Perfil</h1>
    $htmlForm
EOS;

require_once __DIR__ . '/../comun/plantilla.php';
?>
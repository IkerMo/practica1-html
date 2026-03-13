<?php
require_once __DIR__ . '/../../../config.php';
require_once RUTA_CLASES . '/Formularios/FormularioCrearUsuarioAdmin.php';

// Verificar que el usuario es gerente
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || !($_SESSION['esAdmin'] ?? false)) {
    header('Location: ' . RUTA_RAIZ . 'index.php');
    exit();
}

$form = new \es\ucm\fdi\aw\Formularios\FormularioCrearUsuarioAdmin();
$htmlForm = $form->gestiona();

$tituloPagina = 'Crear Usuario - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <h1>Crear Nuevo Usuario</h1>
    $htmlForm
    <div class="enlace-volver">
        <a href="listar.php">← Volver al listado</a>
    </div>
EOS;

require_once __DIR__ . '/../../comun/plantilla.php';
?>
<?php
require_once __DIR__ . '/../../../config.php';
require_once RUTA_CLASES . '/Formularios/FormularioCambiarRolAdmin.php';

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

$form = new \es\ucm\fdi\aw\Formularios\FormularioCambiarRolAdmin($id);
$htmlForm = $form->gestiona();

$tituloPagina = 'Cambiar Rol - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <h1>Cambiar Rol de Usuario</h1>
    $htmlForm
    <div class="enlace-volver">
        <a href="listar.php">← Volver al listado</a>
    </div>
EOS;

require_once __DIR__ . '/../../comun/plantilla.php';
?>
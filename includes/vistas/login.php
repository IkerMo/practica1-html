<?php
require_once __DIR__ . '/../config.php';
require_once RAIZ_APP . '/includes/clases/FormularioLogin.php';

$form = new FormularioLogin();
$htmlFormLogin = $form->gestiona();

$tituloPagina = 'Login - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <div class="contenedor-formulario">
        <h1>Acceso a Bistro FDI</h1>
        $htmlFormLogin
    </div>
EOS;

require_once __DIR__ . '/comun/plantilla.php';
?>
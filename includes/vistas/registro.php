<?php
require_once __DIR__ . '/../config.php';
require_once RAIZ_APP . '/includes/clases/Formularios/FormularioRegistro.php';

$form = new \es\ucm\fdi\aw\FormularioRegistro(); 
$htmlFormRegistro = $form->gestiona();

$tituloPagina = 'Registro - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <div class="contenedor-formulario">
        <h1>Regístrate en Bistro FDI</h1>
        $htmlFormRegistro
    </div>
EOS;

require_once __DIR__ . '/comun/plantilla.php';

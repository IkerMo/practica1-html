<?php
require_once __DIR__ . '/../config.php';
require_once RAIZ_APP . '/includes/clases/Usuario.php';

Usuario::logout();

$rutaRaiz = RUTA_RAIZ;
$tituloPagina = 'Hasta pronto - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <div class="contenedor-logout">
        <h1>¡Hasta pronto!</h1>
        <p>Has cerrado sesión correctamente.</p>
        <p><a href="{$rutaRaiz}index.php">Volver al inicio</a></p>
    </div>
EOS;

require_once __DIR__ . '/comun/plantilla.php';
?>
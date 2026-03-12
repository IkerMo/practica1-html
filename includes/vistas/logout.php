<?php
require_once __DIR__ . '/../config.php';

\es\ucm\fdi\aw\Usuarios\Usuario::logout();

$rutaRaiz = RUTA_RAIZ;
$tituloPagina = 'Hasta pronto - Bistro FDI';

$contenidoPrincipal = <<<EOS
<div class="contenedor-logout" style="text-align: center; margin-top: 50px;">
    <h1>¡Hasta pronto!</h1>
    <p>Has cerrado sesión correctamente.</p>
    <p><a href="{$rutaRaiz}index.php" class="boton">Volver al inicio</a></p>
</div>
EOS;

require_once RAIZ_APP . '/includes/vistas/comun/plantilla.php';
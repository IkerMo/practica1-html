<?php

require_once __DIR__ . '/../../config.php';

function mostrarSaludoDer() {
    if (isset($_SESSION['login']) && ($_SESSION['login']===true)) {
        return "Bienvenido, {$_SESSION['nombre']} <a href='" . RUTA_VISTAS . "/logout.php'>(salir)</a>";
    } 
}
    
?>
<navder id="sidebarIzq">
    <h3>Navegación</h3>
    <ul>
        <li><a href="<?= RUTA_RAIZ ?>index.php">Inicio</a></li>
        <li><a href="<?= RUTA_VISTAS ?>/contacto.php">Contacto</a></li>
        <li><a href="<?= RUTA_VISTAS ?>/detalles.php">Detalles</a></li>
        <li><a href="<?= RUTA_VISTAS ?>/bocetos.php">Bocetos</a></li>
        <li><a href="<?= RUTA_VISTAS ?>/miembros.php">Miembros</a></li>
        <li><a href="<?= RUTA_VISTAS ?>/planificacion.php">Planificación</a></li>

    </ul>

    <div class="saludo"><?= mostrarSaludoDer(); ?></div>
</navder>
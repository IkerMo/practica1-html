<?php
// AL PRINCIPIO DE LA PLANTILLA, AÑADE ESTO:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Luego sigue tu código normal
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $tituloPagina ?? 'Sin título' ?></title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="<?= RUTA_CSS ?? '' ?>/index.css" />
</head>
<body>
    <div id="contenedor">
        <?php
            // Verificar que los archivos existen antes de incluirlos
            $cabecera = RAIZ_APP . '/includes/vistas/comun/cabecera.php';
            $navIzq = RAIZ_APP . '/includes/vistas/comun/navIzq.php';
            $pie = RAIZ_APP . '/includes/vistas/comun/pie.php';
            
            echo "<!-- Cabecera existe: " . (file_exists($cabecera) ? 'SI' : 'NO') . " -->\n";
            echo "<!-- NavIzq existe: " . (file_exists($navIzq) ? 'SI' : 'NO') . " -->\n";
            echo "<!-- Pie existe: " . (file_exists($pie) ? 'SI' : 'NO') . " -->\n";
            
            if (file_exists($cabecera)) include $cabecera; else echo "<p>Error: No se encuentra cabecera</p>";
            if (file_exists($navIzq)) include $navIzq; else echo "<p>Error: No se encuentra navegación</p>";
        ?>

        <main>
            <?php 
                if (isset($contenidoPrincipal)) {
                    echo $contenidoPrincipal; 
                } else {
                    echo "<p style='color:red; font-weight:bold'>ERROR: \$contenidoPrincipal no está definido</p>";
                }
            ?>
        </main>

        <?php
            if (file_exists($pie)) include $pie; else echo "<p>Error: No se encuentra pie</p>";
        ?>
    </div> 
</body>
</html>
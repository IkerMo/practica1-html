<?php
// PLANTILLA DE DEPURACIÓN EXTREMA
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Buffer de salida para capturar errores
ob_start();

echo "<!-- INICIO PLANTILLA -->\n";

// Verificar variables
echo "<!-- Titulo: " . ($tituloPagina ?? 'NO DEFINIDO') . " -->\n";

try {
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
        <div style="background: yellow; padding: 10px; margin: 10px; border: 2px solid red;">
            <h3>🔍 MODO DEPURACIÓN ACTIVADO</h3>
            <?php
            echo "<p>RAIZ_APP: " . (defined('RAIZ_APP') ? RAIZ_APP : 'NO DEFINIDA') . "</p>";
            echo "<p>RUTA_VISTAS: " . (defined('RUTA_VISTAS') ? RUTA_VISTAS : 'NO DEFINIDA') . "</p>";
            
            $cabecera = RAIZ_APP . '/includes/vistas/comun/cabecera.php';
            $navIzq = RAIZ_APP . '/includes/vistas/comun/navIzq.php';
            $pie = RAIZ_APP . '/includes/vistas/comun/pie.php';
            
            echo "<p>Cabecera existe: " . (file_exists($cabecera) ? '✅ SI' : '❌ NO') . " - $cabecera</p>";
            echo "<p>NavIzq existe: " . (file_exists($navIzq) ? '✅ SI' : '❌ NO') . " - $navIzq</p>";
            echo "<p>Pie existe: " . (file_exists($pie) ? '✅ SI' : '❌ NO') . " - $pie</p>";
            ?>
        </div>

        <?php
        echo "<!-- Antes de incluir cabecera -->\n";
        if (file_exists($cabecera)) {
            echo "<!-- Incluyendo cabecera... -->\n";
            include $cabecera;
            echo "<!-- Cabecera incluida OK -->\n";
        } else {
            echo "<p style='color:red'>ERROR: No se encuentra cabecera</p>";
        }
        
        echo "<!-- Antes de incluir navIzq -->\n";
        if (file_exists($navIzq)) {
            echo "<!-- Incluyendo navIzq... -->\n";
            include $navIzq;
            echo "<!-- navIzq incluido OK -->\n";
        } else {
            echo "<p style='color:red'>ERROR: No se encuentra navIzq</p>";
        }
        ?>

        <main>
            <?php 
            echo "<!-- Dentro de main -->\n";
            if (isset($contenidoPrincipal)) {
                echo "<!-- Mostrando contenidoPrincipal -->\n";
                echo $contenidoPrincipal;
                echo "<!-- Fin contenidoPrincipal -->\n";
            } else {
                echo "<p style='color:red; font-weight:bold'>ERROR: \$contenidoPrincipal NO ESTÁ DEFINIDO</p>";
            }
            ?>
        </main>

        <?php
        echo "<!-- Antes de incluir pie -->\n";
        if (file_exists($pie)) {
            echo "<!-- Incluyendo pie... -->\n";
            include $pie;
            echo "<!-- pie incluido OK -->\n";
        } else {
            echo "<p style='color:red'>ERROR: No se encuentra pie</p>";
        }
        ?>
    </div>
</body>
</html>
<?php
    echo "<!-- FIN PLANTILLA -->\n";
    
    // Mostrar el contenido del buffer
    $contenido = ob_get_clean();
    echo $contenido;
    
} catch (Throwable $e) {
    // Capturar cualquier error fatal
    ob_clean();
    echo "<div style='background: red; color: white; padding: 20px; margin: 20px;'>";
    echo "<h1>🔥 ERROR FATAL CAPTURADO</h1>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>
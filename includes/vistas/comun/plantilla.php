<!DOCTYPE html>
<html>
<head>
    <title><?= $tituloPagina ?></title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="<?= RUTA_CSS ?>/index.css" />
</head>
<body>
    <div id="contenedor">
        <?php
            include RAIZ_APP . '/includes/vistas/comun/cabecera.php';
            include RAIZ_APP . '/includes/vistas/comun/navIzq.php';
        ?>

        <main>
            <?= $contenidoPrincipal ?>
        </main>

        <?php
            include RAIZ_APP . '/includes/vistas/comun/pie.php';
        ?>
    </div> 
</body>
</html
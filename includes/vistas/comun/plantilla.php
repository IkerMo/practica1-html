<!DOCTYPE html>
<html>
<head>
    <title><?= $tituloPagina ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="<?= RUTA_CSS ?>/index.css" />
    <link rel="stylesheet" type="text/css" media="screen and (max-width: 768px)" href="<?= RUTA_CSS ?>/movil.css" />
</head>
<body>
    <div class="container">
        <!-- solo en movil -->
        <input type="checkbox" id="hamburger-toggle" class="hamburger-toggle" aria-hidden="true">

        <?php
            include RAIZ_APP . '/includes/vistas/comun/cabecera.php';
        ?>

        <div class="content">
            <?php
                include RAIZ_APP . '/includes/vistas/comun/navIzq.php';
            ?>
            <main>
                <?= $contenidoPrincipal ?>
            </main>
            <?php
                include RAIZ_APP . '/includes/vistas/comun/navDer.php';
            ?>
        </div>

        <?php
            include RAIZ_APP . '/includes/vistas/comun/pie.php';
        ?>
    </div> 
</body>
</html>
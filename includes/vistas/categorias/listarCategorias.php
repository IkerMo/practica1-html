<?php
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\Categoria\CategoriaAppService;

// 1. Verificamos si existe la sesión de login Y si el objeto usuario está ahí
if (!isset($_SESSION['login']) || !isset($_SESSION['usuario'])) {
    // Si no hay usuario, redirigimos al login
    header('Location: ' . $app->resuelve('/login.php'));
    exit();
}

/** @var es\ucm\fdi\aw\Usuarios\Usuario $usuario */
$usuario = $_SESSION['usuario'];

// 2. Ahora sí es seguro llamar al método
$rol = $usuario->getRolActual();
$service = new CategoriaAppService();
$categorias = $service->getTodasCategorias();

$tituloPagina = 'Categorías del Restaurante';

$contenidoPrincipal = "<h1>Categorías</h1>";

if ($rol === 'Gerente') {
    $contenidoPrincipal .= <<<HTML
        <div style="margin-bottom: 20px;">
            <a href="formularioCategoria.php" class="btn btn-primario">+ Nueva Categoría</a>
        </div>
HTML;
}

$contenidoPrincipal .= '<div class="categorias-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">';

foreach ($categorias as $cat) {
    $img = $cat->imagen ?: 'default_cat.png';
    
    $contenidoPrincipal .= "
    <div class='card-categoria' style='border: 1px solid #ddd; padding: 15px; border-radius: 10px; text-align: center; background: white;'>
        <img src='{$app->resuelve('/img/categorias/'.$img)}' style='width: 100%; height: 150px; object-fit: cover; border-radius: 5px;'>
        <h3 style='margin: 15px 0;'>{$cat->nombre}</h3>
        <p style='color: #666; font-size: 0.9em;'>{$cat->descripcion}</p>
        
        <a href='../productos/listarProductos.php?categoria={$cat->nombre}' class='btn' style='display:block; margin-top:10px; font-size: 0.8em;'>Ver productos</a>";

    if ($rol === 'Gerente') {
        $contenidoPrincipal .= "
        <div style='margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;'>
            <a href='formularioCategoria.php?id={$cat->id}' class='btn-edit'>Editar</a>
            <a href='borrarCategoria.php?id={$cat->id}' class='btn-delete' onclick='return confirm(\"¿Borrar categoría?\")'>Borrar</a>
        </div>";
    }

    $contenidoPrincipal .= "</div>";
}

$contenidoPrincipal .= '</div>';

require __DIR__.'/../plantillas/layout.php';
<?php
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\Categoria\CategoriaAppService;

// 1. SEGURIDAD: Usamos la función de config.php para evitar errores de objeto
if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

// 2. RECUPERAR DATOS DE ROL (Variables simples de sesión)
$esGerente = $_SESSION['esAdmin'] ?? false;
$idRol = $_SESSION['rol'] ?? null;

$service = new CategoriaAppService();
$categorias = $service->getTodasCategorias();

$tituloPagina = 'Categorías del Restaurante';
$contenidoPrincipal = "<h1>Categorías</h1>";

// Botón para Gerentes (Rol 4)
if ($esGerente) {
    $contenidoPrincipal .= <<<HTML
        <div style="margin-bottom: 20px;">
            <a href="formularioCategoria.php" class="btn btn-primario" style="background:#2ecc71; color:white; padding:10px; text-decoration:none; border-radius:5px;">+ Nueva Categoría</a>
        </div>
HTML;
}

$contenidoPrincipal .= '<div class="categorias-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">';

foreach ($categorias as $cat) {
    // Usamos RUTA_BASE en lugar de resuelve()
    $img = !empty($cat->imagen) ? $cat->imagen : 'default_cat.png';
    $urlImagen = RUTA_BASE . '/img/categorias/' . $img;
    
    $contenidoPrincipal .= "
    <div class='card-categoria' style='border: 1px solid #ddd; padding: 15px; border-radius: 10px; text-align: center; background: white;'>
        <img src='{$urlImagen}' style='width: 100%; height: 150px; object-fit: cover; border-radius: 5px;'>
        <h3 style='margin: 15px 0;'>{$cat->nombre}</h3>
        <p style='color: #666; font-size: 0.9em;'>{$cat->descripcion}</p>
        
        <a href='../productos/ListarProductos.php?categoria={$cat->id}' class='btn' style='display:block; margin-top:10px; font-size: 0.8em; background:#eee; padding:5px; text-decoration:none; color:black;'>Ver productos</a>";

    if ($esGerente) {
        $contenidoPrincipal .= "
        <div style='margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;'>
            <a href='formularioCategoria.php?id={$cat->id}' class='btn-edit' style='color:#3498db; margin-right:10px;'>Editar</a>
            <a href='borrarCategoria.php?id={$cat->id}' class='btn-delete' style='color:#e74c3c;' onclick='return confirm(\"¿Borrar categoría?\")'>Borrar</a>
        </div>";
    }

    $contenidoPrincipal .= "</div>";
}

$contenidoPrincipal .= '</div>';

// 3. CARGAR PLANTILLA (Asegúrate de que la ruta sea correcta)
require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
<?php
// Archivo: includes/vistas/vistasProductos.php

/**
 * Renderiza la barra de filtros de categorías
 */
function renderizaFiltrosCategorias($objetosCategorias) {
    $html = '<div class="filtros mb-20">';
    $html .= '<a href="ListarProductos.php?categoria=todas" class="btn">Todas</a> ';

    foreach ($objetosCategorias as $cat) {
        $html .= "<a href='ListarProductos.php?categoria={$cat->id}' class='btn'>{$cat->nombre}</a> ";
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Renderiza el grid de productos
 */
function renderizaListaProductos($productos, $mapaCategorias, $esGerente) {
    if (empty($productos)) {
        return '<p>No hay productos disponibles en esta categoría.</p>';
    }

    $html = '<div class="productos-grid">';

    foreach ($productos as $p) {
        $nombreImagen = !empty($p->imagen_principal) ? $p->imagen_principal : 'default.jpg';
        $urlImg = RUTA_BASE . "/IMG/productos/" . $nombreImagen;
        $nombreCat = $mapaCategorias[$p->categoria_id] ?? "Sin categoría";
        $precioFormateado = number_format($p->getPrecioFinal(), 2);

        $html .= "
        <div class='pedido-card'>
            <img src='$urlImg' class='img-card' alt='$p->nombre'>
            <small class='color-gray'>$nombreCat</small>
            <h3 class='mb-5'>$p->nombre</h3>
            <p><strong class='font-bold'>{$precioFormateado} €</strong></p>
            <a href='detalleProducto.php?id={$p->id}' class='btn-detalle'>Ver detalles</a>";
            
        if ($esGerente) {
            $html .= "
            <div class='mt-8 pt-10 border-top font-small'>
                <a href='formularioProducto.php?id={$p->id}' class='color-blue mr-10 no-decoration'>Editar</a>
                <a href='borrarProductos.php?id={$p->id}' class='color-red no-decoration' 
                   onclick='return confirm(\"¿Borrar producto?\")'>Borrar</a>
            </div>";
        }

        $html .= "</div>"; // Cierra pedido-card
    }

    $html .= '</div>'; // Cierra productos-grid
    return $html;
}
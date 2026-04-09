<?php
// Archivo: includes/vistas/helpers/vistaCategorias.php

function renderizaListaCategorias($categorias, $esGerente) {
    if (empty($categorias)) {
        return '<p class="text-gray-center">No hay categorías disponibles.</p>';
    }

    $html = '<div class="categorias-grid">';

    foreach ($categorias as $cat) {
        $img = !empty($cat->imagen) ? $cat->imagen : 'default_cat.png';
        $urlImagen = RUTA_BASE . '/img/categorias/' . $img;
        $urlVer = RUTA_BASE . "/includes/vistas/productos/ListarProductos.php?categoria={$cat->id}";

        $html .= "
        <div class='pedido-card text-center'>
            <img src='{$urlImagen}' class='img-card' alt='{$cat->nombre}'>
            <h3 class='mt-15 mb-15'>{$cat->nombre}</h3>
            <p class='color-gray font-small'>{$cat->descripcion}</p>
            <a href='{$urlVer}' class='btn-detalle font-small'>Ver productos</a>";

        if ($esGerente) {
            $html .= "
            <div class='mt-15 pt-10 border-top font-small'>
                <a href='formularioCategoria.php?id={$cat->id}' class='color-blue mr-10 no-decoration'>Editar</a>
                <a href='borrarCategoria.php?id={$cat->id}' class='color-red no-decoration' 
                   onclick='return confirm(\"¿Borrar categoría?\")'>Borrar</a>
            </div>";
        }

        $html .= "</div>"; // Cierra pedido-card
    }

    $html .= '</div>'; // Cierra categorias-grid
    return $html;
}
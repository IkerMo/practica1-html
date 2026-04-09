<?php
// vistas/productos/formularioProducto.php

require_once __DIR__.'/../../config.php';

// Importamos la clase del formulario
use es\ucm\fdi\aw\Formularios\FormularioProducto;

// --- SEGURIDAD DE ACCESO ---
// 1. Comprobar si está logueado (usando la función de tu config.php)
if (!estaLogueado()) {
    header('Location: ' . RUTA_BASE . '/login.php');
    exit();
}

// 2. Comprobar si es GERENTE
// Usamos la misma lógica que en ListarProductos: $_SESSION['esAdmin']
$esGerente = $_SESSION['esAdmin'] ?? false;

if (!$esGerente) {
    // Si no es gerente, le denegamos el acceso
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Error 403</h1><p>No tienes permisos para gestionar productos.</p>";
    require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
    exit();
}

// --- LÓGICA DEL FORMULARIO ---

// Si nos llega un ID por la URL, es que estamos EDITANDO
$idProducto = $_GET['id'] ?? null;

// Instanciamos nuestra clase Formulario
$form = new FormularioProducto($idProducto);

// El método gestiona() genera el HTML o procesa el POST
$htmlFormulario = $form->gestiona();

// --- PREPARAR LA SALIDA ---

$tituloPagina = $idProducto ? 'Editar Producto' : 'Nuevo Producto';

$contenidoPrincipal = <<<HTML
    <div class="mb-20">
        <a href="ListarProductos.php" class="color-gray font-small flex-gap-10 no-decoration">
            <span class="font-lg">←</span> Volver al listado de productos
        </a>
    </div>
    <h1 class="mt-0">{$tituloPagina}</h1>
    <div class="contenedor-formulario bg-white p-20 rounded-8 border-light">
        $htmlFormulario
    </div>
HTML;

// Usamos la ruta de plantilla que usas en el resto de archivos
require RAIZ_APP . '/includes/vistas/comun/plantilla.php';
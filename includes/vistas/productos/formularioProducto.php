<?php
// vistas/productos/formularioProducto.php

require_once __DIR__.'/../../config.php';

// Importamos la clase del formulario que definimos antes
use es\ucm\fdi\aw\Formularios\FormularioProducto;

// --- REGLA DE ORO 2: SEGURIDAD DE ACCESO A LA PÁGINA ---
// 1. Comprobar si está logueado
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    header('Location: ' . $app->resuelve('/login.php'));
    exit();
}

// 2. Comprobar si es GERENTE (Solo ellos pueden ver esta página)
/** @var es\ucm\fdi\aw\Usuarios\Usuario $usuario */
$usuario = $_SESSION['usuario'];
if ($usuario->getRolActual() !== 'Gerente') {
    // Si no es gerente, le denegamos el acceso (puedes redirigir o mostrar error)
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Error 403</h1><p>No tienes permisos para gestionar productos.</p>";
    require __DIR__.'/../plantillas/layout.php';
    exit();
}

// --- LÓGICA DEL FORMULARIO ---

// Si nos llega un ID por la URL, es que estamos EDITANDO
$idProducto = $_GET['id'] ?? null;

// Instanciamos nuestra clase Formulario
$form = new FormularioProducto($idProducto);

// El método gestiona() hace toda la magia: 
// - Si es GET: genera el HTML del formulario.
// - Si es POST: procesa los datos y redirige si todo está OK.
$htmlFormulario = $form->gestiona();

// --- PREPARAR LA SALIDA PARA LA PLANTILLA ---

$tituloPagina = $idProducto ? 'Editar Producto' : 'Nuevo Producto';

$contenidoPrincipal = <<<HTML
    <nav class="breadcrumb">
        <a href="listarProductos.php">← Volver al listado</a>
    </nav>
    <h1>{$tituloPagina}</h1>
    <div class="contenedor-formulario">
        $htmlFormulario
    </div>
HTML;

// Inyectamos todo en el layout común que tiene el sidebar dinámico
require __DIR__.'/../plantillas/layout.php';
<?php
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\Categoria\CategoriaAppService;

// 1. SEGURIDAD: Solo el Gerente puede borrar
// Usamos la función esAdmin() de tu config.php
if (!esAdmin()) {
    // Si intentan acceder sin ser admin, los echamos con un error
    http_response_code(403);
    die("Acceso denegado: No tienes permisos para borrar categorías.");
}

// 2. PROCESO DE BORRADO
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $service = new CategoriaAppService();
    // Intentamos borrar la categoría
    $res = $service->borrarCategoria($id);
    
    if (!$res) {
        // Opcional: podrías guardar un mensaje de error en la sesión 
        // para mostrarlo en la lista (ej: si tiene productos asociados)
    }
}

// 3. REDIRECCIÓN
header('Location: listarCategorias.php');
exit();
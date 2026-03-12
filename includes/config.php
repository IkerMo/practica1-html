<?php
/**
 * Configuración básica de la aplicación Bistro FDI
 */

// Mostrar errores (quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Constantes de rutas
define('RAIZ_APP', dirname(__DIR__)); // C:\xampp\htdocs\Practica1
define('RUTA_BASE', '/Practica1');
define('RUTA_RAIZ', RUTA_BASE . '/');
define('RUTA_CSS', RUTA_BASE . '/css');
define('RUTA_JS', RUTA_BASE . '/js');
define('RUTA_IMGS', RUTA_BASE . '/img');
define('RUTA_VISTAS', RUTA_BASE . '/includes/vistas');
define('RUTA_CLASES', RAIZ_APP . '/includes/clases');   

// Configuración regional
ini_set('default_charset', 'UTF-8');
date_default_timezone_set('Europe/Madrid');

spl_autoload_register(function ($class) {
    $prefix = 'es\\ucm\\fdi\\aw\\';
    $base_dir = RUTA_CLASES . '/'; 

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Datos de conexión a la BD
$bdDatosConexion = [
    'host' => 'localhost',
    'bd'   => 'bistro_fdi',
    'user' => 'bistro_user',
    'pass' => 'bistro_password'
];

use es\ucm\fdi\aw\Aplicacion;
// Inicializar aplicación
try {
    $app = Aplicacion::getInstance();
    $app->init($bdDatosConexion);
    register_shutdown_function([$app, 'shutdown']);
} catch (Exception $e) {
    error_log("Error al inicializar la aplicación: " . $e->getMessage());
    die("Error interno de la aplicación. Por favor, contacte con el administrador.");
}

// Constantes de roles
define('ROL_CLIENTE', 1);
define('ROL_CAMARERO', 2);
define('ROL_COCINERO', 3);
define('ROL_GERENTE', 4);

// Función helper para depuración (opcional)
if (!function_exists('d') && isset($_GET['debug'])) {
    function d($var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}
?> 
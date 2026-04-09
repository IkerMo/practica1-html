<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


define('RAIZ_APP', dirname(__DIR__));
define('RUTA_BASE', '/Practica1');
define('RUTA_RAIZ', RUTA_BASE . '/');
define('RUTA_CLASES', RAIZ_APP . '/includes/clases');
define('RUTA_CSS', RUTA_BASE . '/css');
define('RUTA_JS', RUTA_BASE . '/js');
define('RUTA_IMGS', RUTA_BASE . '/img');
define('RUTA_VISTAS', RUTA_BASE . '/includes/vistas');


ini_set('default_charset', 'UTF-8');
date_default_timezone_set('Europe/Madrid');

spl_autoload_register(function ($class) {
    $prefix = 'es\\ucm\\fdi\\aw\\';
    $base_dir = RUTA_CLASES . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0)
        return;

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

$bdDatosConexion = [

    //LOCALHOST
    'host' => 'localhost',
    'bd' => 'bistro_fdi',
    'user' => 'root',
    'pass' => ''


    
/*
     //VPS
     'host' => 'vm020.db.swarm.test',
     'bd'   => 'bistro_fdi',
     'user' => 'root',
     'pass' => '_OMmSLCCsgzr_TUVC4Ip'
     */

];

try {
    $app = \es\ucm\fdi\aw\Aplicacion::getInstance();
    $app->init($bdDatosConexion);
    register_shutdown_function([$app, 'shutdown']);
}
catch (Exception $e) {
    error_log("Error al inicializar la aplicación: " . $e->getMessage());
    die("Error crítico: No se pudo conectar con la base de datos.");
}

define('ROL_CLIENTE', 1);
define('ROL_CAMARERO', 2);
define('ROL_COCINERO', 3);
define('ROL_GERENTE', 4);

function estaLogueado()
{
    return isset($_SESSION['login']) && $_SESSION['login'] === true;
}

function esAdmin()
{
    return estaLogueado() && isset($_SESSION['rol']) && $_SESSION['rol'] == ROL_GERENTE;
}

function nombreUsuarioLogueado()
{
    return $_SESSION['nombre'] ?? 'Invitado';
}
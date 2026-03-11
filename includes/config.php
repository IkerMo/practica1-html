<?php
/**
 * Configuración básica de la aplicación Bistro FDI
 */

// Mostrar errores (quitar en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Constantes de rutas
define('RAIZ_APP', dirname(__DIR__));
define('RUTA_BASE', '/Practica1');
define('RUTA_RAIZ', RUTA_BASE . '/');
define('RUTA_CSS', RUTA_BASE . '/css');
define('RUTA_JS', RUTA_BASE . '/js');
define('RUTA_IMGS', RUTA_BASE . '/img');
define('RUTA_VISTAS', RUTA_BASE . '/includes/vistas');

// Configuración regional
ini_set('default_charset', 'UTF-8');
date_default_timezone_set('Europe/Madrid');
/*
// Cargar clase Aplicacion
require_once RAIZ_APP . '/includes/clases/Aplicacion.php';

// Datos de conexión a la BD
$bdDatosConexion = [
    'host' => 'localhost',
    'bd'   => 'bistro_fdi',
    'user' => 'bistro_user',
    'pass' => 'bistro_password'
];

// Inicializar aplicación
$app = Aplicacion::getInstance();
$app->init($bdDatosConexion);
register_shutdown_function([$app, 'shutdown']);

// Constantes de roles
define('ROL_CLIENTE', 1);
define('ROL_CAMARERO', 2);
define('ROL_COCINERO', 3);
define('ROL_GERENTE', 4);
?>*/
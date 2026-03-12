<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


define('RAIZ_APP', dirname(__DIR__));
define('RUTA_BASE', '/Practica1');
define('RUTA_CLASES', RAIZ_APP . '/includes/clases');


require_once RUTA_CLASES . '/Aplicacion.php';
require_once RUTA_CLASES . '/Formulario.php';
require_once RUTA_CLASES . '/FormularioLogin.php';
require_once RUTA_CLASES . '/FormularioRegistro.php';
require_once RUTA_CLASES . '/Usuario.php';

$bdDatosConexion = [
    'host' => 'localhost',
    'bd'   => 'bistro_fdi',
    'user' => 'bistro_user',
    'pass' => 'bistro_password'
];

$app = Aplicacion::getInstance(); 
$app->init($bdDatosConexion);
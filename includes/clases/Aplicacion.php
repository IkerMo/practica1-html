<?php
namespace es\ucm\fdi\aw;

class Aplicacion
{
    private static $instancia = null;
    private $bdDatosConexion;
    private $conexionBD = null;
    private $inicializada = false;
    private $atributosPeticion = [];
    
    private function __construct() {}
    
    public static function getInstance()
    {
        if (self::$instancia === null) {
            self::$instancia = new static();
        }
        return self::$instancia;
    }
    
    public function init($bdDatosConexion)
    {
        if (!$this->inicializada) {
            $this->bdDatosConexion = $bdDatosConexion;
            $this->inicializada = true;
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (isset($_SESSION['attsPeticion'])) {
                $this->atributosPeticion = $_SESSION['attsPeticion'];
                unset($_SESSION['attsPeticion']);
            }
        }
    }
    
    public function shutdown()
    {
        if ($this->conexionBD !== null) {
            $this->conexionBD->close();
            $this->conexionBD = null;
        }
    }
    
    public function getConexionBd()
    {
        
        if (!$this->inicializada) {
            throw new \Exception('Aplicacion no inicializada');
        }
        
        if ($this->conexionBD === null) {
            $host = $this->bdDatosConexion['host'];
            $bd = $this->bdDatosConexion['bd'];
            $user = $this->bdDatosConexion['user'];
            $pass = $this->bdDatosConexion['pass'];
            
            $this->conexionBD = new \mysqli($host, $user, $pass, $bd);
            
            if ($this->conexionBD->connect_error) {
                throw new \Exception('Error de conexión: ' . $this->conexionBD->connect_error);
            }
            
            $this->conexionBD->set_charset('utf8mb4');
        }
        
        return $this->conexionBD;
    }
    
    public function putAtributoPeticion($clave, $valor)
    {
        $this->atributosPeticion[$clave] = $valor;
        $_SESSION['attsPeticion'] = $this->atributosPeticion;
    }
    
    public function getAtributoPeticion($clave)
    {
        return $this->atributosPeticion[$clave] ?? null;
    }
    
    private function __clone() {}
    public function __wakeup() {}
}
?>
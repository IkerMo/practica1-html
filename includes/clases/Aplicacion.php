<?php
namespace es\ucm\fdi\aw;
class Aplicacion {

    private static $instancia;

    private $conn;
    private $datosConexionBd;

    private $atributosPeticion;

    private function __construct() {}

    public static function getInstance() {

        if (!self::$instancia instanceof self) {
            self::$instancia = new static();
        }

        return self::$instancia;
    }

    public function init($bdDatosConexion) {
        $this->datosConexionBd = $bdDatosConexion;
        if (isset($_SESSION['attsPeticion'])) {
            $this->atributosPeticion = $_SESSION['attsPeticion'];
            unset($_SESSION['attsPeticion']);
        }
        else {
            $this->atributosPeticion = [];
        }
    }

    public function getConexionBd() {

        if (!$this->conn) {
            $driver = new \mysqli_driver();
            $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
            $host = $this->datosConexionBd['host'];
            $bd   = $this->datosConexionBd['bd'];
            $user = $this->datosConexionBd['user'];
            $pass = $this->datosConexionBd['pass'];

            $conn = new \mysqli($host, $user, $pass, $bd);

            if ($conn->connect_errno) {
                die("Error de conexión ({$conn->connect_errno}) {$conn->connect_error}");
            }

            $conn->set_charset("utf8mb4");

            $this->conn = $conn;
        }

        return $this->conn;
    }

    public function shutdown() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    public function putAtributoPeticion($clave, $valor) {
        if (!isset($_SESSION['attsPeticion'])) {
            $_SESSION['attsPeticion'] = [];
        }

        $_SESSION['attsPeticion'][$clave] = $valor;
    }

    public function getAtributoPeticion($clave) {

        if (isset($this->atributosPeticion[$clave])) {
            return $this->atributosPeticion[$clave];
        }

        return null;
    }

}
?>
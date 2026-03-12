<?php
namespace es\ucm\fdi\aw;

class Usuario {
    private $id;
    private $nombreUsuario;
    private $nombre;
    private $rolActual;
    private $roles = []; // Aquí guardaremos los roles con su prioridad

    public function __construct($dto, $roles) {
        $this->id = $dto->id;
        $this->nombreUsuario = $dto->nombreUsuario;
        $this->nombre = $dto->nombre;
        $this->roles = $roles; // Array de roles que vienen de la BD
        
        // Asignamos el rol con más prioridad como el actual
        $maxPrio = -1;
        foreach($roles as $rol) {
            if ($rol['prioridad'] > $maxPrio) {
                $maxPrio = $rol['prioridad'];
                $this->rolActual = $rol['nombre'];
            }
        }
    }

    public function getId() { return $this->id; }
    public function getNombreUsuario() { return $this->nombreUsuario; }
    public function getRolActual() { return $this->rolActual; }

    // El método de permisos que ya tenías (está perfecto)
    public function tienePermiso($prioridadMinima) {
        foreach ($this->roles as $rol) {
            if ($rol['prioridad'] >= $prioridadMinima) return true;
        }
        return false;
    }

    public function guardaEnSesion() {
        $_SESSION['login'] = true;
        $_SESSION['usuario'] = $this; // Guardamos el objeto entero
    }

    public static function logout() {
        unset($_SESSION['login']);
        unset($_SESSION['usuario']);
        session_destroy();
    }
}
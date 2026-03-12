<?php
<<<<<<< HEAD
namespace es\ucm\fdi\aw\Usuarios;
use es\ucm\fdi\aw\Aplicacion;

class Usuario
{
=======
namespace es\ucm\fdi\aw;

class Usuario {
>>>>>>> c57322a669621674994105561e86c9ec1d479fb7
    private $id;
    private $nombreUsuario;
    private $nombre;
    private $rolActual;
<<<<<<< HEAD
    
    private function __construct() {}
    
    public function getId() { return $this->id; }
    public function getNombreUsuario() { return $this->nombreUsuario; }
    public function getEmail() { return $this->email; }
    public function getNombre() { return $this->nombre; }
    public function getApellidos() { return $this->apellidos; }
    public function getNombreCompleto() { return $this->nombre . ' ' . $this->apellidos; }
    public function getAvatar() { return $this->avatar; }
    public function getRolActual() { return $this->rolActual; }
    
    public function tieneRol($rolId)
    {
        foreach ($this->roles as $rol) {
            if ($rol['id'] == $rolId) return true;
        }
        return false;
    }
    
    public static function buscaUsuario($identificador)
    {
        try {
            $app = Aplicacion::getInstance();
            $conn = $app->getConexionBd();
            
            $query = sprintf(
                "SELECT * FROM Usuarios WHERE nombreUsuario = '%s' OR email = '%s'",
                $conn->real_escape_string($identificador),
                $conn->real_escape_string($identificador)
            );
            
            $rs = $conn->query($query);
            
            if ($rs && $rs->num_rows > 0) {
                $fila = $rs->fetch_assoc();
                $usuario = new self();
                $usuario->id = $fila['id'];
                $usuario->nombreUsuario = $fila['nombreUsuario'];
                $usuario->email = $fila['email'];
                $usuario->nombre = $fila['nombre'];
                $usuario->apellidos = $fila['apellidos'];
                $usuario->password = $fila['password'];
                $usuario->avatar = $fila['avatar'];
                $usuario->tipoAvatar = $fila['tipoAvatar'];
                $usuario->fechaRegistro = $fila['fechaRegistro'];
                $usuario->activo = $fila['activo'];
                
                $usuario->cargaRoles();
                
                $rs->free();
                return $usuario;
            }
            return false;
            
        } catch (\Exception $e) {
            error_log("Error en buscaUsuario: " . $e->getMessage());
            return false;
        }
    }
    
    private function cargaRoles()
    {
        try {
            $app = Aplicacion::getInstance();
            $conn = $app->getConexionBd();
            
            $query = sprintf(
                "SELECT r.* FROM Roles r 
                 JOIN UsuarioRoles ur ON r.id = ur.rol_id 
                 WHERE ur.usuario_id = %d",
                $this->id
            );
            
            $rs = $conn->query($query);
            
            if ($rs) {
                $this->roles = [];
                while ($fila = $rs->fetch_assoc()) {
                    $this->roles[] = $fila;
                    if ($this->rolActual === null) {
                        $this->rolActual = $fila['id'];
                    }
                }
                $rs->free();
            }
        } catch (\Exception $e) {
            error_log("Error en cargaRoles: " . $e->getMessage());
        }
    }
    
    public function compruebaPassword($password)
    {
        return password_verify($password, $this->password);
    }
    
    public static function login($identificador, $password)
    {
        $usuario = self::buscaUsuario($identificador);
=======
    private $roles = []; // Aquí guardaremos los roles con su prioridad

    public function __construct($dto, $roles) {
        $this->id = $dto->id;
        $this->nombreUsuario = $dto->nombreUsuario;
        $this->nombre = $dto->nombre;
        $this->roles = $roles; // Array de roles que vienen de la BD
>>>>>>> c57322a669621674994105561e86c9ec1d479fb7
        
        // Asignamos el rol con más prioridad como el actual
        $maxPrio = -1;
        foreach($roles as $rol) {
            if ($rol['prioridad'] > $maxPrio) {
                $maxPrio = $rol['prioridad'];
                $this->rolActual = $rol['nombre'];
            }
<<<<<<< HEAD
            return false;
            
        } catch (\Exception $e) {
            error_log("Error en crea usuario: " . $e->getMessage());
            return false;
=======
>>>>>>> c57322a669621674994105561e86c9ec1d479fb7
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
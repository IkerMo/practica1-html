<?php
namespace es\ucm\fdi\aw\Usuarios;
use es\ucm\fdi\aw\Aplicacion;

class Usuario
{
    private $id;
    private $nombreUsuario;
    private $email;
    private $nombre;
    private $apellidos;
    private $password;
    private $avatar;
    private $tipoAvatar;
    private $fechaRegistro;
    private $activo;
    private $roles = [];
    private $rolActual;
    
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
        
        if ($usuario && $usuario->compruebaPassword($password) && $usuario->activo) {
            return $usuario;
        }
        return false;
    }
    
    public static function crea($datos)
    {
        try {
            $app = Aplicacion::getInstance();
            $conn = $app->getConexionBd();
            
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
            
            $query = sprintf(
                "INSERT INTO Usuarios(nombreUsuario, email, nombre, apellidos, password, avatar, tipoAvatar) 
                 VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                $conn->real_escape_string($datos['nombreUsuario']),
                $conn->real_escape_string($datos['email']),
                $conn->real_escape_string($datos['nombre']),
                $conn->real_escape_string($datos['apellidos']),
                $conn->real_escape_string($passwordHash),
                $conn->real_escape_string($datos['avatar'] ?? 'default.png'),
                $conn->real_escape_string($datos['tipoAvatar'] ?? 'defecto')
            );
            
            if ($conn->query($query)) {
                $idUsuario = $conn->insert_id;
                
                $queryRol = sprintf(
                    "INSERT INTO UsuarioRoles(usuario_id, rol_id) VALUES(%d, %d)",
                    $idUsuario,
                    ROL_CLIENTE
                );
                
                if ($conn->query($queryRol)) {
                    return self::buscaUsuario($datos['nombreUsuario']);
                }
            }
            return false;
            
        } catch (\Exception $e) {
            error_log("Error en crea usuario: " . $e->getMessage());
            return false;
        }
    }
    
    public function guardaEnSesion()
    {
        $_SESSION['login'] = true;
        $_SESSION['idUsuario'] = $this->id;
        $_SESSION['nombreUsuario'] = $this->nombreUsuario;
        $_SESSION['nombre'] = $this->nombre;
        $_SESSION['apellidos'] = $this->apellidos;
        $_SESSION['avatar'] = $this->avatar;
        $_SESSION['rol'] = $this->rolActual;
        $_SESSION['esCliente'] = $this->tieneRol(ROL_CLIENTE);
        $_SESSION['esCamarero'] = $this->tieneRol(ROL_CAMARERO);
        $_SESSION['esCocinero'] = $this->tieneRol(ROL_COCINERO);
        $_SESSION['esAdmin'] = $this->tieneRol(ROL_GERENTE);
    }
    
    public static function logout()
    {
        $_SESSION = [];
        session_destroy();
    }

    public function tienePermiso($prioridadMinima) {
    $maxPrioridad = 0;
    foreach ($this->roles as $rol) {
        if ($rol['prioridad'] > $maxPrioridad) {
            $maxPrioridad = $rol['prioridad'];
        }
    }
    return $maxPrioridad >= $prioridadMinima;
}
}
?>
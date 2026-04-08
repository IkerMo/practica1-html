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
    
    // Constructor vacío para el método buscaUsuario (creación por factory)
    private function __construct() {}
    
    // Constructor con parámetros para cuando tenemos todos los datos
    public static function construirDesdeDTO($dto, $roles)
    {
        $usuario = new self();
        $usuario->id = $dto->id;
        $usuario->nombreUsuario = $dto->nombreUsuario;
        $usuario->email = $dto->email;
        $usuario->nombre = $dto->nombre;
        $usuario->apellidos = $dto->apellidos;
        $usuario->password = $dto->password;
        $usuario->avatar = $dto->avatar;
        $usuario->tipoAvatar = $dto->tipoAvatar;
        $usuario->fechaRegistro = $dto->fechaRegistro;
        $usuario->activo = $dto->activo;
        $usuario->roles = $roles;
        
        // Asignar rol con mayor prioridad
        $maxPrio = -1;
        foreach ($roles as $rol) {
            if ($rol['prioridad'] > $maxPrio) {
                $maxPrio = $rol['prioridad'];
                $usuario->rolActual = $rol['id'];
            }
        }
        
        return $usuario;
    }
    
    // Getters
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
    if (empty($this->roles)) {
        return false;
    }
    
    foreach ($this->roles as $rol) {
        $id = $rol['id'] ?? null;
        if ($id && $id == $rolId) {
            return true;
        }
    }
    return false;
}
    
    // Comprueba si tiene permiso según prioridad mínima
    public function tienePermiso($prioridadMinima)
    {
        foreach ($this->roles as $rol) {
            if ($rol['prioridad'] >= $prioridadMinima) return true;
        }
        return false;
    }
    
    // Busca un usuario por nombre de usuario o email
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
    
    // Carga los roles del usuario desde la BD
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
            
            error_log("cargaRoles query para usuario {$this->id}: " . $query);
            
            $rs = $conn->query($query);
            
            if ($rs) {
                $this->roles = [];
                $count = 0;
                while ($fila = $rs->fetch_assoc()) {
                    $this->roles[] = $fila;
                    $count++;
                    if ($this->rolActual === null) {
                        $this->rolActual = $fila['id'];
                    }
                }
                $rs->free();
                error_log("cargaRoles: cargados {$count} roles para usuario {$this->id}");
            } else {
                error_log("cargaRoles: error en query - " . $conn->error);
            }
        } catch (\Exception $e) {
            error_log("Error en cargaRoles: " . $e->getMessage());
        }
    }
    
    // Comprueba si la contraseña es correcta
    public function compruebaPassword($password)
    {
        return password_verify($password, $this->password);
    }
    
    // Login de usuario
    public static function login($identificador, $password)
    {
        $usuario = self::buscaUsuario($identificador);
        
        if ($usuario && $usuario->compruebaPassword($password)) {
            return $usuario;
        }
        
        return false;
    }
    
    // Guarda el usuario en sesión
    public function guardaEnSesion()
{
    $_SESSION['login'] = true;
    $_SESSION['idUsuario'] = $this->id;  
    $_SESSION['nombre'] = $this->nombre;
    $_SESSION['nombreUsuario'] = $this->nombreUsuario;
    $_SESSION['rol'] = $this->rolActual;
    
    $rolNombres = [1 => 'Cliente', 2 => 'Camarero', 3 => 'Cocinero', 4 => 'Gerente'];
    $_SESSION['rolNombre'] = $rolNombres[$this->rolActual] ?? 'Usuario';
    
    $_SESSION['esCliente'] = $this->tieneRol(1);
    $_SESSION['esCamarero'] = $this->tieneRol(2);
    $_SESSION['esCocinero'] = $this->tieneRol(3);
    $_SESSION['esAdmin'] = $this->tieneRol(4);

    error_log("Sesión guardada. ID Usuario: " . $this->id);
}
    
    // Cierra la sesión
    public static function logout()
    {
        $_SESSION = [];
        session_destroy();
    }

    public function actualiza($datos)
{
    try {
        $app = Aplicacion::getInstance();
        $conn = $app->getConexionBd();
        
        $actualizaciones = [];
        
        if (isset($datos['nombre'])) {
            $actualizaciones[] = "nombre = '" . $conn->real_escape_string($datos['nombre']) . "'";
            $this->nombre = $datos['nombre'];
        }
        if (isset($datos['apellidos'])) {
            $actualizaciones[] = "apellidos = '" . $conn->real_escape_string($datos['apellidos']) . "'";
            $this->apellidos = $datos['apellidos'];
        }
        if (isset($datos['email'])) {
            $actualizaciones[] = "email = '" . $conn->real_escape_string($datos['email']) . "'";
            $this->email = $datos['email'];
        }
        if (isset($datos['avatar'])) {
            $actualizaciones[] = "avatar = '" . $conn->real_escape_string($datos['avatar']) . "'";
            $this->avatar = $datos['avatar'];
        }
        if (isset($datos['password'])) {
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
            $actualizaciones[] = "password = '" . $conn->real_escape_string($passwordHash) . "'";
            $this->password = $passwordHash;
        }

        if (isset($datos['nombreUsuario'])) {
            $actualizaciones[] = "nombreUsuario = '" . $conn->real_escape_string($datos['nombreUsuario']) . "'";
            $this->nombreUsuario = $datos['nombreUsuario'];
        }

        if (isset($datos['activo'])) {
            $actualizaciones[] = "activo = " . (int)$datos['activo'];
            $this->activo = (bool)$datos['activo'];
        }
        
        if (empty($actualizaciones)) {
            return true;
        }
        
        $query = "UPDATE Usuarios SET " . implode(', ', $actualizaciones) . " WHERE id = " . $this->id;
        
        return $conn->query($query);
        
    } catch (\Exception $e) {
        error_log("Error en actualiza usuario: " . $e->getMessage());
        return false;
    }
}
public static function buscaUsuarioPorId($id)
{
    try {
        $app = Aplicacion::getInstance();
        $conn = $app->getConexionBd();
        
        error_log("buscaUsuarioPorId - ID recibido: " . $id);
        
        $query = sprintf("SELECT nombreUsuario FROM Usuarios WHERE id = %d", $id);
        error_log("Query: " . $query);
        
        $rs = $conn->query($query);
        
        if ($rs && $rs->num_rows > 0) {
            $fila = $rs->fetch_assoc();
            error_log("Usuario encontrado en BD: " . $fila['nombreUsuario']);
            $usuario = self::buscaUsuario($fila['nombreUsuario']);
            error_log("Resultado buscaUsuario: " . ($usuario ? 'OK' : 'NULL'));
            return $usuario;
        } else {
            error_log("No se encontró usuario con ID: " . $id);
            if ($conn->error) {
                error_log("Error BD: " . $conn->error);
            }
        }
        
        return false;
        
    } catch (\Exception $e) {
        error_log("Error en buscaUsuarioPorId: " . $e->getMessage());
        return false;
    }
}

public function cambiaRol($nuevoRolId)
{
    try {
        $app = Aplicacion::getInstance();
        $conn = $app->getConexionBd();
        
        // Eliminar roles actuales
        $queryDelete = sprintf("DELETE FROM UsuarioRoles WHERE usuario_id = %d", $this->id);
        $conn->query($queryDelete);
        
        // Insertar nuevo rol
        $queryInsert = sprintf(
            "INSERT INTO UsuarioRoles(usuario_id, rol_id) VALUES(%d, %d)",
            $this->id,
            $nuevoRolId
        );
        
        if ($conn->query($queryInsert)) {
            // Recargar roles del usuario
            $this->cargaRoles();
            return true;
        }
        
        return false;
        
    } catch (\Exception $e) {
        error_log("Error en cambiaRol: " . $e->getMessage());
        return false;
    }
}

public static function crea($datos)
{
    try {
        $app = Aplicacion::getInstance();
        $conn = $app->getConexionBd();
        
        // Validar datos obligatorios
        $camposObligatorios = ['nombreUsuario', 'email', 'nombre', 'apellidos', 'password'];
        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                error_log("Error en crea usuario: campo obligatorio '$campo' vacío");
                return false;
            }
        }
        
        // Hashear contraseña
        $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
        
        // Avatar por defecto
        $avatar = $datos['avatar'] ?? 'default.png';
        $tipoAvatar = $datos['tipoAvatar'] ?? 'defecto';
        
        // Insertar usuario
        $query = sprintf(
            "INSERT INTO usuarios (nombreUsuario, email, nombre, apellidos, password, avatar, tipoAvatar, activo) 
             VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', 1)",
            $conn->real_escape_string($datos['nombreUsuario']),
            $conn->real_escape_string($datos['email']),
            $conn->real_escape_string($datos['nombre']),
            $conn->real_escape_string($datos['apellidos']),
            $conn->real_escape_string($passwordHash),
            $conn->real_escape_string($avatar),
            $conn->real_escape_string($tipoAvatar)
        );
        
        error_log("Query crear usuario: " . $query); // Para depurar
        
        if ($conn->query($query)) {
            $idUsuario = $conn->insert_id;
            
            // Asignar rol cliente por defecto
            $queryRol = sprintf(
                "INSERT INTO UsuarioRoles (usuario_id, rol_id) VALUES (%d, %d)",
                $idUsuario,
                1 // ROL_CLIENTE
            );
            
            if ($conn->query($queryRol)) {
                return self::buscaUsuario($datos['nombreUsuario']);
            } else {
                error_log("Error al asignar rol: " . $conn->error);
                return false;
            }
        } else {
            error_log("Error al crear usuario: " . $conn->error);
            return false;
        }
        
    } catch (\Exception $e) {
        error_log("Error en crea usuario: " . $e->getMessage());
        return false;
    }
}

}
?>
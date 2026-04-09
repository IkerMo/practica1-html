<?php
namespace es\ucm\fdi\aw\Formularios;
use es\ucm\fdi\aw\Usuarios\Usuario;
use es\ucm\fdi\aw\Usuarios\UsuarioAppService;
use es\ucm\fdi\aw\Usuarios\UsuarioDAO;

class FormularioRegistro extends Formulario {
    public function __construct() {
        parent::__construct('registro', ['urlRedireccion' => RUTA_RAIZ . 'inicio.php']);
    }

    protected function generaCamposFormulario(&$datos) {
        // Mostrar valores ingresados (con escape)
        $nombreUsuario = htmlspecialchars($datos['nombreUsuario'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($datos['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $nombre = htmlspecialchars($datos['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
        $apellidos = htmlspecialchars($datos['apellidos'] ?? '', ENT_QUOTES, 'UTF-8');
        
        return <<<EOS
        <div class="contenedor-formulario-fdi">
            <fieldset class="form-ajustado">
                <legend>Nuevo Usuario</legend>
                <div class="bloque-entrada">
                    <label>Nombre de usuario</label>
                    <input type="text" name="nombreUsuario" value="$nombreUsuario" />
                    {$this->getError('nombreUsuario')}
                    <small>Mínimo 4 caracteres. Solo letras, números y guión bajo</small>
                </div>
                <div class="bloque-entrada">
                    <label>Email</label>
                    <input type="email" name="email" value="$email" />
                    {$this->getError('email')}
                </div>
                <div class="bloque-entrada">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="$nombre" />
                    {$this->getError('nombre')}
                </div>
                <div class="bloque-entrada">
                    <label>Apellidos</label>
                    <input type="text" name="apellidos" value="$apellidos" />
                    {$this->getError('apellidos')}
                </div>
                <div class="bloque-entrada">
                    <label>Contraseña</label>
                    <input type="password" name="password" />
                    {$this->getError('password')}
                    <small>Mínimo 6 caracteres</small>
                </div>
                <div class="bloque-entrada">
                    <label>Repite contraseña</label>
                    <input type="password" name="password2" />
                    {$this->getError('password2')}
                </div>
                {$this->getGlobalErrors()}
                <button type="submit" class="boton-rojo">Registrarse</button>
            </fieldset>
        </div>
EOS;
    }

    protected function procesaFormulario(&$datos) {
        $this->errores = [];
        
        // Obtener datos (solo trim, sin sanear automáticamente)
        $nombreUsuario = trim($datos['nombreUsuario'] ?? '');
        $email = trim($datos['email'] ?? '');
        $nombre = trim($datos['nombre'] ?? '');
        $apellidos = trim($datos['apellidos'] ?? '');
        $password = trim($datos['password'] ?? '');
        $password2 = trim($datos['password2'] ?? '');
        
        // ========== VALIDACIONES ==========
        
        // 1. Nombre de usuario
        if (empty($nombreUsuario)) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario es obligatorio';
        } elseif (strlen($nombreUsuario) < 4) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario debe tener al menos 4 caracteres';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $nombreUsuario)) {
            $this->errores['nombreUsuario'] = 'Solo se permiten letras, números y guión bajo (_)';
        }
        
        // 2. Email
        if (empty($email)) {
            $this->errores['email'] = 'El email es obligatorio';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = 'El email no es válido';
        }
        
        // 3. Nombre
        if (empty($nombre)) {
            $this->errores['nombre'] = 'El nombre es obligatorio';
        } elseif (strlen($nombre) < 2) {
            $this->errores['nombre'] = 'El nombre debe tener al menos 2 caracteres';
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre)) {
            $this->errores['nombre'] = 'Solo se permiten letras y espacios';
        }
        
        // 4. Apellidos
        if (empty($apellidos)) {
            $this->errores['apellidos'] = 'Los apellidos son obligatorios';
        } elseif (strlen($apellidos) < 3) {
            $this->errores['apellidos'] = 'Los apellidos deben tener al menos 3 caracteres';
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $apellidos)) {
            $this->errores['apellidos'] = 'Solo se permiten letras y espacios';
        }
        
        // 5. Contraseña
        if (empty($password)) {
            $this->errores['password'] = 'La contraseña es obligatoria';
        } elseif (strlen($password) < 6) {
            $this->errores['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        // 6. Confirmación de contraseña
        if ($password !== $password2) {
            $this->errores['password2'] = 'Las contraseñas no coinciden';
        }
        
        // Si hay errores, detener
        if (count($this->errores) > 0) return false;
        
        // ========== VERIFICAR QUE NO EXISTA ==========
        
        // Verificar nombre de usuario
        $usuarioExistente = Usuario::buscaUsuario($nombreUsuario);
        if ($usuarioExistente) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario ya está registrado';
            return false;
        }
        
        // Verificar email
        $emailExistente = Usuario::buscaUsuario($email);
        if ($emailExistente) {
            $this->errores['email'] = 'El email ya está registrado';
            return false;
        }
        
        // ========== SANEAR (SOLO PARA ESCAPAR) ==========
        $nombreUsuarioSafe = filter_var($nombreUsuario, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $emailSafe = filter_var($email, FILTER_SANITIZE_EMAIL);
        $nombreSafe = filter_var($nombre, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $apellidosSafe = filter_var($apellidos, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // ========== CREAR USUARIO ==========
        $service = new UsuarioAppService();
        $dao = new UsuarioDAO();
        $dto = $service->registro($nombreUsuarioSafe, $emailSafe, $nombreSafe, $apellidosSafe, $password);
        
        if ($dto) {
            $roles = $dao->obtenerRoles($dto->id);
            $usuarioSesion = Usuario::construirDesdeDTO($dto, $roles);
            $usuarioSesion->guardaEnSesion();
            return true;
        }
        
        $this->errores[] = 'Error al crear el usuario. Por favor, inténtalo de nuevo.';
        return false;
    }
    
    private function getError($campo) { 
        return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']); 
    }
    
    private function getGlobalErrors() {
        $erroresGlobales = array_filter(array_keys($this->errores), 'is_numeric');
        if (empty($erroresGlobales)) return '';
        
        $html = '<div class="errores-globales">';
        foreach ($erroresGlobales as $clave) {
            $html .= '<p class="error-message">' . htmlspecialchars($this->errores[$clave]) . '</p>';
        }
        $html .= '</div>';
        return $html;
    }
}
?>
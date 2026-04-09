<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Formularios\Formulario;
use es\ucm\fdi\aw\Usuarios\Usuario;

class FormularioCrearUsuarioAdmin extends Formulario
{
    public function __construct()
    {
        parent::__construct('crearUsuarioAdmin', [
            'urlRedireccion' => RUTA_VISTAS . '/usuarios/admin/listar.php',
            'enctype' => 'multipart/form-data'
        ]);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        $rutaImgs = RUTA_IMGS;
        // Mostrar valores ingresados (con escape)
        $nombreUsuario = htmlspecialchars($datos['nombreUsuario'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($datos['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $nombre = htmlspecialchars($datos['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
        $apellidos = htmlspecialchars($datos['apellidos'] ?? '', ENT_QUOTES, 'UTF-8');
        $rol = $datos['rol'] ?? '1';
        
        $html = <<<EOS
        <fieldset>
            <legend>Datos del nuevo usuario</legend>
            
            <div class="campo">
                <label for="nombreUsuario">Nombre de usuario:</label>
                <input id="nombreUsuario" type="text" name="nombreUsuario" value="$nombreUsuario" />
                {$this->getError('nombreUsuario')}
                <small>Mínimo 4 caracteres. Solo letras, números y guión bajo</small>
            </div>
            
            <div class="campo">
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" value="$email" />
                {$this->getError('email')}
            </div>
            
            <div class="campo">
                <label for="nombre">Nombre:</label>
                <input id="nombre" type="text" name="nombre" value="$nombre" />
                {$this->getError('nombre')}
                <small>Solo letras y espacios</small>
            </div>
            
            <div class="campo">
                <label for="apellidos">Apellidos:</label>
                <input id="apellidos" type="text" name="apellidos" value="$apellidos" />
                {$this->getError('apellidos')}
                <small>Solo letras y espacios</small>
            </div>
            
            <div class="campo">
                <label for="password">Contraseña:</label>
                <input id="password" type="password" name="password" />
                <small>Mínimo 6 caracteres</small>
                {$this->getError('password')}
            </div>
            
            <div class="campo">
                <label for="rol">Rol:</label>
                <select id="rol" name="rol">
                    <option value="1" {$this->selected('1', $rol)}>Cliente</option>
                    <option value="2" {$this->selected('2', $rol)}>Camarero</option>
                    <option value="3" {$this->selected('3', $rol)}>Cocinero</option>
                    <option value="4" {$this->selected('4', $rol)}>Gerente</option>
                </select>
            </div>
            
            <div class="campo">
                <label for="avatar">Avatar:</label>
                <select id="avatar" name="avatar">
                    <option value="default.png">Por defecto</option>
                    <option value="chef.png">Chef</option>
                    <option value="waiter.png">Camarero</option>
                    <option value="client.png">Cliente</option>
                    <option value="admin.png">Admin</option>
                </select>
            </div>
            
            {$this->getGlobalErrors()}
            
            <div class="campo">
                <button type="submit">Crear usuario</button>
                <a href="listar.php" class="btn-secondary">Cancelar</a>
            </div>
        </fieldset>
EOS;
        return $html;
    }
    
    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        
        // Obtener datos (solo trim)
        $nombreUsuario = trim($datos['nombreUsuario'] ?? '');
        $email = trim($datos['email'] ?? '');
        $nombre = trim($datos['nombre'] ?? '');
        $apellidos = trim($datos['apellidos'] ?? '');
        $password = trim($datos['password'] ?? '');
        $rol = (int)($datos['rol'] ?? 1);
        $avatar = $datos['avatar'] ?? 'default.png';
        
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
            $this->errores['email'] = 'Email no válido';
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
        
        // 6. Validar rol
        if ($rol < 1 || $rol > 4) {
            $this->errores['rol'] = 'Rol no válido';
        }
        
        if (count($this->errores) > 0) return false;
        
        // ========== VERIFICAR QUE NO EXISTA ==========
        
        // Verificar nombre de usuario
        if (Usuario::buscaUsuario($nombreUsuario)) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario ya existe';
            return false;
        }
        
        // Verificar email
        if (Usuario::buscaUsuario($email)) {
            $this->errores['email'] = 'El email ya está registrado';
            return false;
        }
        
        // ========== SANEAR (SOLO PARA ESCAPAR) ==========
        $nombreUsuarioSafe = filter_var($nombreUsuario, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $emailSafe = filter_var($email, FILTER_SANITIZE_EMAIL);
        $nombreSafe = filter_var($nombre, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $apellidosSafe = filter_var($apellidos, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // ========== CREAR USUARIO ==========
        $datosUsuario = [
            'nombreUsuario' => $nombreUsuarioSafe,
            'email' => $emailSafe,
            'nombre' => $nombreSafe,
            'apellidos' => $apellidosSafe,
            'password' => $password,
            'avatar' => $avatar,
            'tipoAvatar' => 'seleccionado'
        ];
        
        $usuario = Usuario::crea($datosUsuario);
        
        if ($usuario) {
            // Cambiar al rol seleccionado si no es cliente
            if ($rol != 1) {
                $usuario->cambiaRol($rol);
            }
            return true;
        } else {
            $this->errores[] = 'Error al crear el usuario. Por favor, inténtalo de nuevo.';
            return false;
        }
    }
    
    private function getError($campo)
    {
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
    
    private function selected($valor, $actual)
    {
        return $valor === $actual ? 'selected' : '';
    }
}
?>
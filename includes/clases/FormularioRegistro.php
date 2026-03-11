<?php
require_once RAIZ_APP . '/includes/clases/Formulario.php';
require_once RAIZ_APP . '/includes/clases/Usuarios/Usuario.php';

class FormularioRegistro extends Formulario
{
    public function __construct()
    {
        parent::__construct('registro', [
            'urlRedireccion' => RUTA_RAIZ . 'index.php'
        ]);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        $nombreUsuario = $datos['nombreUsuario'] ?? '';
        $email = $datos['email'] ?? '';
        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        
        $html = <<<EOS
        <fieldset>
            <legend>Registro en Bistro FDI</legend>
            
            <div class="campo">
                <label for="nombreUsuario">Nombre de usuario:</label>
                <input id="nombreUsuario" type="text" name="nombreUsuario" value="$nombreUsuario" />
                <small>Mínimo 4 caracteres, único</small>
                {$this->getError('nombreUsuario')}
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
            </div>
            
            <div class="campo">
                <label for="apellidos">Apellidos:</label>
                <input id="apellidos" type="text" name="apellidos" value="$apellidos" />
                {$this->getError('apellidos')}
            </div>
            
            <div class="campo">
                <label for="password">Contraseña:</label>
                <input id="password" type="password" name="password" />
                <small>Mínimo 6 caracteres</small>
                {$this->getError('password')}
            </div>
            
            <div class="campo">
                <label for="password2">Repite la contraseña:</label>
                <input id="password2" type="password" name="password2" />
                {$this->getError('password2')}
            </div>
            
            <div class="campo">
                <label for="avatar">Avatar:</label>
                <select id="avatar" name="avatar">
                    <option value="default.png">Por defecto</option>
                    <option value="chef.png">Chef</option>
                    <option value="waiter.png">Camarero</option>
                    <option value="client.png">Cliente</option>
                </select>
            </div>
            
            <div class="campo">
                <button type="submit">Registrarse</button>
            </div>
            
            <div class="enlaces">
                <a href="{$this->getLoginUrl()}">¿Ya tienes cuenta? Inicia sesión</a>
            </div>
        </fieldset>
EOS;
        return $html;
    }
    
    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        
        // Validar nombre de usuario
        $nombreUsuario = trim($datos['nombreUsuario'] ?? '');
        if (empty($nombreUsuario)) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario es obligatorio';
        } elseif (strlen($nombreUsuario) < 4) {
            $this->errores['nombreUsuario'] = 'Debe tener al menos 4 caracteres';
        }
        
        // Validar email
        $email = trim($datos['email'] ?? '');
        if (empty($email)) {
            $this->errores['email'] = 'El email es obligatorio';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = 'Email no válido';
        }
        
        // Validar nombre
        $nombre = trim($datos['nombre'] ?? '');
        if (empty($nombre)) {
            $this->errores['nombre'] = 'El nombre es obligatorio';
        }
        
        // Validar apellidos
        $apellidos = trim($datos['apellidos'] ?? '');
        if (empty($apellidos)) {
            $this->errores['apellidos'] = 'Los apellidos son obligatorios';
        }
        
        // Validar contraseña
        $password = trim($datos['password'] ?? '');
        if (empty($password)) {
            $this->errores['password'] = 'La contraseña es obligatoria';
        } elseif (strlen($password) < 6) {
            $this->errores['password'] = 'Debe tener al menos 6 caracteres';
        }
        
        // Validar confirmación
        $password2 = trim($datos['password2'] ?? '');
        if ($password != $password2) {
            $this->errores['password2'] = 'Las contraseñas no coinciden';
        }
        
        if (count($this->errores) > 0) {
            return false;
        }
        
        // Verificar si el usuario ya existe
        if (Usuario::buscaUsuario($nombreUsuario)) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario ya está registrado';
            return false;
        }
        
        if (Usuario::buscaUsuario($email)) {
            $this->errores['email'] = 'El email ya está registrado';
            return false;
        }
        
        $datosUsuario = [
            'nombreUsuario' => $nombreUsuario,
            'email' => $email,
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'password' => $password,
            'avatar' => $datos['avatar'] ?? 'default.png',
            'tipoAvatar' => 'seleccionado'
        ];
        
        $usuario = Usuario::crea($datosUsuario);
        
        if ($usuario) {
            $usuario->guardaEnSesion();
            
            if (method_exists('Aplicacion', 'putAtributoPeticion')) {
                $app = Aplicacion::getInstance();
                $app->putAtributoPeticion('mensajes', [
                    '¡Registro completado con éxito!',
                    'Bienvenido a Bistro FDI, ' . $usuario->getNombre()
                ]);
            }
            return true;
        } else {
            $this->errores[] = 'Error al crear el usuario';
            return false;
        }
    }
    
    private function getError($campo)
    {
        return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']);
    }
    
    private function getLoginUrl()
    {
        return RUTA_VISTAS . '/login.php';
    }
}
?>
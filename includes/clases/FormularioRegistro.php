<?php
namespace es\ucm\fdi\aw;

require_once RAIZ_APP . '/includes/clases/Formulario.php';

class FormularioRegistro extends \Formulario
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
                {$this->getError('password')}
            </div>
            <div class="campo">
                <label for="password2">Repite la contraseña:</label>
                <input id="password2" type="password" name="password2" />
                {$this->getError('password2')}
            </div>
            <div class="campo">
                <button type="submit">Registrarse</button>
            </div>
        </fieldset>
EOS;
        return $html;
    }
    
    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        
        $nombreUsuario = trim($datos['nombreUsuario'] ?? '');
        $email = trim($datos['email'] ?? '');
        $password = trim($datos['password'] ?? '');
        $password2 = trim($datos['password2'] ?? '');

        if (strlen($nombreUsuario) < 4) $this->errores['nombreUsuario'] = 'Mínimo 4 caracteres';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $this->errores['email'] = 'Email no válido';
        if (strlen($password) < 6) $this->errores['password'] = 'Mínimo 6 caracteres';
        if ($password !== $password2) $this->errores['password2'] = 'Las contraseñas no coinciden';

        if (count($this->errores) > 0) return false;


        $service = new UsuarioAppService();
        $dao = new UsuarioDAO();

        if ($dao->buscarPorUsername($nombreUsuario)) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario ya existe';
            return false;
        }

        $dto = $service->registro(
            $nombreUsuario, 
            $email, 
            $datos['nombre'] ?? '', 
            $datos['apellidos'] ?? '', 
            $password
        );

        if ($dto) {
            $roles = $dao->obtenerRoles($dto->id);
            $usuarioSesion = new Usuario($dto, $roles);
            $usuarioSesion->guardaEnSesion();
            return true; 
        } else {
            $this->errores[] = 'Error crítico al crear el usuario';
            return false;
        }
    }
    
    private function getError($campo) {
        return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']);
    }
}
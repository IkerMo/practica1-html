<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Usuarios\Usuario;

class FormularioLogin extends Formulario
{
    public function __construct()
    {
        parent::__construct('login', [
            'urlRedireccion' => RUTA_RAIZ . 'index.php'
        ]);
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombreUsuario = $datos['nombreUsuario'] ?? '';
        $html = <<<EOS
        <fieldset>
            <legend>Acceso al Bistro FDI</legend>
            <div class="campo">
                <label for="nombreUsuario">Usuario o Email:</label>
                <input id="nombreUsuario" type="text" name="nombreUsuario" value="$nombreUsuario" />
                {$this->getError('nombreUsuario')}
            </div>
            <div class="campo">
                <label for="password">Contraseña:</label>
                <input id="password" type="password" name="password" />
                {$this->getError('password')}
            </div>
            <div class="campo">
                <button type="submit">Entrar</button>
            </div>
        </fieldset>
EOS;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $nombreUsuario = trim($datos['nombreUsuario'] ?? '');
        $password = trim($datos['password'] ?? '');

        if (empty($nombreUsuario)) $this->errores['nombreUsuario'] = 'El nombre de usuario no puede estar vacío';
        if (empty($password)) $this->errores['password'] = 'La contraseña no puede estar vacía';

        if (count($this->errores) > 0) return false;

        $usuario = Usuario::login($nombreUsuario, $password);

        if (!$usuario) {
            $this->errores[] = 'El usuario o la contraseña no coinciden';
            return false;
        }

        $usuario->guardaEnSesion();
        return true;
    }

    private function getError($campo) {
        return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']);
    }
}
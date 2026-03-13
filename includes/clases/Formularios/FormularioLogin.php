<?php
namespace es\ucm\fdi\aw\Formularios;
use es\ucm\fdi\aw\Usuarios\Usuario;

class FormularioLogin extends Formulario {
    public function __construct() {
        parent::__construct('login', ['urlRedireccion' => RUTA_RAIZ . 'inicio.php']);
    }

    protected function generaCamposFormulario(&$datos) {
        $nombreUsuario = $datos['nombreUsuario'] ?? '';
        return <<<EOS
        <div class="contenedor-formulario-fdi">
            <fieldset class="form-ajustado">
                <legend>Acceso al Bistro FDI</legend>
                <div class="bloque-entrada">
                    <label>Usuario o Email</label>
                    <input type="text" name="nombreUsuario" value="$nombreUsuario" />
                    {$this->getError('nombreUsuario')}
                </div>
                <div class="bloque-entrada">
                    <label>Contraseña</label>
                    <input type="password" name="password" />
                    {$this->getError('password')}
                </div>
                <button type="submit" class="boton-rojo">Entrar</button>
            </fieldset>
        </div>
EOS;
    }

    protected function procesaFormulario(&$datos) {
        $this->errores = [];
        $nombreUsuario = trim($datos['nombreUsuario'] ?? '');
        $password = trim($datos['password'] ?? '');
        if (empty($nombreUsuario)) $this->errores['nombreUsuario'] = 'Obligatorio';
        if (empty($password)) $this->errores['password'] = 'Obligatorio';
        if (count($this->errores) > 0) return false;
        $usuario = Usuario::login($nombreUsuario, $password);
        if (!$usuario) { $this->errores[] = 'Credenciales incorrectas'; return false; }
        $usuario->guardaEnSesion();
        return true;
    }

    private function getError($campo) { return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']); }
}
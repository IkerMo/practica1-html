<?php
require_once RAIZ_APP . '/includes/clases/Formulario.php';
require_once RAIZ_APP . '/includes/clases/Usuario.php';

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
        $identificador = $datos['identificador'] ?? '';
        
        $html = <<<EOS
        <fieldset>
            <legend>Acceso a Bistro FDI</legend>
            <div class="campo">
                <label for="identificador">Usuario o Email:</label>
                <input id="identificador" type="text" name="identificador" value="$identificador" />
                {$this->getError('identificador')}
            </div>
            <div class="campo">
                <label for="password">Contraseña:</label>
                <input id="password" type="password" name="password" />
                {$this->getError('password')}
            </div>
            <div class="campo">
                <button type="submit">Entrar</button>
            </div>
            <div class="enlaces">
                <a href="{$this->getRegistroUrl()}">¿No tienes cuenta? Regístrate</a>
            </div>
        </fieldset>
EOS;
        return $html;
    }
    
    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        
        $identificador = trim($datos['identificador'] ?? '');
        if (empty($identificador)) {
            $this->errores['identificador'] = 'Debes introducir tu usuario o email';
        }
        
        $password = trim($datos['password'] ?? '');
        if (empty($password)) {
            $this->errores['password'] = 'Debes introducir tu contraseña';
        }
        
        if (count($this->errores) === 0) {
            $usuario = Usuario::login($identificador, $password);
            
            if ($usuario) {
                $usuario->guardaEnSesion();
                return true;
            } else {
                $this->errores[] = 'El usuario o la contraseña no coinciden';
            }
        }
        return false;
    }
    
    private function getError($campo)
    {
        return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']);
    }
    
    private function getRegistroUrl()
    {
        return RUTA_VISTAS . '/registro.php';
    }
}
?>
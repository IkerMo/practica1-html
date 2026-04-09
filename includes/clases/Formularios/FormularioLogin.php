<?php
namespace es\ucm\fdi\aw\Formularios;
use es\ucm\fdi\aw\Usuarios\Usuario;

class FormularioLogin extends Formulario {
    public function __construct() {
        parent::__construct('login', ['urlRedireccion' => RUTA_RAIZ . 'inicio.php']);
    }

    protected function generaCamposFormulario(&$datos) {
        $nombreUsuario = htmlspecialchars($datos['nombreUsuario'] ?? '', ENT_QUOTES, 'UTF-8');
        
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
                {$this->getGlobalErrors()}
                <button type="submit" class="boton-rojo">Entrar</button>
            </fieldset>
        </div>
EOS;
    }

    protected function procesaFormulario(&$datos) {
        $this->errores = [];
        
        // Obtener datos (solo trim)
        $identificador = trim($datos['nombreUsuario'] ?? '');
        $password = trim($datos['password'] ?? '');
        
        // VALIDAR
        if (empty($identificador)) {
            $this->errores['nombreUsuario'] = 'Debes introducir tu usuario o email';
        }
        
        if (empty($password)) {
            $this->errores['password'] = 'Debes introducir tu contraseña';
        }
        
        if (count($this->errores) > 0) return false;
        
        // Intentar login
        $usuario = Usuario::login($identificador, $password);
        
        if (!$usuario) { 
            $this->errores[] = 'Credenciales incorrectas. El usuario o la contraseña no coinciden.';
            return false; 
        }
        
        $usuario->guardaEnSesion();
        return true;
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
<?php
<<<<<<< HEAD:includes/clases/Formularios/FormularioLogin.php
namespace es\ucm\fdi\aw\Formularios;
use es\ucm\fdi\aw\Usuarios\Usuario;
=======
namespace es\ucm\fdi\aw;
>>>>>>> c57322a669621674994105561e86c9ec1d479fb7:includes/clases/FormularioLogin.php

require_once RAIZ_APP . '/includes/clases/Formulario.php';

class FormularioLogin extends \Formulario
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
                <label for="identificador">Usuario:</label>
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
        $password = trim($datos['password'] ?? '');
        
        if (empty($identificador)) {
            $this->errores['identificador'] = 'Debes introducir tu usuario';
        }
        if (empty($password)) {
            $this->errores['password'] = 'Debes introducir tu contraseña';
        }
        if (count($this->errores) === 0) {
<<<<<<< HEAD:includes/clases/Formularios/FormularioLogin.php
            
            $usuario = Usuario::login($identificador, $password);
=======
            // 1. Instanciamos el Service y el DAO
            $service = new UsuarioAppService();
            $dao = new UsuarioDAO();

            // 2. Intentamos el login a través del Service
            $dto = $service->login($identificador, $password);
>>>>>>> c57322a669621674994105561e86c9ec1d479fb7:includes/clases/FormularioLogin.php
            
            if ($dto) {
                // 3. Si es correcto, buscamos sus roles para construir el objeto de sesión
                $roles = $dao->obtenerRoles($dto->id);
                
                // 4. Creamos el objeto Usuario (el de sesión) y guardamos
                $usuarioSesion = new Usuario($dto, $roles);
                $usuarioSesion->guardaEnSesion();
                
                return true; 
            } else {
                // Error genérico para no dar pistas a hackers
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
        // Asegúrate de que RUTA_VISTAS esté definida en tu config.php
        return RUTA_APP . '/registro.php'; 
    }
}
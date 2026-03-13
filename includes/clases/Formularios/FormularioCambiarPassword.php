<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Formularios\Formulario;
use es\ucm\fdi\aw\Usuarios\Usuario;

class FormularioCambiarPassword extends Formulario
{
    public function __construct()
    {
        parent::__construct('cambiarPassword', [
            'urlRedireccion' => RUTA_VISTAS . '/usuarios/perfil.php' // 👈 Ajusta la ruta
        ]);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        $html = <<<EOS
        <fieldset>
            <legend>Cambiar contraseña</legend>
            
            <div class="campo">
                <label for="password_actual">Contraseña actual:</label>
                <input id="password_actual" type="password" name="password_actual" />
                {$this->getError('password_actual')}
            </div>
            
            <div class="campo">
                <label for="password_nueva">Nueva contraseña:</label>
                <input id="password_nueva" type="password" name="password_nueva" />
                <small>Mínimo 6 caracteres</small>
                {$this->getError('password_nueva')}
            </div>
            
            <div class="campo">
                <label for="password_confirm">Confirmar nueva contraseña:</label>
                <input id="password_confirm" type="password" name="password_confirm" />
                {$this->getError('password_confirm')}
            </div>
            
            <div class="campo">
                <button type="submit">Cambiar contraseña</button>
                <a href="perfil.php" class="btn-secondary">Cancelar</a>
            </div>
        </fieldset>
EOS;
        return $html;
    }
    
    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        
        // 👇 CORREGIDO: Usar idUsuario de sesión
        $idUsuario = $_SESSION['idUsuario'] ?? 0;
        
        if ($idUsuario === 0) {
            $this->errores[] = 'No hay usuario en sesión';
            return false;
        }
        
        // Buscar usuario por ID
        $usuario = Usuario::buscaUsuarioPorId($idUsuario);
        
        if (!$usuario) {
            $this->errores[] = 'Usuario no encontrado';
            return false;
        }
        
        $passwordActual = $datos['password_actual'] ?? '';
        $passwordNueva = $datos['password_nueva'] ?? '';
        $passwordConfirm = $datos['password_confirm'] ?? '';
        
        if (empty($passwordActual)) {
            $this->errores['password_actual'] = 'Debes introducir tu contraseña actual';
        } elseif (!$usuario->compruebaPassword($passwordActual)) {
            $this->errores['password_actual'] = 'La contraseña actual no es correcta';
        }
        
        if (empty($passwordNueva)) {
            $this->errores['password_nueva'] = 'Debes introducir la nueva contraseña';
        } elseif (strlen($passwordNueva) < 6) {
            $this->errores['password_nueva'] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if ($passwordNueva !== $passwordConfirm) {
            $this->errores['password_confirm'] = 'Las contraseñas no coinciden';
        }
        
        if (count($this->errores) === 0) {
            $datosActualizar = ['password' => $passwordNueva];
            if ($usuario->actualiza($datosActualizar)) {
                return true; // Redirige a perfil.php
            } else {
                $this->errores[] = 'Error al cambiar la contraseña';
            }
        }
        
        return false;
    }
    
    private function getError($campo)
    {
        return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']);
    }
}
?>
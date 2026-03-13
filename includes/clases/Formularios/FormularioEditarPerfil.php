<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Formularios\Formulario;
use es\ucm\fdi\aw\Usuarios\Usuario;

class FormularioEditarPerfil extends Formulario
{
    public function __construct()
    {
        parent::__construct('editarPerfil', [
            'urlRedireccion' => RUTA_VISTAS . '/perfil.php'
        ]);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        $usuario = $_SESSION['usuario'] ?? null;
        $nombre = $datos['nombre'] ?? $usuario->getNombre();
        $apellidos = $datos['apellidos'] ?? $usuario->getApellidos();
        $email = $datos['email'] ?? $usuario->getEmail();
        
        $html = <<<EOS
        <fieldset>
            <legend>Datos personales</legend>
            
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
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" value="$email" />
                {$this->getError('email')}
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
                <button type="submit">Guardar cambios</button>
            </div>
        </fieldset>
EOS;
        return $html;
    }
    
    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $usuario = $_SESSION['usuario'] ?? null;
        
        if (!$usuario) {
            $this->errores[] = 'No hay usuario en sesión';
            return false;
        }
        
        $nombre = trim($datos['nombre'] ?? '');
        if (empty($nombre)) {
            $this->errores['nombre'] = 'El nombre no puede estar vacío';
        }
        
        $apellidos = trim($datos['apellidos'] ?? '');
        if (empty($apellidos)) {
            $this->errores['apellidos'] = 'Los apellidos no pueden estar vacíos';
        }
        
        $email = trim($datos['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = 'Email no válido';
        }
        
        if (count($this->errores) === 0) {
            $datosActualizar = [
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'email' => $email,
                'avatar' => $datos['avatar'] ?? 'default.png'
            ];
            
            if ($usuario->actualiza($datosActualizar)) {
                // Actualizar sesión
                $_SESSION['nombre'] = $nombre;
                $_SESSION['usuario'] = Usuario::buscaUsuario($usuario->getNombreUsuario());
                return true;
            } else {
                $this->errores[] = 'Error al actualizar el perfil';
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
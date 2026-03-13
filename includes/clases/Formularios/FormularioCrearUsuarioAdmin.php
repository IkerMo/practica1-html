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
        $nombreUsuario = $datos['nombreUsuario'] ?? '';
        $email = $datos['email'] ?? '';
        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $rol = $datos['rol'] ?? '1';
        
        $html = <<<EOS
        <fieldset>
            <legend>Datos del nuevo usuario</legend>
            
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
            
            <div class="campo">
                <button type="submit">Crear usuario</button>
            </div>
        </fieldset>
EOS;
        return $html;
    }
    
    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        
        $nombreUsuario = trim($datos['nombreUsuario'] ?? '');
        if (empty($nombreUsuario) || strlen($nombreUsuario) < 4) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario debe tener al menos 4 caracteres';
        }
        
        $email = trim($datos['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = 'Email no válido';
        }
        
        $nombre = trim($datos['nombre'] ?? '');
        if (empty($nombre)) {
            $this->errores['nombre'] = 'El nombre es obligatorio';
        }
        
        $apellidos = trim($datos['apellidos'] ?? '');
        if (empty($apellidos)) {
            $this->errores['apellidos'] = 'Los apellidos son obligatorios';
        }
        
        $password = trim($datos['password'] ?? '');
        if (empty($password) || strlen($password) < 6) {
            $this->errores['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if (count($this->errores) === 0) {
            // Verificar si ya existe
            if (Usuario::buscaUsuario($nombreUsuario)) {
                $this->errores['nombreUsuario'] = 'El nombre de usuario ya existe';
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
                // Cambiar al rol seleccionado si no es cliente
                $rol = (int)($datos['rol'] ?? 1);
                if ($rol != 1) {
                    $usuario->cambiaRol($rol);
                }
                return true;
            } else {
                $this->errores[] = 'Error al crear el usuario';
            }
        }
        
        return false;
    }
    
    private function getError($campo)
    {
        return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']);
    }
    
    private function selected($valor, $actual)
    {
        return $valor === $actual ? 'selected' : '';
    }
}
?>
<?php
namespace es\ucm\fdi\aw\Formularios;
use es\ucm\fdi\aw\Usuarios\Usuario;
use es\ucm\fdi\aw\Usuarios\UsuarioAppService;
use es\ucm\fdi\aw\Usuarios\UsuarioDAO;

class FormularioRegistro extends Formulario {
    public function __construct() {
        parent::__construct('registro', ['urlRedireccion' => RUTA_RAIZ . 'inicio.php']);
    }

    protected function generaCamposFormulario(&$datos) {
        return <<<EOS
        <div class="contenedor-formulario-fdi">
            <fieldset class="form-ajustado">
                <legend>Nuevo Usuario</legend>
                <div class="bloque-entrada"><label>Nombre de usuario</label><input type="text" name="nombreUsuario" /></div>
                <div class="bloque-entrada"><label>Email</label><input type="email" name="email" /></div>
                <div class="bloque-entrada"><label>Nombre</label><input type="text" name="nombre" /></div>
                <div class="bloque-entrada"><label>Apellidos</label><input type="text" name="apellidos" /></div>
                <div class="bloque-entrada"><label>Contraseña</label><input type="password" name="password" /></div>
                <div class="bloque-entrada"><label>Repite contraseña</label><input type="password" name="password2" /></div>
                <button type="submit" class="boton-rojo">Registrarse</button>
            </fieldset>
        </div>
EOS;
    }

    protected function procesaFormulario(&$datos) {
        $this->errores = [];
        $password = $datos['password'] ?? '';
        if ($password !== ($datos['password2'] ?? '')) $this->errores[] = 'Las contraseñas no coinciden';
        if (count($this->errores) > 0) return false;
        $service = new UsuarioAppService();
        $dao = new UsuarioDAO();
        $dto = $service->registro(trim($datos['nombreUsuario']), trim($datos['email']), $datos['nombre'], $datos['apellidos'], $password);
        if ($dto) {
            $roles = $dao->obtenerRoles($dto->id);
            $usuarioSesion = Usuario::construirDesdeDTO($dto, $roles);
            $usuarioSesion->guardaEnSesion();
            return true;
        }
        return false;
    }
}
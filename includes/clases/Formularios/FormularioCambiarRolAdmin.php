<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Formularios\Formulario;
use es\ucm\fdi\aw\Usuarios\Usuario;
use es\ucm\fdi\aw\Usuarios\UsuarioDAO;

class FormularioCambiarRolAdmin extends Formulario
{
    private $idUsuario;
    
    public function __construct($idUsuario)
    {
        $this->idUsuario = $idUsuario;
        parent::__construct('cambiarRolAdmin', [
            'urlRedireccion' => RUTA_VISTAS . '/usuarios/admin/listar.php'
        ]);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        $dao = new UsuarioDAO();
        $usuario = $dao->getPorId($this->idUsuario);
        
        if (!$usuario) {
            return '<p class="error">Usuario no encontrado</p>';
        }
        
        $rolActual = $datos['rol'] ?? $usuario['rol_actual'];
        
        $html = <<<EOS
        <fieldset>
            <legend>Cambiar rol de {$usuario['nombreUsuario']}</legend>
            
            <div class="campo">
                <label>Usuario:</label>
                <span class="texto-info">{$usuario['nombreUsuario']} ({$usuario['nombre']} {$usuario['apellidos']})</span>
            </div>
            
            <div class="campo">
                <label for="rol">Nuevo rol:</label>
                <select id="rol" name="rol">
                    <option value="1" {$this->selected('1', $rolActual)}>Cliente</option>
                    <option value="2" {$this->selected('2', $rolActual)}>Camarero</option>
                    <option value="3" {$this->selected('3', $rolActual)}>Cocinero</option>
                    <option value="4" {$this->selected('4', $rolActual)}>Gerente</option>
                </select>
                {$this->getError('rol')}
            </div>
            
            <div class="campo">
                <button type="submit">Cambiar rol</button>
                <a href="listar.php" class="btn-secondary">Cancelar</a>
            </div>
        </fieldset>
EOS;
        return $html;
    }
    
    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        
        $nuevoRol = (int)($datos['rol'] ?? 0);
        
        if ($nuevoRol < 1 || $nuevoRol > 4) {
            $this->errores['rol'] = 'Rol no válido';
            return false;
        }
        
        $usuario = Usuario::buscaUsuarioPorId($this->idUsuario);
        
        if (!$usuario) {
            $this->errores[] = 'Usuario no encontrado';
            return false;
        }
        
        if ($usuario->cambiaRol($nuevoRol)) {
            return true;
        } else {
            $this->errores[] = 'Error al cambiar el rol';
            return false;
        }
    }
    
    private function getError($campo)
    {
        return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']);
    }
    
    private function selected($valor, $actual)
    {
        return $valor === (string)$actual ? 'selected' : '';
    }
}
?>
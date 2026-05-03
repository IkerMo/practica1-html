<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Formularios\Formulario;
use es\ucm\fdi\aw\Recompensa\RecompensaAppService;
use es\ucm\fdi\aw\Producto\ProductoAppService;

class FormularioRecompensa extends Formulario {
    private $idRecompensa;

    public function __construct($idRecompensa = null) {
        $this->idRecompensa = $idRecompensa;
        parent::__construct('formRecompensa', [
            'urlRedireccion' => RUTA_VISTAS . '/gerente/listarRecompensas.php'
        ]);
    }

    protected function generaCamposFormulario(&$datos) {
        $productoService = new ProductoAppService();
        $productos = $productoService->getTodosProductos();
        
        // Obtener valores actuales si estamos editando
        $productoActual = '';
        $coinsActual = '';
        
        if ($this->idRecompensa) {
            $recompensaService = new RecompensaAppService();
            $recompensa = $recompensaService->obtenerRecompensaPorId($this->idRecompensa);
            if ($recompensa != null) {
                $productoActual = $recompensa->producto_id;
                $coinsActual = $recompensa->bistrocoins_requeridos;
            }
        }
        
        // Si hay datos enviados del formulario (por error), usarlos
        if (isset($datos['producto_id'])) {
            $productoActual = $datos['producto_id'];
        }
        if (isset($datos['bistrocoins_requeridos'])) {
            $coinsActual = $datos['bistrocoins_requeridos'];
        }
        
        // Si coinsActual está vacío, poner 0
        if ($coinsActual == '') {
            $coinsActual = 0;
        }
        
        $opciones = '';
        foreach ($productos as $p) {
            $selected = '';
            if ($productoActual == $p->id) {
                $selected = 'selected';
            }
            $opciones .= "<option value='" . $p->id . "' " . $selected . ">" . $p->nombre . "</option>";
        }

        $titulo = 'Nueva Recompensa';
        if ($this->idRecompensa) {
            $titulo = 'Editar Recompensa';
        }

        return <<<EOS
        <fieldset>
            <legend>{$titulo}</legend>
            <input type="hidden" name="id" value="{$this->idRecompensa}">
            <div class="campo">
                <label>Producto:</label>
                <select name="producto_id" required>
                    <option value="">Selecciona un producto...</option>
                    {$opciones}
                </select>
                {$this->getError('producto_id')}
            </div>
            <div class="campo">
                <label>BistroCoins necesarios:</label>
                <input type="number" name="bistrocoins_requeridos" value="{$coinsActual}" min="1" required>
                {$this->getError('bistrocoins_requeridos')}
            </div>
            <div class="campo">
                <button type="submit" class="btn-primary">Guardar</button>
                <a href="listarRecompensas.php" class="btn-secondary">Cancelar</a>
            </div>
        </fieldset>
EOS;
    }

    protected function procesaFormulario(&$datos) {
        $this->errores = array();
        
        $id = 0;
        if (isset($datos['id'])) {
            $id = (int)$datos['id'];
        }
        
        $productoId = 0;
        if (isset($datos['producto_id'])) {
            $productoId = (int)$datos['producto_id'];
        }
        
        $coins = 0;
        if (isset($datos['bistrocoins_requeridos'])) {
            $coins = (int)$datos['bistrocoins_requeridos'];
        }

        // Validaciones
        if ($productoId <= 0) {
            $this->errores['producto_id'] = 'Debes seleccionar un producto';
        }
        
        if ($coins <= 0) {
            $this->errores['bistrocoins_requeridos'] = 'Los BistroCoins deben ser un número positivo';
        }

        if (count($this->errores) > 0) {
            return false;
        }

        $service = new RecompensaAppService();

        try {
            if ($id) {
                $service->actualizarRecompensa($id, $productoId, $coins);
            } else {
                $service->crearRecompensa($productoId, $coins);
            }
            return true;
        } catch (Exception $e) {
            $this->errores[] = $e->getMessage();
            return false;
        }
    }

    private function getError($campo) {
        $errores = $this->errores;
        if (isset($errores[$campo])) {
            return '<span class="error">' . htmlspecialchars($errores[$campo]) . '</span>';
        }
        return '';
    }
}
?>
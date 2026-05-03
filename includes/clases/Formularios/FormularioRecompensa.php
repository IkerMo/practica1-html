<?php
namespace es\ucm\fdi\aw\Formularios;
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
        $service = new ProductoAppService();
        $productos = $service->getTodosProductos();
        $opciones = '';
        foreach ($productos as $p) {
            $opciones .= "<option value='{$p->id}'>{$p->nombre}</option>";
        }

        $productoId = $datos['producto_id'] ?? '';
        $coins = $datos['bistrocoins_requeridos'] ?? '';

        if ($this->idRecompensa) {
            $recompensaService = new RecompensaAppService();
            $r = $recompensaService->obtenerRecompensaPorId($this->idRecompensa);
            if ($r) {
                $productoId = $productoId ?: $r->producto_id;
                $coins = $coins ?: $r->bistrocoins_requeridos;
            }
        }

        return <<<EOS
        <fieldset>
            <legend>{$this->idRecompensa ? 'Editar' : 'Nueva'} Recompensa</legend>
            <input type="hidden" name="id" value="{$this->idRecompensa}">
            <div><label>Producto:</label>
                <select name="producto_id" required>$opciones</select>
                {$this->getError('producto_id')}
            </div>
            <div><label>BistroCoins necesarios:</label>
                <input type="number" name="bistrocoins_requeridos" value="$coins" min="1" required>
                {$this->getError('bistrocoins_requeridos')}
            </div>
            <button type="submit">Guardar</button>
        </fieldset>
EOS;
    }

    protected function procesaFormulario(&$datos) {
        $service = new RecompensaAppService();
        $id = $datos['id'] ?? null;
        $productoId = (int)$datos['producto_id'];
        $coins = (int)$datos['bistrocoins_requeridos'];

        if ($productoId <= 0 || $coins <= 0) {
            $this->errores[] = 'Datos inválidos';
            return false;
        }

        try {
            if ($id) {
                $service->actualizarRecompensa($id, $productoId, $coins);
            } else {
                $service->crearRecompensa($productoId, $coins);
            }
            return true;
        } catch (\Exception $e) {
            $this->errores[] = $e->getMessage();
            return false;
        }
    }

    private function getError($campo) {
        return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']);
    }
}
?>
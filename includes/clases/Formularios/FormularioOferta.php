<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Oferta\OfertaAppService;
use es\ucm\fdi\aw\Producto\ProductoAppService;

class FormularioOferta extends Formulario {
    private $idOferta;

    public function __construct($idOferta = null) {
        $this->idOferta = $idOferta;
        parent::__construct('formOferta', [
            'urlRedireccion' => 'ofertas.php'
        ]);
    }

    protected function generaCamposFormulario(&$datos) {
        $serviceOferta = new OfertaAppService();
        $serviceProducto = new ProductoAppService();
        $nombre = $datos['nombre'] ?? '';
        $descripcion = $datos['descripcion'] ?? '';
        $fecha_inicio = $datos['fecha_inicio'] ?? date('Y-m-d');
        $fecha_fin = $datos['fecha_fin'] ?? date('Y-m-d', strtotime('+7 days'));
        $porcentaje_descuento = $datos['porcentaje_descuento'] ?? '';
        $activo = isset($datos['activo']) ? 'checked' : '';

        if ($this->idOferta && empty($datos)) {
            $oferta = $serviceOferta->getOferta($this->idOferta);
            if ($oferta) {
                $nombre = $oferta->nombre;
                $descripcion = $oferta->descripcion;
                $fecha_inicio = $oferta->fecha_inicio;
                $fecha_fin = $oferta->fecha_fin;
                $porcentaje_descuento = $oferta->porcentaje_descuento;
                $activo = $oferta->activo ? 'checked' : '';
                $datos['oferta_producto_id'] = array_keys($oferta->productos);
                $datos['oferta_producto_cantidad'] = array_values($oferta->productos);
            }
        }

        $productos = $serviceProducto->getCarta();
        $selector = '';
        foreach ($productos as $producto) {
            $cantidad = '';
            $checked = '';
            if (!empty($datos['oferta_producto_id'])) {
                foreach ($datos['oferta_producto_id'] as $index => $pid) {
                    if ((int)$pid === (int)$producto->id) {
                        $checked = 'checked';
                        $cantidad = $datos['oferta_producto_cantidad'][$index] ?? '';
                    }
                }
            }
            $selector .= "<div><label><input type='checkbox' name='oferta_producto_id[]' value='{$producto->id}' {$checked}> {$producto->nombre} ({$producto->getPrecioFinal()} €)</label> " .
                      "<input type='number' min='0' name='oferta_producto_cantidad[]' value='{$cantidad}' placeholder='cant'></div>";
        }

        return <<<EOS
<fieldset>
    <legend>Oferta</legend>
    <input type="hidden" name="id" value="{$this->idOferta}">
    <div><label>Nombre: <input type="text" name="nombre" value="$nombre" required></label></div>
    <div><label>Descripción: <textarea name="descripcion">$descripcion</textarea></label></div>
    <div><label>Fecha inicio: <input type="date" name="fecha_inicio" value="$fecha_inicio" required></label></div>
    <div><label>Fecha fin: <input type="date" name="fecha_fin" value="$fecha_fin" required></label></div>
    <div><label>% Descuento: <input type="number" step="0.01" name="porcentaje_descuento" value="$porcentaje_descuento" required></label></div>
    <div><label><input type="checkbox" name="activo" $activo> Activa</label></div>
    <fieldset><legend>Productos (pack)</legend>
        $selector
    </fieldset>
    <button type="submit">Guardar</button>
</fieldset>
EOS;
    }

    protected function procesaFormulario(&$datos) {
        $service = new OfertaAppService();

        if (!empty($datos['id'])) {
            $res = $service->actualizarOferta($datos);
        } else {
            $res = $service->crearOferta($datos);
        }

        if (!$res) {
            $this->errores[] = 'Error al guardar la oferta.';
        } elseif (is_array($res) && !empty($res)) {
            foreach ($res as $key => $error) {
                $this->errores[$key] = $error;
            }
        }
    }
}
'@; $content | Out-File -Encoding UTF8 includes\clases\Formularios\FormularioOferta.php
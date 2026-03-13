<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Producto\ProductoAppService;
use es\ucm\fdi\aw\Categoria\CategoriaAppService;

class FormularioProducto extends Formulario {
    private $idProducto;

    public function __construct($idProducto = null) {
        $this->idProducto = $idProducto;
        parent::__construct('formProd', [
            'urlRedireccion' => 'listarProductos.php',
            'enctype' => 'multipart/form-data' 
        ]);
    }

    protected function generaCamposFormulario(&$datos) {
        $serviceProd = new ProductoAppService();
        $serviceCat = new CategoriaAppService();
        
        // Valores por defecto
        $nombre = $datos['nombre'] ?? '';
        $desc = $datos['descripcion'] ?? '';
        $catActual = $datos['categoria'] ?? '';
        $precioBase = $datos['precioBase'] ?? '';
        $iva = $datos['iva'] ?? 10;
        $disponible = isset($datos['disponible']) ? 'checked' : '';

        // Si editamos, cargamos del DTO usando tu método getProducto($id)
        if ($this->idProducto && empty($datos)) {
            $p = $serviceProd->getProducto($this->idProducto);
            if ($p) {
                $nombre = $p->nombre;
                $desc = $p->descripcion;
                $catActual = $p->categoria;
                $precioBase = $p->precioBase;
                $iva = $p->iva;
                $disponible = $p->disponible ? 'checked' : '';
            }
        }

        // Cargamos categorías usando tu método getTodasCategorias()
        $listaCategorias = $serviceCat->getTodasCategorias(); 
        $opciones = "";
        foreach ($listaCategorias as $cat) {
            // Asumo que tu CategoriaDTO tiene el atributo ->nombre
            $sel = ($cat->nombre == $catActual) ? 'selected' : '';
            $opciones .= "<option value='{$cat->nombre}' $sel>{$cat->nombre}</option>";
        }

        return <<<EOS
        <fieldset>
            <legend>Datos del Producto</legend>
            <input type="hidden" name="idProducto" value="{$this->idProducto}">
            
            <div><label>Nombre:</label><input type="text" name="nombre" value="$nombre" required></div>
            <div><label>Descripción:</label><textarea name="description">$desc</textarea></div>
            
            <div>
                <label>Categoría:</label>
                <select name="categoria" required>
                    <option value="">Selecciona...</option>
                    $opciones
                </select>
            </div>

            <div>
                <label>Precio Base (€):</label>
                <input type="number" step="0.01" name="precioBase" id="precioBase" value="$precioBase" oninput="recalc()" required>
            </div>

            <div>
                <label>IVA (%):</label>
                <select name="iva" id="iva" onchange="recalc()">
                    <option value="4"    ($iva == 4 ? 'selected' : '')>4%</option>
                    <option value="10"   ($iva == 10 ? 'selected' : '')>10%</option>
                    <option value="21"   ($iva == 21 ? 'selected' : '')>21%</option>
                </select>
            </div>

            <div style="margin: 10px 0; font-weight: bold;">
                Precio Final: <span id="pFinal">0.00</span> €
            </div>

            <div><label><input type="checkbox" name="disponible" $disponible> Disponible</label></div>
            <div><label>Imágenes:</label><input type="file" name="imagenes[]" multiple></div>

            <button type="submit">Guardar</button>
        </fieldset>

        <script>
            function recalc() {
                const b = parseFloat(document.getElementById('precioBase').value) || 0;
                const i = parseFloat(document.getElementById('iva').value) || 0;
                document.getElementById('pFinal').innerText = (b * (1 + (i/100))).toFixed(2);
            }
            recalc();
        </script>
EOS;
    }

    protected function procesaFormulario(&$datos) {
        $service = new ProductoAppService();
        
        $id = $datos['idProducto'] ?? null;
        $info = [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['description'],
            'categoria' => $datos['categoria'],
            'precioBase' => $datos['precioBase'],
            'iva' => $datos['iva'],
            'disponible' => isset($datos['disponible']),
            'imagenes' => $_FILES['imagenes']['name'] ?? [] // Simplificado
        ];

        if ($id) {
            // Usamos el nuevo método que añadimos arriba
            $res = $service->actualizarProducto($id, $info);
        } else {
            // Usamos tu método original
            $res = $service->registrarProducto($info);
        }

        if (!$res) {
            $this->errores[] = "Error al procesar el producto.";
        }
    }
}
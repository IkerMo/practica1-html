<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Producto\ProductoAppService;
use es\ucm\fdi\aw\Categoria\CategoriaAppService;

class FormularioProducto extends Formulario {
    private $idProducto;

    public function __construct($idProducto = null) {
        $this->idProducto = $idProducto;
        parent::__construct('formProd', [
            'urlRedireccion' => 'ListarProductos.php',
            'enctype' => 'multipart/form-data' 
        ]);
    }

    protected function generaCamposFormulario(&$datos) {
        $serviceProd = new ProductoAppService();
        $serviceCat = new CategoriaAppService();
        
        $nombre = $datos['nombre'] ?? '';
        $desc = $datos['descripcion'] ?? '';
        $catActual = $datos['categoria_id'] ?? '';
        $precioBase = $datos['precio_base'] ?? '';
        $iva = $datos['iva'] ?? 10;
        $disponible = isset($datos['disponible']) ? 'checked' : '';

        if ($this->idProducto && empty($datos)) {
            $p = $serviceProd->getProducto($this->idProducto);
            if ($p) {
                $nombre = $p->nombre;
                $desc = $p->descripcion;
                $catActual = $p->categoria_id;
                $precioBase = $p->precio_base;
                $iva = $p->iva;
                $disponible = $p->disponible ? 'checked' : '';
            }
        }

        $listaCategorias = $serviceCat->getTodasCategorias(); 
        $opciones = "";
        foreach ($listaCategorias as $cat) {
            // Usamos $cat->id como valor para que la base de datos no reciba NULL
            $sel = ($cat->id == $catActual) ? 'selected' : '';
            $opciones .= "<option value='{$cat->id}' $sel>{$cat->nombre}</option>";
        }

        return <<<EOS
        <fieldset>
            <legend>Datos del Producto</legend>
            <input type="hidden" name="idProducto" value="{$this->idProducto}">
            
            <div><label>Nombre:</label><input type="text" name="nombre" value="$nombre" required></div>
            <div><label>Descripción:</label><textarea name="descripcion">$desc</textarea></div>
            
            <div>
                <label>Categoría:</label>
                <select name="categoria_id" required>
                    <option value="">Selecciona...</option>
                    $opciones
                </select>
            </div>

            <div>
                <label>Precio Base (€):</label>
                <input type="number" step="0.01" name="precio_base" id="precio_base" value="$precioBase" oninput="recalc()" required>
            </div>

            <div>
                <label>IVA (%):</label>
                <select name="iva" id="iva" onchange="recalc()">
                    <option value="4"  ($iva == 4 ? 'selected' : '')>4%</option>
                    <option value="10" ($iva == 10 ? 'selected' : '')>10%</option>
                    <option value="21" ($iva == 21 ? 'selected' : '')>21%</option>
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
                const b = parseFloat(document.getElementById('precio_base').value) || 0;
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
        
        // 1. Manejo de la subida
        $nombreImagen = 'default.jpg'; 
        if (isset($_FILES['imagenes']) && $_FILES['imagenes']['error'][0] === UPLOAD_ERR_OK) {
            $nombreReal = $_FILES['imagenes']['name'][0];
            $rutaCarpeta = RAIZ_APP . '/IMG/productos/';
            
            // Creamos la carpeta si no existe para evitar errores
            if (!file_exists($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0777, true);
            }

            $rutaDestino = $rutaCarpeta . $nombreReal;
            
            if (move_uploaded_file($_FILES['imagenes']['tmp_name'][0], $rutaDestino)) {
                $nombreImagen = $nombreReal;
            }
        }

        // 2. Mapeo: Aseguramos que la llave es 'imagen_principal'
        $info = [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'],
            'categoria_id' => $datos['categoria_id'],
            'precio_base' => $datos['precio_base'],
            'iva' => $datos['iva'],
            'disponible' => isset($datos['disponible']),
            'ofertado' => true,
            'imagen_principal' => [$nombreImagen] // Mandamos array porque tu DAO hace implode()
        ];

        if ($id) {
            $res = $service->actualizarProducto($id, $info);
        } else {
            $res = $service->registrarProducto($info);
        }

        return $res ? true : "Error al procesar el producto.";
    }
}
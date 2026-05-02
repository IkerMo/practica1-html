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
        $requiereCocina = (!array_key_exists('idProducto', $datos) || isset($datos['requiere_cocina'])) ? 'checked' : '';
        $imagenesActuales = '';

        if ($this->idProducto && empty($datos)) {
            $p = $serviceProd->getProducto($this->idProducto);
            if ($p) {
                $nombre = $p->nombre;
                $desc = $p->descripcion;
                $catActual = $p->categoria_id;
                $precioBase = $p->precio_base;
                $iva = $p->iva;
                $disponible = $p->disponible ? 'checked' : '';
                $requiereCocina = $p->requiere_cocina ? 'checked' : '';
                
                // Mostrar imágenes existentes
                $todasImgs = $p->getTodasImagenes();
                if (!empty($todasImgs)) {
                    $imagenesActuales = '<div class="mt-10 mb-10"><strong>Imágenes actuales:</strong><div class="flex-gap-10 flex-wrap mt-5" style="display:flex;">';
                    foreach ($todasImgs as $img) {
                        $url = RUTA_BASE . '/IMG/productos/' . $img;
                        $imagenesActuales .= "<img src='$url' class='img-mini-80'>";
                    }
                    $imagenesActuales .= '</div></div>';
                }
            }
        }

        $listaCategorias = $serviceCat->getTodasCategorias(); 
        $opciones = "";
        foreach ($listaCategorias as $cat) {
            $sel = ($cat->id == $catActual) ? 'selected' : '';
            $opciones .= "<option value='{$cat->id}' $sel>{$cat->nombre}</option>";
        }

        $selIva4 = ($iva == 4) ? 'selected' : '';
        $selIva10 = ($iva == 10) ? 'selected' : '';
        $selIva21 = ($iva == 21) ? 'selected' : '';

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
                    <option value="4" $selIva4>4%</option>
                    <option value="10" $selIva10>10%</option>
                    <option value="21" $selIva21>21%</option>
                </select>
            </div>

            <div class="mt-10 mb-10 font-bold">
                Precio Final: <span id="pFinal">0.00</span> €
            </div>

            <div><label><input type="checkbox" name="disponible" $disponible> Disponible</label></div>
            <div><label><input type="checkbox" name="requiere_cocina" $requiereCocina> Requiere preparaciÃ³n en cocina</label></div>
            
            $imagenesActuales
            
            <div>
                <label>Subir imágenes (puedes seleccionar varias):</label>
                <input type="file" name="imagenes[]" multiple accept="image/*">
                <small>La primera imagen será la principal. El resto se mostrarán en la galería del producto.</small>
            </div>

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
        
        $rutaCarpeta = RAIZ_APP . '/IMG/productos/';
        if (!file_exists($rutaCarpeta)) {
            mkdir($rutaCarpeta, 0777, true);
        }

        $imagenPrincipal = null;
        $imagenesAdicionales = [];

        // Procesar TODAS las imágenes subidas
        if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['name'])) {
            $totalFiles = count($_FILES['imagenes']['name']);
            
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($_FILES['imagenes']['error'][$i] === UPLOAD_ERR_OK) {
                    $nombreReal = $_FILES['imagenes']['name'][$i];
                    // Nombre único para evitar colisiones
                    $ext = pathinfo($nombreReal, PATHINFO_EXTENSION);
                    $nombreUnico = 'prod_' . uniqid() . '.' . $ext;
                    $rutaDestino = $rutaCarpeta . $nombreUnico;
                    
                    if (move_uploaded_file($_FILES['imagenes']['tmp_name'][$i], $rutaDestino)) {
                        if ($i === 0 && $imagenPrincipal === null) {
                            $imagenPrincipal = $nombreUnico; // Primera = principal
                        } else {
                            $imagenesAdicionales[] = $nombreUnico;
                        }
                    }
                }
            }
        }

        $nombre = filter_var(trim($datos['nombre'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $descripcion = filter_var(trim($datos['descripcion'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $categoria_id = filter_var($datos['categoria_id'] ?? '', FILTER_VALIDATE_INT);
        $precio_base = filter_var($datos['precio_base'] ?? '', FILTER_VALIDATE_FLOAT);
        $iva = filter_var($datos['iva'] ?? '', FILTER_VALIDATE_INT);
        
        if (empty($nombre)) {
            $this->errores['nombre'] = "El nombre es obligatorio.";
        }
        if ($categoria_id === false || $categoria_id <= 0) {
            $this->errores['categoria_id'] = "Categoría inválida.";
        }
        if ($precio_base === false || $precio_base < 0) {
            $this->errores['precio_base'] = "Precio base inválido.";
        }
        if ($iva === false || !in_array($iva, [4, 10, 21])) {
            $this->errores['iva'] = "IVA inválido.";
        }
        
        if (count($this->errores) === 0) {
            $info = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'categoria_id' => $categoria_id,
                'precio_base' => $precio_base,
                'iva' => $iva,
                'disponible' => isset($datos['disponible']),
                'requiere_cocina' => isset($datos['requiere_cocina']),
                'ofertado' => true,
                'imagen_principal' => $imagenPrincipal,
                'imagenes_adicionales' => $imagenesAdicionales
            ];
        
            if ($id) {
                $res = $service->actualizarProducto($id, $info);
            } else {
                $res = $res = $service->registrarProducto($info);
            }
        }
        if (!$res) {
            $this->errores[] = "Error al procesar el producto.";
        }
    }
}

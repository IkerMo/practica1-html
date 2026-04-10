<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Categoria\CategoriaAppService;

class FormularioCategoria extends Formulario {
    private $idCategoria;

    public function __construct($idCategoria = null) {
        $this->idCategoria = $idCategoria;
        parent::__construct('formCat', [
            'urlRedireccion' => 'listarCategorias.php',
            'enctype' => 'multipart/form-data'
        ]);
    }

    protected function generaCamposFormulario(&$datos) {
        $service = new CategoriaAppService();
        
        $nombre = $datos['nombre'] ?? '';
        $desc = $datos['descripcion'] ?? '';

        if ($this->idCategoria && empty($datos)) {
            $cat = $service->getCategoria($this->idCategoria);
            if ($cat) {
                $nombre = $cat->nombre;
                $desc = $cat->descripcion;
            }
        }

        return <<<EOS
        <fieldset>
            <legend>Datos de la Categoría</legend>
            <input type="hidden" name="idCategoria" value="{$this->idCategoria}">
            
            <div class="grupo-control">
                <label>Nombre de la Categoría:</label>
                <input type="text" name="nombre" value="$nombre" required>
            </div>

            <div class="grupo-control">
                <label>Descripción:</label>
                <textarea name="descripcion" rows="4">$desc</textarea>
            </div>

            <div class="grupo-control">
                <label>Imagen representativa:</label>
                <input type="file" name="imagen" accept="image/*">
            </div>

            <button type="submit" class="btn-primario">Guardar Categoría</button>
        </fieldset>
EOS;
    }

    protected function procesaFormulario(&$datos) {
        $service = new CategoriaAppService();
        $id = $datos['idCategoria'] ?? null;
        $nombre = filter_var(trim($datos['nombre'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $desc = filter_var(trim($datos['descripcion'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        
        // Imagen 
        $nombreImagen = $_FILES['imagen']['name'] ?? null;

        if (empty($nombre)) {
            $this->errores['nombre'] = "El nombre es obligatorio.";
        }

        if (count($this->errores) === 0) {
            if ($id) {
                $res = $service->actualizarCategoria($id, $nombre, $desc, $nombreImagen);
            } else {
                $res = $service->crearCategoria($nombre, $desc, $nombreImagen ?: "default_cat.png");
            }

            if (!$res) {
                $this->errores[] = "Error al guardar la categoría en la base de datos.";
            }
        }
    }
}
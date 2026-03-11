<?php
namespace es\ucm\fdi\aw;

class FormularioCategoria extends Formulario {
    private $idCategoria;

    public function __construct($idCategoria = null) {
        $this->idCategoria = $idCategoria;
        parent::__construct('formCat', ['urlRedireccion' => 'gestionCategorias.php']);
    }

    protected function generaCamposFormulario(&$datos) {
        // Si estamos editando, buscamos los datos actuales
        if ($this->idCategoria) {
            $service = new CategoriaAppService();
            $cat = $service->getCategoria($this->idCategoria);
            $nombre = $cat->nombre;
            $desc = $cat->descripcion;
        }

        $nombre = $datos['nombre'] ?? $nombre ?? '';
        $desc = $datos['descripcion'] ?? $desc ?? '';

        return <<<EOS
        <fieldset>
            <legend>Datos de la Categoría</legend>
            <input type="hidden" name="id" value="{$this->idCategoria}">
            <div><label>Nombre:</label><input type="text" name="nombre" value="$nombre"></div>
            <div><label>Descripción:</label><textarea name="descripcion">$desc</textarea></div>
            <div><label>Imagen:</label><input type="file" name="imagen"></div>
            <button type="submit">Guardar Categoría</button>
        </fieldset>
EOS;
    }

    protected function procesaFormulario(&$datos) {
        $nombre = trim($datos['nombre'] ?? '');
        $desc = trim($datos['descripcion'] ?? '');
        $id = $datos['id'] ?? null;

        if (empty($nombre)) $this->errores['nombre'] = "El nombre es obligatorio.";

        if (count($this->errores) === 0) {
            $service = new CategoriaAppService();
            // Lógica para Crear o Actualizar
            if ($id) {
                // Actualizar...
            } else {
                $service->crearCategoria($nombre, $desc, "imagen_por_defecto.png");
            }
        }
    }
}
?>
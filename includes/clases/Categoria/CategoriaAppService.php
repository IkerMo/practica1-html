<?php
namespace es\ucm\fdi\aw\Categoria;

class CategoriaAppService {
    private $dao;

    public function __construct() {
        $this->dao = new CategoriaDAO();
    }

    public function getTodasCategorias() {
        return $this->dao->listarCategorias();
    }

    public function crearCategoria($nombre, $descripcion, $imagen) {
        $dto = new CategoriaDTO($nombre, $descripcion, $imagen);
        return $this->dao->crear($dto);
    }

    public function getCategoria($id) {
        return $this->dao->buscaPorId($id);
    }
    
    // Aquí podrías añadir lógica: "No borrar si tiene productos"
    public function borrarCategoria($id) {
        return $this->dao->borrar($id);
    }
    public function actualizarCategoria($id, $nombre, $descripcion, $imagen) {
    // Buscamos la categoría actual
        $dto = $this->dao->buscaPorId($id);
        if ($dto) {
            $dto->nombre = $nombre;
            $dto->descripcion = $descripcion;
            // Solo actualizamos la imagen si se envía una nueva
            if ($imagen) {
                $dto->imagen = $imagen;
            }
            return $this->dao->actualizar($dto);
        }
        return false;
    }
}
?>
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
}
?>
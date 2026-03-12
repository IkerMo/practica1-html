<?php
namespace es\ucm\fdi\aw\Categoria;
use es\ucm\fdi\aw\Aplicacion;

class CategoriaDAO {
    
    public function listarCategorias() {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = "SELECT * FROM Categorias ORDER BY nombre ASC";
        $rs = $conn->query($query);
        $categorias = [];
        while ($fila = $rs->fetch_assoc()) {
            $categorias[] = new CategoriaDTO($fila['nombre'], $fila['descripcion'], $fila['imagen'], $fila['id']);
        }
        $rs->free();
        return $categorias;
    }

    public function buscaPorId($id) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("SELECT * FROM Categorias WHERE id=%d", $id);
        $rs = $conn->query($query);
        if ($rs && $rs->num_rows == 1) {
            $fila = $rs->fetch_assoc();
            $cat = new CategoriaDTO($fila['nombre'], $fila['descripcion'], $fila['imagen'], $fila['id']);
            $rs->free();
            return $cat;
        }
        return null;
    }

    public function crear(CategoriaDTO $dto) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("INSERT INTO Categorias (nombre, descripcion, imagen) VALUES ('%s', '%s', '%s')",
            $conn->real_escape_string($dto->nombre),
            $conn->real_escape_string($dto->descripcion),
            $conn->real_escape_string($dto->imagen)
        );
        if ($conn->query($query)) {
            $dto->id = $conn->insert_id;
            return $dto;
        }
        return false;
    }

    public function actualizar(CategoriaDTO $dto) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("UPDATE Categorias SET nombre='%s', descripcion='%s', imagen='%s' WHERE id=%d",
            $conn->real_escape_string($dto->nombre),
            $conn->real_escape_string($dto->descripcion),
            $conn->real_escape_string($dto->imagen),
            $dto->id
        );
        return $conn->query($query);
    }

    public function borrar($id) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("DELETE FROM Categorias WHERE id=%d", $id);
        return $conn->query($query);
    }
}
?>
<?php
namespace es\ucm\fdi\aw\Producto;

use es\ucm\fdi\aw\Aplicacion;

class ProductoDAO {

    public function listarTodos() {
        $conn = Aplicacion::getInstance()->getConexionBd();
        // Solo traemos los que están "ofertados" (en la carta) por defecto
        $query = "SELECT * FROM Productos WHERE ofertado = 1 ORDER BY categoria_id, nombre ASC";
        $rs = $conn->query($query);
        
        $lista = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $lista[] = $this->filaADto($fila);
            }
            $rs->free();
        }
        return $lista;
    }

    public function buscarPorId($id) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT * FROM Productos WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $fila = $result->fetch_assoc();
        $stmt->close();

        return $fila ? $this->filaADto($fila) : null;
    }

    public function crear(ProductoDTO $p) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = "INSERT INTO Productos (nombre, descripcion, categoria_id, imagen_principal, precio_base, iva, disponible, ofertado) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        // Convertimos el array de imágenes a un string (ej: imagen1.jpg,imagen2.jpg)
        $imgsStr = implode(',', $p->imagen_principal);
        $disponible = $p->disponible ? 1 : 0;
        $ofertado = $p->ofertado ? 1 : 0;

        $stmt->bind_param('ssssddii', 
            $p->nombre, $p->descripcion, $p->categoria_id, $imgsStr, 
            $p->precio_base, $p->iva, $disponible, $ofertado
        );
        
        if ($stmt->execute()) {
            $p->id = $conn->insert_id;
            $stmt->close();
            return $p;
        }
        $stmt->close();
        return false;
    }

    public function actualizar(ProductoDTO $p) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = "UPDATE Productos SET nombre=?, descripcion=?, categoria_id=?, imagen_principal=?, 
                  precio_base=?, iva=?, disponible=?, ofertado=? WHERE id=?";
        $stmt = $conn->prepare($query);
        
        $imgsStr = implode(',', $p->imagen_principal);
        $disponible = $p->disponible ? 1 : 0;
        $ofertado = $p->ofertado ? 1 : 0;

        $stmt->bind_param('ssssddiii', 
            $p->nombre, $p->descripcion, $p->categoria_id, $imgsStr, 
            $p->precio_base, $p->iva, $disponible, $ofertado, $p->id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    private function filaADto(array $fila) {
        $p = new ProductoDTO();
        $p->id = (int)$fila['id'];
        $p->nombre = $fila['nombre'];
        $p->descripcion = $fila['descripcion'];
        $p->categoria_id = $fila['categoria_id'];
        // Reconvertimos el string de la BD a un array de imágenes
        $p->imagen_principal = !empty($fila['imagen_principal']) ? explode(',', $fila['imagen_principal']) : [];
        $p->precio_base = (float)$fila['precio_base'];
        $p->iva = (float)$fila['iva'];
        $p->disponible = (bool)$fila['disponible'];
        $p->ofertado = (bool)$fila['ofertado'];
        return $p;
    }
}
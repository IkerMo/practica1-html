<?php
namespace es\ucm\fdi\aw\Producto;

use es\ucm\fdi\aw\Aplicacion;

class ProductoDAO {

    public function listarTodos() {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = "SELECT * FROM Productos WHERE ofertado = 1 ORDER BY categoria_id, nombre ASC";
        $rs = $conn->query($query);
        
        $lista = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $dto = $this->filaADto($fila);
                $dto->imagenes = $this->obtenerImagenes($dto->id);
                $lista[] = $dto;
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

        if (!$fila) return null;
        
        $dto = $this->filaADto($fila);
        $dto->imagenes = $this->obtenerImagenes($dto->id);
        return $dto;
    }

    public function crear(ProductoDTO $p) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = "INSERT INTO Productos (nombre, descripcion, categoria_id, imagen_principal, precio_base, iva, disponible, ofertado) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        $imgPrincipal = $p->imagen_principal ?: 'default.jpg';
        $disponible = $p->disponible ? 1 : 0;
        $ofertado = $p->ofertado ? 1 : 0;

        $stmt->bind_param('ssissdii', 
            $p->nombre, $p->descripcion, $p->categoria_id, $imgPrincipal, 
            $p->precio_base, $p->iva, $disponible, $ofertado
        );
        
        if ($stmt->execute()) {
            $p->id = $conn->insert_id;
            $stmt->close();
            
            // Guardar imágenes adicionales
            if (!empty($p->imagenes)) {
                $this->guardarImagenes($p->id, $p->imagenes);
            }
            
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
        
        $imgPrincipal = $p->imagen_principal ?: 'default.jpg';
        $disponible = $p->disponible ? 1 : 0;
        $ofertado = $p->ofertado ? 1 : 0;

        $stmt->bind_param('ssissdiii', 
            $p->nombre, $p->descripcion, $p->categoria_id, $imgPrincipal, 
            $p->precio_base, $p->iva, $disponible, $ofertado, $p->id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /** Guarda imágenes adicionales en ProductoImagenes */
    public function guardarImagenes($productoId, array $imagenes) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        // Primero borramos las existentes
        $stmtDel = $conn->prepare("DELETE FROM ProductoImagenes WHERE producto_id = ?");
        $stmtDel->bind_param('i', $productoId);
        $stmtDel->execute();
        $stmtDel->close();
        
        // Insertamos las nuevas
        $stmt = $conn->prepare("INSERT INTO ProductoImagenes (producto_id, ruta, orden) VALUES (?, ?, ?)");
        foreach ($imagenes as $orden => $ruta) {
            $stmt->bind_param('isi', $productoId, $ruta, $orden);
            $stmt->execute();
        }
        $stmt->close();
    }

    /** Obtiene las imágenes adicionales de un producto */
    public function obtenerImagenes($productoId) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT ruta FROM ProductoImagenes WHERE producto_id = ? ORDER BY orden ASC");
        $stmt->bind_param('i', $productoId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $imagenes = [];
        while ($fila = $result->fetch_assoc()) {
            $imagenes[] = $fila['ruta'];
        }
        $stmt->close();
        return $imagenes;
    }

    private function filaADto(array $fila) {
        $p = new ProductoDTO();
        $p->id = (int)$fila['id'];
        $p->nombre = $fila['nombre'];
        $p->descripcion = $fila['descripcion'];
        $p->categoria_id = $fila['categoria_id'];
        $p->imagen_principal = $fila['imagen_principal'] ?? 'default.jpg';
        $p->precio_base = (float)$fila['precio_base'];
        $p->iva = (float)$fila['iva'];
        $p->disponible = (bool)$fila['disponible'];
        $p->ofertado = (bool)$fila['ofertado'];
        return $p;
    }
}
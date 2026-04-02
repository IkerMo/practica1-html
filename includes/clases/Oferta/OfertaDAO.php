<?php
namespace es\ucm\fdi\aw\Oferta;
use es\ucm\fdi\aw\Aplicacion;

class OfertaDAO {

    public function listarOfertas($incluirCaducadas = true) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT * FROM Ofertas";
        if (!$incluirCaducadas) {
            $hoy = date('Y-m-d');
            $sql .= " WHERE activo = 1 AND fecha_inicio <= '$hoy' AND fecha_fin >= '$hoy'";
        }
        $sql .= " ORDER BY fecha_inicio DESC";

        $rs = $conn->query($sql);
        $result = [];
        while ($fila = $rs->fetch_assoc()) {
            $oferta = $this->filaAOferta($fila);
            $oferta->productos = $this->listarProductosOferta($oferta->id);
            $result[] = $oferta;
        }
        $rs->free();
        return $result;
    }

    public function buscarPorId($id) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $id = (int)$id;
        $stmt = $conn->prepare("SELECT * FROM Ofertas WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        if (!$rs || $rs->num_rows !== 1) {
            $stmt->close();
            return null;
        }
        $fila = $rs->fetch_assoc();
        $stmt->close();

        $o = $this->filaAOferta($fila);
        $o->productos = $this->listarProductosOferta($o->id);
        return $o;
    }

    public function crear(OfertaDTO $o) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("INSERT INTO Ofertas (nombre, descripcion, fecha_inicio, fecha_fin, porcentaje_descuento, activo) VALUES (?, ?, ?, ?, ?, ?)");
        $activo = $o->activo ? 1 : 0;
        $stmt->bind_param('ssssis', $o->nombre, $o->descripcion, $o->fecha_inicio, $o->fecha_fin, $o->porcentaje_descuento, $activo);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        $o->id = $conn->insert_id;
        $stmt->close();

        $this->guardarProductosOferta($o->id, $o->productos);
        return $o;
    }

    public function actualizar(OfertaDTO $o) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("UPDATE Ofertas SET nombre = ?, descripcion = ?, fecha_inicio = ?, fecha_fin = ?, porcentaje_descuento = ?, activo = ? WHERE id = ?");
        $activo = $o->activo ? 1 : 0;
        $stmt->bind_param('sssisii', $o->nombre, $o->descripcion, $o->fecha_inicio, $o->fecha_fin, $o->porcentaje_descuento, $activo, $o->id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        $stmt->close();

        $this->borrarProductosOferta($o->id);
        $this->guardarProductosOferta($o->id, $o->productos);
        return true;
    }

    public function borrar($id) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $id = (int)$id;
        $this->borrarProductosOferta($id);
        $stmt = $conn->prepare("DELETE FROM Ofertas WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function listarProductosOferta($ofertaId) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT producto_id, cantidad FROM OfertaProductos WHERE oferta_id = ?");
        $stmt->bind_param('i', $ofertaId);
        $stmt->execute();
        $rs = $stmt->get_result();

        $productos = [];
        while ($fila = $rs->fetch_assoc()) {
            $productos[(int)$fila['producto_id']] = (int)$fila['cantidad'];
        }
        $stmt->close();
        return $productos;
    }

    public function guardarProductosOferta($ofertaId, array $productos) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("INSERT INTO OfertaProductos (oferta_id, producto_id, cantidad) VALUES (?, ?, ?)");

        foreach ($productos as $productoId => $cantidad) {
            $pid = (int)$productoId;
            $cant = (int)$cantidad;
            if ($pid <= 0 || $cant <= 0) continue;
            $stmt->bind_param('iii', $ofertaId, $pid, $cant);
            $stmt->execute();
        }
        $stmt->close();
    }

    public function borrarProductosOferta($ofertaId) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("DELETE FROM OfertaProductos WHERE oferta_id = ?");
        $stmt->bind_param('i', $ofertaId);
        $stmt->execute();
        $stmt->close();
    }

    private function filaAOferta($fila) {
        $o = new OfertaDTO();
        $o->id = (int)$fila['id'];
        $o->nombre = $fila['nombre'];
        $o->descripcion = $fila['descripcion'];
        $o->fecha_inicio = $fila['fecha_inicio'];
        $o->fecha_fin = $fila['fecha_fin'];
        $o->porcentaje_descuento = (float)$fila['porcentaje_descuento'];
        $o->activo = (bool)$fila['activo'];
        return $o;
    }
}

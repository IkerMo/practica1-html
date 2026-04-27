<?php
namespace es\ucm\fdi\aw\Recompensa;
use es\ucm\fdi\aw\Aplicacion;

class RecompensaDAO {
    
    public function listarRecompensas() {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = "SELECT * FROM recompensas ORDER BY id ASC";
        $rs = $conn->query($query);
        $recompensas = [];
        while ($fila = $rs->fetch_assoc()) {
            $recompensas[] = new RecompensaDTO($fila['producto_id'], $fila['bistrocoins_requeridos'], $fila['id']);
        }
        $rs->free();
        return $recompensas;
    }

    public function buscaPorId($id) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("SELECT * FROM recompensas WHERE id=%d", $id);
        $rs = $conn->query($query);
        if ($rs && $rs->num_rows == 1) {
            $fila = $rs->fetch_assoc();
            $recompensa = new RecompensaDTO($fila['producto_id'], $fila['bistrocoins_requeridos'], $fila['id']);
            $rs->free();
            return $recompensa;
        }
        return null;
    }

    public function crear(RecompensaDTO $dto) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("INSERT INTO recompensas (producto_id, bistrocoins_requeridos) VALUES (%d, %d)",
            $dto->producto_id,
            $dto->bistrocoins_requeridos
        );
        if ($conn->query($query)) {
            $dto->id = $conn->insert_id;
            return $dto;
        }
        return false;
    }

    public function actualizar(RecompensaDTO $dto) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("UPDATE recompensas SET producto_id=%d, bistrocoins_requeridos=%d WHERE id=%d",
            $dto->producto_id,
            $dto->bistrocoins_requeridos,
            $dto->id
        );
        return $conn->query($query);
    }

    public function eliminar($id) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("DELETE FROM recompensas WHERE id=%d", $id);
        return $conn->query($query);
    }
}
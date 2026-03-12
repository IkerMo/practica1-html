<?php
namespace es\ucm\fdi\aw\Usuarios; 

use es\ucm\fdi\aw\Aplicacion;


class UsuarioDAO {

    public function buscarPorUsername(string $nombreUsuario): ?UsuarioDTO {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare('SELECT * FROM usuarios WHERE nombreUsuario = ?');
        $stmt->bind_param('s', $nombreUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $fila   = $result->fetch_assoc();
        $stmt->close();

        if (!$fila) return null;
        return $this->filaADto($fila);
    }

    public function buscarPorId(int $id): ?UsuarioDTO {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $fila = $result->fetch_assoc();
        $stmt->close();
        
        return $fila ? $this->filaADto($fila) : null;
    }

    public function crear(UsuarioDTO $u): UsuarioDTO {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            'INSERT INTO usuarios (nombreUsuario, email, nombre, apellidos, password, rol, avatar) 
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
       
        $stmt->bind_param('sssssss',
            $u->nombreUsuario, $u->email, $u->nombre,
            $u->apellidos, $u->password, $u->rol, $u->avatar
        );
        
        $stmt->execute();
        $u->id = $conn->insert_id;
        $stmt->close();
        
        return $u;
    }

    public function actualizar(UsuarioDTO $u): void {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            'UPDATE usuarios SET email=?, nombre=?, apellidos=?, rol=?, avatar=? WHERE id=?'
        );
        $stmt->bind_param('sssssi', 
            $u->email, $u->nombre, $u->apellidos, $u->rol, $u->avatar, $u->id
        );
        $stmt->execute();
        $stmt->close();
    }

    public function borrar(int $id): void {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare('DELETE FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    public function listarTodos(): array {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = 'SELECT * FROM usuarios ORDER BY nombreUsuario ASC';
        $result = $conn->query($query);
        
        $lista = [];
        if ($result) {
            while ($fila = $result->fetch_assoc()) {
                $lista[] = $this->filaADto($fila);
            }
            $result->free();
        }
        return $lista;
    }

    public function obtenerRoles(int $idUsuario): array {
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        $query = sprintf(
            "SELECT r.nombre, r.prioridad FROM Roles r 
             JOIN UsuarioRoles ur ON r.id = ur.rol_id 
             WHERE ur.usuario_id = %d",
            $idUsuario
        );
        
        $result = $conn->query($query);
        $roles = [];
        if ($result) {
            while ($fila = $result->fetch_assoc()) {
                $roles[] = $fila;
            }
            $result->free();
        }
        return $roles;
    }

    private function filaADto(array $fila): UsuarioDTO {
        return new UsuarioDTO(
            nombreUsuario: $fila['nombreUsuario'],
            email:         $fila['email'],
            nombre:        $fila['nombre'],
            apellidos:     $fila['apellidos'],
            password:      $fila['password'],
            rol:           $fila['rol'],
            avatar:        $fila['avatar'],
            id:            (int)$fila['id']
        );
    }
}

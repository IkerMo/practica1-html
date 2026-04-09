<?php
namespace es\ucm\fdi\aw\Usuarios; 

use es\ucm\fdi\aw\Aplicacion;

class UsuarioDAO {

    public function buscarPorUsername(string $nombreUsuario): ?UsuarioDTO {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare('SELECT * FROM Usuarios WHERE nombreUsuario = ?');
        $stmt->bind_param('s', $nombreUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $fila = $result->fetch_assoc();
        $stmt->close();

        if (!$fila) return null;
        return $this->filaADto($fila);
    }

    public function buscarPorId(int $id): ?UsuarioDTO {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare('SELECT * FROM Usuarios WHERE id = ?');
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
            'INSERT INTO Usuarios (nombreUsuario, email, nombre, apellidos, password, avatar, tipoAvatar, activo) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        
        $tipoAvatar = $u->tipoAvatar ?? 'defecto';
        $activo = $u->activo ?? 1;
        
        $stmt->bind_param('sssssssi',
            $u->nombreUsuario, 
            $u->email, 
            $u->nombre,
            $u->apellidos, 
            $u->password, 
            $u->avatar,
            $tipoAvatar,
            $activo
        );
        
        $stmt->execute();
        $u->id = $conn->insert_id;
        $stmt->close();

        $idRolCliente = 1; 
        $stmtRol = $conn->prepare('INSERT INTO UsuarioRoles (usuario_id, rol_id) VALUES (?, ?)');
        $stmtRol->bind_param('ii', $u->id, $idRolCliente);
        $stmtRol->execute();
        $stmtRol->close();
        
        return $u;
    }

    public function actualizar(UsuarioDTO $u): bool {
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        $stmt = $conn->prepare(
            'UPDATE Usuarios SET email=?, nombre=?, apellidos=?, avatar=?, tipoAvatar=?, activo=? WHERE id=?'
        );
        
        $stmt->bind_param('sssssii', 
            $u->email, 
            $u->nombre, 
            $u->apellidos, 
            $u->avatar,
            $u->tipoAvatar ?? 'defecto',
            $u->activo ?? 1,
            $u->id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    public function borrar(int $id): void {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare('DELETE FROM Usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    public function listarTodos(): array {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = 'SELECT * FROM Usuarios ORDER BY nombreUsuario ASC';
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
            "SELECT r.id, r.nombre, r.prioridad 
             FROM Roles r 
             JOIN UsuarioRoles ur ON r.id = ur.rol_id 
             WHERE ur.usuario_id = %d
             ORDER BY r.prioridad DESC",
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
            rol:           null,
            avatar:        $fila['avatar'],
            tipoAvatar:    $fila['tipoAvatar'] ?? 'defecto',
            id:            (int)$fila['id'],
            activo:        (bool)($fila['activo'] ?? true),
            fechaRegistro: $fila['fechaRegistro'] ?? null
        );
    }
}
?>
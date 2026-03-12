<?php
namespace es\ucm\fdi\aw\Usuarios;

require_once __DIR__ . '/UsuarioDTO.php';
require_once __DIR__ . '/UsuarioDAO.php';
require_once __DIR__ . '/UsuarioYaExisteException.php';

class UsuarioAppService {

    private UsuarioDAO $dao;

    public function __construct() {
        $this->dao = new UsuarioDAO();
    }

    public function login(string $username, string $password): ?UsuarioDTO {
        $usuario = $this->dao->buscarPorUsername($username);
        if (!$usuario) return null;
        if (!password_verify($password, $usuario->password)) return null;
        return $usuario;
    }

    public function registro(
        string $username, string $email, string $nombre,
        string $apellidos, string $password
    ): UsuarioDTO {
        $u = new UsuarioDTO(
            username:  $username,
            email:     $email,
            nombre:    $nombre,
            apellidos: $apellidos,
            password:  password_hash($password, PASSWORD_DEFAULT),
            rol:       'cliente'
        );
        return $this->dao->crear($u);
    }

    public function actualizarPerfil(UsuarioDTO $u): void {
        $this->dao->actualizar($u);
    }

    public function cambiarPassword(int $id, string $nuevaPassword): void {
        $this->dao->actualizarPassword($id, password_hash($nuevaPassword, PASSWORD_DEFAULT));
    }

    public function listarTodos(): array {
        return $this->dao->listarTodos();
    }

    public function buscarPorId(int $id): ?UsuarioDTO {
        return $this->dao->buscarPorId($id);
    }

    public function cambiarRol(int $id, string $nuevoRol): void {
        $u = $this->dao->buscarPorId($id);
        if ($u) {
            $u->rol = $nuevoRol;
            $this->dao->actualizar($u);
        }
    }

    public function borrar(int $id): void {
        $this->dao->borrar($id);
    }
}
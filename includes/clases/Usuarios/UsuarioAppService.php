<?php
namespace es\ucm\fdi\aw\Usuarios;

class UsuarioAppService {

    private UsuarioDAO $dao;

    public function __construct() {
        $this->dao = new UsuarioDAO();
    }

    public function login(string $nombreUsuario, string $password): ?UsuarioDTO {
        $usuario = $this->dao->buscarPorUsername($nombreUsuario);
        if (!$usuario) return null;
        if (!password_verify($password, $usuario->password)) return null;
        return $usuario;
    }

    public function registro(
        string $nombreUsuario, string $email, string $nombre,
        string $apellidos, string $password, ?array $datosAvatar = null
    ): UsuarioDTO {
        
        $nombreFicheroAvatar = 'default.png';
        
        if ($datosAvatar && $datosAvatar['error'] === UPLOAD_ERR_OK) {
            $extension = pathinfo($datosAvatar['name'], PATHINFO_EXTENSION);
            $nombreFicheroAvatar = "av_" . uniqid() . "." . $extension;
            $rutaDestino = dirname(__DIR__, 2) . '/img/avatars/' . $nombreFicheroAvatar;
            move_uploaded_file($datosAvatar['tmp_name'], $rutaDestino);
        }

        $u = new UsuarioDTO(
            nombreUsuario: $nombreUsuario,
            email:         $email,
            nombre:        $nombre,
            apellidos:     $apellidos,
            password:      password_hash($password, PASSWORD_DEFAULT),
            rol:           'cliente',
            avatar:        $nombreFicheroAvatar
        );
        return $this->dao->crear($u);
    }

    
}
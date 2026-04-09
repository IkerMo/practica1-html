<?php
namespace es\ucm\fdi\aw\Usuarios;

class UsuarioDTO {

    public $id;
    public $nombreUsuario;
    public $email;
    public $nombre;
    public $apellidos;
    public $password;
    public $rol;
    public $avatar;
    public $tipoAvatar;
    public $activo;
    public $fechaRegistro;


    public function __construct($nombreUsuario, $email, $nombre, $apellidos, $password, $rol = 'cliente', $avatar = 'default.png', $tipoAvatar = 'defecto', $id = null, $activo = true, $fechaRegistro = null) {
        $this->id = $id;
        $this->nombreUsuario = $nombreUsuario;
        $this->email = $email;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->password = $password;
        $this->avatar = $avatar;
        $this->tipoAvatar = $tipoAvatar;
        $this->activo = $activo;
        $this->rol = $rol;
        $this->fechaRegistro = $fechaRegistro;
    }


}
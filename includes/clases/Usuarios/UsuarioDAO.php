<?php
namespace es\ucm\fdi\aw\Usuarios;

use es\ucm\fdi\aw\Aplicacion; // Necesario para la BD

class UsuarioDAO {
    // Aquí deben ir tus métodos: buscarPorUsername, crear, etc.
    public function buscarPorUsername($username) {
        $conn = Aplicacion::getInstance()->getConexionBd();
        // ... lógica de BD
    }
}
?>

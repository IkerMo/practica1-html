<?php
namespace es\ucm\fdi\aw;

class CategoriaDTO {
    public $id;
    public $nombre;
    public $descripcion;
    public $imagen; // Ruta del archivo de imagen

    public function __construct($nombre = null, $descripcion = null, $imagen = null, $id = null) {
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->imagen = $imagen;
        $this->id = $id;
    }
}
?>
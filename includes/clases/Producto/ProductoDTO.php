<?php
namespace es\ucm\fdi\aw\Producto;

class ProductoDTO {
    public $id;
    public $nombre;
    public $descripcion;
    public $categoria_id;
    public $imagenes = []; // Array para cumplir con "Una o más imágenes"
    public $precio_base;    // Sin IVA
    public $iva;           
    public $disponible;    // true/false (stock)
    public $ofertado;      // true/false (si está en la carta o retirado)

    public function getPrecioFinal() {
        return $this->precio_base * (1 + ($this->iva / 100));
    }
}
?>
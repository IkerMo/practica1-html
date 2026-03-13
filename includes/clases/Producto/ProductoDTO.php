<?php
namespace es\ucm\fdi\aw\Producto;

class ProductoDTO {
    public $id;
    public $nombre;
    public $descripcion;
    public $categoria_id;
    public $imagen_principal; // String: ruta de la imagen principal
    public $imagenes = [];    // Array de rutas adicionales (desde ProductoImagenes)
    public $precio_base;      // Sin IVA
    public $iva;           
    public $disponible;       // true/false (stock)
    public $ofertado;         // true/false (si está en la carta o retirado)

    public function getPrecioFinal() {
        return $this->precio_base * (1 + ($this->iva / 100));
    }

    /** Devuelve todas las imágenes (principal + adicionales) */
    public function getTodasImagenes() {
        $todas = [];
        if (!empty($this->imagen_principal)) {
            $todas[] = $this->imagen_principal;
        }
        return array_merge($todas, $this->imagenes);
    }
}
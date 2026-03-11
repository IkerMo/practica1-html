namespace es\ucm\fdi\aw;

class ProductoDTO {
    public $id;
    public $nombre;
    public $descripcion;
    public $categoria;
    public $imagenes = []; // Array para cumplir con "Una o más imágenes"
    public $precioBase;    // Sin IVA
    public $iva;           
    public $disponible;    // true/false (stock)
    public $ofertado;      // true/false (si está en la carta o retirado)

    public function getPrecioFinal() {
        return $this->precioBase * (1 + ($this->iva / 100));
    }
}
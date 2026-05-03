<?php
namespace es\ucm\fdi\aw\Producto;

class ProductoAppService {
    private $dao;

    public function __construct() {
        $this->dao = new ProductoDAO();
    }

    /** Devuelve los productos visibles para los clientes */
    public function getCarta() {
        return $this->dao->listarTodos();
    }

    public function getProducto($id) {
        return $this->dao->buscarPorId($id);
    }

    /** Crea un producto desde los datos del formulario */
    public function registrarProducto($datos) {
        $p = new ProductoDTO();
        $p->nombre = $datos['nombre'];
        $p->descripcion = $datos['descripcion'];
        $p->categoria_id = $datos['categoria_id'];
        $p->imagen_principal = $datos['imagen_principal'] ?? 'default.jpg';
        $p->imagenes = $datos['imagenes_adicionales'] ?? [];
        $p->precio_base = (float)$datos['precio_base'];
        $p->iva = (float)($datos['iva'] ?? 21);
        $p->disponible = isset($datos['disponible']) && $datos['disponible'];
        $p->ofertado = true;
        $p->requiere_cocina = isset($datos['requiere_cocina']) ? (bool)$datos['requiere_cocina'] : true;

        return $this->dao->crear($p);
    }

    /** Cambia si un producto tiene stock o no sin borrarlo */
    public function alternarDisponibilidad($id) {
        $p = $this->dao->buscarPorId($id);
        if ($p) {
            $p->disponible = !$p->disponible;
            return $this->dao->actualizar($p);
        }
        return false;
    }

    public function actualizarProducto($id, $datos) {
        $p = $this->dao->buscarPorId($id);
        if ($p) {
            $p->nombre = $datos['nombre'];
            $p->descripcion = $datos['descripcion'];
            $p->categoria_id = $datos['categoria_id'];
            
            // Solo actualizamos imagen principal si se ha subido una nueva
            if (!empty($datos['imagen_principal'])) {
                $p->imagen_principal = $datos['imagen_principal'];
            }
            
            // Imágenes adicionales
            if (!empty($datos['imagenes_adicionales'])) {
                $this->dao->guardarImagenes($p->id, $datos['imagenes_adicionales']);
            }
            
            $p->precio_base = (float)$datos['precio_base'];
            $p->iva = (float)$datos['iva'];
            $p->disponible = $datos['disponible'];
            $p->requiere_cocina = isset($datos['requiere_cocina']) ? (bool)$datos['requiere_cocina'] : true;
            
            return $this->dao->actualizar($p);
        }
        return false;
    }

    /** Retira un producto de la carta (ofertado = false) */
    public function retirarDeLaCarta($id) {
        $p = $this->dao->buscarPorId($id);
        if ($p) {
            $p->ofertado = false;
            return $this->dao->actualizar($p);
        }
        return false;
    }

    public function getTodosProductos() {
        $productoDAO = new ProductoDAO();
        return $productoDAO->listarTodos();
    }
}

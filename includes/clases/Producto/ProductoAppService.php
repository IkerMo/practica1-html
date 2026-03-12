<?php
namespace es\ucm\fdi\aw\Producto;

class ProductoAppService {
    private $dao;

    public function __construct() {
        $this->dao = new ProductoDAO();
    }

    /**
     * Devuelve los productos visibles para los clientes
     */
    public function getCarta() {
        return $this->dao->listarTodos();
    }

    public function getProducto($id) {
        return $this->dao->buscarPorId($id);
    }

    /**
     * Crea un producto desde los datos del formulario
     */
    public function registrarProducto($datos) {
        $p = new ProductoDTO();
        $p->nombre = $datos['nombre'];
        $p->descripcion = $datos['descripcion'];
        $p->categoria = $datos['categoria'];
        $p->imagenes = $datos['imagenes'] ?? []; // Array de nombres de archivo
        $p->precioBase = (float)$datos['precioBase'];
        $p->iva = (float)($datos['iva'] ?? 21);
        $p->disponible = isset($datos['disponible']);
        $p->ofertado = true; // Por defecto se añade a la carta

        return $this->dao->crear($p);
    }

    /**
     * Cambia si un producto tiene stock o no sin borrarlo
     */
    public function alternarDisponibilidad($id) {
        $p = $this->dao->buscarPorId($id);
        if ($p) {
            $p->disponible = !$p->disponible;
            return $this->dao->actualizar($p);
        }
        return false;
    }

    /**
     * Retira un producto de la carta (ofertado = false)
     */
    public function retirarDeLaCarta($id) {
        $p = $this->dao->buscarPorId($id);
        if ($p) {
            $p->ofertado = false;
            return $this->dao->actualizar($p);
        }
        return false;
    }
}
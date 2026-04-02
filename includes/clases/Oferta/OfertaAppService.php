<?php
namespace es\ucm\fdi\aw\Oferta;

use es\ucm\fdi\aw\Producto\ProductoAppService;

class OfertaAppService {
    private $dao;
    private $productoService;

    public function __construct() {
        $this->dao = new OfertaDAO();
        $this->productoService = new ProductoAppService();
    }

    public function listarOfertas($incluirCaducadas = true) {
        return $this->dao->listarOfertas($incluirCaducadas);
    }

    public function getOferta($id) {
        return $this->dao->buscarPorId($id);
    }

    public function crearOferta($datos) {
        $oferta = $this->parsearDatos($datos);
        $errores = $this->validarOferta($oferta);
        if (!empty($errores)) {
            return $errores;
        }

        $resultado = $this->dao->crear($oferta);
        return $resultado ? $resultado : false;
    }

    public function actualizarOferta($datos) {
        if (empty($datos['id'])) {
            return false;
        }
        $oferta = $this->dao->buscarPorId((int)$datos['id']);
        if (!$oferta) {
            return false;
        }
        $oferta = $this->parsearDatos($datos, $oferta);
        $errores = $this->validarOferta($oferta);
        if (!empty($errores)) {
            return $errores;
        }

        return $this->dao->actualizar($oferta);
    }

    public function borrarOferta($id) {
        return $this->dao->borrar($id);
    }

    public function getOfertasActivas() {
        $ofertas = $this->dao->listarOfertas(false);
        $act = [];
        foreach ($ofertas as $o) {
            if ($o->estaActiva()) {
                $act[] = $o;
            }
        }
        return $act;
    }

    public function ofertaEsAplicableAItems($oferta, $items) {
        if (!$oferta || !$oferta->estaActiva()) {
            return false;
        }

        $cant = [];
        foreach ($items as $item) {
            $pid = (int)$item['producto_id'];
            $c = (int)$item['cantidad'];
            if (!isset($cant[$pid])) {
                $cant[$pid] = 0;
            }
            $cant[$pid] += $c;
        }

        foreach ($oferta->productos as $pid => $req) {
            if (!isset($cant[$pid]) || $cant[$pid] < $req) {
                return false;
            }
        }

        return true;
    }

    public function calcularImpactoOferta($oferta) {
        $total = 0;
        foreach ($oferta->productos as $pid => $cantidad) {
            $producto = $this->productoService->getProducto($pid);
            if (!$producto) continue;
            $total += $producto->getPrecioFinal() * $cantidad;
        }

        $total = round($total, 2);
        $descuento = round($total * ($oferta->porcentaje_descuento / 100),2);
        $totalCon = round($total - $descuento,2);

        return [
            'total_sin_descuento' => $total,
            'descuento' => $descuento,
            'total_con_descuento' => $totalCon,
        ];
    }

    private function parsearDatos($datos, $oferta = null) {
        if (!$oferta) {
            $oferta = new OfertaDTO();
        }

        if (isset($datos['id'])) {
            $oferta->id = (int)$datos['id'];
        }

        $oferta->nombre = trim($datos['nombre'] ?? '');
        $oferta->descripcion = trim($datos['descripcion'] ?? '');
        $oferta->fecha_inicio = $datos['fecha_inicio'] ?? '';
        $oferta->fecha_fin = $datos['fecha_fin'] ?? '';
        $oferta->porcentaje_descuento = (float)($datos['porcentaje_descuento'] ?? 0);
        $oferta->activo = isset($datos['activo']) && ($datos['activo'] == 'on' || $datos['activo'] == 1);

        $oferta->productos = [];
        if (!empty($datos['oferta_producto_id']) && is_array($datos['oferta_producto_id'])) {
            foreach ($datos['oferta_producto_id'] as $idx => $pid) {
                $productoId = (int)$pid;
                $cantidad = isset($datos['oferta_producto_cantidad'][$idx]) ? (int)$datos['oferta_producto_cantidad'][$idx] : 0;
                if ($productoId > 0 && $cantidad > 0) {
                    $oferta->productos[$productoId] = $cantidad;
                }
            }
        }

        return $oferta;
    }

    private function validarOferta($oferta) {
        $errores = [];

        if (empty($oferta->nombre)) {
            $errores['nombre'] = 'Debes poner nombre de la oferta';
        }
        if (empty($oferta->fecha_inicio) || empty($oferta->fecha_fin)) {
            $errores['fecha'] = 'Hay que indicar fechas';
        }
        if ($oferta->fecha_inicio > $oferta->fecha_fin) {
            $errores['fecha'] = 'Inicio no puede ser mayor que fin';
        }
        if ($oferta->porcentaje_descuento <= 0 || $oferta->porcentaje_descuento >= 100) {
            $errores['porcentaje_descuento'] = 'Descuento válido entre 0 y 100';
        }
        if (count($oferta->productos) == 0) {
            $errores['productos'] = 'Debes asociar al menos 1 producto';
        }

        return $errores;
    }
}

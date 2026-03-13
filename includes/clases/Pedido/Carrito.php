<?php
namespace es\ucm\fdi\aw\Pedido;

use es\ucm\fdi\aw\Producto\ProductoDAO;

/**
 * Gestiona el carrito de la compra en la sesión PHP.
 * El carrito NO se persiste en BD hasta que se confirma el pedido.
 */
class Carrito {
    
    /** Inicializa el carrito en la sesión si no existe */
    private static function init() {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
        if (!isset($_SESSION['carrito_tipo'])) {
            $_SESSION['carrito_tipo'] = null; // 'local' o 'llevar'
        }
    }

    /** Establece el tipo de pedido */
    public static function setTipo($tipo) {
        self::init();
        $_SESSION['carrito_tipo'] = $tipo;
    }

    public static function getTipo() {
        self::init();
        return $_SESSION['carrito_tipo'];
    }

    /** Agrega un producto al carrito (o incrementa cantidad) */
    public static function agregar($productoId, $cantidad = 1) {
        self::init();
        $productoId = (int)$productoId;
        $cantidad = max(1, (int)$cantidad);
        
        if (isset($_SESSION['carrito'][$productoId])) {
            $_SESSION['carrito'][$productoId]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$productoId] = [
                'producto_id' => $productoId,
                'cantidad' => $cantidad
            ];
        }
    }

    /** Modifica la cantidad de un producto */
    public static function modificarCantidad($productoId, $cantidad) {
        self::init();
        $productoId = (int)$productoId;
        $cantidad = (int)$cantidad;
        
        if ($cantidad <= 0) {
            self::eliminar($productoId);
        } else {
            if (isset($_SESSION['carrito'][$productoId])) {
                $_SESSION['carrito'][$productoId]['cantidad'] = $cantidad;
            }
        }
    }

    /** Elimina un producto del carrito */
    public static function eliminar($productoId) {
        self::init();
        unset($_SESSION['carrito'][(int)$productoId]);
    }

    /** Vacía el carrito */
    public static function vaciar() {
        $_SESSION['carrito'] = [];
        $_SESSION['carrito_tipo'] = null;
    }

    /** Devuelve los items del carrito con datos del producto */
    public static function getItems() {
        self::init();
        $productoDAO = new ProductoDAO();
        $items = [];
        
        foreach ($_SESSION['carrito'] as $productoId => $item) {
            $producto = $productoDAO->buscarPorId($productoId);
            if ($producto) {
                $items[] = [
                    'producto_id' => $productoId,
                    'nombre' => $producto->nombre,
                    'precio_base' => $producto->precio_base,
                    'iva' => $producto->iva,
                    'precio_final' => $producto->getPrecioFinal(),
                    'cantidad' => $item['cantidad'],
                    'subtotal' => $producto->getPrecioFinal() * $item['cantidad'],
                    'imagen' => $producto->imagen_principal
                ];
            }
        }
        return $items;
    }

    /** Devuelve los items raw para el AppService */
    public static function getItemsRaw() {
        self::init();
        return array_values($_SESSION['carrito']);
    }

    /** Total con IVA del carrito */
    public static function getTotal() {
        $items = self::getItems();
        $total = 0;
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        return round($total, 2);
    }

    /** Número de productos distintos */
    public static function getCount() {
        self::init();
        return count($_SESSION['carrito']);
    }

    /** Total de unidades */
    public static function getTotalUnidades() {
        self::init();
        $total = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['cantidad'];
        }
        return $total;
    }

    /** ¿Está vacío? */
    public static function estaVacio() {
        self::init();
        return empty($_SESSION['carrito']);
    }
}

-- 04-tablas-f2.sql
-- Funcionalidad 2: Gestión de Pedidos
USE bistro_fdi;

-- Tabla de pedidos
DROP TABLE IF EXISTS Pedidos;
CREATE TABLE Pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido INT NOT NULL, -- Número por día (reinicia cada día)
    cliente_id INT NOT NULL,
    tipo ENUM('local', 'llevar') NOT NULL,
    estado ENUM(
        'nuevo',           -- En proceso de creación (carrito)
        'recibido',        -- Confirmado, pendiente de pago
        'en_preparacion',  -- Pagado, esperando cocinero
        'cocinando',       -- Siendo preparado por un cocinero
        'listo_cocina',    -- Completado en cocina
        'terminado',       -- Listo para entregar
        'entregado',       -- Entregado al cliente
        'cancelado'        -- Cancelado (solo si está nuevo o recibido)
    ) NOT NULL DEFAULT 'nuevo',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_confirmacion DATETIME NULL, -- Cuando pasa a 'recibido'
    fecha_pago DATETIME NULL, -- Cuando se paga
    fecha_entrega DATETIME NULL, -- Cuando se entrega
    cocinero_id INT NULL, -- Cocinero que prepara el pedido
    camarero_id INT NULL, -- Camarero que entrega el pedido
    total_sin_iva DECIMAL(10,2) NULL,
    total_con_iva DECIMAL(10,2) NULL,
    observaciones TEXT NULL,
    
    FOREIGN KEY (cliente_id) REFERENCES Usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (cocinero_id) REFERENCES Usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (camarero_id) REFERENCES Usuarios(id) ON DELETE SET NULL,
    
    -- Un número de pedido único por día
    UNIQUE KEY uk_numero_dia (numero_pedido, DATE(fecha_creacion))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de líneas de pedido (productos en cada pedido)
DROP TABLE IF EXISTS LineasPedido;
CREATE TABLE LineasPedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario_sin_iva DECIMAL(10,2) NOT NULL, -- Precio en el momento del pedido
    iva TINYINT NOT NULL, -- IVA aplicado
    subtotal_sin_iva DECIMAL(10,2) NOT NULL,
    subtotal_con_iva DECIMAL(10,2) NOT NULL,
    observaciones TEXT NULL,
    
    FOREIGN KEY (pedido_id) REFERENCES Pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES Productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Historial de cambios de estado (para seguimiento)
DROP TABLE IF EXISTS PedidoHistorial;
CREATE TABLE PedidoHistorial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    estado_anterior VARCHAR(30) NOT NULL,
    estado_nuevo VARCHAR(30) NOT NULL,
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NULL, -- Quién realizó el cambio
    observaciones TEXT NULL,
    
    FOREIGN KEY (pedido_id) REFERENCES Pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- 04-tablas-f2.sql
-- Funcionalidad 2: Gestión de Pedidos
USE bistro_fdi;

-- Tabla de pedidos
DROP TABLE IF EXISTS Pedidos;
CREATE TABLE Pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido INT NOT NULL, 
    cliente_id INT NOT NULL,
    tipo ENUM('local', 'llevar') NOT NULL,
    estado ENUM(
        'nuevo', 
        'recibido', 
        'en_preparacion', 
        'cocinando', 
        'listo_cocina', 
        'terminado', 
        'entregado', 
        'cancelado' 
    ) NOT NULL DEFAULT 'nuevo',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_confirmacion DATETIME NULL, 
    fecha_pago DATETIME NULL, 
    fecha_entrega DATETIME NULL, 
    cocinero_id INT NULL, 
    camarero_id INT NULL, 
    total_sin_iva DECIMAL(10,2) NULL,
    total_con_iva DECIMAL(10,2) NULL,
    observaciones TEXT NULL,
    
    FOREIGN KEY (cliente_id) REFERENCES Usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (cocinero_id) REFERENCES Usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (camarero_id) REFERENCES Usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de líneas de pedido
DROP TABLE IF EXISTS LineasPedido;
CREATE TABLE LineasPedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario_sin_iva DECIMAL(10,2) NOT NULL,
    iva TINYINT NOT NULL,
    subtotal_sin_iva DECIMAL(10,2) NOT NULL,
    subtotal_con_iva DECIMAL(10,2) NOT NULL,
    observaciones TEXT NULL,
    
    FOREIGN KEY (pedido_id) REFERENCES Pedidos(id) ON DELETE CASCADE
    -- He comentado la de productos por si aún no has creado esa tabla
    -- , FOREIGN KEY (producto_id) REFERENCES Productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Historial de cambios
DROP TABLE IF EXISTS PedidoHistorial;
CREATE TABLE PedidoHistorial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    estado_anterior VARCHAR(30) NOT NULL,
    estado_nuevo VARCHAR(30) NOT NULL,
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NULL,
    observaciones TEXT NULL,
    
    FOREIGN KEY (pedido_id) REFERENCES Pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
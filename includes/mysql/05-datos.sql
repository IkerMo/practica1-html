-- 05-datos-prueba.sql
-- Datos de prueba para todas las funcionalidades
USE bistro_fdi;

-- Insertar usuarios de prueba
-- Contraseñas: 'admin123', 'cocinero123', 'camarero123', 'cliente123'
-- Los hashes son para '123' (simplificado para pruebas)
INSERT INTO Usuarios (nombreUsuario, email, nombre, apellidos, password, avatar, tipoAvatar) VALUES
('admin', 'admin@bistro.com', 'Admin', 'Principal', '$2y$10$7Vk7yV6neVMRveCia3r0v.LhMw76jNZBd9cQK2K5IPnfqR0ObZUKG', 'default.png', 'defecto'),
('cocinero1', 'cocinero@bistro.com', 'Chef', 'Principal', '$2y$10$nskFY1aFCiwdne8a4M0kBOrJiva9kiRrz8pwfDi6kN2ONsBwrtvj.', 'default.png', 'defecto'),
('camarero1', 'camarero@bistro.com', 'Camarero', 'Principal', '$2y$10$ZPqoUvR5v.itVnZYsyzKaOTHTROVObdfLHcJ4kkqGul2ZnhIZtr.W', 'default.png', 'defecto'),
('cliente1', 'cliente@email.com', 'Cliente', 'Ejemplo', '$2y$10$iR956dD7XC8SFvdRivnP9eDr8vvicd3nQCAhBCIHtl2A46xP.Oem2', 'default.png', 'defecto'),
('cliente2', 'otro@email.com', 'Otro', 'Cliente', '$2y$10$iR956dD7XC8SFvdRivnP9eDr8vvicd3nQCAhBCIHtl2A46xP.Oem2', 'default.png', 'defecto');

-- Asignar roles (asumiendo IDs: 1:admin, 2:cocinero1, 3:camarero1, 4:cliente1, 5:cliente2)
INSERT INTO UsuarioRoles (usuario_id, rol_id) VALUES
(1, 4), -- admin es gerente
(2, 3), -- cocinero1 es cocinero
(3, 2), -- camarero1 es camarero
(4, 1), -- cliente1 es cliente
(5, 1); -- cliente2 es cliente

-- Insertar categorías de productos
INSERT INTO Categorias (nombre, descripcion) VALUES
('Bebidas', 'Refrescos, zumos y aguas'),
('Cafés', 'Café, té e infusiones'),
('Desayunos', 'Tostadas, cereales y bollería'),
('Almuerzos', 'Sándwiches, bocadillos y platos combinados');

-- Insertar productos
INSERT INTO Productos (nombre, descripcion, categoria_id, precio_base, iva, disponible, ofertado) VALUES
('Coca-Cola', 'Refresco de cola 33cl', 1, 1.50, 21, TRUE, TRUE),
('Agua mineral', 'Agua mineral 50cl', 1, 1.00, 10, TRUE, TRUE),
('Café solo', 'Café solo tradicional', 2, 1.20, 10, TRUE, TRUE),
('Café con leche', 'Café con leche', 2, 1.50, 10, TRUE, TRUE),
('Tostada aceite', 'Tostada con aceite de oliva', 3, 1.80, 10, TRUE, TRUE),
('Tostada tomate', 'Tostada con tomate triturado', 3, 2.00, 10, TRUE, TRUE),
('Bocadillo jamón', 'Bocadillo de jamón serrano', 4, 3.50, 10, TRUE, TRUE),
('Bocadillo queso', 'Bocadillo de queso', 4, 3.00, 10, TRUE, TRUE);

-- Insertar algún pedido deejemplo (para pruebas)
-- Primero, necesitamos saber el número de pedido del día
INSERT INTO Pedidos (numero_pedido, cliente_id, tipo, estado, fecha_creacion, total_sin_iva, total_con_iva) 
VALUES (1, 4, 'local', 'entregado', DATE_SUB(NOW(), INTERVAL 1 DAY), 5.70, 6.27);

-- Líneas del pedido
INSERT INTO LineasPedido (pedido_id, producto_id, cantidad, precio_unitario_sin_iva, iva, subtotal_sin_iva, subtotal_con_iva) VALUES
(1, 3, 1, 1.20, 10, 1.20, 1.32), -- Café solo
(1, 4, 1, 1.50, 10, 1.50, 1.65), -- Café con leche
(1, 5, 1, 1.80, 10, 1.80, 1.98), -- Tostada aceite
(1, 6, 1, 2.00, 10, 2.00, 2.20); -- Tostada tomate

-- Historial del pedido
INSERT INTO PedidoHistorial (pedido_id, estado_anterior, estado_nuevo) VALUES
(1, 'nuevo', 'recibido'),
(1, 'recibido', 'en_preparacion'),
(1, 'en_preparacion', 'cocinando'),
(1, 'cocinando', 'listo_cocina'),
(1, 'listo_cocina', 'terminado'),
(1, 'terminado', 'entregado');
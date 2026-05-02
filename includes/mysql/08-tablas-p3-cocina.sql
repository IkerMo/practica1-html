-- 08-tablas-p3-cocina.sql
-- Extiende productos y lineas de pedido para la preparacion por producto
USE bistro_fdi;

ALTER TABLE Productos
    ADD COLUMN requiere_cocina BOOLEAN DEFAULT TRUE AFTER ofertado;

ALTER TABLE LineasPedido
    ADD COLUMN estado_cocina ENUM('pendiente', 'listo_cocina', 'no_requiere_cocina') NOT NULL DEFAULT 'pendiente' AFTER subtotal_con_iva,
    ADD COLUMN cocinero_id INT NULL AFTER estado_cocina,
    ADD COLUMN fecha_listo_cocina DATETIME NULL AFTER cocinero_id,
    ADD CONSTRAINT fk_lineas_cocinero FOREIGN KEY (cocinero_id) REFERENCES Usuarios(id) ON DELETE SET NULL;

UPDATE Productos
SET requiere_cocina = FALSE
WHERE categoria_id IN (SELECT id FROM Categorias WHERE nombre = 'Bebidas');

UPDATE LineasPedido lp
JOIN Productos p ON lp.producto_id = p.id
SET lp.estado_cocina = IF(p.requiere_cocina = 1, 'pendiente', 'no_requiere_cocina');

-- 06-tablas-f4-pedidos.sql
-- Extiende Pedidos y LineasPedido para soportar descuentos de ofertas
USE bistro_fdi;

ALTER TABLE Pedidos
    ADD COLUMN total_sin_descuento DECIMAL(10,2) NULL AFTER total_con_iva,
    ADD COLUMN total_descuento DECIMAL(10,2) NULL AFTER total_sin_descuento;

ALTER TABLE LineasPedido
    ADD COLUMN oferta_id INT NULL AFTER subtotal_con_iva,
    ADD COLUMN subtotal_descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER oferta_id,
    ADD FOREIGN KEY (oferta_id) REFERENCES Ofertas(id) ON DELETE SET NULL;

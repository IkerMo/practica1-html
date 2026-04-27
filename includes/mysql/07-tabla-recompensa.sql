CREATE TABLE Recompensas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    bistrocoins_requeridos INT NOT NULL,
    FOREIGN KEY (producto_id) REFERENCES Productos(id) ON DELETE CASCADE
);
ALTER TABLE Usuarios ADD COLUMN bistro_coins INT DEFAULT 0 NOT NULL;
ALTER TABLE LineasPedido ADD COLUMN es_recompensa BOOLEAN DEFAULT FALSE NOT NULL;
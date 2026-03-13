-- 03-tablas-f1.sql
-- Funcionalidad 1: Gestión de Productos
USE bistro_fdi;

-- Tabla de categorías de productos
DROP TABLE IF EXISTS Categorias;
CREATE TABLE Categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) NULL, -- Ruta de la imagen
    activo BOOLEAN DEFAULT TRUE -- Para borrado lógico
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de productos
DROP TABLE IF EXISTS Productos;
CREATE TABLE Productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria_id INT NOT NULL,
    precio_base DECIMAL(10,2) NOT NULL, -- Precio sin IVA
    iva TINYINT NOT NULL, -- 4, 10, 21
    disponible BOOLEAN DEFAULT TRUE, -- Si hay stock/disponible
    ofertado BOOLEAN DEFAULT TRUE, -- Si está en la carta actualmente
    fecha_alta DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_baja DATETIME NULL, -- Cuando se desactiva
    imagen_principal VARCHAR(255) NULL,
    FOREIGN KEY (categoria_id) REFERENCES Categorias(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de imágenes de productos (una o más por producto)
DROP TABLE IF EXISTS ProductoImagenes;
CREATE TABLE ProductoImagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    orden INT DEFAULT 0, -- Para ordenar las imágenes
    FOREIGN KEY (producto_id) REFERENCES Productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Función para calcular precio con IVA (útil para vistas)
-- Nota: Esto es solo para referencia, se calculará en PHP
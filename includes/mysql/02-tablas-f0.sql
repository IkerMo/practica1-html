-- 02-tablas-f0.sql
-- Funcionalidad 0: Gestión de Usuarios
USE bistro_fdi;

-- Tabla de roles (jerárquicos)
DROP TABLE IF EXISTS Roles;
CREATE TABLE Roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL UNIQUE,
    prioridad INT NOT NULL, -- 1: cliente, 2: camarero, 3: cocinero, 4: gerente
    descripcion VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de usuarios
DROP TABLE IF EXISTS Usuarios;
CREATE TABLE Usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombreUsuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL, -- Hash de la contraseña
    avatar VARCHAR(255) NULL, -- Ruta de la imagen o identificador
    tipoAvatar ENUM('defecto', 'seleccionado', 'subido') DEFAULT 'defecto',
    fechaRegistro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE -- Para borrado lógico
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de roles de usuario (relación N:M)
DROP TABLE IF EXISTS UsuarioRoles;
CREATE TABLE UsuarioRoles (
    usuario_id INT NOT NULL,
    rol_id INT NOT NULL,
    PRIMARY KEY (usuario_id, rol_id),
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id) REFERENCES Roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar roles básicos (jerárquicos)
INSERT INTO Roles (nombre, prioridad, descripcion) VALUES
('cliente', 1, 'Cliente que realiza pedidos'),
('camarero', 2, 'Personal que atiende y entrega pedidos'),
('cocinero', 3, 'Personal que prepara los pedidos'),
('gerente', 4, 'Personal que gestiona productos y categorías');
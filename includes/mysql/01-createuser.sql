-- 01-createuser.sql
-- Crear usuario para la aplicación (ajusta según tu configuración)
CREATE USER IF NOT EXISTS 'bistro_user'@'localhost' IDENTIFIED BY 'bistro_password';
GRANT ALL PRIVILEGES ON bistro_fdi.* TO 'bistro_user'@'localhost';
FLUSH PRIVILEGES;
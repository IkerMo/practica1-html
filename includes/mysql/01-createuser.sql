-- 01-createuser.sql
-- Crear usuario para la aplicación (ajusta según tu configuración)
CREATE USER IF NOT EXISTS 'bistro_user'@'127.0.0.1' IDENTIFIED BY 'bistro_password';
GRANT ALL PRIVILEGES ON bistro_fdi.* TO 'bistro_user'@'127.0.0.1';
FLUSH PRIVILEGES;
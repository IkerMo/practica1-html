<?php
// Generar hashes para contraseñas de prueba
echo "Hash para 'admin123': " . password_hash('admin123', PASSWORD_DEFAULT) . "\n";
echo "Hash para 'cocinero123': " . password_hash('cocinero123', PASSWORD_DEFAULT) . "\n";
echo "Hash para 'camarero123': " . password_hash('camarero123', PASSWORD_DEFAULT) . "\n";
echo "Hash para 'cliente123': " . password_hash('cliente123', PASSWORD_DEFAULT) . "\n";
?>
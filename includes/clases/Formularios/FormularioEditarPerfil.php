<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Formularios\Formulario;
use es\ucm\fdi\aw\Usuarios\Usuario;


class FormularioEditarPerfil extends Formulario
{


    public function __construct()
    {
        parent::__construct('editarPerfil', [
            'urlRedireccion' => RUTA_VISTAS . '/usuarios/perfil.php',
            'enctype' => 'multipart/form-data' // 👈 IMPORTANTE para subir archivos
        ]);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        $rutaImgs = RUTA_IMGS;
        $idUsuario = $_SESSION['idUsuario'] ?? 0;
        $usuario = Usuario::buscaUsuarioPorId($idUsuario);
        
        if (!$usuario) {
            return '<p class="error">Error: Usuario no encontrado</p>';
        }
        
        $nombre = $datos['nombre'] ?? $usuario->getNombre();
        $apellidos = $datos['apellidos'] ?? $usuario->getApellidos();
        $email = $datos['email'] ?? $usuario->getEmail();
        $avatarActual = $usuario->getAvatar();
        $tipoAvatar = $usuario->tipoAvatar ?? 'defecto';
        
        // Lista de avatares predefinidos
        $avataresPredefinidos = ['default.png', 'chef.png', 'waiter.png', 'client.png', 'admin.png'];
        
        // Generar opciones del selector
        $opcionesAvatares = '';
        foreach ($avataresPredefinidos as $ava) {
            $selected = ($tipoAvatar == 'seleccionado' && $avatarActual == $ava) ? 'selected' : '';
            $opcionesAvatares .= "<option value=\"$ava\" $selected>$ava</option>";
        }
        
        $html = <<<EOS
        <fieldset>
            <legend>Editar mis datos</legend>
            
            <div class="campo">
                <label for="nombre">Nombre:</label>
                <input id="nombre" type="text" name="nombre" value="$nombre" />
                {$this->getError('nombre')}
            </div>
            
            <div class="campo">
                <label for="apellidos">Apellidos:</label>
                <input id="apellidos" type="text" name="apellidos" value="$apellidos" />
                {$this->getError('apellidos')}
            </div>
            
            <div class="campo">
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" value="$email" />
                {$this->getError('email')}
            </div>
            
            <div class="campo-avatar">
                <label>Avatar actual:</label>
                <div class="avatar-preview">
                    <img src="{$rutaImgs}/avatares/{$avatarActual}" alt="Avatar actual" id="preview-avatar" class="avatar-vista-previa">
                </div>
            </div>
            
            <div class="campo">
                <label>Tipo de avatar:</label>
                <div class="opciones-avatar">
                    <label class="radio-option">
                        <input type="radio" name="tipo_avatar" value="defecto" 
                               {$this->checked('defecto', $tipoAvatar)}> 
                        Avatar por defecto
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="tipo_avatar" value="seleccionado" 
                               {$this->checked('seleccionado', $tipoAvatar)}> 
                        Seleccionar de la lista
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="tipo_avatar" value="subido" 
                               {$this->checked('subido', $tipoAvatar)}> 
                        Subir mi propia foto
                    </label>
                </div>
            </div>
            
            <div class="campo avatar-selector" id="avatar-selector" 
                 style="display: {$this->displayStyle($tipoAvatar == 'seleccionado')}">
                <label for="avatar">Selecciona un avatar:</label>
                <select id="avatar" name="avatar" onchange="actualizarPreview(this.value)">
                    $opcionesAvatares
                </select>
                <div class="mini-previews">
EOS;
        
        // Añadir miniaturas de los avatares predefinidos
        foreach ($avataresPredefinidos as $ava) {
            $clase = ($tipoAvatar == 'seleccionado' && $avatarActual == $ava) ? 'miniatura-seleccionada' : '';
            $html .= "<img src=\"{$rutaImgs}/avatares/{$ava}\" 
                           alt=\"{$ava}\" 
                           class=\"avatar-miniatura {$clase}\" 
                           onclick=\"seleccionarAvatar('{$ava}')\">";
        }
        
        $html .= <<<EOS
                </div>
            </div>
            
            <div class="campo avatar-upload" id="avatar-upload" 
                 style="display: {$this->displayStyle($tipoAvatar == 'subido')}">
                <label for="foto_subida">Sube tu propia foto:</label>
                <input type="file" id="foto_subida" name="foto_subida" accept="image/jpeg,image/png,image/gif">
                <small>Máximo 2MB. Formatos: JPG, PNG, GIF</small>
                <div id="upload-preview"></div>
                {$this->getError('foto_subida')}
            </div>
            
            <div class="campo">
                <button type="submit">Guardar cambios</button>
                <a href="perfil.php" class="btn-secondary">Cancelar</a>
            </div>
        </fieldset>
        
        <script>
        function actualizarPreview(valor) {
            document.getElementById('preview-avatar').src = '{$rutaImgs}/avatares/' + valor;
        }
        
        function seleccionarAvatar(avatar) {
            document.getElementById('avatar').value = avatar;
            actualizarPreview(avatar);
            
            // Quitar selección de todas las miniaturas
            document.querySelectorAll('.avatar-miniatura').forEach(function(img) {
                img.classList.remove('miniatura-seleccionada');
            });
            
            // Añadir clase a la miniatura seleccionada
            event.target.classList.add('miniatura-seleccionada');
        }
        
        // Mostrar/ocultar secciones según el tipo de avatar seleccionado
        document.querySelectorAll('input[name="tipo_avatar"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.getElementById('avatar-selector').style.display = 
                    this.value === 'seleccionado' ? 'block' : 'none';
                document.getElementById('avatar-upload').style.display = 
                    this.value === 'subido' ? 'block' : 'none';
            });
        });
        
        // Previsualizar imagen subida
        document.getElementById('foto_subida')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-avatar').src = e.target.result;
                    document.getElementById('upload-preview').innerHTML = 
                        '<img src="' + e.target.result + '" class="avatar-miniatura">';
                }
                reader.readAsDataURL(file);
            }
        });
        </script>
EOS;
        return $html;
    }
    
    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        
        $idUsuario = $_SESSION['idUsuario'] ?? 0;
        $usuario = Usuario::buscaUsuarioPorId($idUsuario);
        
        if (!$usuario) {
            $this->errores[] = 'No hay usuario en sesión';
            return false;
        }
        
        // Validar campos básicos
        $nombre = trim($datos['nombre'] ?? '');
        if (empty($nombre)) {
            $this->errores['nombre'] = 'El nombre no puede estar vacío';
        }
        
        $apellidos = trim($datos['apellidos'] ?? '');
        if (empty($apellidos)) {
            $this->errores['apellidos'] = 'Los apellidos no pueden estar vacíos';
        }
        
        $email = trim($datos['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = 'Email no válido';
        }
        
        // Procesar avatar
        $tipoAvatar = $datos['tipo_avatar'] ?? 'defecto';
        $avatar = $usuario->getAvatar();
        
        if ($tipoAvatar === 'seleccionado') {
            $avatar = $datos['avatar'] ?? 'default.png';
        } elseif ($tipoAvatar === 'subido') {
            // Procesar archivo subido
            if (isset($_FILES['foto_subida']) && $_FILES['foto_subida']['error'] === UPLOAD_ERR_OK) {
                $archivo = $_FILES['foto_subida'];
                
                // Validar tipo de archivo
                $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($archivo['type'], $tiposPermitidos)) {
                    $this->errores['foto_subida'] = 'Formato no permitido. Usa JPG, PNG o GIF';
                }
                
                // Validar tamaño (2MB máximo)
                if ($archivo['size'] > 2 * 1024 * 1024) {
                    $this->errores['foto_subida'] = 'La imagen no puede superar los 2MB';
                }
                
                if (count($this->errores) === 0) {
                    // Generar nombre único
                    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
                    $nombreArchivo = 'usuario_' . $idUsuario . '_' . time() . '.' . $extension;
                    $rutaDestino = RAIZ_APP . '/img/avatares/' . $nombreArchivo;
                    
                    // Mover archivo
                    if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                        $avatar = $nombreArchivo;
                    } else {
                        $this->errores['foto_subida'] = 'Error al subir el archivo';
                    }
                }
            } else {
                $this->errores['foto_subida'] = 'Debes seleccionar una imagen';
            }
        }
        
        if (count($this->errores) === 0) {
            $datosActualizar = [
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'email' => $email,
                'avatar' => $avatar,
                'tipoAvatar' => $tipoAvatar
            ];
            
            if ($usuario->actualiza($datosActualizar)) {
                // Actualizar sesión
                $_SESSION['nombre'] = $nombre;
                return true;
            } else {
                $this->errores[] = 'Error al actualizar el perfil';
            }
        }
        
        return false;
    }
    
    private function getError($campo)
    {
        return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']);
    }
    
    private function checked($valor, $actual)
    {
        return $valor === $actual ? 'checked' : '';
    }
    
    private function displayStyle($condicion)
    {
        return $condicion ? 'block' : 'none';
    }
}
?>
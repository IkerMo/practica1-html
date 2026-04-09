<?php
namespace es\ucm\fdi\aw\Formularios;

use es\ucm\fdi\aw\Formularios\Formulario;
use es\ucm\fdi\aw\Usuarios\Usuario;
use es\ucm\fdi\aw\Usuarios\UsuarioDAO;

class FormularioEditarUsuarioAdmin extends Formulario
{
    private $idUsuario;
    
    public function __construct($idUsuario)
    {
        $this->idUsuario = $idUsuario;
        parent::__construct('editarUsuarioAdmin', [
            'urlRedireccion' => RUTA_VISTAS . '/usuarios/admin/listar.php',
            'enctype' => 'multipart/form-data'
        ]);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        $rutaImgs = RUTA_IMGS;
        
        // Obtener datos del usuario a editar
        $dao = new UsuarioDAO();
        $usuario = $dao->buscarPorId($this->idUsuario);
        
        if (!$usuario) {
            return '<p class="error">Usuario no encontrado</p>';
        }
        
        // Mostrar valores ingresados (con escape)
        $nombreUsuario = htmlspecialchars($datos['nombreUsuario'] ?? $usuario->nombreUsuario, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($datos['email'] ?? $usuario->email, ENT_QUOTES, 'UTF-8');
        $nombre = htmlspecialchars($datos['nombre'] ?? $usuario->nombre, ENT_QUOTES, 'UTF-8');
        $apellidos = htmlspecialchars($datos['apellidos'] ?? $usuario->apellidos, ENT_QUOTES, 'UTF-8');
        $avatarActual = $usuario->avatar ?? 'default.png';
        $tipoAvatar = $datos['tipo_avatar'] ?? $usuario->tipoAvatar ?? 'defecto';
        $activo = isset($datos['activo']) ? 'checked' : (($usuario->activo ?? true) ? 'checked' : '');
        
        // Lista de avatares predefinidos
        $avataresPredefinidos = ['default.png', 'chef.png', 'waiter.png', 'client.png', 'admin.png'];
        
        // Generar opciones del selector de avatar
        $opcionesAvatares = '';
        foreach ($avataresPredefinidos as $ava) {
            $selected = ($tipoAvatar == 'seleccionado' && $avatarActual == $ava) ? 'selected' : '';
            $opcionesAvatares .= "<option value=\"$ava\" $selected>$ava</option>";
        }
        
        $html = <<<EOS
        <fieldset>
            <legend>Editar usuario: {$usuario->nombreUsuario}</legend>
            
            <div class="campo">
                <label for="nombreUsuario">Nombre de usuario:</label>
                <input id="nombreUsuario" type="text" name="nombreUsuario" value="$nombreUsuario" />
                {$this->getError('nombreUsuario')}
                <small>Mínimo 4 caracteres. Solo letras, números y guión bajo</small>
            </div>
            
            <div class="campo">
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" value="$email" />
                {$this->getError('email')}
            </div>
            
            <div class="campo">
                <label for="nombre">Nombre:</label>
                <input id="nombre" type="text" name="nombre" value="$nombre" />
                {$this->getError('nombre')}
                <small>Solo letras y espacios</small>
            </div>
            
            <div class="campo">
                <label for="apellidos">Apellidos:</label>
                <input id="apellidos" type="text" name="apellidos" value="$apellidos" />
                {$this->getError('apellidos')}
                <small>Solo letras y espacios</small>
            </div>
            
            <div class="campo">
                <label for="password">Nueva contraseña (dejar en blanco para no cambiar):</label>
                <input id="password" type="password" name="password" />
                <small>Mínimo 6 caracteres si se cambia</small>
                {$this->getError('password')}
            </div>
            
            <div class="campo-checkbox">
                <label>
                    <input type="checkbox" name="activo" value="1" $activo>
                    Usuario activo
                </label>
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
                        Mantener actual / Por defecto
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="tipo_avatar" value="seleccionado" 
                               {$this->checked('seleccionado', $tipoAvatar)}> 
                        Seleccionar de la lista
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="tipo_avatar" value="subido" 
                               {$this->checked('subido', $tipoAvatar)}> 
                        Subir nueva foto
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
                <label for="foto_subida">Sube una nueva foto:</label>
                <input type="file" id="foto_subida" name="foto_subida" accept="image/jpeg,image/png,image/gif">
                <small>Máximo 2MB. Formatos: JPG, PNG, GIF</small>
                <div id="upload-preview"></div>
                {$this->getError('foto_subida')}
            </div>
            
            {$this->getGlobalErrors()}
            
            <div class="campo">
                <button type="submit">Guardar cambios</button>
                <a href="listar.php" class="btn-secondary">Cancelar</a>
            </div>
        </fieldset>
        
        <script>
        function actualizarPreview(valor) {
            document.getElementById('preview-avatar').src = '{$rutaImgs}/avatares/' + valor;
        }
        
        function seleccionarAvatar(avatar) {
            document.getElementById('avatar').value = avatar;
            actualizarPreview(avatar);
            
            document.querySelectorAll('.avatar-miniatura').forEach(function(img) {
                img.classList.remove('miniatura-seleccionada');
            });
            
            event.target.classList.add('miniatura-seleccionada');
        }
        
        document.querySelectorAll('input[name="tipo_avatar"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.getElementById('avatar-selector').style.display = 
                    this.value === 'seleccionado' ? 'block' : 'none';
                document.getElementById('avatar-upload').style.display = 
                    this.value === 'subido' ? 'block' : 'none';
            });
        });
        
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
        
        // Obtener usuario actual
        $usuario = Usuario::buscaUsuarioPorId($this->idUsuario);
        
        if (!$usuario) {
            $this->errores[] = 'Usuario no encontrado';
            return false;
        }
        
        // ========== OBTENER Y VALIDAR DATOS ==========
        
        // 1. Nombre de usuario
        $nombreUsuario = trim($datos['nombreUsuario'] ?? '');
        if (empty($nombreUsuario)) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario no puede estar vacío';
        } elseif (strlen($nombreUsuario) < 4) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario debe tener al menos 4 caracteres';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $nombreUsuario)) {
            $this->errores['nombreUsuario'] = 'Solo se permiten letras, números y guión bajo (_)';
        }
        
        // 2. Email
        $email = trim($datos['email'] ?? '');
        if (empty($email)) {
            $this->errores['email'] = 'El email es obligatorio';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = 'Email no válido';
        }
        
        // 3. Nombre
        $nombre = trim($datos['nombre'] ?? '');
        if (empty($nombre)) {
            $this->errores['nombre'] = 'El nombre es obligatorio';
        } elseif (strlen($nombre) < 2) {
            $this->errores['nombre'] = 'El nombre debe tener al menos 2 caracteres';
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre)) {
            $this->errores['nombre'] = 'Solo se permiten letras y espacios';
        }
        
        // 4. Apellidos
        $apellidos = trim($datos['apellidos'] ?? '');
        if (empty($apellidos)) {
            $this->errores['apellidos'] = 'Los apellidos son obligatorios';
        } elseif (strlen($apellidos) < 3) {
            $this->errores['apellidos'] = 'Los apellidos deben tener al menos 3 caracteres';
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $apellidos)) {
            $this->errores['apellidos'] = 'Solo se permiten letras y espacios';
        }
        
        // 5. Contraseña (opcional)
        $password = trim($datos['password'] ?? '');
        if (!empty($password) && strlen($password) < 6) {
            $this->errores['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        // ========== VERIFICAR QUE NO EXISTA OTRO CON MISMO NOMBRE/EMAIL ==========
        
        // Verificar nombre de usuario (si cambió)
        if ($nombreUsuario != $usuario->getNombreUsuario()) {
            $usuarioExistente = Usuario::buscaUsuario($nombreUsuario);
            if ($usuarioExistente) {
                $this->errores['nombreUsuario'] = 'El nombre de usuario ya está en uso';
            }
        }
        
        // Verificar email (si cambió)
        if ($email != $usuario->getEmail()) {
            $emailExistente = Usuario::buscaUsuario($email);
            if ($emailExistente) {
                $this->errores['email'] = 'El email ya está registrado';
            }
        }
        
        // ========== PROCESAR AVATAR ==========
        
        $tipoAvatar = $datos['tipo_avatar'] ?? 'defecto';
        $avatar = $usuario->getAvatar();
        
        if ($tipoAvatar === 'seleccionado') {
            $avatar = $datos['avatar'] ?? 'default.png';
        } elseif ($tipoAvatar === 'subido') {
            if (isset($_FILES['foto_subida']) && $_FILES['foto_subida']['error'] === UPLOAD_ERR_OK) {
                $archivo = $_FILES['foto_subida'];
                
                $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($archivo['type'], $tiposPermitidos)) {
                    $this->errores['foto_subida'] = 'Formato no permitido. Usa JPG, PNG o GIF';
                }
                
                if ($archivo['size'] > 2 * 1024 * 1024) {
                    $this->errores['foto_subida'] = 'La imagen no puede superar los 2MB';
                }
                
                if (count($this->errores) === 0) {
                    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
                    $nombreArchivo = 'usuario_' . $this->idUsuario . '_' . time() . '.' . $extension;
                    $rutaDestino = RAIZ_APP . '/img/avatares/' . $nombreArchivo;
                    
                    if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                        $avatar = $nombreArchivo;
                    } else {
                        $this->errores['foto_subida'] = 'Error al subir el archivo';
                    }
                }
            } elseif ($_FILES['foto_subida']['error'] !== UPLOAD_ERR_NO_FILE) {
                $this->errores['foto_subida'] = 'Error al subir el archivo';
            } else {
                $this->errores['foto_subida'] = 'Debes seleccionar una imagen';
            }
        }
        
        if (count($this->errores) > 0) return false;
        
        // ========== SANEAR (SOLO PARA ESCAPAR) ==========
        $nombreUsuarioSafe = filter_var($nombreUsuario, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $emailSafe = filter_var($email, FILTER_SANITIZE_EMAIL);
        $nombreSafe = filter_var($nombre, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $apellidosSafe = filter_var($apellidos, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // ========== ACTUALIZAR ==========
        $datosActualizar = [
            'nombreUsuario' => $nombreUsuarioSafe,
            'email' => $emailSafe,
            'nombre' => $nombreSafe,
            'apellidos' => $apellidosSafe,
            'avatar' => $avatar,
            'tipoAvatar' => $tipoAvatar
        ];
        
        if (!empty($password)) {
            $datosActualizar['password'] = $password;
        }
        
        // Actualizar activo
        $activo = isset($datos['activo']) ? 1 : 0;
        $datosActualizar['activo'] = $activo;
        
        if ($usuario->actualiza($datosActualizar)) {
            return true;
        } else {
            $this->errores[] = 'Error al actualizar el usuario';
            return false;
        }
    }
    
    private function getError($campo)
    {
        return self::createMensajeError($this->errores, $campo, 'span', ['class' => 'error']);
    }
    
    private function getGlobalErrors() {
        $erroresGlobales = array_filter(array_keys($this->errores), 'is_numeric');
        if (empty($erroresGlobales)) return '';
        
        $html = '<div class="errores-globales">';
        foreach ($erroresGlobales as $clave) {
            $html .= '<p class="error-message">' . htmlspecialchars($this->errores[$clave]) . '</p>';
        }
        $html .= '</div>';
        return $html;
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
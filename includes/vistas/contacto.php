<?php
require_once __DIR__ . '/../config.php';

$tituloPagina = 'Contacto - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <h1>Contacto</h1>
    
    <p class="subtitulo-contacto">
        Estamos aquí para ayudarte. Completa el formulario y nos pondremos en contacto contigo.
    </p>

    <div class="formulario-contacto">
        <form action="mailto:aafernan@ucm.es" method="post" enctype="text/plain">
            
            <div class="campo-formulario">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="" required placeholder="Introduce tu nombre completo"/>
            </div>
            
            <div class="campo-formulario">
                <label for="correo">Correo electrónico:</label>
                <input type="email" id="correo" name="correo" value="" required placeholder="ejemplo@correo.com"/>
            </div>
            
            <div class="campo-formulario">
                <label>Motivo de la consulta:</label>
                <div class="opciones-radio">
                    <div class="opcion-radio">
                        <input type="radio" id="evaluacion" name="motivo" value="1" required>
                        <label for="evaluacion">Evaluación</label>
                    </div>
                    
                    <div class="opcion-radio">
                        <input type="radio" id="sugerencias" name="motivo" value="2">
                        <label for="sugerencias">Sugerencias</label>
                    </div>
                    
                    <div class="opcion-radio">
                        <input type="radio" id="criticas" name="motivo" value="3">
                        <label for="criticas">Críticas</label>
                    </div>
                </div>
            </div>
            
            <div class="campo-formulario checkbox">
                <input type="checkbox" id="terminos" name="terminos" value="aceptado" required>
                <label for="terminos">
                    Marque esta casilla para verificar que ha leído nuestros términos y condiciones del servicio
                </label>
            </div>
            
            <div class="campo-formulario">
                <label for="consulta">Consulta:</label>
                <textarea id="consulta" name="consulta" rows="6" cols="40" required placeholder="Escribe aquí tu mensaje..."></textarea>
            </div>
            
            <div class="campo-formulario botones">
                <button type="submit" class="btn-enviar">Enviar mensaje</button>
                <button type="reset" class="btn-reset">Limpiar formulario</button>
            </div>
        </form>
        
        <div class="info-contacto">
            <h3>Otros medios de contacto</h3>
            <p>
                <strong>Email:</strong> <a href="mailto:info@bistrofdi.com">info@bistrofdi.com</a><br>
                <strong>Teléfono:</strong> +34 91 234 56 78<br>
                <strong>Dirección:</strong> Calle Ejemplo, 123, 28040 Madrid
            </p>
            
            <h3>Horario de atención</h3>
            <p>
                Lunes a viernes: 9:00 - 20:00<br>
                Sábados: 10:00 - 14:00<br>
                Domingos: Cerrado
            </p>
        </div>
    </div>
EOS;

// Usar la plantilla específica para la práctica 1
require_once __DIR__ . '/comun/plantilla.php';
?>
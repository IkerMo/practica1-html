<?php
require_once __DIR__ . '/../config.php';

$tituloPagina = 'Miembros del equipo - Bistro FDI';
$rutaImgs = RUTA_IMGS; // Asignar constante a variable

// Generar el índice de miembros (enlaces a anclas)
$indiceMiembros = <<<EOS
    <div class="indice-miembros">
        <ul>
            <li><a href="#Carlos">Carlos Lucas Ruiz</a></li>
            <li><a href="#Aaron">Aarón Fernández Herradón</a></li>
            <li><a href="#Matin">Xiangzhenhua Liu</a></li>
            <li><a href="#Iker">Iker Moreno Rodríguez</a></li>
        </ul>
    </div>
EOS;

// Contenido principal con todos los miembros
$contenidoPrincipal = <<<EOS
    <h1>Miembros del proyecto</h1>
    
    <h2>Integrantes:</h2>
    
    $indiceMiembros
    
    <div class="miembro" id="Carlos">
        <h3>Carlos Lucas Ruiz</h3>
        <p>
            <strong>Nombre completo:</strong> Carlos Lucas Ruiz<br>
            <strong>Correo:</strong> <a href="mailto:carlluca@ucm.es">carlluca@ucm.es</a><br>
            <strong>Aficiones:</strong> Me gusta jugar a la brisca y comer pulpo. Por las noches salgo a correr.
        </p>
        <figure>
            <img src="{$rutaImgs}/fotocarlos.jpg" alt="Foto Carlos" style="width:400px;">
            <figcaption>Carlos Lucas Ruiz</figcaption>
        </figure>
        <hr>
    </div>
    
    <div class="miembro" id="Aaron">
        <h3>Aarón Fernández Herradón</h3>
        <p>
            <strong>Nombre completo:</strong> Aarón Fernández Herradón<br>
            <strong>Correo:</strong> <a href="mailto:aafernan@ucm.es">aafernan@ucm.es</a><br>
            <strong>Aficiones:</strong> Me gusta el cine, las series y leer de vez en cuando. También me gusta el Getafe C.F. Llevo 245 días de racha en Duolingo ¡qué pasada!
        </p>
        <figure>
            <img src="{$rutaImgs}/fotoaaron.jpg" alt="Foto Aaron" style="width:400px;">
            <figcaption>Aarón Fernández Herradón</figcaption>
        </figure>
        <hr>
    </div>
    
    <div class="miembro" id="Matin">
        <h3>Xiangzhenhua Liu</h3>
        <p>
            <strong>Nombre completo:</strong> Xiangzhenhua Liu<br>
            <strong>Correo:</strong> <a href="mailto:xiangliu@ucm.es">xiangliu@ucm.es</a><br>
            <strong>Aficiones:</strong> Soy un aficionado de la literatura. Mi obra favorita es La Celestina de Fernando de Rojas. También me gusta el sushi y dar paseos con mi perro por la mañana.
        </p>
        <figure>
            <img src="{$rutaImgs}/fotomatin.png" alt="Foto Matin" style="width:400px;">
            <figcaption>Xiangzhenhua Liu</figcaption>
        </figure>
        <hr>
    </div>
    
    <div class="miembro" id="Iker">
        <h3>Iker Moreno Rodríguez</h3>
        <p>
            <strong>Nombre completo:</strong> Iker Moreno Rodríguez<br>
            <strong>Correo:</strong> <a href="mailto:ikemo01@ucm.es">ikemo01@ucm.es</a><br>
            <strong>Aficiones:</strong> Me gusta el fútbol, soy un gran aficionado del Real Madrid. Mi juego favorito para los viernes por la noche es el Catán, ¡vaya tardes con los amigos hemos pasado!
        </p>
        <figure>
            <img src="{$rutaImgs}/fotoiker.jpg" alt="Foto Iker" style="width:400px;">
            <figcaption>Iker Moreno Rodríguez</figcaption>
        </figure>
    </div>
EOS;

require_once __DIR__ . '/comun/plantilla.php';
?>
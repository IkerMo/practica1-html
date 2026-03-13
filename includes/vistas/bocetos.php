<?php
require_once __DIR__ . '/../config.php';
$rutaImgs = RUTA_IMGS;

$tituloPagina = 'Bocetos de la Aplicación - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <h1>Bocetos de la Aplicación</h1>
    
    <p class="intro-bocetos">
        En esta sección se presentan los bocetos de baja fidelidad de las principales pantallas 
        de la aplicación Bistro FDI. Estos diseños representan la estructura visual inicial del sistema 
        y pueden evolucionar durante el desarrollo del proyecto.
    </p>

    <!-- BOCETO 1 -->
    <section class="boceto" id="boceto1">
        <h2>Boceto 1 – Página Principal</h2>
        
        <figure class="boceto-imagen">
            <img src="{$rutaImgs}/Boceto1.jpg" alt="Boceto página principal" width="600">
            <figcaption>Boceto de la página principal</figcaption>
        </figure>
        
        <div class="boceto-info">
            <p><strong>Funcionalidad asociada:</strong> Acceso general a la aplicación.</p>
            <p>
                La página principal muestra el logotipo del restaurante, una breve descripción y 
                botones para iniciar sesión o registrarse. Desde aquí los usuarios pueden acceder 
                a las distintas funcionalidades según su rol.
            </p>
        </div>
    </section>

    <!-- BOCETO 2 -->
    <section class="boceto" id="boceto2">
        <h2>Boceto 2 – Vista Cliente: Carta de Productos</h2>
        
        <figure class="boceto-imagen">
            <img src="{$rutaImgs}/Boceto2.jpg" alt="Boceto carta de productos" width="600">
            <figcaption>Boceto de la carta de productos</figcaption>
        </figure>
        
        <div class="boceto-info">
            <p><strong>Funcionalidad asociada:</strong> Gestión de pedidos.</p>
            <p>
                En esta pantalla el cliente puede ver las categorías de productos, consultar la 
                información de cada producto y añadirlos al carrito. También aparece el icono 
                del carrito para revisar el pedido actual.
            </p>
        </div>
    </section>

    <!-- BOCETO 3 -->
    <section class="boceto" id="boceto3">
        <h2>Boceto 3 – Carrito y Confirmación</h2>
        
        <div class="imagenes-multiples">
            <figure class="boceto-imagen">
                <img src="{$rutaImgs}/Boceto31.jpg" alt="Boceto carrito - vista 1" width="600">
                <figcaption>Carrito de compras</figcaption>
            </figure>
            
            <figure class="boceto-imagen">
                <img src="{$rutaImgs}/Boceto32.jpg" alt="Boceto carrito - vista 2" width="600">
                <figcaption>Confirmación del pedido</figcaption>
            </figure>
        </div>
        
        <div class="boceto-info">
            <p><strong>Funcionalidad asociada:</strong> Confirmación y pago del pedido.</p>
            <p>
                El cliente puede revisar los productos añadidos, modificar cantidades, eliminar 
                productos y confirmar el pedido. Tras la confirmación, se genera un número de pedido 
                y se muestra su estado.
            </p>
        </div>
    </section>

    <!-- BOCETO 4 -->
    <section class="boceto" id="boceto4">
        <h2>Boceto 4 – Vista Cocinero</h2>
        
        <figure class="boceto-imagen">
            <img src="{$rutaImgs}/Boceto4.jpg" alt="Boceto vista cocinero" width="600">
            <figcaption>Vista del cocinero</figcaption>
        </figure>
        
        <div class="boceto-info">
            <p><strong>Funcionalidad asociada:</strong> Preparación de pedidos.</p>
            <p>
                El cocinero visualiza los pedidos en estado "En preparación", puede aceptar uno, 
                marcar productos como preparados y cambiar el estado a "Listo cocina".
            </p>
        </div>
    </section>

    <!-- BOCETO 5 -->
    <section class="boceto" id="boceto5">
        <h2>Boceto 5 – Vista Camarero</h2>
        
        <figure class="boceto-imagen">
            <img src="{$rutaImgs}/Boceto5.jpg" alt="Boceto vista camarero" width="600">
            <figcaption>Vista del camarero</figcaption>
        </figure>
        
        <div class="boceto-info">
            <p><strong>Funcionalidad asociada:</strong> Cobro y entrega de pedidos.</p>
            <p>
                El camarero puede confirmar el pago de un pedido y posteriormente marcarlo 
                como entregado al cliente.
            </p>
        </div>
    </section>

    <!-- BOCETO 6 -->
    <section class="boceto" id="boceto6">
        <h2>Boceto 6 – Vista Gerente</h2>
        
        <figure class="boceto-imagen">
            <img src="{$rutaImgs}/Boceto6.jpg" alt="Boceto panel gerente" width="600">
            <figcaption>Panel del gerente</figcaption>
        </figure>
        
        <div class="boceto-info">
            <p><strong>Funcionalidad asociada:</strong> Gestión de productos, ofertas y usuarios.</p>
            <p>
                El gerente dispone de un panel de administración donde puede crear, modificar, 
                listar y eliminar productos, ofertas, recompensas y usuarios.
            </p>
        </div>
    </section>

    <section class="navegacion-general">
        <h2>Navegación General</h2>
        
        <p>El flujo principal de navegación es el siguiente:</p>
        
        <ol class="flujo-navegacion">
            <li>Acceso a la página principal.</li>
            <li>Inicio de sesión.</li>
            <li>Acceso a la vista correspondiente según el rol.</li>
            <li>Uso de las funcionalidades específicas.</li>
        </ol>
        
        <p class="nota">
            <strong>Nota:</strong> Cada usuario visualiza únicamente las pantallas correspondientes a su rol.
        </p>
    </section>
EOS;

// Usar la plantilla específica para la práctica 1
require_once __DIR__ . '/comun/plantilla.php';
?>
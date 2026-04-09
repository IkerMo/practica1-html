<?php
//Inicio del procesamiento
require_once __DIR__ . '/includes/config.php';

$tituloPagina = 'Portada';

$contenidoPrincipal=<<<EOS
    <h1><strong>Index </strong></h1>
        <img src="IMG/logo1.png" alt="Logo Bistro FDI" class="w-400">

    <section class="hero">
        <h2>Bienvenido a Bistro FDI</h2>
        <p>Donde la tecnología y la gastronomía se encuentran.</p>
    </section>
    
    <h2>Descripción del Proyecto</h2>
        <p>Bistro FDI es una aplicación web diseñada para gestionar de forma integral el funcionamiento del restaurante universitario. Permite a los clientes registrarse, consultar la carta, realizar pedidos desde su dispositivo móvil o portátil y hacer seguimiento del estado de sus pedidos en tiempo real.
        Además, el sistema facilita al personal del restaurante (camareros, cocineros y gerente) la organización y gestión de productos, pedidos y usuarios, mejorando la eficiencia del servicio.
        La aplicación está diseñada para adaptarse a distintos dispositivos y ofrecer una experiencia intuitiva tanto para clientes como para empleados, optimizando el proceso desde la creación del pedido hasta su entrega final.</p>

EOS;

require("includes/vistas/comun/plantilla.php");
?>
<?php
require_once __DIR__ . '/../config.php';
$rutaImgs = RUTA_IMGS;

$tituloPagina = 'Detalles del Proyecto - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <h1>Detalles del Proyecto</h1>
    
    <figure class="logo-principal">
        <img src="{$rutaImgs}/logo1.png" alt="Logo Bistro FDI" style="width:400px;">
        <figcaption>Logo de Bistro FDI</figcaption>
    </figure>

    <section class="descripcion-general">
        <h2>Descripción General</h2>
        <p>
            Bistro FDI es una aplicación web diseñada para gestionar de forma digital el funcionamiento del restaurante. 
            Su objetivo principal es facilitar a los clientes la realización de pedidos desde su propio dispositivo y 
            permitirles consultar el estado de sus pedidos en tiempo real.
        </p>
        <p>
            Además, la aplicación ayuda al personal del restaurante a organizar el trabajo interno, mejorando la 
            coordinación entre camareros, cocineros y gerente. De esta forma, se busca agilizar el proceso desde que 
            el cliente realiza el pedido hasta que lo recibe.
        </p>
        <p>
            La aplicación estará adaptada a distintos dispositivos y contará con diferentes vistas según el tipo de usuario.
        </p>
    </section>

    <section class="usuarios-sistema">
        <h2>Usuarios del Sistema</h2>
        <div class="tarjetas-usuarios">
            <div class="tarjeta-usuario cliente">
                <h3>👤 Cliente</h3>
                <p>
                    Es el usuario que realiza pedidos. Puede registrarse en la aplicación, iniciar sesión, 
                    consultar la carta de productos, añadir productos al carrito, confirmar pedidos y consultar 
                    el estado de los mismos. Además, podrá beneficiarse de ofertas y recompensas.
                </p>
            </div>
            
            <div class="tarjeta-usuario cocinero">
                <h3>👨‍🍳 Cocinero</h3>
                <p>
                    Es el responsable de preparar los pedidos. Puede seleccionar pedidos pendientes, 
                    marcar productos como preparados y finalizar la preparación.
                </p>
            </div>
            
            <div class="tarjeta-usuario camarero">
                <h3>🧑‍💼 Camarero</h3>
                <p>
                    Es el encargado de gestionar el cobro de los pedidos y su entrega. Puede cambiar 
                    el estado de los pedidos cuando se pagan y cuando se entregan al cliente.
                </p>
            </div>
            
            <div class="tarjeta-usuario gerente">
                <h3>👔 Gerente</h3>
                <p>
                    Tiene el mayor nivel de permisos. Puede gestionar usuarios, productos, categorías, 
                    ofertas y recompensas, además de consultar el estado general de los pedidos.
                </p>
            </div>
        </div>
    </section>

    <section class="funcionalidades">
        <h2>Funcionalidades Principales</h2>
        
        <div class="funcion">
            <h3>📋 Gestión de usuarios</h3>
            <p>
                Permite el registro, inicio de sesión y modificación de datos personales. 
                <strong>Cliente y Gerente:</strong> El gerente puede cambiar roles y gestionar los usuarios del sistema.
            </p>
        </div>
        
        <div class="funcion">
            <h3>🍽️ Gestión de productos</h3>
            <p>
                <strong>Gerente:</strong> Puede crear, modificar, listar y eliminar categorías y productos. 
                También puede indicar si un producto está disponible o no.
            </p>
        </div>
        
        <div class="funcion">
            <h3>🛒 Gestión de pedidos</h3>
            <p>
                <strong>Cliente:</strong> Puede crear pedidos seleccionando productos y confirmarlos.<br>
                <strong>Camarero:</strong> Gestiona el cobro y la entrega.<br>
                <strong>Cocinero:</strong> Prepara los pedidos y actualiza su estado.
            </p>
        </div>
        
        <div class="funcion">
            <h3>🏷️ Gestión de ofertas</h3>
            <p>
                <strong>Gerente:</strong> Puede crear y administrar ofertas con descuentos.<br>
                <strong>Cliente:</strong> Puede aplicarlas a sus pedidos si cumplen las condiciones.
            </p>
        </div>
        
        <div class="funcion">
            <h3>⭐ Gestión de recompensas</h3>
            <p>
                <strong>Cliente:</strong> Acumula BistroCoins por sus compras y puede canjearlas por productos.<br>
                <strong>Gerente:</strong> Administra las recompensas disponibles.
            </p>
        </div>
    </section>
EOS;

// Usar la plantilla específica para la práctica 1
require_once __DIR__ . '/comun/plantilla.php';
?>
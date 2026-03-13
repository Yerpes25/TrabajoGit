<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Cubetic bonos') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet" /> <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    <footer class="bg-white shadow mt-6">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} {{ config('app.name', 'Cubetic bonos') }}. Todos los derechos reservados.
        </div>
    </footer>
    <script type="module">
        document.addEventListener('DOMContentLoaded', () => {
            const miIdDeUsuario = "{{ auth()->id() }}";

            if (window.Echo && miIdDeUsuario) {
                const canalNotificaciones = `App.Models.User.${miIdDeUsuario}`;

                window.Echo.private(canalNotificaciones)
                    .notification((datos) => {

                        // 1. ACTUALIZAR BURBUJAS (Escritorio y Móvil)
                        const actualizarBurbuja = (id) => {
                            const burbuja = document.getElementById(id);
                            if (burbuja) {
                                let numeroActual = parseInt(burbuja.innerText) || 0;
                                burbuja.innerText = numeroActual + 1;
                                burbuja.classList.remove('hidden');
                            }
                        };

                        actualizarBurbuja('burbuja_contador'); // Escritorio
                        actualizarBurbuja('burbuja_contador_movil'); // Móvil

                        // 2. AÑADIR EL MENSAJE A LAS LISTAS (Escritorio y Móvil)
                        const anadirMensaje = (idCaja, idMensajeVacio) => {
                            const cajaMensajes = document.getElementById(idCaja);
                            const mensajeVacio = document.getElementById(idMensajeVacio);

                            if (cajaMensajes) {
                                if (mensajeVacio) {
                                    mensajeVacio.remove();
                                }

                                const nuevoDiv = document.createElement('div');
                                nuevoDiv.className = 'p-3 text-sm text-gray-700 border-b hover:bg-gray-50 transition bg-blue-50';
                                nuevoDiv.innerText = datos.mensaje;

                                cajaMensajes.prepend(nuevoDiv);
                            }
                        };

                        anadirMensaje('lista_mensajes_web', 'mensaje_vacio'); // Escritorio
                        anadirMensaje('lista_mensajes_web_movil', 'mensaje_vacio_movil'); // Móvil
                    });
            }
        });
    </script>
</body>

</html>
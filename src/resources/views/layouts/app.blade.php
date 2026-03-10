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
    @if(Auth::check() && Auth::user()->role === 'client')
    @php
    /**
    * Buscamos el modelo Client asociado al usuario actual
    * para poder usar su ID en el canal privado de WebSockets.
    */
    $clienteLogueado = \App\Models\Client::where('user_id', Auth::id())->first();
    @endphp

    @if($clienteLogueado)
    <script type="module">
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Esperando notificaciones del técnico...");

            if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
                Notification.requestPermission();
            }

            window.Echo.private('canal-cliente.{{ $clienteLogueado->id }}')
                .listen('.alerta.nueva.intervencion', (evento) => {

                    console.log("¡Señal recibida del WebSocket!", evento);

                    if (Notification.permission === "granted") {
                        const opcionesNotificacion = {
                            body: `El técnico ha actualizado el parte: ${evento.titulo}\nEstado: ${evento.estado}`,
                            icon: '/favicon.svg',
                            requireInteraction: true
                        };

                        new Notification("Novedades en tu panel", opcionesNotificacion);
                    } else {
                        console.warn("La señal llegó, pero no tienes permisos de Windows para mostrarla.");
                    }
                })
                .error((error) => {
                    console.error("Error al conectar al canal privado:", error);
                });
        });
    </script>
    @endif
    @endif
</body>

</html>
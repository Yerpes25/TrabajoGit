<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Menú de Navegación Principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-14 w-14 fill-current text-black" />
                    </a>
                </div>

                <!-- Enlaces de Navegación (Escritorio) -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" style="color: black;">
                        {{ __('Inicio') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Opciones del lado derecho (Escritorio) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">

                <!-- INICIO CAMPANA NOTIFICACIONES (ESCRITORIO) -->
                <div class="relative mr-4" x-data="{ abierto: false }">
                    <button @click="
                        abierto = !abierto; 
                        
                        /* 1. Ocultamos la burbuja visualmente */
                        let burbuja = document.getElementById('burbuja_contador');
                        if (burbuja && !burbuja.classList.contains('hidden')) {
                            burbuja.classList.add('hidden');
                            burbuja.innerText = '0';
                            
                            /* 2. Avisamos a Laravel por detras para que actualice la Base de Datos */
                            fetch('{{ route('notificaciones.leidas') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                        }
                    "
                        class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none transition duration-150">

                        @php
                        // Calculamos cuantas notificaciones no leidas hay al cargar la pagina
                        $cantidadNoLeidas = auth()->user() ? auth()->user()->unreadNotifications->count() : 0;
                        @endphp

                        <!-- LA BURBUJA DEL CONTADOR (Oculta si hay 0) -->
                        <span id="burbuja_contador"
                            class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full transform translate-x-1/4 -translate-y-1/4 {{ $cantidadNoLeidas > 0 ? '' : 'hidden' }}">
                            {{ $cantidadNoLeidas }}
                        </span>

                        <!-- ICONO SVG DE LA CAMPANA -->
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </button>

                    <!-- CAJA DESPLEGABLE DE MENSAJES (ESCRITORIO) -->
                    <div x-show="abierto" @click.away="abierto = false" class="absolute right-0 mt-2 w-72 bg-white border rounded shadow-lg z-50 overflow-hidden" style="display: none;">
                        <div class="p-2 text-xs font-bold text-gray-400 uppercase border-b bg-gray-50 flex justify-between">
                            <span>Notificaciones</span>
                        </div>

                        <div id="lista_mensajes_web" class="max-h-64 overflow-y-auto">
                            @php
                            $todasLasNotificaciones = auth()->user() ? auth()->user()->notifications : collect();
                            @endphp

                            @forelse($todasLasNotificaciones as $notificacion)
                            <div class="p-3 text-sm text-gray-700 border-b hover:bg-gray-50 transition {{ is_null($notificacion->read_at) ? 'bg-blue-50' : '' }}">
                                {{ $notificacion->data['mensaje'] }}
                            </div>
                            @empty
                            <p class="p-3 text-sm text-gray-500 italic" id="mensaje_vacio">No hay avisos nuevos</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <!-- FIN CAMPANA NOTIFICACIONES (ESCRITORIO) -->

                <!-- Contenedor para las notificaciones Push visuales -->
                <div id="contenedorPush" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"></div>

                <!-- Dropdown de Usuario -->
                <x-dropdown align="right" width="60">
                    <x-slot name="trigger">
                        <button class="flex items-center focus:outline-none ml-2">
                            <div style="width: 30px; height: 30px; font-size: 20px;" class="bg-slate-200 text-slate-700 rounded-full flex items-center justify-center font-bold overflow-hidden border border-slate-300 shadow-sm hover:bg-slate-300 transition-colors">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <!-- Autenticación -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="whitespace-nowrap px-4 py-1 text-sm">
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger e Iconos Móviles -->
            <div class="-me-2 flex items-center sm:hidden">

                <!-- INICIO CAMPANA NOTIFICACIONES (MÓVIL) -->
                <div class="relative mr-2" x-data="{ abierto: false }">
                    <button @click="
                        abierto = !abierto; 
                        
                        let burbujaMovil = document.getElementById('burbuja_contador_movil');
                        if (burbujaMovil && !burbujaMovil.classList.contains('hidden')) {
                            burbujaMovil.classList.add('hidden');
                            burbujaMovil.innerText = '0';
                            
                            fetch('{{ route('notificaciones.leidas') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                        }
                    "
                        class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none transition duration-150">

                        @php
                        $cantidadNoLeidas = auth()->user() ? auth()->user()->unreadNotifications->count() : 0;
                        @endphp

                        <!-- BURBUJA CONTADOR MÓVIL -->
                        <span id="burbuja_contador_movil"
                            class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full transform translate-x-1/4 -translate-y-1/4 {{ $cantidadNoLeidas > 0 ? '' : 'hidden' }}">
                            {{ $cantidadNoLeidas }}
                        </span>

                        <!-- ICONO SVG CAMPANA -->
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </button>

                    <!-- CAJA DESPLEGABLE MÓVIL (Ajustada para pantalla pequeña) -->
                    <div x-show="abierto" @click.away="abierto = false" class="absolute right-0 mt-2 w-64 bg-white border rounded shadow-lg z-50 overflow-hidden" style="display: none;">
                        <div class="p-2 text-xs font-bold text-gray-400 uppercase border-b bg-gray-50 flex justify-between">
                            <span>Notificaciones</span>
                        </div>

                        <div id="lista_mensajes_web_movil" class="max-h-64 overflow-y-auto">
                            @php
                            $todasLasNotificaciones = auth()->user() ? auth()->user()->notifications : collect();
                            @endphp

                            @forelse($todasLasNotificaciones as $notificacion)
                            <div class="p-3 text-sm text-gray-700 border-b hover:bg-gray-50 transition {{ is_null($notificacion->read_at) ? 'bg-blue-50' : '' }}">
                                {{ $notificacion->data['mensaje'] }}
                            </div>
                            @empty
                            <p class="p-3 text-sm text-gray-500 italic" id="mensaje_vacio_movil">No hay avisos nuevos</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <!-- FIN CAMPANA NOTIFICACIONES (MÓVIL) -->

                <!-- Botón Hamburger -->
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú de Navegación Responsivo (El que se abre con las tres rayas) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" style="color: black;">
                {{ __('Inicio') }}
            </x-responsive-nav-link>
        </div>

        <!-- Opciones de Ajustes Responsivas -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- El enlace de Notificaciones se ha eliminado de aqui porque ya esta en la campana -->

                <x-responsive-nav-link href="#">
                    {{ __('Ajustes') }}
                </x-responsive-nav-link>

                <!-- Autenticación -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
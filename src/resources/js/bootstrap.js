/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Archivo de configuracion para conectar el frontend con el servidor WebSocket (Reverb).
 * Utiliza Laravel Echo para suscribirse a los canales y escuchar los eventos en tiempo real.
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

import './echo';

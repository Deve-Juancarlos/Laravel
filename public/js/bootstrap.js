/**
 * Cargamos la biblioteca HTTP 'axios' que nos da una API fácil
 * para enviar peticiones AJAX.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Cargamos Bootstrap. Esto nos da acceso a todas las funciones 
 * de JavaScript de Bootstrap (como Modales, Dropdowns, etc.).
 */
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

/**
 * (Opcional) Aquí es donde podrías cargar otras bibliotecas 
 * como Echo para websockets.
 */
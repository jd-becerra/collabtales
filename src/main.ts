import './assets/main.css';

import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { createVuetify } from 'vuetify';
import 'vuetify/styles';


const vuetify = createVuetify()

// Configuración de Axios
import axios from 'axios';
axios.defaults.baseURL = import.meta.env.VITE_PHP_SERVER + "/";
axios.defaults.headers.common['Content-Type'] = 'application/json';

// Añadir el token de autorización a las peticiones (si el token existe)
axios.interceptors.request.use(
  function(config) {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    } else {
      delete config.headers['Authorization'];
    }
    return config;
  },
  function(error) {
    return Promise.reject(error);
  }
);

axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && (
         error.response.status === 401 ||
         (error.response.data.error && error.response.data.error === 'Unauthorized')
       )) {
      localStorage.removeItem('token');
      localStorage.removeItem('id_alumno');
      router.push('/');
    }
    return Promise.reject(error);
  }
);

const app = createApp(App);

app.use(router);
app.use(vuetify);

app.mount('#app');

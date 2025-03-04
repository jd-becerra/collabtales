import './assets/main.css';

import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { createVuetify } from 'vuetify';
import 'vuetify/styles';

const vuetify = createVuetify() 

// Configuración de Axios
import axios from 'axios';
axios.defaults.baseURL = 'http://localhost:8000/';
axios.defaults.headers.common['Content-Type'] = 'application/json'; // Se recomienda JSON en lugar de URL encoded

const app = createApp(App);

app.use(router);
app.use(vuetify);

app.mount('#app');

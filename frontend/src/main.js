import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';

import App from './App.vue';

// Estilos globales de Tailwind e Iconos de FontAwesome
import './style.css';
import '@fortawesome/fontawesome-free/css/all.css';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

app.mount('#app');

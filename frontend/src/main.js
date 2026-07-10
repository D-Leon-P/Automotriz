import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';

import App from './App.vue';

// Estilos globales de Tailwind e Iconos de FontAwesome
import './style.css';
import '@fortawesome/fontawesome-free/css/all.css';

const app = createApp(App);
const pinia = createPinia();

// Directiva global para Tooltips premium (v-title)
app.directive('title', {
  mounted(el, binding) {
    if (!binding.value) return;
    el.classList.add('relative', 'group');
    const tooltip = document.createElement('div');
    
    // Determinar posicionamiento basado en modificadores (.right)
    const positionClasses = binding.modifiers.right
      ? 'right-0 mb-2 origin-bottom-right'
      : 'left-1/2 -translate-x-1/2 mb-2 origin-bottom';
      
    tooltip.className = `absolute bottom-full ${positionClasses} invisible opacity-0 scale-95 group-hover:visible group-hover:opacity-100 group-hover:scale-100 z-50 bg-slate-950/95 backdrop-blur-md text-slate-200 text-[10px] uppercase tracking-wider font-extrabold px-2.5 py-1 rounded-lg border border-white/10 shadow-2xl whitespace-nowrap pointer-events-none transition-all duration-150 ease-out`;
    tooltip.innerText = binding.value;
    el.appendChild(tooltip);
  },
  updated(el, binding) {
    const tooltip = el.querySelector('.absolute.bottom-full');
    if (tooltip) {
      tooltip.innerText = binding.value || '';
    }
  }
});

app.use(pinia);
app.use(router);

app.mount('#app');

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

import Dashboard from '../views/Dashboard.vue';
import Login from '../views/Login.vue';
import Prospectos from '../views/Prospectos.vue';
import Ventas from '../views/Ventas.vue';
import Seguros from '../views/Seguros.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guest: true }
  },
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/prospectos',
    name: 'Prospectos',
    component: Prospectos,
    meta: { requiresAuth: true }
  },
  {
    path: '/ventas',
    name: 'Ventas',
    component: Ventas,
    meta: { requiresAuth: true }
  },
  {
    path: '/seguros',
    name: 'Seguros',
    component: Seguros,
    meta: { requiresAuth: true }
  },
  {
    path: '/generales/empleados',
    name: 'Empleados',
    component: () => import('../views/generales/Empleados.vue'),
    meta: { requiresAuth: true, permission: 'ver_empleados' }
  },
  {
    path: '/generales/clientes',
    name: 'Clientes',
    component: () => import('../views/generales/Clientes.vue'),
    meta: { requiresAuth: true, permission: 'ver_clientes' }
  },
  {
    path: '/generales/roles',
    name: 'Roles',
    component: () => import('../views/generales/Roles.vue'),
    meta: { requiresAuth: true, permission: 'ver_roles' }
  },
  // Redireccionar cualquier otra ruta al dashboard
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Guard de Navegación Global
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isLoggedIn = authStore.isAuthenticated;

  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isLoggedIn) {
      next({ name: 'Login' });
    } else {
      // Verificar permisos de ruta
      const matchedRecord = to.matched.find(record => record.meta.permission);
      if (matchedRecord && !authStore.hasPermission(matchedRecord.meta.permission)) {
        next({ name: 'Dashboard' });
      } else {
        next();
      }
    }
  } else if (to.matched.some(record => record.meta.guest)) {
    if (isLoggedIn) {
      next({ name: 'Dashboard' });
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;

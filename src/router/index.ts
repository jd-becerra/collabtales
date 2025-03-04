import { createRouter, createWebHistory } from 'vue-router'
import CrearCuento from '../components/CrearCuento.vue';
import ListaCuentos from '@/components/ListaCuentos.vue';
import Inicio_Sesion from '@/views/Inicio_Sesion.vue';
import Crear_Cuento from '../components/CrearCuento.vue';
import Perfil_Usuario from '@/views/Perfil_Usuario.vue';
import Panel_Inicio from '@/views/Panel_Inicio.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'inicio_sesion',
      component: Inicio_Sesion
    },
    {
      path: '/crear_cuento',
      name: 'crear_cuento',
      component: Crear_Cuento
    },
    {
      path: '/perfil_usuario',
      name: 'perfil_usuario',
      component: Perfil_Usuario
    },
    {
      path: '/panel_inicio',
      name: 'panel_inicio',
      component: Panel_Inicio
    }
  ],
});

export default router;

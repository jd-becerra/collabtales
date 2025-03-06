import { createRouter, createWebHistory } from 'vue-router'
import CrearCuento from '../components/CrearCuento.vue';
import ListaCuentos from '@/components/ListaCuentos.vue';
import Inicio_Sesion from '@/views/InicioSesionview.vue';
import Crear_Cuento from '../components/CrearCuento.vue';
import Perfil_Usuario from '@/views/PerfilUsuarioview.vue';
import Panel_Inicio from '@/views/PanelInicioview.vue';
import VerCuento from '@/views/VerCuentoview.vue';
import EditarCuento from '@/components/EditarCuento.vue';

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
    },
    {
      path: '/crear_cuento',
      name: 'crear_cuento',
      component: CrearCuento
    },
    {
      path: '/lista_cuentos',
      name: 'lista_cuentos',
      component: ListaCuentos
    },
    {
      path: '/ver_cuento',
      name: 'ver_cuento',
      component: VerCuento
    },
    {
      path: '/editar_cuento',
      name: 'editar_cuento',
      component: EditarCuento
    }
  ],
});

export default router;

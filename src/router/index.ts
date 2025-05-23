import { createRouter, createWebHistory } from 'vue-router'
import Inicio_Sesion from '@/views/InicioSesionView.vue';
import Perfil_Usuario from '@/views/PerfilUsuarioView.vue';
import Panel_Inicio from '@/views/PanelInicioView.vue';
import VerCuento from '@/views/VerCuentoView.vue';
import RestaurarContraseña from '@/views/ValidarTokenRestauracionView.vue';
import EditarAportacion from '@/components/EditarAportacion.vue';
import Descarga from '@/views/VerPDF.vue'
import VerCuentoPublicoView from '@/views/VerCuentoPublicoView.vue';
import MisCuentosView from '@/views/MisCuentosView.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'inicio_sesion',
      component: Inicio_Sesion
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
      path: '/mis_cuentos',
      name: 'mis_cuentos',
      component: MisCuentosView
    },
    {
      path: '/ver_cuento',
      name: 'ver_cuento',
      component: VerCuento
    },
    {
      path: '/ver_cuento/:id_cuento',
      name: 'ver_cuento_id',
      component: VerCuento,
      props: true
    },
    {
      path: '/ver_cuento_publico/:id_cuento',
      name: 'ver_cuento_publico_id',
      component: VerCuentoPublicoView,
      props: true
    },
    {
      path: '/editar_aportacion',
      name: 'editar_aportacion',
      component: EditarAportacion
    },
    {
      path: '/descarga',
      name: 'descarga',
      component: Descarga
    },
    {
      path: '/restaurar_contrasena',  // NOTA: contrasena no lleva "ñ"
      name: 'RestaurarContraseña',
      component: RestaurarContraseña,
      props: route => ({
        token: route.query.token,
        correo: route.query.correo
      })
    }
  ],
});

router.beforeEach((to, from, next) => {
  const publicPages = ['/'];
  const authRequired = !publicPages.includes(to.path);
  const loggedIn = localStorage.getItem('id_alumno');

  if (authRequired && !loggedIn) {
    return next('/');
  }

  next();
}
);

export default router;

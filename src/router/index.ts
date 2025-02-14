import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue';
import CrearCuento from '../components/CrearCuento.vue'; 
import UserProfile from '@/views/UserProfile.vue';
import AboutView from '../views/AboutView.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/about',
      name: 'about',
      component: AboutView,
    },
    {
      path: '/crear_cuento',
      name: 'CrearCuento',
      component: CrearCuento,
    },
    {
      path: '/user',
      name: 'UserProfile',
      component: UserProfile,
    },
  ],
});

export default router;

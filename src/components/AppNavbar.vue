<template>
  <v-app-bar app class="app-navbar" :color="getCSSBackgroundColor()" >
    <LogoCollabtalesSm/>

    <v-spacer></v-spacer>

    <v-menu>
      <template v-slot:activator="{ props }">
        <v-btn
          icon
          v-bind="props" >
          <v-img
            src="/icons/account.svg"
            alt="Perfil icono"
            width="40"
            height="40"
            class="icon"
          ></v-img>
        </v-btn>
      </template>

      <v-list>
        <v-list-item @click="router.push('/perfil_usuario')">
          <v-list-item-title class="font-weight-bold">Ver Mi Perfil</v-list-item-title>
        </v-list-item>
        <v-list-item @click="logout" class="text-red-darken-3">
          <v-list-item-title>Cerrar Sesión</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>
  </v-app-bar>
</template>

<script setup lang="ts">
import LogoCollabtalesSm from '@/components/icons/LogoCollabtalesSm.vue';
import { useRouter } from 'vue-router';
const router = useRouter();

function getCSSBackgroundColor() {
  // Usamos esto porque por alguna razón el fondo sigue desapareciendo si lo cambiamos en <style>
  const color = getComputedStyle(document.documentElement).getPropertyValue('--color-background-padding');
  return color;
}

function logout() {
  router.push('/'); // Redirect to login page
  alert('Has cerrado sesión.');
}
</script>



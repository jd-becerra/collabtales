<template>
  <v-menu>
    <template v-slot:activator="{ props }" >
      <v-btn class="mr-6"
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
      <v-list-item @click="logout" class="logout-text">
        <v-list-item-title>Cerrar Sesión</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
const router = useRouter();

function logout() {
  localStorage.removeItem('token'); // Elimina el token de sesión

  router.push('/'); // Redirect to login page
  alert('Has cerrado sesión.');
}
</script>

<style scoped>
.logout-text {
  color: var(--color-error);
}
</style>

<script setup lang="ts">
import { ref } from 'vue'
import { RouterView } from 'vue-router'

const drawer = ref(false) 
</script>

<template>
  <v-app>

    <v-app-bar app color="green-darken-3" density="comfortable" elevation="4">
      <v-container class="d-flex align-center">
        <v-img src="@/assets/logo.svg" alt="Vue logo" max-height="40" max-width="40" />

        <v-spacer></v-spacer>

        <div class="hidden-sm-and-down">
          <v-btn variant="text" to="/" exact color="white">Inicio</v-btn>
          <v-btn variant="text" to="/about" color="white">About</v-btn>
          <v-btn variant="text" to="/crear_cuento" color="white">Crear Cuento</v-btn>
        </div>

        <v-app-bar-nav-icon class="hidden-md-and-up" @click="drawer = !drawer" />
      </v-container>
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" temporary>
      <v-list>
        <v-list-item to="/" exact title="Inicio" />
        <v-list-item to="/about" title=" " />
        <v-list-item to="/crear_cuento" title="Crear Cuento" />
      </v-list>
    </v-navigation-drawer>

    <v-main class="bg-green-lighten-4">
      <v-container>
        <RouterView />
      </v-container>
    </v-main>
  </v-app>
</template>

<style scoped>
.v-btn {
  font-weight: bold;
  text-transform: none;
}

.v-navigation-drawer {
  background-color: white;
}
</style>

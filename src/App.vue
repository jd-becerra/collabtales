<script setup lang="ts">
import './assets/base.css'
import { ref, onMounted, watchEffect } from 'vue'
import { RouterView } from 'vue-router'

const idAlumno = ref<string | null>(null)

// Función para verificar si hay un usuario logeado
const checkAuth = () => {
  idAlumno.value = localStorage.getItem('id_alumno')
}

// Llamamos a la función al cargar la página
onMounted(checkAuth)

// Observamos cambios en localStorage
watchEffect(() => {
  idAlumno.value = localStorage.getItem('id_alumno')
})
</script>

<template>
  <v-app>
    <v-main>
      <RouterView />
    </v-main>

    <v-footer app>
      <span id="footer-info">Collabtales - 2025 - Github: @jd-becerra  |  @aldo  |  @moctezuma</span>
    </v-footer>
  </v-app>
</template>



<style scoped>
.v-app {
  background-color: var(--color-background);
}

.v-btn {
  font-weight: bold;
  text-transform: none;
}

.v-navigation-drawer {
  background-color: white;
}

.v-app-bar {
  background-color: var(--color-background-padding);
  /* Remove shadow */
  box-shadow: none;
  height: auto;
}

.v-footer {
  background-color: var(--color-background-padding);
  height: var(--background-padding-height);
}
</style>

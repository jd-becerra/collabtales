<script setup lang="ts">
import './assets/base.css'
import { ref, onMounted, watchEffect } from 'vue'
import { RouterView } from 'vue-router'
import LangBtn from './components/LangBtn.vue'

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
      <LangBtn class="lang_btn_main ma-4" />
      <RouterView />
    </v-main>

    <v-footer app class="d-flex justify-space-between align-center">
      <span id="footer-info">Collabtales - 2025 - Github:
        <a href="https://github.com/jd-becerra" target="_blank" rel="noopener noreferrer" class="github-link">
          @<u>jd-becerra</u>
        </a>
        <span> &#x2022; </span>
        <a href="https://github.com/Aval0G" target="_blank" rel="noopener noreferrer" class="github-link">
          @<u>Aval0G</u>
        </a>
        <span> &#x2022; </span>
        <a href="https://github.com/Gaia74" target="_blank" rel="noopener noreferrer" class="github-link">
          @<u>Gaia74</u>
        </a>
      </span>
      <span class="gh-project-link">
        For more information, visit our
        <a href="https://github.com/jd-becerra/collabtales"
            target="_blank" rel="noopener noreferrer" class="github-link">
            <u>GitHub project page</u>
        </a>
      </span>
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

.github-link {
  color: var(--color-text-input-fg-default);
  text-decoration: none;

  &:hover {
    background-color: transparent;
  }
}

.lang_btn_main {
  position: fixed;
  top: 10px;
  right: 10px;
  z-index: 1000;
}
</style>

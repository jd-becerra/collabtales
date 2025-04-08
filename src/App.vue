<script setup lang="ts">
import { ref, onMounted, watchEffect } from 'vue'
import { useRouter } from 'vue-router'
import { RouterView } from 'vue-router'

const drawer = ref(false)
const router = useRouter()
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

const cerrarSesion = () => {
  localStorage.removeItem('id_alumno')
  idAlumno.value = null
  router.push('/')
}
</script>

<template>
  <v-app>
    <v-app-bar app color="green-darken-3" density="comfortable" elevation="4">
      <v-container class="d-flex align-center">
        <v-img src="@/assets/logo.svg" alt="Vue logo" max-height="40" max-width="40" />

        <v-spacer></v-spacer>

        <div class="hidden-sm-and-down">
          <v-btn variant="text" to="/" exact color="white">Inicio</v-btn>

          <!-- Si el usuario está logeado -->
          <template v-if="idAlumno">
            <v-btn variant="text" to="/crear_cuento" color="white">Crear Cuento</v-btn>
            <v-btn variant="text" to="/perfil_usuario" color="white">Mi Perfil</v-btn>
            <v-btn variant="text" @click="cerrarSesion">Cerrar Sesión</v-btn>
          </template>

          <!-- Si el usuario NO está logeado -->
          <template v-else>
            <v-btn variant="text" to="/login" color="white">Iniciar Sesión</v-btn>
            <v-btn variant="text" to="/register" color="white">Registrarse</v-btn>
          </template>
        </div>

        <v-app-bar-nav-icon class="hidden-md-and-up" @click="drawer = !drawer" />
      </v-container>
    </v-app-bar>

    <!-- Menú lateral (para móviles) -->
    <v-navigation-drawer v-model="drawer" temporary>
      <v-list>
        <v-list-item to="/" exact title="Inicio" />

        <template v-if="idAlumno">
          <v-list-item to="/crear_cuento" title="Crear Cuento" />
          <v-list-item title="Cerrar Sesión" @click="cerrarSesion">
            <template v-slot:prepend>
              <v-icon color="red">mdi-logout</v-icon>
            </template>
          </v-list-item>
        </template>

        <template v-else>
          <v-list-item to="/login" title="Iniciar Sesión" />
          <v-list-item to="/register" title="Registrarse" />
        </template>
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

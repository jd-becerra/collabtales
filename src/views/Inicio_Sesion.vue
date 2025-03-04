<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

// Reactive state
const showRegister = ref(false);
const registerData = ref({ nombre: '', contrasena: '' });
const loginData = ref({ nombre: '', contrasena: '' });
const loading = ref(false); // Estado de carga

const PHP_URL = import.meta.env.VITE_PHP_SERVER;

// Router
const router = useRouter();

// Método para registrar usuario
async function register() {
  if (!registerData.value.nombre || !registerData.value.contrasena) {
    alert('Error: campos vacíos');
    return;
  }

  loading.value = true; // Inicia la carga

  try {
    const response = await axios.post(`${PHP_URL}/php/crear_alumno.php`, {
      nombre: registerData.value.nombre,
      contrasena: registerData.value.contrasena,
    }, {
      headers: { 'Content-Type': 'application/json' }
    });

    if (response.data.error) {
      alert(response.data.error);
      return;
    }

    if (response.data.result === 'El usuario ya existe') {
      alert('Error: El usuario ya existe');
      return;
    }

    // Si la respuesta contiene un ID, lo guardamos en localStorage
    if (response.data.id_alumno) {
      localStorage.setItem('id_alumno', response.data.id_alumno);
      alert(`Cuenta creada con éxito: ${registerData.value.nombre}. Redirigiendo...`);
      router.push('/panel_inicio');
    } else {
      alert('Error: No se recibió el ID del usuario.');
    }
  } catch (error) {
    console.error('Error en registro:', error);
    alert('Error en el servidor. Intente nuevamente.');
  } finally {
    loading.value = false; // Detiene la carga
  }
}

// Método para iniciar sesión
async function login() {
  if (!loginData.value.nombre || !loginData.value.contrasena) {
    alert('Error: campos vacíos');
    return;
  }

  loading.value = true; // Inicia la carga

  try {
    const response = await axios.post(`${PHP_URL}/php/iniciar_sesion.php`, {
      nombre: loginData.value.nombre,
      contrasena: loginData.value.contrasena,
    }, {
      headers: { 'Content-Type': 'application/json' }
    });

    const datos = response.data;

    if (datos === 'Error: campos vacíos') {
      alert(datos);
      return;
    }

    if (Array.isArray(datos) && datos.length > 0) {
      localStorage.setItem('id_alumno', datos[0].id_alumno);
      alert(`¡Bienvenido: ${loginData.value.nombre}! Redirigiendo...`);
      router.push('/panel_inicio');
    } else {
      alert('ERROR: Usuario o contraseña incorrectos');
    }
  } catch (error) {
    console.error('Error en inicio de sesión:', error);
    alert('Error en el servidor. Intente nuevamente.');
  } finally {
    loading.value = false;
  }
}
</script>


<template>
  <v-container class="fill-height d-flex justify-center align-center">
    <v-card class="pa-5" width="400" elevation="10">
      <v-slide-x-transition mode="out-in">
        <div v-if="showRegister" key="register">
          <v-card-title class="text-center text-h5">Registro de cuenta</v-card-title>
          <v-card-text>
            <v-form @submit.prevent="register">
              <v-text-field
                label="Usuario"
                prepend-inner-icon="mdi-account"
                v-model="registerData.nombre"
                outlined
                required
              />
              <v-text-field
                label="Contraseña"
                prepend-inner-icon="mdi-lock"
                v-model="registerData.contrasena"
                type="password"
                outlined
                required
              />
              <v-btn block color="green-darken-3" class="mt-2" type="submit" :disabled="loading">
                <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
                Registrarse
              </v-btn>
              <v-btn block variant="text" class="mt-2" @click="showRegister = false">
                Ya tengo una cuenta
              </v-btn>
            </v-form>
          </v-card-text>
        </div>

        <div v-else key="login">
          <v-card-title class="text-center text-h5">Iniciar sesión</v-card-title>
          <v-card-text>
            <v-form @submit.prevent="login">
              <v-text-field
                label="Usuario"
                prepend-inner-icon="mdi-account"
                v-model="loginData.nombre"
                outlined
                required
              />
              <v-text-field
                label="Contraseña"
                prepend-inner-icon="mdi-lock"
                v-model="loginData.contrasena"
                type="password"
                outlined
                required
              />
              <v-btn block color="green-darken-3" class="mt-2" type="submit" :disabled="loading">
                <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
                Iniciar sesión
              </v-btn>
              <v-btn block variant="text" class="mt-2" @click="showRegister = true">
                Crear una cuenta
              </v-btn>
            </v-form>
          </v-card-text>
        </div>
      </v-slide-x-transition>
    </v-card>
  </v-container>
</template>

<style scoped>
.fill-height {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>

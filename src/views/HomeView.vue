<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

// Reactive state
const showRegister = ref(false);
const registerData = ref({ nombre: '', contrasena: '' });
const loginData = ref({ nombre: '', contrasena: '' });
const loading = ref(false); // New loading state

const PHP_URL = import.meta.env.VITE_PHP_SERVER;

// Router
const router = useRouter();

// Methods
function register() {
  if (!registerData.value.nombre || !registerData.value.contrasena) {
    alert('Error: campos vacíos');
    return;
  }

  loading.value = true; // Start loading

  axios.post(`${PHP_URL}/php/insert_alumno.php`,
      JSON.stringify({
        nombre: registerData.value.nombre,
        contrasena: registerData.value.contrasena,
      }),
      {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    )
    .then((response) => {
      if (response) {
        console.log(response);

        if (response.data.result == 'El usuario ya existe') {
          alert('Error: El usuario ya existe');
          return;
        }

        alert(
          `Creación de cuenta exitosa para: ${registerData.value.nombre}, haga click para continuar.`
        );
        localStorage.setItem('id_alumno', response.data.trim());
        router.push('/dashboard');
      } else {
        alert(response);
      }
    })
    .catch((error) => {
      console.error('Error en registro:', error);
    })
    .finally(() => {
      loading.value = false; // Stop loading
    });
}

function login() {
  if (!loginData.value.nombre || !loginData.value.contrasena) {
    alert('Error: campos vacíos');
    return;
  }

  loading.value = true; // Start loading

  axios.post(`${PHP_URL}/php/login_alumno.php`,
      JSON.stringify({
        nombre: loginData.value.nombre,
        contrasena: loginData.value.contrasena,
      }),
      {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    )
    .then((response) => {
      const datos = response.data;

      if (datos === 'Error: campos vacios') {
        alert(datos);
        return;
      }

      if (datos.length > 0) {
        alert(
          `¡Bienvenido: ${loginData.value.nombre}! Haga click para continuar`
        );
        localStorage.setItem('id_alumno', datos[0].id_alumno);
        router.push('/dashboard');
      } else {
        alert('ERROR: Usuario o contraseña incorrectos');
      }
    })
    .catch((error) => {
      console.error('Error en inicio de sesión:', error);
    })
    .finally(() => {
      loading.value = false; 
    });
}
</script>

<template>
  <v-container class="fill-height d-flex align-center justify-center">
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
}
</style>

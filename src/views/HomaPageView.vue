<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

// Reactive state
const showRegister = ref(false);
const registerData = ref({ username: '', password: '' });
const loginData = ref({ username: '', password: '' });

// Router
const router = useRouter();

// Methods
function register() {
  axios
    .post('./php/insert_alumno.php', new URLSearchParams(registerData.value))
    .then((response) => {
      console.log(response.data);
      console.log(response);
      alert(response);
      if (response.data.trim() !== 'El usuario ya existe') {
        alert(
          `Creación de cuenta exitosa para: ${registerData.value.username}, haga click para continuar`
        );
        localStorage.setItem('id_alumno', response.data.trim());
        router.push('/dashboard');
      } else {
        alert(response.data);
      }
    })
    .catch((error) => {
      console.error('Error en registro:', error);
    });
}

function login() {
  axios
    .post('./php/login_alumno.php', new URLSearchParams(loginData.value))
    .then((response) => {
      const datos = response.data;
      if (datos.length > 0) {
        alert(
          `¡Bienvenido: ${loginData.value.username}! Haga click para continuar`
        );
        localStorage.setItem('id_alumno', datos[0].id_alumno);
        router.push('/dashboard');
      } else {
        alert('ERROR: Usuario o contraseña incorrectos');
      }
    })
    .catch((error) => {
      console.error('Error en inicio de sesión:', error);
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
                v-model="registerData.username"
                outlined
                required
              />
              <v-text-field
                label="Contraseña"
                prepend-inner-icon="mdi-lock"
                v-model="registerData.password"
                type="password"
                outlined
                required
              />
              <v-btn block color="green-darken-3" class="mt-2" type="submit">
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
                v-model="loginData.username"
                outlined
                required
              />
              <v-text-field
                label="Contraseña"
                prepend-inner-icon="mdi-lock"
                v-model="loginData.password"
                type="password"
                outlined
                required
              />
              <v-btn block color="green-darken-3" class="mt-2" type="submit">
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

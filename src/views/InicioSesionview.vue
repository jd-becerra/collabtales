<template>
  <v-container class="fill-height d-flex justify-center align-center">
    <v-card class="pa-6 rounded-lg elevation-10 login-card">
      <v-slide-y-transition mode="out-in">
        <div v-if="showRegister" key="register">
          <v-card-title class="text-center text-h5 font-weight-bold">✨ Crea tu cuenta ✨</v-card-title>
          <v-card-subtitle class="text-center text-body-2">Únete y empieza a disfrutar de nuestras historias.</v-card-subtitle>
          <v-card-text>
            <v-form @submit.prevent="register">
              <v-text-field
                label="Usuario"
                prepend-inner-icon="mdi-account"
                v-model="registerData.nombre"
                outlined
                required
                class="custom-input"
              />
              <v-text-field
                label="Correo"
                prepend-inner-icon="mdi-email"
                v-model="registerData.correo"
                type="email"
                outlined
                required
                class="custom-input"
              />
              <v-text-field
                label="Contraseña"
                prepend-inner-icon="mdi-lock"
                v-model="registerData.contrasena"
                type="password"
                outlined
                required
                class="custom-input"
              />
              <v-btn block color="green-darken-3" class="mt-3 rounded-lg" type="submit" :disabled="loading">
                <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
                Registrarse
              </v-btn>
              <v-btn block color="primary" class="mt-3 rounded-lg" @click="showRegister = false">
                Ya tengo una cuenta
              </v-btn>
            </v-form>
          </v-card-text>
        </div>

        <div v-else key="login">
          <v-card-title class="text-center text-h5 font-weight-bold">🔐 Iniciar sesión</v-card-title>
          <v-card-subtitle class="text-center text-body-2">Bienvenido de nuevo. Ingresa tus credenciales.</v-card-subtitle>
          <v-card-text>
            <v-form @submit.prevent="login">
              <v-text-field
                label="Usuario"
                prepend-inner-icon="mdi-account"
                v-model="loginData.nombre"
                outlined
                required
                class="custom-input"
              />
              <v-text-field
                label="Contraseña"
                prepend-inner-icon="mdi-lock"
                v-model="loginData.contrasena"
                type="password"
                outlined
                required
                class="custom-input"
              />
              <v-btn block color="green-darken-3" class="mt-3 rounded-lg" type="submit" :disabled="loading">
                <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
                Iniciar sesión
              </v-btn>
              <v-btn block color="blue-darken-3" class="mt-3 rounded-lg" @click="showRegister = true">
                Crear una cuenta
              </v-btn>
            </v-form>
          </v-card-text>
        </div>
      </v-slide-y-transition>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

// Reactive state
const showRegister = ref(false);
const registerData = ref({ nombre: '', correo: '', contrasena: '' });
const loginData = ref({ nombre: '', contrasena: '' });
const loading = ref(false);

const PHP_URL = import.meta.env.VITE_PHP_SERVER;
const router = useRouter();

// Método para registrar usuario
async function register() {
  if (!registerData.value.nombre || !registerData.value.correo || !registerData.value.contrasena) {
    alert('⚠ Error: Campos vacíos');
    return;
  }

  loading.value = true;

  try {
    const response = await axios.post(`${PHP_URL}/php/crear_alumno.php`, registerData.value, {
      headers: { 'Content-Type': 'application/json' }
    });

    if (response.data.error) {
      alert(response.data.error);
      return;
    }

    if (response.data.result === 'El usuario ya existe') {
      alert('⚠ Error: El usuario ya existe');
      return;
    }

    if (response.data.id_alumno) {
      localStorage.setItem('id_alumno', response.data.id_alumno);
      alert(`✅ Cuenta creada con éxito: ${registerData.value.nombre}`);
      router.push('/panel_inicio');
    } else {
      alert('❌ Error: No se recibió el ID del usuario.');
    }
  } catch (_error) {
    alert('❌ Error en el servidor. Intente nuevamente: ' + _error);
  } finally {
    loading.value = false;
  }
}

// Método para iniciar sesión
async function login() {
  if (!loginData.value.nombre || !loginData.value.contrasena) {
    alert('⚠ Error: Campos vacíos');
    return;
  }

  loading.value = true;

  try {
    const response = await axios.post(`${PHP_URL}/php/iniciar_sesion.php`, loginData.value, {
      headers: { 'Content-Type': 'application/json' }
    });

    const datos = response.data;

    if (datos === 'Error: campos vacíos') {
      alert(datos);
      return;
    }

    if (datos.id_alumno) {
      localStorage.setItem('id_alumno', datos.id_alumno);
      alert(`✅ ¡Bienvenido ${loginData.value.nombre}!`);
      router.push('/panel_inicio');
    } else {
      alert('❌ ERROR: Usuario o contraseña incorrectos');
    }
  } catch (_error) {
    alert('❌ Error en el servidor. Intente nuevamente: ' + _error);
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
/* Fondo con degradado */
.fill-height {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Estilo de la tarjeta */
.login-card {
  width: 400px;
  border-radius: 12px;
  background: white;
  box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.2);
}

/* Campos de entrada personalizados */
.custom-input {
  background: #f4f6f8;
  border-radius: 8px;
}

/* Botones estilizados */
.v-btn {
  font-weight: bold;
  transition: 0.3s ease-in-out;
}

.v-btn:hover {
  transform: scale(1.03);
}

.text-blue-darken-2 {
  color: #1976d2 !important;
}
</style>

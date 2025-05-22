<template>
  <v-form @submit.prevent="login">
    <TextInputMd
      label="Nombre de usuario"
      v-model="loginData.nombre"
      type="text"
      outlined
      required
      class="custom-input"
    />
    <TextInputMd
      label="Contraseña"
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
    <v-btn block color="blue-darken-3" class="mt-3 rounded-lg" @click="$emit('show-register')">
      Crear una cuenta
    </v-btn>
    <v-btn block color="text-blue-darken-2" class="mt-3 rounded-lg" @click="$emit('show-restore')">
      ¿Olvidaste tu contraseña?
    </v-btn>
  </v-form>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

// Componentes
import TextInputMd from '@/components/TextInputMd.vue';

const PHP_URL = import.meta.env.VITE_PHP_SERVER;

const loginData = ref({ nombre: '', contrasena: '' });
const router = useRouter();
const loading = ref(false);

const emit = defineEmits(['show-register', 'show-restore', 'popup']);
function emitPopup(title: string, msg: string) {
  emit('popup', {
    title: title,
    msg: msg
  });
}

async function login() {
  if (!loginData.value.nombre || !loginData.value.contrasena) {
    emitPopup("Error", "Asegúrate de llenar todos los campos.");
    return;
  }

  loading.value = true;

  try {
    const response = await axios.post(`${PHP_URL}/php/iniciar_sesion.php`, loginData.value, {
      headers: { 'Content-Type': 'application/json' }
    });

    const datos = response.data;

    if (datos.id_alumno) {
      localStorage.setItem('id_alumno', datos.id_alumno);
      localStorage.setItem('token', datos.token);
      emitPopup("Éxito:", `Bienvenido, ${loginData.value.nombre}`);
      router.push('/panel_inicio');
    }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error.status === 500) {
      emitPopup("Error", `Error en el servidor, intente nuevamente.`);
    } else if (error.status === 400) {
      emitPopup("Error", `Parametros incorrectos. Llena todos los campos correctamente.`);
    } else if (error.status === 401) {
      emitPopup("Error", `Credenciales incorrectas. Verifica tu usuario y contraseña.`);
    } else if (error.status === 429) {
      emitPopup("Error", `Has excedido el límite de intentos permitido. Intenta más tarde.`);
    }
  } finally {
    loading.value = false;
  }
}

</script>

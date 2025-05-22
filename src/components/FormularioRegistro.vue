<template>
  <v-form @submit.prevent="register">
    <TextInputMd
      label="Nombre de usuario  "
      v-model="registerData.nombre"
      type="text"
      outlined
      required
      class="custom-input"
    />
    <TextInputMd
      label="Correo"
      v-model="registerData.correo"
      type="email"
      outlined
      required
      class="custom-input"
    />
    <v-text-field
      label="Contraseña"
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
    <v-btn block color="primary" class="mt-3 rounded-lg" @click="$emit('show-login')">
      Ya tengo una cuenta
    </v-btn>
  </v-form>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios';
import TextInputMd from '@/components/TextInputMd.vue';

const PHP_URL = import.meta.env.VITE_PHP_SERVER;

const registerData = ref({ nombre: '', correo: '', contrasena: '' });
const router = useRouter();
const loading = ref(false);

const emit = defineEmits(['show-login', 'popup']);
function showPopup(title: string, msg: string) {
  emit('popup', {
    title: title,
    msg: msg
  });
}

async function register() {
  if (!registerData.value.nombre || !registerData.value.correo || !registerData.value.contrasena) {
    showPopup("Error", "Campos vacios!");
    return;
  }

  loading.value = true;

  try {
    const response = await axios.post(`${PHP_URL}/php/crear_alumno.php`, registerData.value, {
      headers: { 'Content-Type': 'application/json' }
    });
    if (response.data.error) {
      showPopup("Error", `${response.data.error}`);
      return;
    }

    if (response.data.result === 'El usuario ya existe') {
      showPopup("Error", `El usuario ya existe!`);
      return;
    }
    if (response.data.id_alumno) {
      localStorage.setItem('id_alumno', response.data.id_alumno);
      localStorage.setItem('token', response.data.token);
      showPopup("Éxito", `Cuenta ${registerData.value.nombre} creada correctamente.`);
      router.push('/panel_inicio');
    }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error.status === 500) {
      showPopup("Error", `Error en el servidor, intente nuevamente.`);
    } else if (error.status === 400) {
      showPopup("Error", `Parametros incorrectos. Llena todos los campos correctamente.`);
    } else if (error.status === 401) {
      showPopup("Error", `Usuario o correo ya registrados.`);
    } else if (error.status === 429) {
      showPopup("Error", `Has excedido el límite de intentos permitido. Intenta más tarde.`);
    }
  } finally {
    loading.value = false;
  }
}
</script>

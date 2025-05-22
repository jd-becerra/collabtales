<template>
  <v-form @submit.prevent="register">
    <TextInputMd
      label="Nombre de usuario  "
      v-model="registerData.nombre"
      type="text"
      placeholder="Ejemplo: usuario123"
      outlined
      required
      class="custom-input"
    />
    <TextInputMd
      label="Correo"
      v-model="registerData.correo"
      type="email"
      placeholder="Ejemplo: correo@gmail.com"
      outlined
      required
      class="custom-input"
    />
    <TextInputMd
      label="Contraseña (al menos 8 caracteres y un carácter especial)"
      v-model="registerData.contrasena"
      type="password"
      placeholder="Escribe tu contraseña aquí"
      outlined
      required
      class="custom-input"
    />
    <TextInputMd
      label="Repite tu contraseña"
      v-model="registerData.repetir_contrasena"
      type="password"
      placeholder="Asegúrate de que tus contraseñas coincidan"
      outlined
      required
      class="custom-input"
    />
    <span class="result-msg" :style="{ color: popupValues.color }">
      {{ popupValues.titulo }}<span v-if="popupValues.titulo && popupValues.mensaje">
      : </span>{{ popupValues.mensaje }}
    </span>
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

const registerData = ref({ nombre: '', correo: '', contrasena: '', repetir_contrasena: '' });
const router = useRouter();
const loading = ref(false);

defineEmits(['show-login']);

const popupValues = ref({ titulo: '', mensaje: '', color: '' });
function getCSSVar(variable: string): string {
  // Esta función obtiene el valor de una variable CSS definida en assets/base.css
  return getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
}
function showPopup(title: string, msg: string) {
  // Para mayor compatibilidad, usaremos solamente valores CSS ya definidos
  const color =
    title.toLowerCase().includes('error') ? getCSSVar('--color-error') : getCSSVar('--color-save');

  popupValues.value = {
    titulo: title,
    mensaje: msg,
    color
  };
}

async function register() {
  if (!registerData.value.nombre || !registerData.value.correo || !registerData.value.contrasena || !registerData.value.repetir_contrasena) {
    showPopup("Error", "Asegúrate de llenar todos los campos.");
    return;
  }

  if (registerData.value.contrasena !== registerData.value.repetir_contrasena) {
    showPopup("Error", "Las contraseñas no coinciden.");
    return;
  }

  // Validar que las contraseñas tengan al menos 8 caracteres (letras o numeros) y al menos un caracter especial
  const passwordRegex = /^(?=.*[!@#$%^&*+]).{8,}$/;
  if (!passwordRegex.test(registerData.value.contrasena)) {
    showPopup("Error", "La contraseña debe tener al menos 8 caracteres y un carácter especial.");
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
    } else if (error.status === 409) {
      showPopup("Error", `Usuario o correo ya registrados.`);
    } else if (error.status === 429) {
      showPopup("Error", `Has excedido el límite de intentos permitido. Intenta más tarde.`);
    }
  } finally {
    loading.value = false;
  }
}
</script>

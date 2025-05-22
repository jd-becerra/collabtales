<template>
   <v-form>
    <TextInputMd
      label="Correo"
      v-model="restoreData.correo"
      type="email"
      outlined
      required
      class="custom-input"
    />
    <span class="result-msg" :style="{ color: popupValues.color }">
      {{ popupValues.titulo }}<span v-if="popupValues.titulo && popupValues.mensaje">
      : </span>{{ popupValues.mensaje }}
    </span>
    <v-btn block color="green-darken-3" class="mt-3 rounded-lg" @click="restorePassword">
      <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
      Restaurar contraseña
    </v-btn>
    <v-btn block color="blue-darken-3" class="mt-3 rounded-lg" @click="$emit('show-register')">
      Crear una cuenta
    </v-btn>
  </v-form>
</template>


<script lang="ts" setup>
import { ref } from 'vue';
import axios from 'axios';
// Componentes
import TextInputMd from '@/components/TextInputMd.vue';

const PHP_URL = import.meta.env.VITE_PHP_SERVER;
const restoreData = ref({ correo: '' });
const loading = ref(false);

// Aquí necesitamos definir emit para regresar a la vista de inicio de sesión
const emit = defineEmits(['show-register', 'show-login']);

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
async function restorePassword() {
  if (!restoreData.value.correo) {
    showPopup("Error", "Por favor, ingresa tu correo electrónico.");
    return;
  }

  loading.value = true;

  try {
    const response = await axios.post(`${PHP_URL}/php/generar_token_restauracion.php`, restoreData.value, {
      headers: { 'Content-Type': 'application/json' }
    });

    if (response.data.error) {
      alert(response.data.error);
      return;
    }

    if (response.data.success) {
      showPopup("Éxito", `Correo enviado, revisa tu bandeja de entrada`);
      emit('show-login');
    } else {
      showPopup("Error", `No se pudo restaurar la contraseña`);
    }
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  } catch (error) {
    showPopup("Error", `Hubo un error en el servidor. Intente nuevamente más tarde.`);
  } finally {
    loading.value = false;
  }
}
</script>

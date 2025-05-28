<template>
   <v-form>
      <v-container class="restore-fields d-flex flex-column">
      <TextInputMd
        label="Correo"
        v-model="restoreData.correo"
        type="email"
        placeholder="Ejemplo: micorreo@gmail.com"
        outlined
        required
        class="custom-input"
        @keyup.enter="restorePassword"
      />
      <small class="result-msg" :style="{ color: popupValues.color }" v-if="popupValues.mensaje">
        {{ popupValues.mensaje }}
      </small>
    </v-container>

    <v-container class="restore-buttons-container d-flex flex-column">
      <small class="restore-small">
        ¿Ya tienes una cuenta? <a class="goto-login" href="#" @click="$emit('show-login')">Inicia sesión aquí</a>
      </small>
      <v-container class="restore-buttons d-flex flex-column">
        <BotonMd color_type="blue" @click="restorePassword">
          <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
          Restaurar contraseña
        </BotonMd>
        <BotonMd @click="$emit('show-register')">
          Crea una cuenta
        </BotonMd>
      </v-container>
    </v-container>
  </v-form>
</template>


<script lang="ts" setup>
import { ref } from 'vue';
import axios from 'axios';
// Componentes
import TextInputMd from '@/components/TextInputMd.vue';
import BotonMd from './BotonMd.vue';


const restoreData = ref({ correo: '' });
const loading = ref(false);

// Aquí necesitamos definir emit para regresar a la vista de inicio de sesión
const emit = defineEmits(['show-register', 'show-login']);

const popupValues = ref({ mensaje: '', color: '' });
function getCSSVar(variable: string): string {
  // Esta función obtiene el valor de una variable CSS definida en assets/base.css
  return getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
}
function showPopup(title: string, msg: string) {
  // Para mayor compatibilidad, usaremos solamente valores CSS ya definidos
  const color =
    title.toLowerCase().includes('error') ? getCSSVar('--color-error') : getCSSVar('--color-save');

  popupValues.value = {
    mensaje: title + ': ' + msg,
    color
  };
}

async function restorePassword() {
  if (!restoreData.value.correo) {
    showPopup("Error", "Por favor, ingresa tu correo electrónico.");
    return;
  }

  // Si el correo no es válido, mostramos un mensaje de error
  if (!/\S+@\S+\.\S+/.test(restoreData.value.correo)) {
    showPopup("Error", "Por favor, ingresa un correo electrónico válido.");
    return;
  }

  loading.value = true;

  try {
    const response = await axios.post(`https://collabtalesserver.avaldez0.com/php/generar_token_restauracion.php`, restoreData.value, {
      headers: { 'Content-Type': 'application/json' }
    });

    if (response.data.success) {
      showPopup("Éxito", `Correo enviado, revisa tu bandeja de entrada`);
      emit('show-login');
    } else {
      showPopup("Error", `No se pudo restaurar la contraseña`);
    }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error.status === 404) {
      showPopup("Error", `El correo electrónico no está registrado.`);
    } else if (error.status === 409) {
      showPopup("Error", `Ya existe una solicitud de restauración pendiente para este correo.`);
    } else if (error.status === 429){
      showPopup("Error", `Demasiadas solicitudes. Por favor, espera un momento antes de intentar nuevamente.`);
    } else {
      showPopup("Error", `Hubo un error en el servidor. Intente nuevamente más tarde.`);
    }
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.restore-fields {
  margin-left: 0;
  padding-left: 0;
  gap: 1rem;
}

.restore-buttons-container {
  margin-left: 0;
  padding-left: 0;
  margin-top: 0.1rem;
}
.restore-buttons {
  margin-left: 0;
  padding-left: 0;
  gap: 1rem;
}
.restore-small {
  margin-bottom: -0.5rem;
}

.result-msg {
  font-size: 0.9rem;
  padding: 0;
  margin-top: -0.5rem;
}
.goto-login {
  color: var( --color-text-blue);
  text-decoration: underline;
  cursor: pointer;
  margin-bottom: 2px;
}
</style>

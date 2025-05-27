<template>
  <v-form @submit.prevent="register">
    <v-container class="register-fields d-flex flex-column">
      <TextInputMd
        label="Nombre de usuario  "
        v-model="registerData.nombre"
        type="text"
        placeholder="Ejemplo: usuario123"
        outlined
        required
        class="custom-input"
        @keyup.enter="register"
      />
      <TextInputMd
        label="Correo"
        v-model="registerData.correo"
        type="email"
        placeholder="Ejemplo: correo@gmail.com"
        outlined
        required
        class="custom-input"
        @keyup.enter="register"
      />
      <TextInputMd
        label="Contraseña (al menos 8 caracteres y un carácter especial)"
        v-model="registerData.contrasena"
        type="password"
        placeholder="Escribe tu contraseña aquí"
        outlined
        required
        class="custom-input"
        @keyup.enter="register"
      />
      <TextInputMd
        label="Repite tu contraseña"
        v-model="registerData.repetir_contrasena"
        type="password"
        placeholder="Asegúrate que las contraseñas coincidan"
        outlined
        required
        class="custom-input"
        @keyup.enter="register"
      />
      <small class="result-msg" :style="{ color: popupValues.color }" v-if="popupValues.mensaje">
        {{ popupValues.mensaje }}
      </small>
    </v-container>

    <v-container class="register-buttons d-flex flex-column">
      <small class="mb-1">
        Ya tienes una cuenta <a class="goto-login" href="#" @click="$emit('show-login')">Inicia sesión aquí</a>
      </small>
      <BotonMd :disabled="loading" @click="register">
        <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
        Regístrate
      </BotonMd>
    </v-container>
  </v-form>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import axios from 'axios';
import TextInputMd from '@/components/TextInputMd.vue';
import BotonMd from './BotonMd.vue';



const registerData = ref({ nombre: '', correo: '', contrasena: '', repetir_contrasena: '' });
const loading = ref(false);

const emit = defineEmits(['show-login']);

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
    const response = await axios.post(`https://collabtalesserver.avaldez0.com/php/crear_alumno.php`, {
      nombre: registerData.value.nombre,
      correo: registerData.value.correo,
      contrasena: registerData.value.contrasena
    }, {
      headers: { 'Content-Type': 'application/json' }
    });
    if (response.data.error) {
      showPopup("Error", `${response.data.error}`);
      return;
    }

    if (response.data.id_alumno) {
      showPopup("Éxito", `Usuario '${registerData.value.nombre}' creado correctamente. Inicia sesión para continuar.`);
      setTimeout(() => {
        emit('show-login');
      }, 2000);
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

<style scoped>
.register-fields {
  margin-left: 0;
  padding-left: 0;
  gap: 0.5rem;
}

.register-buttons {
  margin-left: 0;
  padding-left: 0;
  margin-top: 0.2rem;
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

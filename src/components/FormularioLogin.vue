<template>
    <v-form @submit.prevent="login">
      <v-container class="login-fields d-flex flex-column">
        <TextInputMd
          label="Nombre de usuario"
          v-model="loginData.nombre"
          type="text"
          placeholder="Ejemplo: usuario123"
          outlined
          required
          class="custom-input"
          @keyup.enter="login"
        />
        <TextInputMd
          label="Contraseña"
          v-model="loginData.contrasena"
          type="password"
          placeholder="Escribe tu contraseña aquí"
          outlined
          required
          class="custom-input"
          @keyup.enter="login"
        />
        <small class="result-msg" :style="{ color: popupValues.color }" v-if="popupValues.mensaje">
          {{ popupValues.mensaje }}
        </small>
      </v-container>

      <v-container class="login-buttons d-flex flex-column">
        <small class="mb-1">
          ¿Olvidaste tu contraseña? <a class="goto-restore" href="#" @click="$emit('show-restore')">Haz click aquí</a>
        </small>
        <BotonMd color_type="blue" :disabled="loading" @click="login" >
          <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
          Iniciar Sesión
        </BotonMd>
        <BotonMd @click="$emit('show-register')" class="mt-3">
          Crea una cuenta
        </BotonMd>
      </v-container>
    </v-form>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

// Componentes
import TextInputMd from '@/components/TextInputMd.vue';
import BotonMd from './BotonMd.vue';



const loginData = ref({ nombre: '', contrasena: '' });
const router = useRouter();
const loading = ref(false);

defineEmits(['show-register', 'show-restore']);

function getCSSVar(variable: string): string {
  // Esta función obtiene el valor de una variable CSS definida en assets/base.css
  return getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
}
const popupValues = ref({ mensaje: '', color: '' });
function showPopup(title: string, msg: string) {
  // Para mayor compatibilidad, usaremos solamente valores CSS ya definidos
  const color =
    title.toLowerCase().includes('error') ? getCSSVar('--color-error') : getCSSVar('--color-save');

  popupValues.value = {
    mensaje: title + ': ' + msg,
    color
  };
}

async function login() {
  if (!loginData.value.nombre || !loginData.value.contrasena) {
    showPopup("Error", "Asegúrate de llenar todos los campos.");
    return;
  }

  loading.value = true;

  try {
    const response = await axios.post(`https://collabtalesserver.avaldez0.com/php/iniciar_sesion.php`, loginData.value, {
      headers: { 'Content-Type': 'application/json' }
    });

    const datos = response.data;

    if (datos.id_alumno) {
      localStorage.setItem('id_alumno', datos.id_alumno);
      localStorage.setItem('token', datos.token);
      showPopup("Éxito", `Bienvenido, ${loginData.value.nombre}`);
      setTimeout(() => {
        router.push('/mis_cuentos');
      }, 1000);
    }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error.status === 500) {
      showPopup("Error", `Error en el servidor, intente nuevamente.`);
    } else if (error.status === 400) {
      showPopup("Error", `Parametros incorrectos. Llena todos los campos correctamente.`);
    } else if (error.status === 401) {
      showPopup("Error", `Credenciales incorrectas. Verifica tu usuario y contraseña.`);
    } else if (error.status === 429) {
      showPopup("Error", `Has excedido el límite de intentos permitido. Intenta más tarde.`);
    }
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.login-fields {
  margin-left: 0;
  padding-left: 0;
  gap: 0.5rem;
}

.login-buttons {
  margin-left: 0;
  padding-left: 0;
  margin-top: 1.5rem;
}
.result-msg {
  font-size: 0.9rem;
  padding: 0;
  margin-top: -0.5rem;
}
.goto-restore {
  color: var( --color-text-blue);
  text-decoration: underline;
  cursor: pointer;
  margin-bottom: 2px;
}
</style>

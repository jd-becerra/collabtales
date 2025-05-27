<template>
    <v-form @submit.prevent="editarAlumno">
      <v-container class="login-fields d-flex flex-column">
        <TextInputSm
          label="Nombre de usuario"
          v-model="localDatosAlumno.nombre"
          type="text"
          outlined
          required
          class="custom-input"
        />
        <TextInputSm
          label="Correo electronico"
          v-model="localDatosAlumno.correo"
          type="text"
          outlined
          required
          class="custom-input"
        />
      </v-container>

      <small class="result-msg" :style="{ color: popupValues.color }" v-if="popupValues.mensaje">
        {{ popupValues.mensaje }}
      </small>

      <v-container class="login-buttons d-flex justify-space-between align-center">
        <BotonWideXs type="submit" class="mt-3" color_type="white_green">
          Guardar cambios
        </BotonWideXs>

        <BotonWideXs @click="$emit('show-perfil')" class="mt-3" color_type="white_red">
          Cancelar
        </BotonWideXs>
      </v-container>
    </v-form>
</template>

<script lang="ts" setup>
import { ref, defineProps, watch } from 'vue';
// Componentes
import TextInputSm from './TextInputSm.vue';
import BotonWideXs from './BotonWideXs.vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const props = defineProps<{
  datosAlumno: {
    id_alumno: string;
    nombre: string;
    correo: string;
  };
}>();

const router = useRouter();
const localDatosAlumno = ref({ ...props.datosAlumno });
const PHP_URL = import.meta.env.VITE_PHP_SERVER;
const popupValues = ref({ mensaje: '', color: '' });

watch(
  () => props.datosAlumno,
  (newVal) => {
    localDatosAlumno.value = { ...newVal };
  },
  { deep: true, immediate: true } // 'deep' for nested objects, 'immediate' to run on initial mount
);

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

function editarAlumno() {
  axios
    .put(`${PHP_URL}/php/editar_alumno.php`, {
      nombre: localDatosAlumno.value.nombre,
      correo: localDatosAlumno.value.correo,
    },
    {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    .then((response) => {
      if (response.data.success) {  // Si hay un mensaje de éxito
        alert('Datos actualizados correctamente.');
        router.push('/panel_inicio');
      } else if (response.data.error) {  // Si hay un mensaje de error
        alert(response.data.error);
      } else {
        alert('Error desconocido al actualizar datos.');
      }
    })
    .catch((error) => {
      console.error('Error al editar:', error);
      if (error.status === 500) {
        showPopup("Error", `Error en el servidor, intente nuevamente.`);
      } else if (error.status === 400) {
        showPopup("Error", `Parametros incorrectos. Llena todos los campos correctamente.`);
      } else if (error.status === 409) {
        showPopup("Error", `Usuario o correo ya registrados.`);
      } else if (error.status === 429) {
        showPopup("Error", `Has excedido el límite de intentos permitido. Intenta más tarde.`);
      }
    });
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

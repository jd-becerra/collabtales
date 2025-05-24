<template>
  <v-card class="dialog-card">
    <v-container>
      <v-btn flat class="close-btn" @click="$emit('close-popup')">
        <v-img
          src="/icons/close.svg"
          width="20"
          height="20"
          class="close-icon"
        />
      </v-btn>

      <h2 class="unirse-header text-h6 mt-3">UNIRSE A UN CUENTO</h2>
      <p class="text-caption">Escribe la ID del cuento al que deseas unirte (consulta con el creador del cuento si no la sabes).</p>

      <div class="mt-4">
        <TextInputSm
          v-model="idCuentoUnirse"
          label="ID del cuento"
          type="text"
          placeholder="Ejemplo: 44209"
          required
          @keyup.enter="unirseCuento()"
        />

        <v-container class="d-flex justify-space-between align-start pa-0 mt-5">
          <small
            class="result-msg"
            :style="{ color: popupValues.color }"
          >
            {{ popupValues.mensaje }}
          </small>
          <BotonXs @click="unirseCuento()">Unirse</BotonXs>
        </v-container>
      </div>
    </v-container>
  </v-card>
</template>

<script setup lang="ts">
import '../assets/base.css';

import { ref } from 'vue';
import axios from 'axios';
import TextInputSm from '@/components/TextInputSm.vue';
import  BotonXs from '@/components/BotonXs.vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const idCuentoUnirse = ref('');

defineEmits(['close-popup']);

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

const unirseCuento = async () => {
  if (!idCuentoUnirse.value) {
    showPopup('Error', 'Por favor, ingresa un ID de cuento válido.');
    return;
  }

  try {
    const id_alumno = localStorage.getItem('id_alumno');
    if (!id_alumno) {
      showPopup('Error', 'Tiempo inactivo excedido. Recarge la página y vuelva a iniciar sesión.');
      return;
    }
    const response = await axios.post(`${import.meta.env.VITE_PHP_SERVER}/php/unirse_cuento.php`,
    {
      codigo: idCuentoUnirse.value,
      id_alumno
    });

    if (response.status === 200 && !response.data.error) {
      showPopup('Éxito', 'Te has unido al cuento correctamente. Redirigiendo...');
      localStorage.setItem('id_cuento', idCuentoUnirse.value);
      router.push('/ver_cuento' );
    } else {
      showPopup('Error', 'No se pudo unir al cuento. Por favor, intenta más tarde.');
    }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error.status === 400) {
      showPopup('Error',  'Asegúrate de que la ID proporcionada sea válida.');
    } else if (error.status === 403) {
      showPopup('Error',  'No tienes permiso para unirte a este cuento.');
    } else if (error.status === 404) {
      showPopup('Error', 'No se encontró el cuento con la ID proporcionada.');
    } else if (error.status === 409) {
      showPopup('Error', 'Ya estás unido a este cuento.');
    } else if (error.status === 400) {
      showPopup('Error', 'Error en la solicitud. Por favor, verifica los datos.');
    } else if (error.status === 500) {
      showPopup('Error', 'Error interno del servidor. Por favor, intenta más tarde.');
    } else {
      showPopup('Error', 'Ocurrió un error inesperado. Por favor, intenta más tarde.');
    }
  }
};
</script>

<style scoped>
.dialog-card {
  border-radius: var(--border-radius-default);
}

.close-btn {
  position: absolute;

  top: 0;
  right: 0;
  padding: 0.3rem;
  margin: 0.5rem;

  min-width: unset; /* override Vuetify's default min-width */
  width: auto;
  height: auto;
}

.unirse-header {
  font-weight: bold;
}

.text-caption {
  font-size: var(--font-size-md);
  color: var(--color-text-black);
}

.result-msg {
  font-size: 0.8rem;
  padding: 0;
  margin-top: -0.5rem;
  margin-right: 0.5rem;
  left: 0;
  display: flex;
  justify-content: flex-start;
}
</style>

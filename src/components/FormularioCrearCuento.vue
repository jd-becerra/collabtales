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

      <h2 class="unirse-header text-h6 mt-3">CREAR UN CUENTO</h2>
      <p class="text-caption">Escribe el nombre de tu cuento y cuéntanos brevemente de qué tratará en la descripción.</p>

      <div class="mt-4">
        <TextInputSm
          v-model="nuevo_cuento.nombre"
          label="Nombre del cuento"
          type="text"
          placeholder="Ejemplo: El Lago de los Sueños"
          required
          @keyup.enter="crearCuento()"
        />
        <TextInputSmWide class="mt-3"
          v-model="nuevo_cuento.descripcion"
          label="Descripción"
          type="text"
          placeholder="Escribe una breve descripción del cuento"
          required
          @keyup.enter="crearCuento()"
        />

        <v-container class="d-flex justify-space-between align-start pa-0 mt-5">
          <small
            class="result-msg"
            :style="{ color: popupValues.color }"
          >
            {{ popupValues.mensaje }}
          </small>
          <BotonXs @click="crearCuento()">Crear Cuento</BotonXs>
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
import TextInputSmWide from './TextAreaSmWide.vue';
import  BotonXs from '@/components/BotonXs.vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const nuevo_cuento = ref({nombre: '', descripcion: ''});

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

const crearCuento = async () => {
  if (!nuevo_cuento.value.nombre.trim() || !nuevo_cuento.value.descripcion.trim()) {
    showPopup('Error', 'Por favor, completa todos los campos.');
    return;
  }

  try {
    const response = await axios.post(
      `${import.meta.env.VUE_APP_SERVER}/php/crear_cuento.php`,
      JSON.stringify({
        nombre: nuevo_cuento.value.nombre,
        descripcion: nuevo_cuento.value.descripcion,
      }),
      {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        }
      }
    );

    if (response.status === 201) {
      showPopup('Éxito', 'Cuento creado exitosamente. Redirigiendo...');
    }
    setTimeout(() => {
      localStorage.setItem('id_cuento', response.data.id_cuento_creado);
      router.push('/ver_cuento_creado/' + response.data.id_cuento_creado);
    }, 2000); // Redirige después de 2 segundos
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error?.response?.status === 400) {
      showPopup('Error', 'Comprueba que los campos sean válidos.');
    } else if (error?.response?.status === 500) {
      showPopup('Error', 'Error en el servidor. Intenta más tarde.');
    } else {
      showPopup('Error', 'Error inesperado al crear cuento.');
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

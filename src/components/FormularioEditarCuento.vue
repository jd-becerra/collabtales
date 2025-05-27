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

      <h2 class="unirse-header text-h6 mt-3">EDITAR CUENTO</h2>
      <p class="text-caption">Escribe el nombre y la descripción de tu cuento. Asegúrate de no dejar campos vacíos</p>

      <div class="mt-4">
        <TextInputSm
          v-model="editar_cuento.nombre"
          label="Nombre del cuento"
          type="text"
          required
          @keyup.enter="editarCuento()"
        />
        <TextInputSmWide class="mt-3"
          v-model="editar_cuento.descripcion"
          label="Descripción"
          type="text"
          required
          @keyup.enter="editarCuento()"
        />

        <v-container class="d-flex justify-space-between align-start pa-0 mt-5">
          <small
            class="result-msg"
            :style="{ color: popupValues.color }"
          >
            {{ popupValues.mensaje }}
          </small>
          <BotonXs
            color_type="white_green"
            @click="editarCuento()"
            :disabled="disableGuardar"
            >Guardar cambios</BotonXs>
        </v-container>
      </div>
    </v-container>
  </v-card>
</template>

<script setup lang="ts">
import '../assets/base.css';

import { onMounted, ref } from 'vue';
import axios from 'axios';
import TextInputSm from '@/components/TextInputSm.vue';
import TextInputSmWide from './TextAreaSmWide.vue';
import  BotonXs from '@/components/BotonXs.vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const emit = defineEmits(['close-popup']);

const props = defineProps<{
  id_cuento: number | null;
  nombre: string;
  descripcion: string;
}>();

const editar_cuento = ref({nombre: props.nombre, descripcion: props.descripcion});

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

const id_cuento = ref<number | null>(props.id_cuento);
const disableGuardar = ref(false);

const editarCuento = async () => {
  if (!editar_cuento.value.nombre.trim() || !editar_cuento.value.descripcion.trim()) {
    showPopup('Error', 'Por favor, completa todos los campos.');
    return;
  }
  disableGuardar.value = true;

  try {
    const response = await axios.put(
      `${import.meta.env.VUE_APP_SERVER}/php/editar_cuento.php`,
      JSON.stringify({
        id_cuento: id_cuento,
        nombre_cuento: editar_cuento.value.nombre,
        descripcion_cuento: editar_cuento.value.descripcion,
      }),
      {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        }
      }
    );

    if (response.status === 200) {
      showPopup('Éxito', 'Cuento editado correctamente. Redigiriendo a tu cuento...');
      setTimeout(() => {
        emit('close-popup');
        router.push('/ver_cuento_creado/' + id_cuento.value);
      }, 1000);
    }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error?.response?.status === 400) {
      showPopup('Error', 'Comprueba que los campos sean válidos.');
    } else if (error?.response?.status === 403) {
      showPopup('Error', 'No tienes permiso para editar este cuento.');
    } else if (error?.response?.status === 404) {
      showPopup('Error', 'Cuento no encontrado.');
    } else if (error?.response?.status === 500) {
      showPopup('Error', 'Error en el servidor. Intenta más tarde.');
    } else {
      showPopup('Error', 'Error inesperado al crear cuento.');
    }
  }
};

onMounted(() => {
  disableGuardar.value = false;
});

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

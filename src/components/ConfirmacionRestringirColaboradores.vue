<template>
  <v-card class="dialog-card">
      <v-btn flat class="close-btn" @click="$emit('close-popup')">
        <v-img
          src="/icons/close.svg"
          width="20"
          height="20"
          class="close-icon"
        />
      </v-btn>

      <h2 class="ocultar-cuento-header text-h6 my-3">RESTRINGIR COLABORADORES</h2>
      <p class="ocultar-cuento-subheader text-caption mb-6">Esta acción impedirá que nuevos usuarios se unan al cuento con el código asignado. La única manera de volver a añadir usuarios es volviendo a habilitar esta opción</p>

      <small
        class="result-msg"
        :style="{
          color: popupValues.color,
        }"
      >
        {{ popupValues.mensaje }}
      </small>
      <v-container class="d-flex justify-space-between align-start pa-0 mt-5">
          <div></div>
          <BotonXs
            class="ocultar-btn"
            color_type="white_green"
            @click="restringirColaboradores()"
            :disabled="disableEliminar"
          >
            Continuar
          </BotonXs>
        </v-container>

  </v-card>
</template>

<script setup lang="ts">
import '../assets/base.css';

import { onMounted, ref } from 'vue';
import axios from 'axios';
import  BotonXs from '@/components/BotonXs.vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const emit = defineEmits(['close-popup']);

const props = defineProps<{
  id_cuento: number | null;
}>();

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
const disableEliminar = ref(false);

const restringirColaboradores = async () => {
  if (!id_cuento.value) {
    // Regresamos a la página de cuentos si el ID no es válido (ya que hubo un error)
    showPopup('Error', 'ID del cuento no válido.');
    router.push('/mis_cuentos');
    return;
  }

  disableEliminar.value = true;

  try {
    const response = await axios.put(
      `${import.meta.env.VUE_APP_SERVER}/php/restringir_colaboradores.php`,
      {
        id_cuento: id_cuento.value
      },
      {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        }
      }
    );

    if (response.status === 200) {
      showPopup('Éxito', 'Los usuarios ya no podrán unirse al cuento.');

      setTimeout(() => {
        emit('close-popup');
        router.go(0);
      }, 1000);
    }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error?.response?.status === 400) {
      showPopup('Error', 'Acción inválida. Intente recargar la página.');
    } else if (error?.response?.status === 403) {
      showPopup('Error', 'No tienes permiso para realizar esta acción.');
    } else if (error?.response?.status === 404) {
      showPopup('Error', 'Cuento no encontrado.');
    } else if (error?.response?.status === 500) {
      showPopup('Error', 'Error en el servidor. Intenta más tarde.');
    } else {
      showPopup('Error', 'Error inesperado al realizar la acción.');
    }
    disableEliminar.value = false;
  }
};

onMounted(() => {
  disableEliminar.value = false;
});

</script>

<style scoped>
.dialog-card {
  border-radius: var(--border-radius-default);
  padding: 2rem;
}

.close-btn {
  position: absolute;

  top: 0;
  right: 0;
  padding: 0.3rem;
  margin: 0.5rem;
  min-width: unset;
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

.ocultar-cuento-header {
  font-weight: bold;
}

.ocultar-cuento-header {
  color: var(--vt-c-black);
}

.result-msg {
  font-size: 0.8rem;
  padding: 0;
  margin-top: -0.5rem;
  left: 0;
  display: flex;
  justify-content: flex-start;
}

.options-container {
  display: flex;
  justify-content: space-between;
  align-items: end;
  width: 100%;
  padding: 0;
}

.ocultar-btn {
  width: 9em;
}
</style>

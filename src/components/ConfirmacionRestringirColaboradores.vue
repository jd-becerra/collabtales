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

      <h2 class="ocultar-cuento-header text-h6 my-3">{{ $t('manage_collaborators.confirm_restrict_title') }}</h2>
      <p class="ocultar-cuento-subheader text-caption mb-6">
        {{ $t('manage_collaborators.confirm_restrict_message') }}
      </p>

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
            {{ $t('manage_collaborators.confirm_button') }}
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
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
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
    showPopup(t('message_headers.error'), t('manage_collaborators.invalid_id'));
    router.push('/mis_cuentos');
    return;
  }

  disableEliminar.value = true;

  try {
    const response = await axios.put(
      `https://collabtalesserver.avaldez0.com/php/restringir_colaboradores.php`,
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
      showPopup(t('message_headers.success'), t('manage_collaborators.restrict_success'));

      setTimeout(() => {
        emit('close-popup');
        router.go(0);
      }, 1000);
    }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error?.response?.status === 400) {
      showPopup(t('message_headers.error'), t('error_codes.400'));
    } else if (error?.response?.status === 403) {
      showPopup(t('message_headers.error'), t('error_codes.403'));
    } else if (error?.response?.status === 404) {
      showPopup(t('message_headers.error'), t('error_codes.404'));
    } else if (error?.response?.status === 500) {
      showPopup(t('message_headers.error'), t('error_codes.500'));
    } else {
      showPopup(t('message_headers.error'), t('error_codes.unknown'));
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

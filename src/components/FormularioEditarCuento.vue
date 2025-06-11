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

      <h2 class="unirse-header text-h6 mt-3">{{ $t('modify_tale.title') }}</h2>
      <p class="text-caption">
        {{ $t('modify_tale.subtitle') }}
      </p>

      <v-form class="mt-4" @submit.prevent="editarCuento">
        <TextInputSm
          v-model="editar_cuento.nombre"
          :label="$t('modify_tale.tale_title')"
          :placeholder="$t('modify_tale.tale_title_placeholder')"
          type="text"
          required
        />
        <TextInputSmWide class="mt-3"
          v-model="editar_cuento.descripcion"
          :label="$t('modify_tale.tale_description')"
          :placeholder="$t('modify_tale.tale_description_placeholder')"
          type="text"
          required
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
            :disabled="disableGuardar"
            type="submit"
            >
            {{ $t('modify_tale.save_changes_button') }}
          </BotonXs>
        </v-container>
      </v-form>
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
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
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
    showPopup(t('message_headers.error'), t('modify_tale.fill_fields'));
    return;
  }
  disableGuardar.value = true;

  try {
    const response = await axios.put(
      `https://collabtalesserver.avaldez0.com/php/editar_cuento.php`,
      {
        id_cuento: id_cuento.value,
        nombre_cuento: editar_cuento.value.nombre,
        descripcion_cuento: editar_cuento.value.descripcion,
      },
      {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        }
      }
    );

    if (response.status === 200) {
      showPopup(t('message_headers.success'), t('modify_tale.tale_modified', { title: editar_cuento.value.nombre }));
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
      showPopup(t('message_headers.error'), t('modify_tale.unauthorized'));
    } else if (error?.response?.status === 404) {
      showPopup(t('message_headers.error'), t('modify_tale.not_found'));
    } else if (error?.response?.status === 500) {
      showPopup(t('message_headers.error'), t('error_codes.500'));
    } else {
      showPopup(t('message_headers.error'), t('modify_tale.unexpected_error'));
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

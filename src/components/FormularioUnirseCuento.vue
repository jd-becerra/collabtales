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

      <h2 class="unirse-header text-h6 mt-3">{{ t('join_tale.title') }}</h2>
      <p class="text-caption">{{ t('join_tale.subtitle') }}</p>

      <div class="mt-4">
        <TextInputSm
          v-model="idCuentoUnirse"
          :label="t('join_tale.tale_code')"
          type="text"
          :placeholder="t('join_tale.tale_code_placeholder')"
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
          <BotonXs @click="unirseCuento()">{{ t('join_tale.join_tale_button') }}</BotonXs>
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
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

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
    showPopup(t('message_headers.error'), t('join_tale.fill_fields'));
    return;
  }

  try {
    const response = await axios.post(`https://collabtalesserver.avaldez0.com/php/unirse_cuento.php`,
    {
      codigo: idCuentoUnirse.value,
    });

    if (response.status === 200 && !response.data.error) {
      showPopup(t('message_headers.success'), t('join_tale.tale_joined'));
      router.push('/ver_cuento_colaborador/' + response.data.id_cuento);
    } else {
      showPopup(t('message_headers.error'), response.data.error || t('join_tale.tale_error'));
    }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error.status === 400) {
      showPopup(t('message_headers.error'), t('join_tale.fill_fields'));
    } else if (error.status === 403) {
      showPopup(t('message_headers.error'), t('join_tale.cannot_join'));
    } else if (error.status === 404) {
      showPopup(t('message_headers.error'), t('join_tale.tale_not_found'));
    } else if (error.status === 409) {
      showPopup(t('message_headers.error'), t('join_tale.already_joined'));
    } else if (error.status === 500) {
      showPopup(t('message_headers.error'), t('error_codes.500'));
    } else {
      showPopup(t('message_headers.error'), t('error_codes.tale_error'));
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

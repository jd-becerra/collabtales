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

      <h2 class="unirse-header text-h6 mt-3">{{ $t('create_tale.title') }}</h2>
      <p class="text-caption">
        {{ $t('create_tale.subtitle') }}
      </p>

      <form class="mt-4" @submit.prevent="crearCuento">
        <TextInputSm
          v-model="nuevo_cuento.nombre"
          :label="$t('create_tale.tale_title')"
          type="text"
          :placeholder="$t('create_tale.tale_title_placeholder')"
          required
        />
        <TextInputSmWide class="mt-3"
          v-model="nuevo_cuento.descripcion"
          :label="$t('create_tale.tale_description')"
          type="text"
          :placeholder="$t('create_tale.tale_description_placeholder')"
          required
        />

        <v-container class="d-flex justify-space-between align-start pa-0 mt-5">
          <small
            class="result-msg"
            :style="{ color: popupValues.color }"
          >
            {{ popupValues.mensaje }}
          </small>
          <BotonXs @click="crearCuento()" :disabled="disableGuardar">
            {{ $t('create_tale.create_tale_button') }}
          </BotonXs>
        </v-container>
      </form>
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
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const router = useRouter();

const nuevo_cuento = ref({nombre: '', descripcion: ''});
const disableGuardar = ref(false);

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
    showPopup(t('message_headers.error'), t('create_tale.fill_fields'));
    return;
  }

  try {
    disableGuardar.value = true;

    const response = await axios.post(
      `https://collabtalesserver.avaldez0.com/php/crear_cuento.php`,
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
      showPopup(t('message_headers.success'), t('create_tale.tale_created', { title: nuevo_cuento.value.nombre }));
    }
    setTimeout(() => {
      localStorage.setItem('id_cuento', response.data.id_cuento_creado);
      router.push('/ver_cuento_creado/' + response.data.id_cuento_creado);
    }, 2000); // Redirige después de 2 segundos
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    disableGuardar.value = false;
    if (error?.response?.status === 400) {
      showPopup(t('message_headers.error'), t('error_codes.400'));
    } else if (error?.response?.status === 500) {
      showPopup(t('message_headers.error'), t('error_codes.500'));
    } else {
      showPopup(t('message_headers.error'), t('create_tale.unexpected_error'));
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

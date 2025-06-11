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

      <h2 class="eliminar-cuento-header text-h6 my-3">{{ $t('manage_collaborators.confirm_unblock_title') }}</h2>
      <p class="eliminar-cuento-subheader text-caption mb-6">
        {{ $t('manage_collaborators.confirm_unblock_message', { username: props.nombre_usuario_desbloquear }) }}
      </p>

      <small
        v-if="popupValues.mensaje"
        class="result-msg"
        :style="{
          color: popupValues.color,
        }"
      >
        {{ popupValues.mensaje }}
      </small>
      <div class="options-container">
          <BotonXs
            class="return-btn"
            @click="emit('close-popup')"
          >
            {{ $t('manage_collaborators.cancel_button') }}
          </BotonXs>
          <BotonXs
            class="eliminar-btn"
            color_type="white_green"
            @click="bloquarUsuario()"
            :disabled="disableDesbloquear"
          >
            {{ $t('manage_collaborators.confirm_button') }}
          </BotonXs>
        </div>

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
  id_usuario_desbloquear: number;
  nombre_usuario_desbloquear: string;
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
const id_usuario_desbloquear = ref<number | undefined>(props.id_usuario_desbloquear);
const disableDesbloquear = ref(false);

const bloquarUsuario = async () => {
  if (!id_cuento.value) {
    // Regresamos a la página de cuentos si el ID no es válido (ya que hubo un error)
    showPopup(t('message_headers.error'), t('manage_collaborators.invalid_id'));
    router.push('/mis_cuentos');
    return;
  }

  disableDesbloquear.value = true;

  try {
    const response = await axios.delete(
      `https://collabtalesserver.avaldez0.com/php/eliminar_bloqueado.php`,
      {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
        data: {
          id_cuento: id_cuento.value,
          id_alumno_desbloquear: id_usuario_desbloquear.value
        }
      }
    );

    if (response.status === 200) {
      showPopup(t('message_headers.success'), t('manage_collaborators.unblock_success'));

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
    disableDesbloquear.value = false;
  }
};

onMounted(() => {
  disableDesbloquear.value = false;
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

.eliminar-cuento-header {
  font-weight: bold;
}

.eliminar-cuento-header {
  color: var(--color-text-black);
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
  width: 100%;
  padding: 0;
}

.eliminar-btn {
  width: 9em;
}
</style>

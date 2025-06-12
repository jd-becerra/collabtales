<template>
    <v-form @submit.prevent="editarAlumno">
      <v-container class="login-fields d-flex flex-column">
        <TextInputSm
          :label="$t('profile.username')"
          :placeholder="$t('profile.username_placeholder')"
          v-model="localDatosAlumno.nombre"
          type="text"
          outlined
          required
          class="custom-input"
        />
        <TextInputSm
          :label="$t('profile.email')"
          :placeholder="$t('profile.email_placeholder', { email: 'john_doe@gmail.com'})"
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
        <BotonWideXs
          @click="$emit('show-perfil')"
          color_type="white_red"
          class="actions"
          :disabled="disabled"
          >
          {{ $t('profile.cancel') }}
        </BotonWideXs>
        <BotonWideXs
          type="submit"
          color_type="white_green"
          class="actions"
          :disabled="disabled"
          >
          {{ $t('profile.save_changes') }}
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
import { useI18n } from 'vue-i18n';

const props = defineProps<{
  datosAlumno: {
    id_alumno: string;
    nombre: string;
    correo: string;
  };
}>();

const { t } = useI18n();
const router = useRouter();
const localDatosAlumno = ref({ ...props.datosAlumno });
const disabled = ref(false); // Para deshabilitar el botón si es necesario

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
    .put(`https://collabtalesserver.avaldez0.com/php/editar_alumno.php`, {
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
        showPopup(t('message_headers.success'), t('profile.update_success'));
        disabled.value = true;
        setTimeout(() => {
          router.go(0);
        }, 1000);
      }
    })
    .catch((error) => {
      if (error.status === 500) {
        showPopup(t('message_headers.error'), t('error_codes.500'));
      } else if (error.status === 400) {
        showPopup(t('message_headers.error'), t('error_codes.400'));
      } else if (error.status === 409) {
        showPopup(t('message_headers.error'), t('profile.already_exists'));
      } else if (error.status === 429) {
        showPopup(t('message_headers.error'), t('error_codes.429'));
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
.actions {
  width: 12rem;
}
</style>

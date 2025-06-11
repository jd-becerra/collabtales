<template>
    <v-form @submit.prevent="login">
      <v-container class="login-fields d-flex flex-column">
        <TextInputMd
          :label="$t('login.username')"
          v-model="loginData.nombre"
          type="text"
          :placeholder="$t('login.username_placeholder')"
          outlined
          required
          class="custom-input"
          @keyup.enter="login"
        />
        <TextInputMd
          :label="$t('login.password')"
          v-model="loginData.contrasena"
          type="password"
          :placeholder="$t('login.password_placeholder')"
          outlined
          required
          class="custom-input"
          @keyup.enter="login"
        />
        <small class="result-msg" :style="{ color: popupValues.color }" v-if="popupValues.mensaje">
          {{ popupValues.mensaje }}
        </small>
      </v-container>

      <v-container class="login-buttons d-flex flex-column">
        <small class="mb-1">
          {{ $t('login.forgot_password')}} <a class="goto-restore" href="#" @click="$emit('show-restore')">{{ $t('login.forgot_password_link') }}</a>
        </small>
        <BotonMd color_type="blue" :disabled="loading" @click="login" >
          <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
          {{ $t('login.login_button') }}
        </BotonMd>
        <BotonMd @click="$emit('show-register')" class="mt-3">
          {{ $t('login.register_button') }}
        </BotonMd>
      </v-container>
    </v-form>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

// Componentes
import TextInputMd from '@/components/TextInputMd.vue';
import BotonMd from './BotonMd.vue';

const { t } = useI18n();

const loginData = ref({ nombre: '', contrasena: '' });
const router = useRouter();
const loading = ref(false);

defineEmits(['show-register', 'show-restore']);

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

async function login() {
  if (!loginData.value.nombre || !loginData.value.contrasena) {
    showPopup(t('message_headers.error'), t('login.fill_fields'));
    return;
  }

  loading.value = true;

  try {
    const response = await axios.post(`https://collabtalesserver.avaldez0.com/php/iniciar_sesion.php`, loginData.value, {
      headers: { 'Content-Type': 'application/json' }
    });

    const datos = response.data;

    if (datos.id_alumno) {
      localStorage.setItem('id_alumno', datos.id_alumno);
      localStorage.setItem('token', datos.token);
      showPopup(t('message_headers.success'), t('login.login_success', { username: loginData.value.nombre }));
      setTimeout(() => {
        router.push('/mis_cuentos');
      }, 1000);
    }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error.status === 500) {
      showPopup(t('message_headers.error'), t('error_codes.500'));
    } else if (error.status === 400) {
      showPopup(t('message_headers.error'), t('error_codes.400'));
    } else if (error.status === 401) {
      showPopup(t('message_headers.error'), t('login.invalid_credentials'));
    } else if (error.status === 429) {
      showPopup(t('message_headers.error'), t('error_codes.429'));
    }
  } finally {
    loading.value = false;
  }
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
</style>

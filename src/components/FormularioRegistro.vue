<template>
  <v-form @submit.prevent="register">
    <v-container class="register-fields d-flex flex-column">
      <TextInputMd
        :label="$t('register.username')"
        v-model="registerData.nombre"
        type="text"
        :placeholder="$t('register.username_placeholder')"
        outlined
        required
        class="custom-input"
        @keyup.enter="register"
      />
      <TextInputMd
        :label="$t('register.email')"
        v-model="registerData.correo"
        type="email"
        :placeholder="$t('register.email_placeholder', { email: 'john_doe@gmail.com' })"
        outlined
        required
        class="custom-input"
        @keyup.enter="register"
      />
      <TextInputMd
        :label="$t('register.password')"
        v-model="registerData.contrasena"
        type="password"
        :placeholder="$t('register.password_placeholder')"
        outlined
        required
        class="custom-input"
        @keyup.enter="register"
      />
      <ul class="requirements_container">
        <li :class="{ 'valid': has8Characters, 'invalid': !has8Characters }">
          {{ $t('register.8_characters') }}
        </li>
        <li :class="{ 'valid': hasSpecialCharacter, 'invalid': !hasSpecialCharacter }">
          {{ $t('register.special_character') }}
        </li>
        <li :class="{ 'valid': passwordMatch, 'invalid': !passwordMatch }">
          {{ $t('register.password_match') }}
        </li>
      </ul>
      <TextInputMd
        :label="$t('register.confirm_password')"
        v-model="registerData.repetir_contrasena"
        type="password"
        :placeholder="$t('register.confirm_password_placeholder')"
        outlined
        required
        class="custom-input"
        @keyup.enter="register"
      />
      <small class="result-msg" :style="{ color: popupValues.color }" v-if="popupValues.mensaje">
        {{ popupValues.mensaje }}
      </small>
    </v-container>

    <v-container class="register-buttons d-flex flex-column">
      <small class="mb-1">
        {{ $t('register.already_registered') }} <a class="goto-login" href="#" @click="$emit('show-login')">{{ $t('register.already_registered_link') }}</a>
      </small>
      <BotonMd :disabled="loading" @click="register">
        <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
        {{ $t('register.register_button') }}
      </BotonMd>
    </v-container>
  </v-form>
</template>

<script lang="ts" setup>
import { ref, watchEffect } from 'vue'
import axios from 'axios';
import TextInputMd from '@/components/TextInputMd.vue';
import BotonMd from './BotonMd.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const registerData = ref({ nombre: '', correo: '', contrasena: '', repetir_contrasena: '' });
const loading = ref(false);
const has8Characters = ref(false);
const hasSpecialCharacter = ref(false);
const passwordMatch = ref(false);

const emit = defineEmits(['show-login']);

const popupValues = ref({ mensaje: '', color: '' });
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

function checkPasswordRequirements() {
  const password = registerData.value.contrasena;

  has8Characters.value = password.length >= 8;
  hasSpecialCharacter.value = /[!@#$%^&*+]/.test(password);
  passwordMatch.value = registerData.value.contrasena === registerData.value.repetir_contrasena && registerData.value.contrasena.length > 0;
}
watchEffect(() => {
  checkPasswordRequirements();
});

async function register() {
  if (!registerData.value.nombre || !registerData.value.correo || !registerData.value.contrasena || !registerData.value.repetir_contrasena) {
    showPopup(t("message_headers.error"), t("register.fill_fields"));
    return;
  }

  if (registerData.value.contrasena !== registerData.value.repetir_contrasena) {
    showPopup(t("message_headers.error"), t("register.password_mismatch"));
    return;
  }

  // Validar que las contraseñas tengan al menos 8 caracteres (letras o numeros) y al menos un caracter especial
  const passwordRegex = /^(?=.*[!@#$%^&*+]).{8,}$/;
  if (!passwordRegex.test(registerData.value.contrasena)) {
    showPopup(t("message_headers.error"), t("register.password_invalid"));
    return;
  }

  loading.value = true;
  try {
    const response = await axios.post(`https://collabtalesserver.avaldez0.com/php/crear_alumno.php`, {
      nombre: registerData.value.nombre,
      correo: registerData.value.correo,
      contrasena: registerData.value.contrasena
    }, {
      headers: { 'Content-Type': 'application/json' }
    });

    if (response.data.id_alumno) {
      showPopup(t("message_headers.success"), t("register.registration_successful", { username: registerData.value.nombre }));
      setTimeout(() => {
        emit('show-login');
      }, 2000);
    }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error.status === 500) {
      showPopup(t("message_headers.error"), t("error_codes.500"));
    } else if (error.status === 400) {
      showPopup(t("message_headers.error"), t("error_codes.400"));
    } else if (error.status === 409) {
      showPopup(t("message_headers.error"), t("register.user_already_exists"));
    } else if (error.status === 429) {
      showPopup(t("message_headers.error"), t("error_codes.429"));
    }
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.register-fields {
  margin-left: 0;
  padding-left: 0;
  gap: 0.5rem;
}

.register-buttons {
  margin-left: 0;
  padding-left: 0;
  margin-top: 0.2rem;
}

.result-msg {
  font-size: 0.9rem;
  padding: 0;
  margin-top: -0.5rem;
}
.goto-login {
  color: var( --color-text-blue);
  text-decoration: underline;
  cursor: pointer;
  margin-bottom: 2px;
}

.requirements_container {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  margin-top: -0.5rem;
  margin-bottom: 0.5rem;
}
.valid {
  color: var(--color-save); /* or your green */
  margin-left: 1.5rem;
}
.invalid {
  color: var(--color-error); /* already used in your spans */
  margin-left: 1.5rem;
}

</style>

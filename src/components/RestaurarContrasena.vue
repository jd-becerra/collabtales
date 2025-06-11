<template>
  <div>
    <v-slide-y-transition mode="out-in">
      <v-card-text>
        <v-form @submit.prevent="submitForm">
          <v-text-field
            :label="$t('reset_password.new_password')"
            :placeholder="$t('reset_password.new_password_placeholder')"
            prepend-inner-icon="mdi-lock"
            v-model="password"
            type="password"
            outlined
            required
            class="custom-input"
          />
          <v-text-field
            :label="$t('reset_password.confirm_new_password')"
            :placeholder="$t('reset_password.confirm_new_password_placeholder')"
            prepend-inner-icon="mdi-lock"
            v-model="repeatPassword"
            type="password"
            outlined
            required
            class="custom-input"
          />
          <BotonXs block color_type="white_green" class="mt-3 rounded-lg" type="submit" :disabled="loading">
              <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
            {{ $t('reset_password.reset_button') }}
          </BotonXs>
        </v-form>
    </v-card-text>
  </v-slide-y-transition>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import router from '@/router';
import axios from 'axios';
import BotonXs from '@/components/BotonXs.vue';


const RestaurarContrasena = defineComponent({
  props: ['token', 'id_usuario'],
  components: {
    BotonXs,
  },
  setup(props) {
    const password = ref('');
    const repeatPassword = ref('');
    const loading = ref(false);

    const submitForm = async () => {
      if (!password.value || !repeatPassword.value) {
        alert('Please fill in both fields');
        return;
      }

      if (password.value !== repeatPassword.value) {
        alert('Paswords do not match');
        return;
      }

      loading.value = true;
      try {
        const response = await axios.put(`https://collabtalesserver.avaldez0.com/php/restaurar_contrasena.php`, {
          token: props.token,
          id_usuario: props.id_usuario,
          nueva_contrasena: password.value,
        });

        if (response.data.success) {
          alert('Password has been reset successfully.');
          router.push('/');
        } else {
          alert('An error occurred while resetting the password. Please try again.');
        }
      } catch (error) {
        console.error(error);
        alert('An error occurred while processing your request. Check that your new password is not the same as your previous password or try again later.');
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      if (!props.token || !props.id_usuario) {
        router.push('/');
      }
    });

    return {
      password,
      repeatPassword,
      submitForm,
      loading,
    };
  },
});

export default RestaurarContrasena;
</script>

<style scoped>
/* Fondo con degradado */
.fill-height {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Estilo de la tarjeta */
.login-card {
  width: 400px;
  border-radius: 12px;
  background: white;
  box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.2);
}

/* Campos de entrada personalizados */
.custom-input {
  background: #f4f6f8;
  border-radius: 8px;
}

/* Botones estilizados */
.v-btn {
  font-weight: bold;
  transition: 0.3s ease-in-out;
}

.v-btn:hover {
  transform: scale(1.03);
}

.text-blue-darken-2 {
  color: #1976d2 !important;
}
</style>

<template>
  <div>
    <v-slide-y-transition mode="out-in">
      <v-card-text>
        <v-form @submit.prevent="submitForm">
          <v-text-field
            label="Nueva Contraseña"
            prepend-inner-icon="mdi-lock"
            v-model="password"
            type="password"
            outlined
            required
            class="custom-input"
          />
          <v-text-field
            label="Repetir Contraseña"
            prepend-inner-icon="mdi-lock"
            v-model="repeatPassword"
            type="password"
            outlined
            required
            class="custom-input"
          />
          <v-btn block color="green-darken-3" class="mt-3 rounded-lg" type="submit" :disabled="loading">
              <v-progress-circular v-if="loading" indeterminate color="white" size="20" class="mr-2" />
              Cambiar Contraseña
          </v-btn>
        </v-form>
    </v-card-text>
  </v-slide-y-transition>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';
import router from '@/router';
import axios from 'axios';



const RestaurarContrasena = defineComponent({
  props: ['token', 'id_usuario'],
  setup(props) {
    const password = ref('');
    const repeatPassword = ref('');
    const loading = ref(false);

    const submitForm = async () => {
      if (password.value !== repeatPassword.value) {
        alert('Las contraseñas no coinciden');
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
          alert('Contraseña restaurada correctamente');
          router.push('/');
        } else {
          alert('Error: ' + response.data.error);
        }
      } catch (error) {
        alert('Error: ' + error);
      } finally {
        loading.value = false;
      }
    };

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

<template>
  <div>
    <h1>Restaurar Contraseña</h1>
    <form @submit.prevent="submitForm">
      <div>
        <label for="password">Nueva Contraseña:</label>
        <input type="password" id="password" v-model="password" required />
      </div>
      <div>
        <label for="repeatPassword">Repetir Contraseña:</label>
        <input type="password" id="repeatPassword" v-model="repeatPassword" required />
      </div>
      <button type="submit">Restaurar Contraseña</button>
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';
import axios from 'axios';

const PHP_URL = import.meta.env.VITE_PHP_SERVER;

const RestaurarContrasena = defineComponent({
  props: ['token', 'correo'],
  setup(props) {
    const password = ref('');
    const repeatPassword = ref('');

    const submitForm = async () => {
      if (password.value !== repeatPassword.value) {
        alert('Las contraseñas no coinciden');
        return;
      }

      try {
        const response = await axios.post(`${PHP_URL}/php/restaurar_contrasena.php`, {
          token: props.token,
          correo: props.correo,
          nueva_contrasena: password.value,
        });

        if (response.data.success) {
          alert('Contraseña restaurada correctamente');
        } else {
          console.error(response.data);
          alert('Error al restaurar contraseña');
        }
      } catch (error) {
        console.error(error);
        alert('Error al restaurar contraseña');
      }
    };

    return {
      password,
      repeatPassword,
      submitForm,
    };
  },
});

export default RestaurarContrasena;
</script>

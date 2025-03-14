<template>
  <div>
    <h1>Validar Token de Restauración</h1>
    <p v-if="!validado">Validando token...</p>
    <p v-if="mensaje_error">{{ mensaje_error }}</p>

    <!-- Conditionally render the RestaurarContrasena component if the token is valid -->
    <RestaurarContrasena v-if="validado" :token="token" :id_usuario="id_usuario" />
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';
import RestaurarContrasena from '@/components/RestaurarContrasena.vue';
import axios from 'axios';

const PHP_URL = import.meta.env.VITE_PHP_SERVER;

export default defineComponent({
  props: ['token', 'correo'],
  components: {
    RestaurarContrasena,
  },
  setup(props) {
    const mensaje_error = ref('');
    const validado = ref(false);
    const id_usuario = ref(null);

    const validarToken = async () => {
      try {
        const response = await axios.post(`${PHP_URL}/php/validar_token_restauracion.php`, {
          token: props.token,
          correo: props.correo,
        });

        if (response.data.error) {
          mensaje_error.value = response.data.error;
        }

        if (response.data.id_usuario) {
          id_usuario.value = response.data.id_usuario;
          validado.value = true;
        }

      } catch (error) {
        console.error(error);
        mensaje_error.value = 'Error al validar token';
      }
    };

    // Call validarToken when the component is mounted
    validarToken();

    return { mensaje_error, validado, id_usuario };
  },
});
</script>

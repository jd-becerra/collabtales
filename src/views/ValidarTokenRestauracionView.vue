<template>
 <v-container class="fill-height d-flex justify-center align-center">
  <v-card class="pa-6 rounded-lg elevation-10 login-card">
    <v-card-title class="text-center text-h5 font-weight-bold">Restaurar contraseña</v-card-title>
    <div v-if="loading" class="d-flex justify-center">
      <v-progress-circular indeterminate color="white" size="20" class="mr-2" />
      <v-card-subtitle class="text-center text-body-2">Validando token</v-card-subtitle>
    </div>

    <div v-if="mensaje_error">
      <v-card-text>
        <p
          style="color: red;"
        >{{ mensaje_error }}</p>
      </v-card-text>
    </div>

    <!-- Conditionally render the RestaurarContrasena component if the token is valid -->
    <RestaurarContrasena v-if="validado" :token="token" :id_usuario="id_usuario" />
  </v-card>
</v-container>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from 'vue';
import RestaurarContrasena from '@/components/RestaurarContrasena.vue';
import axios from 'axios';
import { useRouter } from 'vue-router';



export default defineComponent({
  props: ['token', 'correo'],
  components: {
    RestaurarContrasena,
  },
  setup(props) {
    const mensaje_error = ref('');
    const validado = ref(false);
    const id_usuario = ref(null);
    const loading = ref(false);
    const router = useRouter();

    const validarToken = async () => {
      loading.value = true;
      try {
        const response = await axios.get(`https://collabtalesserver.avaldez0.com/php/validar_token_restauracion.php`,
        {
          params: {
            token: props.token,
            correo: props.correo,
          }
        });

        if (response.data.error) {
          mensaje_error.value = response.data.error;
        }

        if (response.data.id_usuario) {
          id_usuario.value = response.data.id_usuario;
          validado.value = true;
        }

      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      } catch (error) {
        alert ('Error al validar token');
        router.push('/');
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      // Check if the token and correo are provided
      if (!props.token || !props.correo) {
        alert('Datos no válidos.');
        router.push('/');
        return;
      }
    });

    // Call validarToken when the component is mounted
    validarToken();

    return { mensaje_error, validado, id_usuario, loading };
  },
});
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

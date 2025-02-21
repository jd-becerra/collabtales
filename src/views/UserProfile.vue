<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const datosAlumno = ref({ id_alumno: '', nombre: '', contrasena: '' });
const showDeleteDialog = ref(false);
const PHP_URL = import.meta.env.VITE_PHP_SERVER;

onMounted(() => {
  getDatosAlumno();
});

function getDatosAlumno() {
  const id_alumno = localStorage.getItem('id_alumno');
  if (!id_alumno) {
    alert('Error: No hay usuario logueado.');
    router.push('/');
    return;
  }

  axios
    .get(`${PHP_URL}/php/get_alumno.php`, {
      params: {
        id_alumno: id_alumno
      }
    })
    .then((response) => {
      datosAlumno.value = response.data;
    })
    .catch((error) => {
      console.error('Error al obtener datos del alumno:', error);
    });
}

function editarAlumno() {
  axios
    .post(`${PHP_URL}/php/editar_alumno.php`, {
      id_alumno: localStorage.getItem('id_alumno'),
      nombre: datosAlumno.value.nombre,
      contrasena: datosAlumno.value.contrasena,
    },
    {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })
    .then((response) => {
      console.log(response.data);

      if (response.data.message) {  // Si hay un mensaje de éxito
        alert(response.data.message);
        router.push('/user');
      } else if (response.data.error) {  // Si hay un mensaje de error
        alert(response.data.error);
      } else {
        alert('Error desconocido al actualizar datos.');
      }
    })
    .catch((error) => {
      console.error('Error al editar:', error);
      alert('Ocurrió un error al intentar actualizar los datos.');
    });
}

function eliminarAlumno() {
  axios
    .post(`${PHP_URL}/php/eliminar_alumno.php`, {
      id_alumno: localStorage.getItem('id_alumno')
     },
    {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })
    .then(() => {
      alert('Cuenta eliminada con éxito.');
      localStorage.removeItem('id_alumno');
      router.push('/');
    })
    .catch((error) => {
      console.error('Error al eliminar cuenta:', error);
    });
}
</script>

<template>
  <v-container class="fill-height d-flex justify-center align-center">
    <v-card class="pa-5" width="500" elevation="10">
      <v-card-title class="text-h5 text-center">Editar Perfil</v-card-title>
      <v-card-text>
        <v-form @submit.prevent="editarAlumno">
          <v-text-field v-model="datosAlumno.nombre" label="Nombre" outlined required />
          <v-text-field v-model="datosAlumno.contrasena" label="Contraseña" type="password" outlined required />
          <v-btn block color="green-darken-3" class="mt-3" type="submit">Guardar Cambios</v-btn>
          <v-btn block variant="text" class="mt-2" @click="router.push('/user')">Cancelar</v-btn>
        </v-form>
      </v-card-text>
    </v-card>

    <v-btn color="red-darken-3" class="mt-5" @click="showDeleteDialog = true">
      Eliminar Cuenta
    </v-btn>

    <v-dialog v-model="showDeleteDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h6">Confirmar Eliminación</v-card-title>
        <v-card-text>
          ¿Estás seguro de querer eliminar tu cuenta?<br />
          Se eliminarán todos tus cuentos y cualquier modificación que hayan hecho tus compañeros.
        </v-card-text>
        <v-card-actions>
          <v-btn color="red-darken-3" @click="eliminarAlumno">Eliminar</v-btn>
          <v-btn variant="text" @click="showDeleteDialog = false">Cancelar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<style scoped>
.fill-height {
  height: 100vh;
}
</style>

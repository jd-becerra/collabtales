<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

// Estado reactivo
const datosAlumno = ref({ id_alumno: '', nombre: '', contrasena: '' });
const showDeleteDialog = ref(false);
const router = useRouter();

// Obtener datos del alumno al cargar la vista
onMounted(() => {
  getDatosAlumno();
});

// Obtener datos del alumno desde PHP
function getDatosAlumno() {
  const id_alumno = localStorage.getItem('id_alumno');
  if (!id_alumno) {
    alert('Error: No hay usuario logueado.');
    router.push('/');
    return;
  }

  axios
    .post('./php/get_alumno.php', new URLSearchParams({ id_alumno }))
    .then((response) => {
      if (response.data.length > 0) {
        datosAlumno.value = response.data[0];
      } else {
        alert('Error: Usuario no encontrado.');
        router.push('/');
      }
    })
    .catch((error) => {
      console.error('Error al obtener datos:', error);
    });
}

// Guardar cambios en PHP
function editarAlumno() {
  axios
    .post('./php/editar_alumno.php', new URLSearchParams(datosAlumno.value))
    .then((response) => {
      if (response.data.trim() === '1') {
        alert('Datos actualizados correctamente.');
        router.push('/user');
      } else {
        alert('Error al actualizar datos.');
      }
    })
    .catch((error) => {
      console.error('Error al editar:', error);
    });
}

// Eliminar cuenta
function eliminarAlumno() {
  const id_alumno = localStorage.getItem('id_alumno') || '';
  axios
    .post('./php/eliminar_alumno.php', new URLSearchParams({ id_alumno }))
    .then(() => {
      alert('Cuenta eliminada con éxito.');
      localStorage.removeItem('id_alumno'); // Eliminar usuario del almacenamiento
      router.push('/'); // Redirigir a inicio
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

    <!-- Botón de eliminar cuenta -->
    <v-btn color="red-darken-3" class="mt-5" @click="showDeleteDialog = true">
      Eliminar Cuenta
    </v-btn>

    <!-- Popup de confirmación -->
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

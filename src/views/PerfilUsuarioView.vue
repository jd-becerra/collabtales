<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import FormularioPerfil from '@/components/FormularioPerfil.vue';

const router = useRouter();
const datosAlumno = ref({ id_alumno: '', nombre: '' , correo: ''});
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
    .get(`${PHP_URL}/php/obtener_alumno.php`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      params: {
        id_alumno: id_alumno
      }
    })
    .then((response) => {
      datosAlumno.value = {
        id_alumno: response.data.id_alumno,
        nombre: response.data.nombre,
        correo: response.data.correo
      };
    })
    .catch((error) => {
      console.error('Error al obtener datos del alumno:', error);
    });
}

function editarAlumno() {
  axios
    .put(`${PHP_URL}/php/editar_alumno.php`, {
      id_alumno: datosAlumno.value.id_alumno,
      nombre: datosAlumno.value.nombre,
      correo: datosAlumno.value.correo,
    },
    {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    .then((response) => {
      console.log(response);

      if (response.data.success) {  // Si hay un mensaje de éxito
        alert('Datos actualizados correctamente.');
        router.push('/panel_inicio');
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
    .post(`${PHP_URL}/php/eliminar_alumno.php`,
    {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`
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
  <div class="main-container">
    <v-container class="fill-height d-flex">
      <v-card color="transparent" width="500" variant="flat">
        <div class="d-flex justify-space-between align-center">
          <v-card-title class="text-h5">Mi perfil</v-card-title>
          <v-btn class="text-decoration-underline" variant="text" color="red-darken-3" @click="showDeleteDialog = true">Eliminar Mi Cuenta</v-btn>
        </div>
        <v-card-text>
          <FormularioPerfil :datosAlumno="datosAlumno"/>
        </v-card-text>

        <v-btn
          prepend-icon="mdi-arrow-left" variant="text"
          class="text-h7 justify-start" @click="router.push('/panel_inicio')"
        >
          Regresar a panel de inicio
        </v-btn>
      </v-card>

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

    <v-container>
        <v-img
          width="420"
          class="logo-image"
          src="/img/perfil.png"
          alt="Imagen decorativa de un dragon atacando un castillo"
        />
    </v-container>
  </div>
</template>

<style scoped>
.fill-height {
  height: 100vh;
}

.main-container {
  width: 100vw;
  padding: 2rem;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  align-content: center;

  gap: 0;

}
</style>

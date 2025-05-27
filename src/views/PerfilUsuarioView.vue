<template>
  <AppNavbar/>
  <div class="main-container">
    <v-container class="fill-height d-flex">
      <v-card color="transparent" width="500" variant="flat">
        <div class="d-flex justify-space-between align-center">
          <v-card-title class="text-h5">Mi perfil</v-card-title>
          <v-btn class="text-decoration-underline" variant="text" color="red-darken-3" @click="showDeleteDialog = true">Eliminar Mi Cuenta</v-btn>
        </div>
        <v-card-text>
          <FormularioPerfil :datosAlumno="datosAlumno"
          v-if="showPerfil"
          key="perfil"
          @show-perfil-edicion="showPerfilEdicionForm"
          />

          <FormularioPerfilEdicion :datosAlumno="datosAlumno"
          v-if="showPerfilEdicion"
          key="edicion"
          @show-perfil="showPerfilForm"
          />
        </v-card-text>

        <v-btn
          variant="text"
          class="text-h7 justify-start"
          @click="router.push('/panel_inicio')"
        >
          <v-img
            src="/icons/chevron_left_black.svg"
            alt="Back arrow icon"
            width="24"
            height="24"
            class="mr-2 icon"
          ></v-img>
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

    <v-container class="d-flex justify-center align-center">
        <v-img
          max-width="480"
          class="logo-image"
          src="/img/perfil.png"
          alt="Imagen decorativa de un dragon atacando un castillo"
        />
    </v-container>
  </div>
</template>


<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import FormularioPerfil from '@/components/FormularioPerfil.vue';
import AppNavbar from '@/components/AppNavbar.vue';
import FormularioPerfilEdicion from '@/components/FormularioPerfilEdicion.vue';

const router = useRouter();
const datosAlumno = ref({ id_alumno: '', nombre: '' , correo: ''});
const showDeleteDialog = ref(false);
const showPerfil = ref(true);
const showPerfilEdicion = ref(false);
const PHP_URL = import.meta.env.VITE_PHP_SERVER;

onMounted(() => {
  getDatosAlumno();
});

function showPerfilForm() {
  showPerfil.value = true;
  showPerfilEdicion.value = false;
}

function showPerfilEdicionForm() {
  showPerfil.value = false;
  showPerfilEdicion.value = true;
}

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

function eliminarAlumno() {
  axios
    .delete(`${PHP_URL}/php/eliminar_alumno.php`,
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

<style scoped>
.fill-height {
  height: 90vh;
}

.main-container {
  width: 100vw;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  align-content: center;

  gap: 0;

}
</style>

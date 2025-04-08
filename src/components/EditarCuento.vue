<template>
  <v-container>
    <v-card class="pa-4" max-width="500" outlined>
      <v-card-title>Editar Cuento</v-card-title>
      <v-card-text>
        <v-form @submit.prevent="editarCuento">
          <v-text-field label="Nombre del cuento" v-model="nombre_cuento" required></v-text-field>
          <v-textarea label="Descripción" v-model="descripcion_cuento" required></v-textarea>
          <v-btn color="primary" type="submit" class="mr-2">Guardar Cambios</v-btn>
          <v-btn color="error" @click="cancelar">Cancelar</v-btn>
          <v-btn color="purple" class="mt-4" @click="showPublishCuentoPopup = true">Publicar Cuento</v-btn>
          <v-btn color="red" class="mt-4" @click="showDeleteCuentoPopup = true">Eliminar Cuento</v-btn>
        </v-form>
      </v-card-text>
    </v-card>

    <!-- Popup Publicar Cuento -->
    <v-dialog v-model="showPublishCuentoPopup" max-width="400">
      <v-card>
        <v-card-title>Publicar Cuento</v-card-title>
        <v-card-text>
          ¿Estás seguro de que quieres publicar este cuento?
        </v-card-text>
        <v-card-actions>
          <v-btn color="purple" @click="publicarCuento">Publicar</v-btn>
          <v-btn color="gray" @click="showPublishCuentoPopup = false">Cancelar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Popup Eliminar Cuento -->
    <v-dialog v-model="showDeleteCuentoPopup" max-width="400">
      <v-card>
        <v-card-title>Eliminar Cuento</v-card-title>
        <v-card-text>
          ¿Estás seguro de que quieres eliminar este cuento?
        </v-card-text>
        <v-card-actions>
          <v-btn color="red" @click="eliminarCuento">Eliminar</v-btn>
          <v-btn color="gray" @click="showDeleteCuentoPopup = false">Cancelar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const nombre_cuento = ref('');
const descripcion_cuento = ref('');
const router = useRouter();
const id_cuento = localStorage.getItem('id_cuento');
const id_usuario = localStorage.getItem('id_alumno');
const showPublishCuentoPopup = ref(false);
const showDeleteCuentoPopup = ref(false);

if (!id_usuario) {
  alert("No tienes permiso para acceder a esta página.");
  router.push('/panel_inicio');
}

const cargarCuento = async () => {
  if (!id_cuento) {
    alert('Error: No tienes permiso para acceder a esta página.');
    router.push('/panel_inicio');
    return;
  }

  try {
    const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_vista_cuento.php`, {
      params: { id_cuento }
    });

    if (response.data) {
      nombre_cuento.value = response.data[0].nombre;
      descripcion_cuento.value = response.data[0].descripcion;
    } else {
      alert('Error: No se encontraron datos del cuento.');
      router.push('/panel_inicio');
    }
  } catch (error) {
    console.error('Error al cargar el cuento:', error);
    alert('Error al cargar los datos del cuento.');
    router.push('/panel_inicio');
  }
};

const editarCuento = async () => {
  if (!nombre_cuento.value.trim() || !descripcion_cuento.value.trim()) {
    alert('⚠️ Por favor, completa todos los campos.');
    return;
  }
  try {
    const response = await axios.post(`${import.meta.env.VITE_PHP_SERVER}/php/editar_cuento.php`, {
      id_cuento,
      nombre_cuento: nombre_cuento.value,
      descripcion_cuento: descripcion_cuento.value,
    });
    alert(response.data);
    router.push('/ver_cuento');
  } catch (error) {
    console.error('Error al actualizar el cuento:', error);
    alert('❌ Error al actualizar el cuento. Intente nuevamente.');
  }
};

const publicarCuento = async () => {
  try {
    console.log(id_cuento, id_usuario);
    const response = await axios.post(`${import.meta.env.VITE_PHP_SERVER}/php/publicar_cuento.php`, {
      id_cuento,
      id_alumno: id_usuario
    });
    console.log(response.data);
    console.log(response.data.success || response.data.error);
    alert(response.data.success || response.data.error);
    showPublishCuentoPopup.value = false;
    router.push('/ver_cuento');
  } catch (error) {
    console.error('Error al publicar el cuento:', error);
  }
};

const eliminarCuento = async () => {
  try {
    await axios.post(`${import.meta.env.VITE_PHP_SERVER}/php/eliminar_cuento.php`, { id_cuento });
    localStorage.removeItem("id_cuento");
    router.push('/panel_inicio');
  } catch (error) {
    console.error('Error al eliminar el cuento:', error);
  }
};

const cancelar = () => {
  router.push('/ver_cuento');
};

onMounted(cargarCuento);
</script>

<style scoped>
.v-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
</style>

<template>
  <v-container>
    <v-card class="pa-4" max-width="500" outlined>
      <v-card-title>✏️ Editar Cuento</v-card-title>
      <v-card-text>
        <v-form @submit.prevent="editarCuento">
          <v-text-field
            label="Nombre del cuento"
            v-model="nombre_cuento"
            required
          ></v-text-field>
          <v-textarea
            label="Descripción"
            v-model="descripcion_cuento"
            required
          ></v-textarea>
          <v-btn color="primary" type="submit" class="mr-2">Guardar Cambios</v-btn>
          <v-btn color="error" @click="cancelar">Cancelar</v-btn>
        </v-form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const nombre_cuento = ref('');
const descripcion_cuento = ref('');
const router = useRouter();
const id_cuento = localStorage.getItem('id_cuento'); // Obtener el id del cuento

// Cargar los datos actuales del cuento
const cargarCuento = async () => {
  if (!id_cuento) {
    alert('Error: No se encontró el ID del cuento.');
    router.push('/panel_inicio');
    return;
  }

  try {
    const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuento.php`, {
      params: { id_cuento }
    });

    if (response.data) {
      nombre_cuento.value = response.data.nombre;
      descripcion_cuento.value = response.data.descripcion;
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

// Guardar los cambios
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
    }, {
      headers: { 'Content-Type': 'application/json' }
    });

    alert(response.data); 
    router.push('/panel_inicio'); 
  } catch (error) {
    console.error('Error al actualizar el cuento:', error);
    alert('❌ Error al actualizar el cuento. Intente nuevamente.');
  }
};

const cancelar = () => {
  router.push('/panel_inicio');
};

// Cargar datos del cuento cuando se monta el componente
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

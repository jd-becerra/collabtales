<template>
  <v-container>
    <v-card class="pa-4" max-width="500" outlined>
      <v-card-title>Crear un Cuento</v-card-title>
      <v-card-text>
        <v-form @submit.prevent="crearCuento">
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
          <v-btn color="success" type="submit" class="mr-2">Crear Cuento</v-btn>
          <v-btn color="error" @click="cancelar">Cancelar</v-btn>
        </v-form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const nombre_cuento = ref('');
const descripcion_cuento = ref('');
const router = useRouter();

const crearCuento = () => {
  if (!nombre_cuento.value.trim() || !descripcion_cuento.value.trim()) {
    alert('Por favor, completa todos los campos.');
    return;
  }


  axios.post(`${import.meta.env.VITE_PHP_SERVER}/php/crear_cuento.php`,
  JSON.stringify({
    id_alumno: localStorage.getItem('id_alumno'),
    nombre: nombre_cuento.value,
    descripcion: descripcion_cuento.value,
  }),
  { headers: { 'Content-Type': 'application/json' } }
  )
  .then(() => {
    alert('Cuento creado exitosamente.');
    router.push('/panel_inicio'); // <-- Only navigate after success
  })
  .catch((error) => {
    alert('Error al crear cuento.');
    console.error('Error al crear cuento:', error);
  });
};

const cancelar = () => {
  router.push('/panel_inicio');
};
</script>

<style scoped>
.v-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
</style>

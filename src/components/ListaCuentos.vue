<template>
  <v-card class="mt-4 pa-4">
    <v-card-title>Tus cuentos</v-card-title>
    <v-list>
      <v-list-item v-for="cuento in cuentos" :key="cuento.id_cuento">
          <v-list-item-title>{{ cuento.nombre }}</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-card>
</template>

<script setup lang="ts">
  import { ref, onMounted } from 'vue';
  import axios from 'axios';

  interface Cuento {
    id_cuento: number;
    nombre: string;
  }

  const cuentos = ref<Cuento[]>([]);

  const getCuentos = async () => {
    try {
      const id_alumno = localStorage.getItem('id_alumno');
      if (!id_alumno) {
        console.error('No id_alumno found in localStorage');
        return;
      }

      const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuentos.php`, {
        params: { id_alumno }
      });

      cuentos.value = response.data;
      console.log(cuentos.value);
    } catch (error) {
      console.error('Error fetching data:', error);
    }
  };

  onMounted(getCuentos);
</script>

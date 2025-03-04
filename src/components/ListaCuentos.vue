<template>
  <div>
    <h1>Lista de Cuentos</h1>
    <ul>
      <li v-for="cuento in cuentos" :key="cuento.id_cuento">{{ cuento.nombre }}</li>
    </ul>
  </div>
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

      const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/get_cuentos.php`, {
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

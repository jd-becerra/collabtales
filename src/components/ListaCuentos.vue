<template>
  <v-card class="mt-4 pa-4">
    <v-card-title>
      <v-btn @click="showAlumnoCuentos">Tus cuentos</v-btn>
      <v-btn @click="showGlobalCuentos">Cuentos Globales</v-btn>
    </v-card-title>
    <v-list v-if="showAlumno">
      <v-list-item v-for="cuento in cuentos" :key="cuento.id_cuento">
        <v-list-item-content>
          <v-list-item-title>{{ cuento.nombre }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>
    <v-list v-if="showGlobal">
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
  const showAlumno = ref(true);
  const showGlobal = ref(false);

  const getCuentosAlumno = async () => {
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

  const getCuentosGlobal = async () => {
    try {
      const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuentos_globales.php`);

      cuentos.value = response.data;
      console.log(cuentos.value);
    } catch (error) {
      console.error('Error fetching data:', error);
    }
  };

  const showAlumnoCuentos = () => {
    showAlumno.value = true;
    showGlobal.value = false;
    getCuentosAlumno();
  };

  const showGlobalCuentos = () => {
    showAlumno.value = false;
    showGlobal.value = true;
    getCuentosGlobal();
  };

  onMounted(getCuentosAlumno);
</script>

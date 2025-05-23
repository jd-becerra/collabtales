<template>
    <v-list v-if="showAlumno">
      <v-list-item
        v-for="cuento in cuentos"
        :key="cuento.id_cuento"
        class="cuento-item"
        @click="verCuento(cuento.id_cuento)"
      >
          <v-list-item-title class="text-subtitle-1 font-weight-bold">📖 Título: {{ cuento.nombre }}</v-list-item-title>
          <v-list-item-subtitle class="text-body-2 text--secondary">
            📝 Descripción: {{ cuento.descripcion || 'Sin descripción disponible' }}
          </v-list-item-subtitle>
        <v-list-item-action>
          <v-icon color="blue">mdi-chevron-right</v-icon>
        </v-list-item-action>
      </v-list-item>
    </v-list>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

interface Cuento {
  id_cuento: number;
  nombre: string;
  descripcion: string;
  autores?: string[];
  unido?: boolean;
}

const cuentos = ref<Cuento[]>([]);

const getCuentosAlumno = async () => {
  try {
    const id_alumno = localStorage.getItem('id_alumno');
    if (!id_alumno) {
      console.error('No id_alumno found in localStorage');
      return;
    }

    const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuentos.php`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      params: { id_alumno }
    });
    console.log(response.data);

    cuentos.value = response.data;
  } catch (error) {
    console.error('Error fetching data:', error);
  }

  getCuentosAlumno();

  onMounted(() => {
    getCuentosAlumno();
});
};

const verCuento = (id_cuento: number) => {
  localStorage.setItem('id_cuento', id_cuento.toString());
  router.push('/ver_cuento');
};
</script>

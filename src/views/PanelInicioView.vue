<template>
  <v-container>
    <v-card class="mt-4 pa-4 elevation-4">
      <v-card-title class="text-h5 font-weight-bold text-center">
        📚 Biblioteca de Cuentos
      </v-card-title>
      <v-divider class="my-3"></v-divider>

      <v-row justify="center" class="mb-4">
        <v-btn class="ma-2" :class="{ 'active-tab': showAlumno }" @click="showAlumnoCuentos">
          Tus cuentos
        </v-btn>
        <v-btn class="ma-2" :class="{ 'active-tab': showGlobal }" @click="showGlobalCuentos">
          Cuentos Globales
        </v-btn>
      </v-row>

      <!-- Sección: Tus Cuentos -->
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

      <v-list v-if="showGlobal">
        <v-list-item v-for="cuento in cuentosGlobales" :key="cuento.id_cuento" class="cuento-item">
          <v-row align="center" class="w-100">
            <v-col cols="9">
                <v-list-item-title class="text-subtitle-1 font-weight-bold">📖 Título: {{ cuento.nombre }}</v-list-item-title>
                <v-list-item-subtitle class="text-body-2 text--secondary">
                  📝 Descripción: {{ cuento.descripcion || 'Sin descripción disponible' }}
                </v-list-item-subtitle>
            </v-col>
            <v-col cols="3" class="d-flex justify-end">
              <v-btn v-if="!cuento.unido" color="green" outlined @click="unirseCuento(cuento.id_cuento)">
                Unirse
              </v-btn>
            </v-col>
          </v-row>
        </v-list-item>
      </v-list>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

interface Cuento {
  id_cuento: number;
  nombre: string;
  descripcion: string;
  unido?: boolean;
}

const cuentos = ref<Cuento[]>([]);
const cuentosGlobales = ref<Cuento[]>([]);
const showAlumno = ref(true);
const showGlobal = ref(false);
const router = useRouter();

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
  } catch (error) {
    console.error('Error fetching data:', error);
  }
};

const getCuentosGlobal = async () => {
  try {
    const id_alumno = localStorage.getItem('id_alumno');
    if (!id_alumno) {
      console.error('No id_alumno found in localStorage');
      return;
    }

    const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuentos_globales.php`, {
      params: { id_alumno }
    });

    console.log('Cuentos globales:', response.data);

    // Marcar cuentos en los que el usuario ya está unido
    if (!response.data || !response.data.length) {
      cuentosGlobales.value = [];
      return;
    }

    cuentosGlobales.value = response.data.map((cuento: Cuento) => ({
      ...cuento,
      unido: cuentos.value.some((c) => c.id_cuento === cuento.id_cuento),
    }));
  } catch (error) {
    console.error('Error fetching global cuentos:', error);
  }
};

const unirseCuento = async (id_cuento: number) => {
  try {
    const id_alumno = localStorage.getItem('id_alumno');
    if (!id_alumno) {
      console.error('No id_alumno found in localStorage');
      return;
    }

    const response = await axios.post(`${import.meta.env.VITE_PHP_SERVER}/php/unirse_cuento.php`, {
      id_cuento,
      id_alumno
    });

    console.log('Respuesta del servidor:', response.data);
    alert('Te has unido al cuento con éxito 🎉');

    getCuentosAlumno();
    getCuentosGlobal();
  } catch (error) {
    console.error('Error al unirse al cuento:', error);
    alert('Hubo un error al unirse al cuento. Inténtalo nuevamente.');
  }
};

const verCuento = (id_cuento: number) => {
  localStorage.setItem('id_cuento', id_cuento.toString());
  router.push('/ver_cuento');
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

onMounted(() => {
  getCuentosAlumno();
  getCuentosGlobal();
});
</script>

<style scoped>
/* Personalización de botones de navegación */
.v-btn {
  font-weight: bold;
  text-transform: none;
  border-radius: 10px;
}

.active-tab {
  background-color: #42a5f5 !important;
  color: white !important;
}

.cuento-item {
  border-radius: 8px;
  transition: background-color 0.3s ease-in-out;
  padding: 10px;
}

.cuento-item:hover {
  background-color: #e3f2fd;
  cursor: pointer;
}

.v-btn.outlined {
  border: 2px solid #2e7d32 !important;
  color: #2e7d32 !important;
  font-weight: bold;
}
</style>

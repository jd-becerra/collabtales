<template>
  <v-container>
    <v-card class="mt-4 pa-4 elevation-4">
      <v-card-title class="text-h5 font-weight-bold text-center d-flex justify-space-between align-center">
        Biblioteca de Cuentos
        <v-btn color="primary" @click="dialog = true">Unirse a un cuento</v-btn>
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
              <v-btn v-if="!cuento.unido" color="green" outlined @click="verCuentoPublico(cuento.id_cuento)">
                Ver cuento
              </v-btn>
            </v-col>
          </v-row>
        </v-list-item>
      </v-list>
    </v-card>

    <v-dialog v-model="dialog" max-width="400">
      <v-card>
        <v-card-title class="text-h5">Unirse a un cuento</v-card-title>
        <v-card-text>
          <v-text-field v-model="idCuentoUnirse" label="ID del cuento" required></v-text-field>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="confirmarUnirse">Unirse</v-btn>
          <v-btn color="error" @click="dialog = false">Cancelar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
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
const dialog = ref(false);
const idCuentoUnirse = ref('');

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
      alert('No tienes acceso a esta seccion.');
      router.push('/');
      return;
    }

    const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuentos_globales.php`, {
      params: { id_alumno }
    });

    console.log('Cuentos globales:', response.data);

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

    if (response.data.error) {
      alert(response.data.error); 
      return;
    }

    alert(response.data.success);
    getCuentosAlumno();
    getCuentosGlobal();
  } catch (error) {
    console.error('Error al unirse al cuento:', error);
    alert('Hubo un error al unirse al cuento. Inténtalo nuevamente.');
  }
};

const verCuentoPublico = (id_cuento: number) => {
  localStorage.setItem('id_cuento', id_cuento.toString());
  router.push('/ver_cuento_publico');
};

const confirmarUnirse = () => {
  if (!idCuentoUnirse.value.trim()) {
    alert('Por favor, ingresa un ID de cuento válido.');
    return;
  }
  unirseCuento(Number(idCuentoUnirse.value));
  dialog.value = false;
};

const verCuento = (id_cuento: number) => {
  localStorage.setItem('id_cuento', id_cuento.toString());
  router.push('/ver_cuento');
};

const showAlumnoCuentos = () => {
  showAlumno.value = true;
  showGlobal.value = false;
};

const showGlobalCuentos = () => {
  showAlumno.value = false;
  showGlobal.value = true;
};

onMounted(() => {
  getCuentosAlumno();
  getCuentosGlobal();
});
</script>
<template>
  <v-container>
    <AppNavbar />

    <v-container class="d-flex flex-column align-start justify-star">
      <div class="home-header-container d-flex align-start justify-start">
        <v-img
          class="icon"
          src="/icons/public.svg"
          alt="Ícono para cuentos públicos"
          contain
        />
        <h1 class="home-header">BIBLIOTECA DE CUENTOS</h1>
      </div>
      <p class="home-subheader text-h6">Explora e interactúa con las historias que han escrito otros usuarios.</p>
    </v-container>

    <v-divider class="my-3"></v-divider>

  <v-container class="home-main-container d-flex flex-row justify-space-between">
    <v-card class="lista-cuentos-container">
      <v-list>
        <v-list-item v-for="cuento in cuentosGlobales" :key="cuento.id_cuento" class="cuento-item">
            <v-list-item-title class="text-subtitle-1 font-weight-bold">
              <a @click="verCuentoPublico(cuento.id_cuento)" class="text-decoration-none">
              {{ cuento.nombre }}
              </a>
            </v-list-item-title>
            <v-list-item-subtitle class="text-body-2 text--secondary">
              Descripción: {{ cuento.descripcion || 'Sin descripción disponible' }}
            </v-list-item-subtitle>
        </v-list-item>
      </v-list>
    </v-card>

    <div  class="d-flex flex-column">
      <TextInputSearch
        class="mb-4"
        placeholder="Buscar cuento por título"
        v-model="searchQuery"
        @keyup.enter="filterCuentos"
      />
      <BotonSm @click="dialog = true">Unirse a un cuento</BotonSm>
    </div>
  </v-container>

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
import '../assets/base.css';

import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

// Componentes
import AppNavbar from '@/components/AppNavbar.vue';
import TextInputSearch from '@/components/TextInputSearch.vue';
import BotonSm from '@/components/BotonSm.vue';

interface Cuento {
  id_cuento: number;
  nombre: string;
  descripcion: string;
  autores?: string[];
}

const cuentosGlobales = ref<Cuento[]>([]);

const router = useRouter();
const dialog = ref(false);
const idCuentoUnirse = ref('');
const searchQuery = ref('');

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
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      params: { id_alumno }
    });

    console.log(response.data);

    if (!response.data || !response.data.length) {
      cuentosGlobales.value = [];
      return;
    }

    cuentosGlobales.value = response.data.map((cuento: Cuento) => ({
      ...cuento,
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
    const response = await axios.post(`${import.meta.env.VITE_PHP_SERVER}/php/unirse_cuento.php`,
    {
      id_cuento,
      id_alumno
    });

    console.log('Response:', response.data);

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

// Opción preferible: filtrar los cuentos que ya existen (aqui podemos usar @input )
/* const filterCuentos = () => {
  if (!searchQuery.value) {
    getCuentosGlobal();
    return;
  }

  const filteredCuentos = cuentosGlobales.value.filter(cuento =>
    cuento.nombre.toLowerCase().includes(searchQuery.value.toLowerCase())
  );

  cuentosGlobales.value = filteredCuentos;
};

const verCuentoPublico = (id_cuento: number) => {
  localStorage.setItem('id_cuento', id_cuento.toString());
  router.push('/ver_cuento_publico');
};
 */

const filterCuentos = () => {
  if (!searchQuery.value) {
    getCuentosGlobal();
    return;
  }

  // Si hay un query, se hace la busqueda en el servidor
  filterCuentosServer();
};

// Opción para escuela: obtener resultados de busqueda del servidor (usar @input resulta en muchas llamadas, por lo que usamos enter con @keyup.enter) )
const filterCuentosServer = async () => {
  try {
    const id_alumno = localStorage.getItem('id_alumno');
    if (!id_alumno) {
      console.error('No id_alumno found in localStorage');
      return;
    }

    const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/buscar_cuentos_globales.php`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      params: { busqueda: searchQuery.value}
    });

    console.log(response.data);

    if (!response.data || !response.data.length) {
      cuentosGlobales.value = [];
      return;
    }

    cuentosGlobales.value = response.data.map((cuento: Cuento) => ({
      ...cuento,
    }));
  } catch (error) {
    console.error('Error fetching global cuentos:', error);
  }
};

const confirmarUnirse = () => {
  if (!idCuentoUnirse.value.trim()) {
    alert('Por favor, ingresa un ID de cuento válido.');
    return;
  }
  unirseCuento(Number(idCuentoUnirse.value));
  dialog.value = false;
};

onMounted(() => {
  getCuentosGlobal();
});
</script>

<style scoped>
.icon {
  width: var(--icon-size-default);
  height: var(--icon-size-default);
}

.home-main-container {
  width: 100%;
}

.lista-cuentos-container {
  width: 100%;
}

</style>

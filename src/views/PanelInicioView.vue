<template>
  <v-container>
    <AppNavbar />

    <v-container class="d-flex flex-column align-start justify-start">
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

    <v-container class="home-main-container d-flex flex-row justify-space-between">
      <v-card class="lista-cuentos-container">
        <v-list>
          <CuentoListItem
            v-for="(cuento, index) in cuentosGlobales"
            :id_cuento="Number(cuento.id_cuento)"
            :key="cuento.id_cuento"
            :nombre="cuento.nombre"
            :descripcion="cuento.descripcion"
            :autores="cuento.autores"
            :ultimo="index === cuentosGlobales.length - 1"
          />
        </v-list>
      </v-card>

      <div  class="d-flex flex-column align-center ">
        <TextInputSearch
          class="mb-6"
          placeholder="Buscar cuento por título"
          v-model="searchQuery"
          @input="filterCuentos"
        />
        <v-container class="home-options d-flex flex-column align-center justify-center">
          <BotonSm icon_path="/icons/edit_text.svg" @click="showCrearCuentoDialog()">Crear un cuento</BotonSm>
          <BotonSm icon_path="/icons/group_add.svg" @click="showUnirseDialog()">Unirse a un cuento</BotonSm>
          <BotonSm icon_path="/icons/description.svg">Ver mis cuentos</BotonSm>
        </v-container>
      </div>
    </v-container>

    <v-dialog v-model="dialog" max-width="400" class="options-dialog">
      <FormularioUnirseCuento v-if="dialog_section === 'unirse'"
        @close-popup="dialog = false"
      />
      <FormularioCrearCuento v-else-if="dialog_section === 'crear'"
        @close-popup="dialog = false"
      />
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
import CuentoListItem from '@/components/CuentoListItem.vue';
import FormularioUnirseCuento from '@/components/FormularioUnirseCuento.vue';
import FormularioCrearCuento from '@/components/FormularioCrearCuento.vue';

interface Cuento {
  id_cuento: number;
  nombre: string;
  descripcion: string;
  autores?: string[];
}

const cuentosGlobales = ref<Cuento[]>([]);

const router = useRouter();
const dialog = ref(false);
const dialog_section = ref('');
const searchQuery = ref('');

const showCrearCuentoDialog = () => {
  dialog.value = true;
  dialog_section.value = "crear";
};

const showUnirseDialog = () => {
  dialog.value = true;
  dialog_section.value = "unirse";
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
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      params: { id_alumno }
    });

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
  gap: 2rem;
}

.lista-cuentos-container {
  width: 100%;
  height: 60vh;
  border: 1px solid var(--color-text-input-border);
  background-color: var(--color-text-input-bg-default);

  overflow-y: scroll;
}

.home-header {
  font-weight: bold;
}

.home-options {
  gap: 1rem;
}

.dialog-card {
  border-radius: var(--border-radius-default);
}
</style>

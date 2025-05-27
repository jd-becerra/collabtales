<template>
    <AppNavbar />
  <div class="main-container">
    <v-container class="d-flex flex-column align-start justify-start mb-0 py-0 mt-2">
      <div class="home-header-container d-flex align-center">
        <v-img
          class="icon mr-2"
          src="/icons/bookmark.svg"
          alt="Ícono para cuentos públicos"
          contain
          width="24"
          height="24"
        />
        <h1 class="home-header">MIS CUENTOS</h1>
      </div>
      <p class="home-subheader text-h6 mb-0 pb-0">Estos son los cuentos que has creado o en los que estás participando.</p>
    </v-container>

    <v-container class="home-main-container d-flex flex-row justify-space-between">
      <div class="list-cuentos-main-container d-flex flex-column mt-0 py-0">
        <div class="d-flex justify-end align-center pa-0 ma-0 ">
          <v-switch class="filter-owner-switch"
            label="Filtrar cuentos creados por mí"
            color="info"
            v-model="toggleFilterByOwner"
          ></v-switch>
        </div>
        <v-card class="lista-cuentos-container">
          <v-list>
            <CuentoListItemPrivado
              v-for="(cuento, index) in cuentosListados"
              :id_cuento="Number(cuento.id_cuento)"
              :key="cuento.id_cuento"
              :nombre="cuento.nombre"
              :descripcion="cuento.descripcion"
              :autores="cuento.autores"
              :es_dueño="cuento.es_dueño === 1"
              :ultimo="index === cuentosListados.length - 1"
            />
          </v-list>
        </v-card>
      </div>

      <div  class="d-flex flex-column align-center ">
        <TextInputSearch
          class="mb-6"
          placeholder="Buscar cuento por título"
          v-model="searchQuery"
        />
        <v-container class="home-options d-flex flex-column align-center justify-center">
          <BotonSm icon_path="/icons/edit_text.svg" @click="showCrearCuentoDialog()">Crear un cuento</BotonSm>
          <BotonSm icon_path="/icons/group_add.svg" @click="showUnirseDialog()">Unirse a un cuento</BotonSm>
          <BotonSm icon_path="/icons/public.svg" @click="redirectCuentosPublicos()">Cuentos publicados</BotonSm>
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
  </div>
</template>

<script setup lang="ts">
import '../assets/base.css';

import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

// Componentes
import AppNavbar from '@/components/AppNavbar.vue';
import TextInputSearch from '@/components/TextInputSearch.vue';
import BotonSm from '@/components/BotonSm.vue';
import CuentoListItemPrivado from '@/components/CuentoListItemPrivado.vue';
import FormularioUnirseCuento from '@/components/FormularioUnirseCuento.vue';
import FormularioCrearCuento from '@/components/FormularioCrearCuento.vue';

interface Cuento {
  id_cuento: number;
  nombre: string;
  descripcion: string;
  autores?: string[];
  es_dueño?: number;
}

const cuentosListados = ref<Cuento[]>([]);
const cuentosCompletos = ref<Cuento[]>([]);

const router = useRouter();
const dialog = ref(false);
const toggleFilterByOwner = ref(false);
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

const redirectCuentosPublicos = () => {
  router.push('/panel_inicio');
};

const getCuentosUsuario = async () => {
  try {
    const id_alumno = localStorage.getItem('id_alumno');
    if (!id_alumno) {
      console.error('No id_alumno found in localStorage');
      alert('No tienes acceso a esta seccion.');
      router.push('/');
      return;
    }

    const response = await axios.get(`${import.meta.env.VUE_APP_SERVER}/php/obtener_cuentos.php`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      params: { id_alumno }
    });

    if (!response.data || !response.data.length) {
      cuentosListados.value = [];
      return;
    }

    cuentosCompletos.value = response.data.map((cuento: Cuento) => ({
      ...cuento,
    }));

    // Filtrar los cuentos según sea necesario
    applyFilters();
  } catch (error) {
    console.error('Error fetching global cuentos:', error);
  }
};

const applyFilters = () => {
  let filtered = [...cuentosCompletos.value];

  // Si se necesita filtrar por cuentos que pertenezcan al usuario
  if (toggleFilterByOwner.value) {
    filtered = filtered.filter(cuento => cuento.es_dueño === 1);
  }

  // Si se necesita filtrar por el nombre del cuento según la búsqueda
  if (searchQuery.value.trim() !== '') {
    filtered = filtered.filter(cuento =>
      cuento.nombre.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  cuentosListados.value = filtered;
};

// Si estos valores cambian, filtrar cuentos según sea necesario
watch([toggleFilterByOwner, searchQuery], () => {
  applyFilters();
});

onMounted(() => {
  getCuentosUsuario();
});
</script>

<style scoped>
.main-container {
  height: 100%;
  width: 100vw;
}

.home-main-container {
  gap: 2rem;
}

.list-cuentos-main-container {
  gap: 0;
  width: 70%;
}

.filter-owner-switch {
  padding: 0;
  margin: 0;

  display: flex;
}

.lista-cuentos-container {
  width: 100%;
  height: 55vh;
  border: 1px solid var(--color-text-input-border);
  background-color: var(--color-text-input-bg-default);

  overflow-y: scroll;
  border-radius: var(--border-radius-default);
}

.home-header {
  font-weight: bold;
  font-size: var(--font-main-header-size);
}

.home-options {
  gap: 1rem;
}

.dialog-card {
  border-radius: var(--border-radius-default);
}
</style>

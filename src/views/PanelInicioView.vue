<template>
  <div>
    <AppNavbar />

    <v-container class="d-flex flex-column align-start justify-start">
      <div class="home-header-container d-flex align-center">
        <v-img
          class="icon mr-2"
          src="/icons/public.svg"
          alt="Ícono para cuentos públicos"
          contain
          width="24"
          height="24"
        />
        <h1 class="home-header">BIBLIOTECA DE CUENTOS</h1>
      </div>
      <p class="home-subheader text-h6">Explora e interactúa con las historias que han escrito otros usuarios.</p>
    </v-container>

    <v-container class="home-main-container d-flex flex-row">
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
        <v-card-text v-if="errorMsg" class="text-center">
          {{ errorMsg }}
        </v-card-text>
      </v-card>

      <div  class="d-flex flex-column align-center ">
        <TextInputSearch
          class="mb-6"
          placeholder="Buscar cuento por título"
          v-model="searchQuery"
          :searchFunction="filterCuentosServer"
          @input="filterCuentos"
        />
        <v-container class="home-options d-flex flex-column align-center justify-center">
          <BotonSm icon_path="/icons/edit_text.svg" @click="showCrearCuentoDialog()">Crear un cuento</BotonSm>
          <BotonSm icon_path="/icons/group_add.svg" @click="showUnirseDialog()">Unirse a un cuento</BotonSm>
          <BotonSm icon_path="/icons/description.svg" @click="redirectMisCuentos()">Ver mis cuentos</BotonSm>
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

import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

// Componentes
import AppNavbar from '@/components/AppNavbar.vue';
import TextInputSearch from '@/components/TextInputSearch.vue';
import BotonSm from '@/components/BotonSm.vue';
import CuentoListItem from '@/components/CuentoListItemPublico.vue';
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
const errorMsg = ref('');

const showCrearCuentoDialog = () => {
  dialog.value = true;
  dialog_section.value = "crear";
};

const showUnirseDialog = () => {
  dialog.value = true;
  dialog_section.value = "unirse";
};

const redirectMisCuentos = () => {
  router.push('/mis_cuentos');
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

    const response = await axios.get(`${import.meta.env.VUE_APP_SERVER}/php/obtener_cuentos_globales.php`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      params: { id_alumno }
    });

    if (!response.data || !response.data.length) {
      cuentosGlobales.value = [];
      errorMsg.value = 'No se encontraron cuentos publicados.';
      return;
    }

    cuentosGlobales.value = response.data.map((cuento: Cuento) => ({
      ...cuento,
    }));
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  } catch (error) {
    errorMsg.value = 'Error al obtener los cuentos. Por favor, inténtalo de nuevo más tarde.';
    return;
  }
};

const filterCuentos = () => {
  if (!searchQuery.value) {
    getCuentosGlobal();
    return;
  }

  // Si hay un query, se hace la busqueda en el servidor
  filterCuentosServer();
};

const filterCuentosServer = async () => {
  try {
    const id_alumno = localStorage.getItem('id_alumno');
    if (!id_alumno) {
      return;
    }

    const response = await axios.get(`${import.meta.env.VUE_APP_SERVER}/php/buscar_cuentos_globales.php`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      params: { busqueda: searchQuery.value}
    });

    if (!response.data || !response.data.length) {
      cuentosGlobales.value = [];
      return;
    }

    cuentosGlobales.value = response.data.map((cuento: Cuento) => ({
      ...cuento,
    }));
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  } catch (error) {
    errorMsg.value = 'Error al filtrar los cuentos. Por favor, inténtalo de nuevo más tarde.';
    return;
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
  border-radius: var(--border-radius-default);

  overflow-y: scroll;
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

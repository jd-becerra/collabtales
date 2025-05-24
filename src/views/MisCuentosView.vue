<template>
  <v-container>
    <AppNavbar />

    <v-container class="d-flex flex-column align-start justify-start">
      <div class="home-header-container d-flex align-start justify-start">
        <v-img
          class="icon"
          src="/icons/bookmark.svg"
          alt="Ícono para cuentos públicos"
          contain
        />
        <h1 class="home-header">MIS CUENTOS</h1>
      </div>
      <p class="home-subheader text-h6">Estos son los cuentos que has creado o en los que estás participando.</p>
    </v-container>

    <v-container class="home-main-container d-flex flex-row justify-space-between">
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

      <div  class="d-flex flex-column align-center ">
        <TextInputSearch
          class="mb-6"
          placeholder="Buscar cuento por título"
          v-model="searchQuery"
          :searchFunction="filterCuentos"
          @input="filterCuentos"
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

    const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuentos.php`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      params: { id_alumno }
    });

    if (!response.data || !response.data.length) {
      cuentosListados.value = [];
      return;
    }

    cuentosListados.value = response.data.map((cuento: Cuento) => ({
      ...cuento,
    }));
    // Para evitar más llamadas al servidor, guardamos los cuentos completos para reutilizar en el filtro
    cuentosCompletos.value = [...cuentosListados.value];
  } catch (error) {
    console.error('Error fetching global cuentos:', error);
  }
};

const filterCuentos = () => {
  if (!searchQuery.value) {
    cuentosListados.value = [...cuentosCompletos.value];
    return;
  }

  const filteredCuentos = cuentosListados.value.filter(cuento =>
    cuento.nombre.toLowerCase().includes(searchQuery.value.toLowerCase())
  );

  cuentosListados.value = filteredCuentos;
};

onMounted(() => {
  getCuentosUsuario();
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

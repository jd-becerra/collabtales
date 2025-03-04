<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import ListaCuentos from '@/components/ListaCuentos.vue';

const router = useRouter();
const cuentos = ref<{ nombre_cuento: string }[]>([]);
const PHP_URL = import.meta.env.VITE_PHP_SERVER;

onMounted(() => {
  fetchCuentos();
});

function fetchCuentos() {
  axios.get(`${PHP_URL}/php/obtener_cuentos.php`)
    .then((response) => {
      cuentos.value = response.data;
    })
    .catch((error) => {
      console.error('Error al obtener cuentos:', error);
    });
}
</script>

<template>
  <v-container>
    <v-btn class="mb-3" @click="router.push('/perfil_usuario')">Perfil de usuario</v-btn>
    <v-btn class="mb-3" @click="router.push('/crear_cuento')">Crear un cuento nuevo</v-btn>
    <v-btn class="mb-3" @click="router.push('/unirse_cuento')">Unirse a un cuento</v-btn>

    <ListaCuentos/>
  </v-container>
</template>

<style scoped>
.v-container {
  max-width: 600px;
  margin: auto;
}
</style>

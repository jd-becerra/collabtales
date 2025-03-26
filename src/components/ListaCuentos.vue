<template>
  <v-container>
    <v-card class="mt-4 pa-4 elevation-4">
      <v-card-title class="text-h5 font-weight-bold text-center">
        📚 Biblioteca de Cuentos
      </v-card-title>
      <v-divider class="my-3"></v-divider>

      <v-row justify="center">
        <v-btn class="ma-2" :class="{ 'active-tab': showAlumno }" @click="showAlumnoCuentos">
          Tus cuentos
        </v-btn>
        <v-btn class="ma-2" :class="{ 'active-tab': showGlobal }" @click="showGlobalCuentos">
          Cuentos Globales
        </v-btn>
      </v-row>

      <!-- Sección: Tus Cuentos -->
      <v-list v-if="showAlumno">
        <template v-if="cuentos.length > 0">
          <v-list-item v-for="cuento in cuentos" :key="cuento.id_cuento" class="cuento-item">
            <v-list-item-content @click="verCuento(cuento.id_cuento)">
              <v-list-item-title class="text-subtitle-1 font-weight-bold">📖 {{ cuento.nombre }}</v-list-item-title>
              <v-list-item-subtitle class="text-body-2 text--secondary">
                📝 {{ cuento.descripcion || 'Sin descripción disponible' }}
              </v-list-item-subtitle>
            </v-list-item-content>

            <v-list-item-action>
              <v-btn color="blue" outlined @click.stop="editarCuento(cuento.id_cuento)">Editar
              </v-btn>
            </v-list-item-action>
          </v-list-item>
        </template>
        <p v-else class="no-cuentos-text">⚠️ Aún no tienes cuentos asignados. ¡Únete a uno o crea tu propia historia!</p>
      </v-list>

      <!-- Sección: Cuentos Globales -->
      <v-list v-if="showGlobal">
        <template v-if="cuentosGlobales.length > 0">
          <v-list-item v-for="cuento in cuentosGlobales" :key="cuento.id_cuento" class="cuento-item">
              <v-list-item-title class="text-subtitle-1 font-weight-bold">📖 {{ cuento.nombre }}</v-list-item-title>
              <v-list-item-subtitle class="text-body-2 text--secondary">
                📝 {{ cuento.descripcion || 'Sin descripción disponible' }}
              </v-list-item-subtitle>
            <v-list-item-action v-if="!cuento.unido">
              <v-btn color="green" outlined @click="unirseCuento(cuento.id_cuento)">
                Unirse
              </v-btn>
            </v-list-item-action>
          </v-list-item>
        </template>
        <p v-else class="no-cuentos-text">⚠️ No hay cuentos disponibles en este momento. Vuelve más tarde o crea uno nuevo. 🚀</p>
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

    // Marcar cuentos en los que el usuario ya está unido
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

    await axios.post(`${import.meta.env.VITE_PHP_SERVER}/php/unirse_cuento.php`, {
      id_cuento,
      id_alumno
    });

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

const editarCuento = (id_cuento: number) => {
  localStorage.setItem('id_cuento', id_cuento.toString());
  router.push('/editar_cuento');
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

.no-cuentos-text {
  text-align: center;
  font-size: 16px;
  font-weight: bold;
  color: #666;
  padding: 20px;
}
</style>

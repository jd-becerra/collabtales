<!-- eslint-disable vue/no-v-text-v-html-on-component -->
<template>
  <v-overlay :value="loading" absolute>
    <p>Loading</p>
    <v-progress-circular indeterminate color="primary"></v-progress-circular>
  </v-overlay>

  <v-container class="vista-cuento" v-if="!loading">
    <v-btn color="primary" class="mb-2" :to="'/panel_inicio'">
      <v-icon left>mdi-arrow-left</v-icon>
      Volver a Mis Cuentos
    </v-btn>

    <v-btn v-if=es_dueno color="blue" class="mb-2 ml-2" :to="'/editar_cuento'">Editar Cuento</v-btn>

    <v-card class="my-4 pa-4" elevation="6" v-if="cuento">
      <v-card-title class="text-h5 font-weight-bold">Título: {{ cuento.nombre }}</v-card-title>
      <v-divider></v-divider>
      <v-card-text class="mt-2">
        <p class="text-body-1">Descripción: {{ cuento.descripcion }}</p>
      </v-card-text>
    </v-card>

    <v-card class="pa-4 mb-4 code-card" elevation="6">
      <v-card-title class="text-h6 font-weight-bold">Código para unirse</v-card-title>
      <v-card-text class="text-center text-h5 font-weight-bold green--text">
        {{ id_cuento }}
      </v-card-text>
    </v-card>

    <v-card class="pa-4 aportaciones-card" elevation="6">
      <v-card-title class="text-h6 font-weight-bold">Aportaciones</v-card-title>
      <v-divider></v-divider>
      <v-card-text class="mt-2">
        <v-list v-if="aportaciones.length > 0">
          <v-list-item v-for="aportacion in aportaciones" :key="aportacion.id_aportacion" class="aportacion-item" :class="{ 'borde-rojo': Number(id_alumno) === aportacion.id_alumno }">
            <div class="d-flex justify-space-between align-center w-100">
              <v-list-item-title class="text-body-1 font-weight-bold">{{ aportacion.nombre_alumno }}</v-list-item-title>
              <v-btn color="green" v-if="Number(id_alumno) === aportacion.id_alumno" @click="navegarAportacion()">Editar Aportación</v-btn>
            </div>
            <v-divider></v-divider>
              <v-list-item-title v-html="aportacion.contenido" class="contenido text-body-2"></v-list-item-title>
          </v-list-item>
        </v-list>
        <p v-else class="no-aportaciones">Actualmente no existen aportaciones en este cuento.</p>
      </v-card-text>
    </v-card>

    <v-btn color="secondary" class="mt-4 float-right mr-2 mb-4" :to="'/descarga'">
      <v-icon left>mdi-arrow-left</v-icon>
      Visualizar cuento
    </v-btn>

    <!-- Popup Eliminar Aportación -->
    <v-dialog v-model="showDeleteAportacionPopup" max-width="400">
      <v-card>
        <v-card-title>Abandonar cuento</v-card-title>
        <v-card-text>
          ¿Estás seguro de abandonar este cuento? Tu aportación será eliminada permanentemente.
        </v-card-text>
        <v-card-actions>
          <v-btn color="red" @click="eliminarAportacion">Abandonar</v-btn>
          <v-btn color="gray" @click="showDeleteAportacionPopup = false">Cancelar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script lang="ts" setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { QuillDeltaToHtmlConverter } from 'quill-delta-to-html';

const router = useRouter();

const cuento = ref<{ id: number; nombre: string; descripcion: string } | null>(null);
const aportaciones = ref<Array<{ id_aportacion: number; contenido: string; nombre_alumno: string; id_alumno: number }>>([]);
const id_aportacion = ref<string | null>(null);
const showDeleteAportacionPopup = ref(false);

const loading = ref(false);
const id_alumno = ref<string | null>(localStorage.getItem("id_alumno"));
const es_dueno = ref(false);

function convertDeltaToHtml(contenido: string | object): string {
  const delta = typeof contenido === 'string' ? JSON.parse(contenido) : contenido;
  const converter = new QuillDeltaToHtmlConverter(delta.ops, {});
  const html = converter.convert();
  return html;
}

const props = defineProps<{
  id_cuento: {
    type: string;
    required: true;
  }
}>();

async function obtenerCuentoPrivado() {
  try {
    const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuento_privado.php`, {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` },
      params: {
        id_cuento: props.id_cuento,
      }
    });

    console.log(response.data);

    if (response.data.error) {
      console.log(response.data.error);
      alert(response.data.error);
      router.push('/panel_inicio');
      return false;
    }

    es_dueno.value = response.data.es_dueno;
    return true;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error.status === 404) {
      alert("Cuento no encontrado.");
    } else if (error.status === 403) {
      alert("No tienes permiso para ver este cuento.");
    } else {
      alert("Error al obtener el cuento. Por favor, inténtalo de nuevo más tarde.");
    }
    // Redirigir al usuario al panel de inicio
    router.push('/panel_inicio');
    return;
  }
}3

async function eliminarAportacion() {
  loading.value = true;
  try {
    await axios.post('/php/eliminar_aportacion.php',
      { id_cuento: props.id_cuento },
      {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
      }
    );
  } catch (error) {
    console.error("Error al eliminar la aportación:", error);
  } finally {
    loading.value = false;
  }
}

async function navegarAportacion() {
  try {
    if (aportaciones.value.length === 0) {
      alert("No puedes editar una aportación que no existe.");
      return;
    }
    router.push('/editar_aportacion');
  } catch (error) {
    console.error("Error al obtener el ID de la aportación:", error);
  }
}

onMounted(async () => {
  if (!id_cuento.value || !id_alumno.value) {
    alert("No tienes permiso para ver este cuento.");
    return;
  }
  loading.value = true;
  try {
    const verificar = await verificarCuento();
    if (!verificar) return;
    await Promise.all([obtenerCuento(), obtenerAportaciones()]);
  } catch (error) {
    console.error("Error cargando la vista del cuento:", error);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.aportacion-item {
  border: 2px solid rgb(192, 192, 192);
  padding: 10px;
  border-radius: 5px;
  margin: 20px;
}

.contenido {
  font-size: 1.2em;
  line-height: 1.5;
  min-height: 100px;
  padding: 10px;
}

.no-aportaciones {
  text-align: center;
  font-size: 1.2em;
  color: #888;
}

.v-btn {
  margin-top: 20px;
}

.v-dialog {
  max-width: 400px;
}

.v-card-title {
  font-weight: bold;
}

.v-list-item-title {
  font-weight: normal;
}

.v-divider {
  margin: 10px 0;
}

.borde-rojo {
  border: 2px solid black;
  padding: 10px;
  border-radius: 5px;
  margin:20px;
}
</style>

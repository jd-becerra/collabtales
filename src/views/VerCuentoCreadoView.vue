<!-- eslint-disable vue/no-v-text-v-html-on-component -->
<template>
  <AppNavbar />

  <v-overlay :value="loading" absolute>
    <p>Loading</p>
    <v-progress-circular indeterminate color="primary"></v-progress-circular>
  </v-overlay>

    <div>
      <div v-if="cuento">
        <h2 class="text-h5 font-weight-bold">Título: {{ cuento.nombre }}</h2>
        <p class="text-body-1">Descripción: {{ cuento.descripcion }}</p>
      </div>
      <v-card class="pa-4 aportaciones-card" elevation="6">
        <v-card-title class="text-h6 font-weight-bold">Aportaciones</v-card-title>
        <v-divider></v-divider>
        <v-card-text class="mt-2">
          <v-list v-if="aportaciones.length > 0">
            <v-list-item v-for="(aportacion, idx) in aportaciones" :key="idx" class="aportacion-item" :class="{ 'borde-rojo': aportacion.es_autor }">
              <div class="d-flex justify-space-between align-center w-100">
                <v-list-item-title class="text-body-1 font-weight-bold">{{ aportacion.autor}}</v-list-item-title>
                <v-btn color="green" v-if="aportacion.es_autor" @click="navegarAportacion()">Editar Aportación</v-btn>
              </div>
              <v-divider></v-divider>
                <v-list-item-title v-html="aportacion.contenido" class="contenido text-body-2"></v-list-item-title>
            </v-list-item>
          </v-list>
          <p v-else class="no-aportaciones">Actualmente no existen aportaciones en este cuento.</p>
        </v-card-text>
      </v-card>
    </div>

    <v-btn color="primary" class="mb-2" :to="'/mis_cuentos'">
      <v-icon left>mdi-arrow-left</v-icon>
      Volver a Mis Cuentos
    </v-btn>
    <v-card>
      <v-card-title>CÓDIGO: {{ cuento?.codigo_compartir }}</v-card-title>
      <p>Comparte este código para añadir colaboradores.</p>
      <v-card-text>
        <h6>COLABORADORES:</h6>
        <v-list>
          <v-list-item v-for="(aportacion, idx) in aportaciones" :key="idx">
            <v-list-item-title>{{ aportacion.autor }}</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-card-text>
    </v-card>

    <BotonSm icon_path="/icons/visibility.svg">
      Visualizar cuento
    </BotonSm>
    <BotonSm icon_path="/icons/edit_note.svg" color_type="white_purple">
      Modificar datos del cuento
    </BotonSm>
    <BotonSm icon_path="/icons/groups.svg" color_type="white_purple">
      Gestionar colaboradores
    </BotonSm>
    <BotonSm icon_path="/icons/delete_forever.svg" color_type="white_red" @click="showDeleteAportacionPopup = true">
      Eliminar cuento
    </BotonSm>



</template>

<script lang="ts">
import { ref, onMounted, defineComponent } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { QuillDeltaToHtmlConverter } from 'quill-delta-to-html';
import AppNavbar from '@/components/AppNavbar.vue';
import BotonSm from '@/components/BotonSm.vue';

function convertDeltaToHtml(contenido: string | object): string {
  const delta = typeof contenido === 'string' ? JSON.parse(contenido) : contenido;
  const converter = new QuillDeltaToHtmlConverter(delta.ops, {});
  const html = converter.convert();
  return html;
}

export default defineComponent({
  name: 'VerCuentoCreadoView',
  components: {
    AppNavbar,
    BotonSm
  },
  props: {
    id_cuento: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const router = useRouter();
    const cuento = ref<{
      nombre: string;
      descripcion: string,
      codigo_compartir: string,
      publicado: number,
      } | null>(null);
    const aportaciones = ref<Array<{ autor: string; contenido: string; es_autor: boolean }>>([]);
    const id_aportacion = ref<string | null>(null);
    const showDeleteAportacionPopup = ref(false);
    const loading = ref(false);

    const obtenerCuentoPrivadoCreador = async () => {
      try {
        const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuento_privado_creador.php`, {
          headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` },
          params: {
            id_cuento: props.id_cuento,
          }
        });

        if (response.data) {
          console.log("Datos obtenidos:", response.data);
          cuento.value = response.data.cuento;
          aportaciones.value = response.data.aportaciones.map((aportacion: { autor: string, contenido: string}) => ({
            ...aportacion,
            contenido: convertDeltaToHtml(aportacion.contenido)
          }));
          id_aportacion.value = response.data.id_aportacion || null;

          console.log("Datos: ", cuento.value, aportaciones.value, id_aportacion.value);
        }
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      } catch (error: any) {
        if (error.status === 404) {
          alert("Cuento no encontrado.");
        } else if (error.status === 403) {
          alert("No tienes permiso para ver este cuento.");
        } else {
          console.error("Error al obtener el cuento:", error);
          alert("Error al obtener el cuento. Por favor, inténtalo de nuevo más tarde.");
        }
        // Redirigir al usuario al panel de inicio
        router.push('/mis_cuentos');
        return;
      }
    }

    const eliminarAportacion = async () => {
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

    const navegarAportacion = () => {
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

    onMounted(() => {
      if (!props.id_cuento) {
        alert("No tienes permiso para ver este cuento.");
        router.push('/mis_cuentos');
        return;
      }
      loading.value = true;
      obtenerCuentoPrivadoCreador();
      loading.value = false;
    });

    return {
      cuento,
      aportaciones,
      id_aportacion,
      showDeleteAportacionPopup,
      loading,
      obtenerCuentoPrivadoCreador,
      eliminarAportacion,
      navegarAportacion
    };
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

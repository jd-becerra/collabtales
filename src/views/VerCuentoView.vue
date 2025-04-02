<!-- eslint-disable vue/no-v-text-v-html-on-component -->
<template>
  <v-overlay :value="loading" absolute>
    <p>Loading</p>
    <v-progress-circular indeterminate color="primary"></v-progress-circular>
  </v-overlay>
  
  <v-container class="vista-cuento" v-if="!loading">
    <v-btn color="primary" class="mb-4" :to="'/panel_inicio'">
      <v-icon left>mdi-arrow-left</v-icon>
      Volver a Mis Cuentos
    </v-btn>

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
          <v-list-item v-for="aportacion in aportaciones" :key="aportacion.id_aportacion" class="aportacion-item">
              <v-list-item-title class="text-body-1 font-weight-bold">{{ aportacion.nombre_alumno }}</v-list-item-title>
              <v-divider></v-divider>
              <v-list-item-title v-html="aportacion.contenido" class="contenido text-body-2"></v-list-item-title>
          </v-list-item>
        </v-list>
        <p v-else class="no-aportaciones">Actualmente no existen aportaciones en este cuento.</p>
      </v-card-text>
    </v-card>

    <!-- Botón para editar cuento -->
    <v-btn color="blue" class="mt-4 float-right mr-2" :to="'/editar_cuento'">Editar Cuento</v-btn>

    <!-- Botón para editar aportación -->
    <v-btn color="green" class="mt-4 float-right" @click="navegarAportacion()">Editar Aportación</v-btn>

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

<script lang="ts">
import axios from 'axios';
import { QuillDeltaToHtmlConverter } from 'quill-delta-to-html';

function convertDeltaToHtml(contenido: string | object): string {
  const delta = typeof contenido === 'string' ? JSON.parse(contenido) : contenido;
  const converter = new QuillDeltaToHtmlConverter(delta.ops, {});
  const html = converter.convert();
  return html;
}

export default {
  name: 'CuentoView',
  data() {
    return {
      cuento: {} as { id: number; nombre: string; descripcion: string } | null,
      aportaciones: [] as Array<{ id_aportacion: number; contenido: string; nombre_alumno: string }>,
      showDeleteAportacionPopup: false,
      id_cuento: localStorage.getItem("id_cuento") || null,
      loading: false, // New loading state
    };
  },
  async mounted() {
    this.id_cuento = localStorage.getItem("id_cuento");
    const id_alumno = localStorage.getItem("id_alumno")

    if (!this.id_cuento || !id_alumno) {
      alert("No tienes permiso para ver este cuento.");
      this.$router.push('/panel_inicio');
      return;
    } 

    this.loading = true;
    try {
      await Promise.all([this.obtenerCuento(), this.obtenerAportaciones()]);
    } catch (error) {
      console.error("Error cargando la vista del cuento:", error);
    } finally {
      this.loading = false;
    }
  },
  methods: {
    async obtenerCuento() {
      try {
        const response = await axios.get('/php/obtener_vista_cuento.php', {
          params: { id_cuento: this.id_cuento }
        });
        this.cuento = response.data.length ? response.data[0] : [];
      } catch (error) {
        console.error("Error al obtener el cuento:", error);
      }
    },
    async obtenerAportaciones() {
      try {
        const response = await axios.get('/php/obtener_aportaciones.php', {
          params: {
            id_cuento: this.id_cuento,
            id_alumno: localStorage.getItem("id_alumno")
          }
        });

        const newAportaciones = response.data.aportaciones.map(({ id_aportacion, contenido, nombre_alumno }: { id_aportacion: number; contenido: string; nombre_alumno: string }) => ({
          id_aportacion,
          contenido: convertDeltaToHtml(contenido),
          nombre_alumno
        }));

        this.aportaciones = [];
        this.$nextTick(() => {
          this.aportaciones = newAportaciones;
        });

        localStorage.setItem("id_aportacion", response.data.id_aportacion_alumno);
      } catch (error) {
        console.error("Error al obtener las aportaciones:", error);
      }
    },
    async eliminarAportacion() {
      this.loading = true;
      try {
        await axios.post('/php/eliminar_aportacion.php', { id_cuento: this.id_cuento });
        this.obtenerAportaciones();
      } catch (error) {
        console.error("Error al eliminar la aportación:", error);
      } finally {
        this.loading = false;
      }
    },
    async navegarAportacion() {
      try {
        if (this.aportaciones.length === 0) {
          alert("No puedes editar una aportación que no existe.");
          return;
        }
        this.$router.push('/editar_aportacion');
      } catch (error) {
        console.error("Error al obtener el ID de la aportación:", error);
      }
    }
  }
};
</script>

<style scoped>
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
</style>

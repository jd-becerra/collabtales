<!-- eslint-disable vue/no-v-text-v-html-on-component -->
<template>
  <v-overlay :value="loading" absolute>
    <p>Loading</p>
    <v-progress-circular indeterminate color="primary"></v-progress-circular>
  </v-overlay>

  <v-container class="vista-cuento" v-if="!loading">
    <div class="d-flex justify-end">
      <v-btn color="primary" class="mb-4 mr-4" :to="'/panel_inicio'">
        Volver a Mis Cuentos
      </v-btn>
        <v-btn class="justify-end" color="danger" @click="descargar">
        Descargar
        </v-btn>
    </div>

    <v-card class="my-4 pa-4" elevation="6" v-if="cuento">
      <v-card-title class="text-h5 font-weight-bold">Título: {{ cuento.nombre }}</v-card-title>
      <v-divider></v-divider>
      <v-card-text class="mt-2">
        <p class="text-body-1 text-wrap">Descripción: {{ cuento.descripcion }}</p>
      </v-card-text>
    </v-card>

    <v-card class="pa-4 aportaciones-card" elevation="6">
      <v-card-title class="text-h6 font-weight-bold" >Aportaciones</v-card-title>
      <v-divider></v-divider>
      <v-card-text class="mt-2">
        <v-list v-if="aportaciones.length > 0">
          <v-list-item v-for="aportacion in aportaciones" :key="aportacion.id_aportacion" class="aportacion-item">
              <v-list-item-title v-html="aportacion.contenido" class="contenido text-wrap"></v-list-item-title>
          </v-list-item>
        </v-list>
        <p v-else class="no-aportaciones">Actualmente no existen aportaciones en este cuento.</p>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script lang="ts">
import axios from 'axios';
import { QuillDeltaToHtmlConverter } from 'quill-delta-to-html';
// @ts-expect-error: TypeScript cannot find module definitions for html2pdf.js
import html2pdf from "html2pdf.js/dist/html2pdf.bundle.min.js"

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
      id_alumno: localStorage.getItem("id_alumno") || null, // Add id_alumno
      es_dueno: false
    };
  },
  async mounted() {
    console.log("ID cuento:", this.id_cuento);
    console.log("ID alumno:", this.id_alumno);
    if (!this.id_cuento || !this.id_alumno) {
      alert("No tienes permiso para ver este cuento.");
      return;
    }
    this.loading = true;
    try {
      const verificar = await this.verificarCuento();
      if (!verificar)
      return;

      await Promise.all([this.obtenerCuento(), this.obtenerAportaciones()]);
    } catch (error) {
      console.error("Error cargando la vista del cuento:", error);
    } finally {
      this.loading = false;
    }
  },
  methods: {
    async verificarCuento() {
      try {
        const response = await axios.post('/php/verificacion.php', {
          id_cuento: this.id_cuento,
          id_alumno: this.id_alumno
        });

        console.log(response.data);

        if (response.data.error) {
          console.log(response.data.error);
          alert(response.data.error);
          this.$router.push('/panel_inicio');
          return false;
        }

        this.es_dueno = response.data.es_dueno;
        return true;
      } catch (error) {
        console.error("Error en la verificación:", error);
        alert("No tienes permiso.");
        this.$router.push('/panel_inicio');
        return false;
      }
    },
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
    async descargar() {
      try {
        if (this.aportaciones.length === 0) {
          alert("No puedes descargar un cuento sin ninguna aportación!");
          return;
        }

        this.loading = true;
        await Promise.all([this.obtenerCuento(), this.obtenerAportaciones()]);

        const contenidoHTML = document.createElement('div');
          contenidoHTML.innerHTML = `
            <div style="font-family: Arial, sans-serif; color: #000; background: #fff; padding: 20px; max-width: 800px; margin: auto;">
              <h1 style="color: #333;">${this.cuento?.nombre}</h1>
              <p style="font-size: 14px; line-height: 1.6;">${this.cuento?.descripcion}</p>
              <hr style="border: 1px solid #ccc;">
              ${this.aportaciones.map(aport => `
                <div style="margin-bottom: 15px; padding: 10px; solid #ddd">
                  <p style="font-size: 14px; color: #555;">${aport.contenido}</p>
                </div>
              `).join('')}
            </div>
          `;

        // 📥 Convertir a PDF y descargarlo
        html2pdf().from(contenidoHTML).save(`${this.cuento?.nombre || 'cuento'}.pdf`);
      } catch (error) {
        console.error("Error descargando", error);
      } finally {
        alert("Cuento descargado")
      }
      console.log("Navigate to panel_inicio");
      this.$router.push('/panel_inicio');
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

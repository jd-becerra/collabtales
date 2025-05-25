<!-- eslint-disable vue/no-v-text-v-html-on-component -->
<template>
  <div class="ver-cuento-publico-view">
    <AppNavbarWhite />
    <v-overlay :value="loading" absolute>
      <p>Loading</p>
      <v-progress-circular indeterminate color="primary"></v-progress-circular>
    </v-overlay>

    <div class="vista-cuento d-flex" v-if="!loading">
      <v-card class="cuento-card pa-4 aportaciones-card" elevation="6" v-if="cuento">
        <h4 class="text-h5 font-weight-bold"> {{ cuento.nombre }}</h4>
        <p class="autores-header" v-if="cuento.autores">
          Autores:
          <span v-for="(autor, index) in cuento.autores" :key="index">
            {{ autor }}{{ index < cuento.autores.length - 1 ? '  |  ' : '' }}
          </span>
        </p>
        <v-divider></v-divider>
        <v-card-text >
          <v-list v-if="aportaciones.length > 0">
            <v-list-item v-for="(aportacion, idx) in aportaciones" :key="idx" class="aportacion-item">
                <v-list-item-title v-html="aportacion.contenido" class="contenido text-wrap"></v-list-item-title>
            </v-list-item>
          </v-list>
          <p v-else class="no-aportaciones">El cuento se encuentra vacío actualmente.</p>
        </v-card-text>
      </v-card>

      <div class="d-flex flex-column">
        <button class="mb-4 mr-4" @click="gotoPanelInicio()">
          <v-img
            src="/icons/chevron-left.svg"
            width="50"
            height="50"
            contain
          />
          VOLVER A MIS CUENTOS
        </button>

        <p v-if="cuento">
          <strong>Descripción:</strong> <i> {{ cuento.descripcion }} </i>
        </p>

        <v-btn class="justify-end" color="danger" @click="descargarPdf() " :disabled="aportaciones.length === 0">
          <v-icon left>mdi-file-download</v-icon>
        Descargar
        </v-btn>
      </div>

    </div>
  </div>
</template>

<script lang="ts">
import '../assets/base.css';

import axios from 'axios';
import { QuillDeltaToHtmlConverter } from 'quill-delta-to-html';
// @ts-expect-error: TypeScript cannot find module definitions for html2pdf.js
import html2pdf from "html2pdf.js/dist/html2pdf.bundle.min.js"
import { ref, onMounted, defineComponent } from 'vue';
import { useRouter } from 'vue-router';
import AppNavbarWhite from '@/components/AppNavbarWhite.vue';

function convertDeltaToHtml(contenido: string | object): string {
  const delta = typeof contenido === 'string' ? JSON.parse(contenido) : contenido;
  const converter = new QuillDeltaToHtmlConverter(delta.ops, {});
  const html = converter.convert();
  return html;
}

export default defineComponent({
  name: 'VerCuentoPublicoView',
  components: {
    AppNavbarWhite
  },
  props: {
    id_cuento: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const cuento = ref<{ id: number; nombre: string; descripcion: string, autores: string[] } | null>(null);
    const aportaciones = ref<Array<{ contenido: string }>>([]);
    const loading = ref(false);
    const route = useRouter();

    const obtenerCuentoPublico = async () => {
      try {
        const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuento_publico.php`, {
          params: { id_cuento: props.id_cuento }
        });
        if (response.status === 200) {
          cuento.value = response.data.cuento;
          aportaciones.value = response.data.aportaciones
            .map((aport: { contenido: string }) => ({
              ...aport,
              contenido: convertDeltaToHtml(aport.contenido)
            }))
            .filter((aport: { contenido: string }) => aport.contenido.trim() !== '');
        } else {
          alert("No se pudo obtener el cuento.");
          return
        }
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
        route.push('/panel_inicio');
        return;
      }
    }

    const descargarPdf = async () => {
      try {
        if (!cuento.value) {
          alert("No hay cuento para descargar.");
          return;
        }

        if (aportaciones.value.length === 0) {
          alert("No puedes descargar un cuento sin ninguna aportación!");
          return;
        }

        loading.value = true;

        const contenidoHTML = document.createElement('div');
          contenidoHTML.innerHTML = `
            <div style="font-family: Arial, sans-serif; color: #000; background: #fff; padding: 20px; max-width: 800px; margin: auto;">
              <h1 style="color: #333;">${cuento.value?.nombre}</h1>
              <p style="font-size: 14px; line-height: 1.6;">${cuento.value?.descripcion}</p>
              <hr style="border: 1px solid #ccc;">
              ${aportaciones.value.map(aport => `
                <div style="margin-bottom: 15px; padding: 10px; solid #ddd">
                  <p style="font-size: 14px; color: #555;">${aport.contenido}</p>
                </div>
              `).join('')}
            </div>
          `;

        html2pdf().from(contenidoHTML).save(`${cuento.value?.nombre || 'cuento'}.pdf`);
      } catch (error) {
        console.error("Error descargando", error);
      } finally {
        alert("Cuento descargado");
      }
      loading.value = false;
    }

    const  gotoPanelInicio = () => {
      route.push('/panel_inicio');
    }

    onMounted(() => {
      if (!props.id_cuento) {
        alert("No tienes permiso para ver este cuento.");
        return;
      }

      obtenerCuentoPublico();
    });

    return {
      cuento,
      aportaciones,
      loading,
      descargarPdf,
      gotoPanelInicio
    };
  }
});
</script>

<style scoped>
.ver-cuento-publico-view {
  background-color: var(--color-background-padding);
  width: 100%;
  height: 100%;
}

.vista-cuento {
  display: flex;
  flex-direction: row;
  align-items: baseline;
  padding: 1rem;
  padding-left: 5rem;
  padding-right: 5rem;

  gap: 2rem;
}

.cuento-card {
  width: 100%;
  height: 75vh;
  overflow-y: auto;
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
</style>

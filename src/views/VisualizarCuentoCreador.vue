<!-- eslint-disable vue/no-v-text-v-html-on-component -->
<template>
  <div class="visualizar-creador-view">
    <AppNavbarWhite />
    <v-overlay :value="loading" absolute>
      <p>Loading</p>
      <v-progress-circular indeterminate color="primary"></v-progress-circular>
    </v-overlay>

    <div class="vista-cuento d-flex" v-if="!loading">
      <v-card class="cuento-card pa-4 aportaciones-card" elevation="6" v-if="cuento">
        <h4 class="cuento-nombre text-h4 font-weight-bold"> {{ cuento.nombre }}</h4>
        <p class="autores-header mb-3" v-if="cuento.autores">
          Autores:
          <span v-for="(autor, index) in cuento.autores" :key="index">
            {{ autor }}{{ index < cuento.autores.length - 1 ? '  |  ' : '' }}
          </span>
        </p>
        <div class="divider"></div>
        <v-list v-if="aportaciones.length > 0" class="contenido">
          <v-list-item v-for="(aportacion, idx) in aportaciones" :key="idx" class="aportacion-item">
              <v-list-item-title v-html="aportacion.contenido" class="text-wrap"></v-list-item-title>
          </v-list-item>
        </v-list>
        <p v-else class="no-aportaciones">El cuento se encuentra vacío actualmente.</p>
      </v-card>

      <div class="options-container d-flex flex-column">
        <ReturnBtn @click="gotoCuento">VOLVER A PANEL DE INICIO</ReturnBtn>
        <p class="cuento-descripcion" v-if="cuento">
          <strong>Descripción:</strong> <i> {{ cuento.descripcion }} </i>
        </p>
        <div class="move-bottom">
          <BotonSm
            class="return-btn w-100"
            @click="showConfirmarOcultarCuento = true"
            icon_path="/icons/public_off.svg"
            color_type="white_gray"
          >
            Ocultar Cuento
          </BotonSm>
          <BotonSm
            class="download-btn w-100"
            @click="descargarPdf() "
            icon_path="/icons/download.svg"
            :disabled="aportaciones.length === 0"
            >
            Descargar en PDF
          </BotonSm>
        </div>
      </div>
    </div>

    <v-dialog v-model="showConfirmarOcultarCuento" max-width="400" class="options-dialog">
      <ConfirmacionOcultarCuento
        v-if="showConfirmarOcultarCuento"
        :id_cuento="Number(id_cuento)"
        :nombre_cuento="cuento?.nombre || ''"
        @close-popup="showConfirmarOcultarCuento = false"
      />
    </v-dialog>
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
import BotonSm from '@/components/BotonSm.vue';
import ReturnBtn from '@/components/ReturnBtn.vue';
import ConfirmacionOcultarCuento from '@/components/ConfirmacionOcultarCuento.vue';
import DOMPurify from 'dompurify';

function convertDeltaToHtml(contenido: string | object): string {
  const delta = typeof contenido === 'string' ? JSON.parse(contenido) : contenido;
  const converter = new QuillDeltaToHtmlConverter(delta.ops, {});
  const html = converter.convert();
  return DOMPurify.sanitize(html, {
    ALLOWED_TAGS: ['b', 'i', 'u', 'strike', 'span', 'ul', 'ol', 'li', 'a'],
    ALLOWED_ATTR: ['href', 'style'],
  });
}

export default defineComponent({
  name: 'PrevisualizarCuentoCreador',
  components: {
    AppNavbarWhite,
    BotonSm,
    ReturnBtn,
    ConfirmacionOcultarCuento
  },
  props: {
    id_cuento: {
      type: String,
      required: true
    },
  },
  setup(props) {
    const cuento = ref<{ nombre: string; descripcion: string, autores: string[] } | null>(null);
    const aportaciones = ref<Array<{ contenido: string }>>([]);
    const loading = ref(false);
    const showConfirmarOcultarCuento = ref(false);
    const route = useRouter();

    const obtenerCuentoPrevisualizar = async () => {
      try {
        const response = await axios.get(`${import.meta.env.VUE_APP_SERVER}/php/obtener_cuento_previsualizar.php`, {
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
          route.push('/mis_cuentos');
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
        route.push('/mis_cuentos');
        return;
      }
    }

    const descargarPdf = async () => {
      try {
        if (!cuento.value || aportaciones.value.length === 0) {
          alert("Cuento no disponible para descargar.");
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

    const  gotoCuento = () => {
      route.push('/ver_cuento_creado/' + props.id_cuento);
    }

    onMounted(() => {
      if (!props.id_cuento) {
        alert("No tienes permiso para ver este cuento.");
        return;
      }

      obtenerCuentoPrevisualizar();
    });

    return {
      cuento,
      aportaciones,
      loading,
      descargarPdf,
      gotoCuento,
      showConfirmarOcultarCuento,
    };
  }
});
</script>

<style scoped>
.visualizar-creador-view {
  background-color: var(--color-background-padding);
  width: 100%;
  height: 100%;
}

.vista-cuento {
  display: flex;
  flex-direction: row;
  padding: 1.5rem;
  padding-left: 5rem;
  padding-right: 5rem;

  gap: 2rem;
}

.cuento-card {
  width: 100%;
  height: 70vh;
  overflow-y: auto;
  border: 1px solid var(--color-text-input-fg-default);
  background-color: var(--color-text-input-bg-default);
  margin-top: 1rem;
}

.contenido {
  width: 100%;
  padding: 0;
  margin: 0;

  background-color: transparent;
}

.cuento-nombre {
  color: var(--color-text-blue);
}

.no-aportaciones {
  text-align: center;
  font-size: 1.2em;
  color: var(--vt-c-gray-dark);
}

.options-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 70vh;

  padding: 0;
  margin: 0;

}

.move-bottom {
  width: 100%;
  height: 100%;

  display: flex;
  flex-direction: column;       /* Stack children vertically */
  justify-content: flex-end;
  gap: 1rem;
}

.divider {
  border: 1px solid var(--vt-c-gray-soft);
  margin-top: 1rem;
  margin-bottom: 1rem;
}
</style>

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
        <h4 class="cuento-nombre text-h4 font-weight-bold"> {{ cuento.nombre }}</h4>
        <v-chip
          class="like-chip mt-4"
          label
        >
          <v-img
            src="/icons/like_filled.svg"
            width="16"
            height="16"
            class="mr-2"
          ></v-img>
          <strong>{{ cuento?.likes }}</strong> &nbsp; {{ cuento?.likes === 1 ? $t('cuento_publico.like_count_1') : $t('cuento_publico.like_count') }}
        </v-chip>
        <p class="autores-header mb-3" v-if="cuento.autores">
          {{ cuento.autores.length === 1 ? $t('cuento_publico.author') : $t('cuento_publico.authors') }}:
          <span v-for="(autor, index) in cuento.autores" :key="index">
            {{ autor }}{{ index < cuento.autores.length - 1 ? '  &#x2022;  ' : '' }}
          </span>
        </p>
        <div class="divider"></div>
        <v-list v-if="aportaciones.length > 0" class="contenido">
          <v-list-item v-for="(aportacion, idx) in aportaciones" :key="idx" class="aportacion-item">
              <v-list-item-title v-html="aportacion.contenido" class="text-wrap"></v-list-item-title>
          </v-list-item>
        </v-list>
        <p v-else class="no-aportaciones">{{ $t('cuento_publico.empty') }}</p>
      </v-card>

      <div class="options-container d-flex flex-column">
        <ReturnBtn @click="goToCuento">
          {{ $t('cuento_publico.return_colaborador') }}
        </ReturnBtn>
        <div class="text-left">
          <p class="cuento-descripcion" v-if="cuento">
            <strong>{{ $t('cuento_publico.description') }}:</strong> <i> {{ cuento.descripcion }} </i>
          </p>
        </div>
        <div class="move-bottom">
          <BotonSm
            class="download-btn w-100"
            @click="descargarPdf() "
            icon_path="/icons/download.svg"
            :disabled="aportaciones.length === 0"
            >
            {{ $t('cuento_publico.download_pdf') }}
          </BotonSm>
        </div>
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
import { useI18n } from 'vue-i18n';

import AppNavbarWhite from '@/components/AppNavbarWhite.vue';
import BotonSm from '@/components/BotonSm.vue';
import ReturnBtn from '@/components/ReturnBtn.vue';
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
  name: 'VerCuentoPublicoView',
  components: {
    AppNavbarWhite,
    BotonSm,
    ReturnBtn
  },
  props: {
    id_cuento: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const cuento = ref<{
      nombre: string;
      descripcion: string,
      autores: string[],
      likes: number,
      has_liked: boolean
    } | null>(null);
    const aportaciones = ref<Array<{ contenido: string }>>([]);
    const loading = ref(false);
    const errorMsg = ref('');
    const route = useRouter();
    const { t } = useI18n();

    const obtenerCuentoPublico = async () => {
      try {
        const response = await axios.get(`https://collabtalesserver.avaldez0.com/php/obtener_cuento_publico.php`, {
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
          alert("Could not fetch this tale. Please try again later.");
          return
        }
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      } catch (error: any) {
        if (error.status === 404) {
          alert("Resource not found");
        } else if (error.status === 403) {
          alert("You do not have permission to access this resource.");
        } else {
          alert("Am unexpected error occurred while fetching this tale. Please try again later.");
        }
        // Redirigir al usuario al panel de inicio
        route.push('/cuentos_publicados');
        return;
      }
    }

    const descargarPdf = async () => {
      try {
        if (!cuento.value) {
          alert("No tale data available to download.");
          return;
        }

        if (aportaciones.value.length === 0) {
          alert("You cannot download a tale without contributions.");
          return;
        }

        loading.value = true;

        const contenidoHTML = document.createElement('div');
          contenidoHTML.innerHTML = `
            <div style="font-family: Arial, sans-serif; color: #000; background: #fff; padding: 20px; max-width: 800px; margin: auto;">
              <h1 style="color: #333;">${cuento.value?.nombre}</h1>
              <p style="font-size: 14px; color: #555;">${cuento.value?.autores.length === 1 ? t('publish_tale.author') : t('publish_tale.authors')}:
              ${cuento.value?.autores.join(', ')}</p>
              <hr style="border: 1px solid #ccc; margin: 20px 0;">
              ${aportaciones.value.map(aport => `
                <div style="margin-bottom: 15px; padding: 10px; solid #ddd">
                  <p style="font-size: 14px; color: #555;">${aport.contenido}</p>
                </div>
              `).join('')}
            </div>
          `;

        html2pdf().from(contenidoHTML).save(`${cuento.value?.nombre || 'cuento'}.pdf`);
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      } catch (error) {
        console.error("An unexpected error occurred while downloading the tale");
      }

      loading.value = false;
    }

    const  goToCuento = () => {
      route.push('/ver_cuento_colaborador/' + props.id_cuento);
    }

    onMounted(() => {
      if (!props.id_cuento) {
        alert("You do not have permission to view this tale.");
        return;
      }

      obtenerCuentoPublico();
    });

    return {
      cuento,
      aportaciones,
      loading,
      errorMsg,
      descargarPdf,
      goToCuento,
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
  width: 85%;
}

.no-aportaciones {
  text-align: center;
  font-size: 1.2em;
  color: var(--vt-c-gray-dark);
}

.options-container {
  display: flex;
  height: 70vh;
  max-width: 30%;
  min-width: 30%;

  padding: 0;
  margin: 0;

}

.likes-container {
  display: inline-flex;
  width: 100%;
  justify-content: center;
  align-items: center;
}

.like-btn {
  width: 100%;
  color: var(--color-text-input-fg-default);
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.move-bottom {
  width: 100%;
  height: 100%;

  display: flex;
  flex-direction: column;       /* Stack children vertically */
  justify-content: flex-end;
}

.divider {
  border: 1px solid var(--vt-c-gray-soft);
  margin-top: 1rem;
  margin-bottom: 1rem;
}

.error-msg {
  color: var(--color-error);
  margin-bottom: 0.5rem;
}

.like-chip {
  position: absolute;
  top: 0;
  right: 1rem;
  z-index: 10;
}
</style>

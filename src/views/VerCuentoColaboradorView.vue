<!-- eslint-disable vue/no-v-text-v-html-on-component -->
<template>
  <AppNavbar />

  <v-overlay :value="loading" absolute>
    <p>Loading</p>
    <v-progress-circular indeterminate color="primary"></v-progress-circular>
  </v-overlay>

    <div class="main-container">
      <div>
        <div v-if="cuento">
          <h2 class="cuento-titulo text-h4 font-weight-bold">Cuento: {{ cuento.nombre }}</h2>
          <p class="text-body-1 mb-6">Descripción: {{ cuento.descripcion }}</p>

          <div class="d-flex justify-space-between align-center">
            <h3 class="aportaciones-header text-h6">Aportaciones: </h3>
          </div>
        </div>
          <v-card class="aportaciones-card" flat>
            <div class="aportaicones-list" v-if="aportaciones.length > 0">
              <div v-for="(aportacion, idx) in aportaciones" :key="idx">
                <AportacionAutorItem
                  v-if="aportacion.es_autor"
                  :id_cuento="id_cuento !== null ? Number(id_cuento) : null"
                  :id_aportacion="id_aportacion !== null ? Number(id_aportacion) : null"
                  :contenido="aportacion.contenido"
                  :first_in_list="idx === 0"
                />
                <AportacionItem
                  v-else
                  :id_cuento="id_cuento !== null ? Number(id_cuento) : null"
                  :id_aportacion="id_aportacion !== null ? Number(id_aportacion) : null"
                  :contenido="aportacion.contenido"
                  :autor="aportacion.autor"
                  :first_in_list="idx === 0"
                />
              </div>
            </div>
            <p v-else class="no-aportaciones">Actualmente no existen aportaciones en este cuento.</p>
          </v-card>
        </div>
      <div class="options-container">
        <ReturnBtn @click="goToMisCuentos()">VOLVER A MIS CUENTOS</ReturnBtn>

        <v-card class="info-card" >
          <h3 class="colaboradores-header">Colaboradores:</h3>
          <ol class="colaboradores-list">
            <li class="colaboradores-item" v-for="(aportacion, idx) in aportaciones" :key="idx">
              {{ aportacion.autor }}
            </li>
          </ol>

        </v-card>

        <div class="buttons-container">
          <!-- <BotonSm class="cuento-btn" icon_path="/icons/visibility.svg">
            Previsualizar cuento
          </BotonSm> -->
          <BotonSm
            class="cuento-btn"
            icon_path="/icons/delete_forever.svg"
            color_type="white_red"
            @click="showAbandonarCuento = true"
          >
            Abandonar cuento
          </BotonSm>
        </div>
      </div>

    <v-dialog v-model="showAbandonarCuento" max-width="400" class="options-dialog">
      <ConfirmacionAbandonarCuento
        v-if="showAbandonarCuento"
        :id_cuento="Number(id_cuento)"
        :nombre_cuento="cuento?.nombre ?? ''"
        @close-popup="showAbandonarCuento = false"
      />
    </v-dialog>
    </div>
</template>

<script lang="ts">
import { ref, onMounted, defineComponent } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import AppNavbar from '@/components/AppNavbar.vue';
import BotonSm from '@/components/BotonSm.vue';
import ReturnBtn from '@/components/ReturnBtn.vue';
import AportacionItem from '@/components/AportacionItem.vue';
import AportacionAutorItem from '@/components/AportacionAutorItem.vue';
import { QuillDeltaToHtmlConverter } from 'quill-delta-to-html';
import ConfirmacionAbandonarCuento from '@/components/ConfirmacionAbandonarCuento.vue';
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
  name: 'VerCuentoCreadoView',
  components: {
    AppNavbar,
    BotonSm,
    ReturnBtn,
    AportacionItem,
    AportacionAutorItem,
    ConfirmacionAbandonarCuento
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
      } | null>(null);
    const aportaciones = ref<Array<{ autor: string; contenido: string; es_autor: boolean }>>([]);
    const id_aportacion = ref<string | null>(null);
    const showAbandonarCuento = ref(false);
    const loading = ref(false);
    const id_cuento = props.id_cuento;

    const obtenerCuentoPrivadoColaborador = async () => {
      try {
        const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuento_privado_colaborador.php`, {
          headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` },
          params: {
            id_cuento: id_cuento
          }
        });

        if (response.data) {
          cuento.value = response.data.cuento;
          aportaciones.value = response.data.aportaciones.map((aportacion: { autor: string, contenido: string}) => ({
            ...aportacion,
            contenido: convertDeltaToHtml(aportacion.contenido)
          }));
          id_aportacion.value = response.data.id_aportacion || null;
        }
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      } catch (error: any) {
        router.push('/mis_cuentos');
        if (error.status === 404) {
          alert("Cuento no encontrado.");
        } else if (error.status === 403) {
          alert("No tienes permiso para ver este cuento.");
        } else {
          alert("Error al obtener el cuento. Por favor, inténtalo de nuevo más tarde.");
          console.error("Error al obtener el cuento:", error);
        }
        return;
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

    const goToMisCuentos = () => {
      router.push('/mis_cuentos');
    }

    onMounted(() => {
      if (!props.id_cuento) {
        router.push('/mis_cuentos');
        alert("No tienes permiso para ver este cuento.");
        return;
      }
      loading.value = true;
      obtenerCuentoPrivadoColaborador();
      loading.value = false;
    });

    return {
      cuento,
      aportaciones,
      id_aportacion,
      showAbandonarCuento,
      loading,
      obtenerCuentoPrivadoColaborador,
      navegarAportacion,
      goToMisCuentos,
    };
  }
});
</script>

<style scoped>
.main-container {
  display: flex;
  justify-content: space-between;
  padding: 2rem;
  gap: 3rem;
}

.aportaciones-card {
  border: 1px solid var(--color-text-input-fg-default);
  background-color: var(--color-text-input-bg-default);
  padding: 2rem;
}

.cuento-titulo {
  font-size: var(--font-main-header-size);
  color: var(--color-text-blue);
}

.aportaciones-header {
  font-weight: bold;
}

.aportaciones-list {
  display: flex;
  flex-direction: column;
}

.info-card {
  padding: 1rem;
  background-color: var(--vt-c-white-soft);
  border: 1px solid var(--vt-c-gray-soft);
  border-radius: var(--border-radius-default);

  display: flex;
  flex-direction: column;
}

.colaboradores-list {
  max-height: 220px;
  overflow-y: scroll;
  border: 1px solid var(--vt-c-gray-soft);
  padding: 2rem;
  padding-top: 0.5rem;

  display: flex;
  flex-direction: column;
  align-items: baseline;
}

.colaboradores-item {
  margin: 0;
  padding: 0;
}

.buttons-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 2.5rem;
}

.cuento-btn {
  width: auto;
}

</style>

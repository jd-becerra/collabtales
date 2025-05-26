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
            <h2
              class="publicado-header d-flex align-center"
              :style="{
                color: cuento.publicado === 1 ? getCSSVar('--color-text-blue') : getCSSVar('--color-text-off'),
              }"
            >
              <v-img
                class="mr-2"
                :src="cuento.publicado === 1 ? '/icons/public.svg' : '/icons/public_off.svg'"
                contain
                width="16"
                height="16"
              />
              {{ cuento.publicado === 1 ? 'TU CUENTO ES PÚBLICO' : 'TU CUENTO ES PRIVADO' }}
            </h2>
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
          <h3 class="codigo-header">CÓDIGO: {{ cuento?.codigo_compartir }}</h3>
          <p class="codigo-subheader">Comparte este código para añadir colaboradores. Modifica esta opción en “Gestionar colaboradores".</p>
          <div class="divider"></div>



          <h3 class="colaboradores-header">Colaboradores:</h3>
          <ol class="colaboradores-list">
            <li class="colaboradores-item" v-for="(aportacion, idx) in aportaciones" :key="idx">
              {{ aportacion.autor }}
            </li>
          </ol>

        </v-card>

        <div class="buttons-container">
          <BotonSm class="cuento-btn" icon_path="/icons/visibility.svg" v-if="cuento?.publicado === 1">
            Visualizar cuento
          </BotonSm>
          <BotonSm class="cuento-btn" color_type="white_purple" icon_path="/icons/share.svg" v-else>
            Publicar cuento
          </BotonSm>
          <BotonSm class="cuento-btn" icon_path="/icons/edit_note.svg" color_type="white_purple">
            Modificar cuento
          </BotonSm>
          <BotonSm
            class="cuento-btn"
            icon_path="/icons/groups.svg"
            color_type="white_purple"
            icon_size="24"
            gap="0.5rem"
          >
            Gestionar colaboradores
          </BotonSm>
          <BotonSm class="cuento-btn" icon_path="/icons/delete_forever.svg" color_type="white_red" @click="showDeleteAportacionPopup = true">
            Eliminar cuento
          </BotonSm>
        </div>
      </div>
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
    BotonSm,
    ReturnBtn,
    AportacionItem,
    AportacionAutorItem,
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
    const id_cuento = props.id_cuento;

    const obtenerCuentoPrivadoCreador = async () => {
      try {
        const response = await axios.get(`${import.meta.env.VITE_PHP_SERVER}/php/obtener_cuento_privado_creador.php`, {
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
        }
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

    const goToMisCuentos = () => {
      router.push('/mis_cuentos');
    }

    const getCSSVar = (varName: string) => {
      return getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    }

    onMounted(() => {
      if (!props.id_cuento) {
        router.push('/mis_cuentos');
        alert("No tienes permiso para ver este cuento.");
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
      navegarAportacion,
      goToMisCuentos,
      getCSSVar,
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

.publicado-header {
  font-size: var(--font-state-size);
  font-weight: bold;
}

.aportaciones-header {
  font-weight: bold;
}

.aportaciones-list {
  display: flex;
  flex-direction: column;
}

.codigo-header {
  font-weight: bold;
  margin-bottom: 0.1rem;
}

.codigo-subheader {
  font-size: 0.9rem;
  line-height: 1.3;
}

.info-card {
  padding: 1rem;
  background-color: var(--vt-c-white-soft);
  border: 1px solid var(--vt-c-gray-soft);
  border-radius: var(--border-radius-default);

  display: flex;
  flex-direction: column;
}

.divider {
  border-top: 1px solid var(--vt-c-gray-soft);
  margin-top: 1rem;
  margin-bottom: 1rem;
}

.colaboradores-list {
  max-height: 120px;
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

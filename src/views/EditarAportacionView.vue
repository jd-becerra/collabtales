<template>
  <div class="root-editar">
    <AppNavbarWhite />
    <ReturnBtn class="return-btn" @click="goToCuento">
      REGRESAR AL CUENTO
    </ReturnBtn>
    <div class="editar-aportacion-view">
      <v-overlay :value="loading">
        <v-progress-circular indeterminate size="64"></v-progress-circular>
      </v-overlay>
      <!-- Sección con el cuento en su estado actual -->
      <div class="vista-cuento">
        <p>El cuento hasta el momento:</p>
        <v-card class="cuento-card">
          <v-card-text>
            <div id="lectura" v-if="aportaciones.length === 0">
              <p class="no-aportaciones">El cuento se encuentra vacío actualmente.</p>
            </div>
            <div id="lectura" v-else>
              <v-list>
                <v-list-item v-for="(aportacion, idx) in aportaciones" :key="idx" class="aportacion-item">
                  <p
                    v-if="aportacion.es_autor"
                    class="aportacion-autor"
                  >
                    -- TU APORTACIÓN EMPIEZA AQUÍ --
                  </p>
                  <div v-html="aportacion.contenido" class="text-wrap"></div>
                  <p
                    v-if="aportacion.es_autor"
                    class="aportacion-autor"
                  >
                    -- TU APORTACIÓN TERMINA AQUÍ --
                  </p>
                </v-list-item>
              </v-list>
            </div>
          </v-card-text>
        </v-card>
      </div>
      <!-- Sección con el texto a editar usando Quill.js -->
      <div class="vista-editar-aportacion">
        <p>Editar Aportación:</p>
        <v-card class="editor-card">
          <v-card-text class="editor-card-text">
            <!-- Quill Editor Container -->
            <div id="quill-container" v-show="!loading">
              <!-- Toolbar for Quill.js -->
              <div id="toolbar">
                  <button class="ql-bold" aria-label="Negrita" title="Negrita"></button>
                  <button class="ql-italic" aria-label="Cursiva" title="Cursiva"></button>
                  <button class="ql-underline" aria-label="Subrayado" title="Subrayado"></button>
                  <button class="ql-strike" aria-label="Tachado" title="Tachado"></button>
                  <select class="ql-color" aria-label="Color de texto" title="Color de texto"></select>
                  <select class="ql-background" aria-label="Color de fondo" title="Color de fondo"></select>
                  <button class="ql-list" value="ordered" aria-label="Lista ordenada" title="Lista ordenada"></button>
                  <button class="ql-list" value="bullet" aria-label="Lista con viñetas" title="Lista con viñetas"></button>
                  <select class="ql-align" aria-label="Alinear texto" title="Alinear texto"></select>
                  <button class="ql-link" aria-label="Insertar enlace" title="Insertar enlace"></button>
              </div>
              <div id="editor"></div>
            </div>
          </v-card-text>
        </v-card>

        <!-- Botón para guardar cambios -->
        <div class="d-flex justify-end">
          <BotonXs
            color_type="white_green"
            @click="guardarCambios"
            :disabled="loading"
          >
              Guardar Cambios
          </BotonXs>
                </div>
        </div>
    </div>
  </div>
</template>

<script lang="ts">
import '../assets/base.css';

import { defineComponent, ref, onMounted, nextTick, onBeforeUnmount } from 'vue';
import axios from 'axios';
import Quill from 'quill';
import Delta from 'quill-delta';
import { QuillDeltaToHtmlConverter } from 'quill-delta-to-html';
import 'quill/dist/quill.snow.css';
import { useRouter } from 'vue-router';
import DOMPurify from 'dompurify';

import AppNavbarWhite from '@/components/AppNavbarWhite.vue';
import ReturnBtn from '@/components/ReturnBtn.vue';
import BotonXs from '@/components/BotonSm.vue';

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
  name: 'EditarAportacionView',
  components: {
    AppNavbarWhite,
    ReturnBtn,
    BotonXs,
  },
  props: {
    id_aportacion: {
      type: String,
      required: true,
    },
    id_cuento: {
      type: String,
      required: true,
    },
  },
  setup(props) {
    const loading = ref(false);
    const quill = ref<Quill | null>(null);
    const contenidoInicial = ref<Delta>(new Delta());
    const es_creador = ref<boolean>(false);
    const aportaciones = ref([{
      contenido: '',
      es_autor: false,
    }]);
    const CHARACTER_MAX= 8000;

    const router = useRouter();

    const destroyQuill = () => {
      if (quill.value) {
        try {
          // Remove all event listeners and clean up
          const container = quill.value.container;
          if (container) {
            container.innerHTML = '';
          }
          quill.value = null;
        } catch (error) {
          console.warn('Error destroying Quill instance:', error);
        }
      }
    };

    const initializeQuill = async () => {
      // Clean up any existing instance
      destroyQuill();

      await nextTick();

      // Wait a bit more to ensure DOM is fully ready
      await new Promise(resolve => setTimeout(resolve, 100));

      const editorElement = document.getElementById('editor');
      const toolbarElement = document.getElementById('toolbar');

      if (!editorElement || !toolbarElement) {
        console.error('Editor or toolbar element not found');
        return;
      }

      // Clear the editor element completely
      editorElement.innerHTML = '';

      try {
        // Initialize Quill with a clean state
        quill.value = new Quill(editorElement, {
          modules: {
            toolbar: {
              container: toolbarElement,
              handlers: {
                // Custom handlers to prevent issues
              }
            }
          },
          theme: 'snow',
          placeholder: 'Escribe tu aportación aquí...',
          formats: ['bold', 'italic', 'underline', 'strike', 'color', 'background', 'list', 'align', 'link']
        });

        // Wait for Quill to be fully initialized
        await nextTick();

        // Set initial content if available
        if (quill.value && contenidoInicial.value && contenidoInicial.value.ops) {
          try {
            quill.value.setContents(contenidoInicial.value);
            console.log(quill.value.getLength());
          } catch (error) {
            console.warn('Error setting initial content:', error);
            // If there's an error with the delta, just insert as plain text
            const plainText = contenidoInicial.value.ops
              ?.map(op => typeof op.insert === 'string' ? op.insert : '')
              .join('') || '';
            if (plainText) {
              quill.value.setText(plainText);
            }
          }
        }

        // Focus after a short delay
        setTimeout(() => {
          if (quill.value) {
            try {
              quill.value.focus();
            } catch (error) {
              console.warn('Error focusing editor:', error);
            }
          }
        }, 500);

      } catch (error) {
        console.error('Error initializing Quill:', error);
      }
    };

    const fetchAportacion = async () => {
      if (!props.id_cuento || !props.id_aportacion) {
        alert('No hay aportación seleccionada.');
        router.push('/mis_cuentos');
        return;
      }
      loading.value = true;
      try {
        const response = await axios.get('https://collabtalesserver.avaldez0.com/php/obtener_aportacion_individual.php', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
          },
          params: {
            id_cuento: props.id_cuento,
            id_aportacion: props.id_aportacion,
          },
        });

        if (response.status === 200) {
          aportaciones.value = response.data.aportaciones.map((aport: { contenido: string, es_autor: boolean }) => ({
            ...aport,
            contenido: convertDeltaToHtml(aport.contenido),
          }));
          es_creador.value = response.data.es_creador;

          // Parse contenido_autor safely
          try {
            contenidoInicial.value = response.data.contenido_autor
              ? JSON.parse(response.data.contenido_autor)
              : new Delta();
          } catch (parseError) {
            console.warn('Error parsing contenido_autor:', parseError);
            contenidoInicial.value = new Delta();
          }

          // Initialize Quill after all data is processed
          loading.value = false;
          await nextTick();
          await initializeQuill();
        } else {
          alert('No se pudo obtener la aportación. Por favor, inténtalo de nuevo.');
          router.push('/mis_cuentos');
        }
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      } catch (error: any) {
        console.error('Fetch error:', error);
        switch (error.status) {
          case 400:
            alert('Error al mandar la petición. Por favor, inténtalo de nuevo.');
            break;
          case 401:
            alert('Petición no autorizada. Por favor, inicia sesión de nuevo.');
            localStorage.removeItem('token');
            router.push('/');
            break;
          case 403:
            alert('No tienes permiso para editar esta aportación.');
            break;
          case 404:
            alert('Aportación no encontrada. Por favor, verifica el ID de la aportación.');
            break;
          case 500:
            alert('Error interno del servidor. Por favor, inténtalo de nuevo más tarde.');
            break;
          default:
            alert('Error inesperado. Por favor, inténtalo de nuevo.');
            break;
        }
        router.push('/mis_cuentos');
      } finally {
        if (loading.value) {
          loading.value = false;
        }
      }
    };

    const guardarCambios = async () => {
      if (!quill.value) {
        console.error('Quill instance is not initialized.');
        alert('El editor no está inicializado. Por favor, recarga la página.');
        return;
      }

      if (quill.value.getLength() <= 1) {
        alert('El contenido de la aportación no puede estar vacío.');
        return;
      }
      if (quill.value.getLength() > CHARACTER_MAX) {
        alert(`El contenido excede el límite de ${CHARACTER_MAX} caracteres.`);
        return;
      }

      loading.value = true;

      try {
        const delta = quill.value.getContents();
        const deltaString = JSON.stringify(delta);

        await axios.put('https://collabtalesserver.avaldez0.com/php/editar_aportacion.php',
          {
            id_cuento: props.id_cuento,
            id_aportacion: props.id_aportacion,
            contenido: deltaString,
          },
          {
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
          }
        );
        goToCuento();
      } catch (error) {
        console.error('Error saving changes:', error);
        alert('Error al guardar los cambios. Por favor, inténtalo de nuevo.');
      } finally {
        loading.value = false;
      }
    };

    const goToCuento = () => {
      if (es_creador.value) {
        router.push(`/ver_cuento_creado/${props.id_cuento}`);
      } else {
        router.push(`/ver_cuento_colaborador/${props.id_cuento}`);
      }
    };

    onMounted(() => {
      fetchAportacion();
    });

    onBeforeUnmount(() => {
      destroyQuill();
    });

    return {
      loading,
      guardarCambios,
      goToCuento,
      aportaciones,
    };
  },
});
</script>

<style scoped>
.editor-card-text {
  padding: 0;
  height: 100%;
}

#quill-container {
  height: 100%;
  display: flex;
  flex-direction: column;
}

#editor {
  flex: 1;
  min-height: 300px;
  border: 1px solid #ccc;
  border-top: none;
  background: white;
}

#lectura {
  height: 100%;
  border: 1px solid #ccc;
  padding: 15px;
  overflow-y: auto;
  background: white;
}

.ql-toolbar {
  border: 1px solid #ccc !important;
  border-bottom: none !important;
  background: #f8f9fa;
  padding: 8px;
  flex-shrink: 0;
}

.ql-container {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 14px;
  line-height: 1.42;
  border: 1px solid #ccc !important;
  border-top: none !important;
}

.ql-editor {
  padding: 12px 15px;
  line-height: 1.42;
  font-size: 14px;
}

.ql-editor p {
  margin-bottom: 0.5em;
}

.ql-editor.ql-blank::before {
  font-style: italic;
  color: #aaa;
}

.ql-toolbar svg {
  width: 16px;
  height: 16px;
}

.ql-picker-label,
.ql-picker-options {
  font-size: 14px;
}

.ql-toolbar button,
.ql-toolbar .ql-picker {
  min-width: 30px;
  height: 30px;
  margin: 2px;
}

.editar-aportacion-view {
  width: 100%;
  height: 70vh;
  display: flex;
  justify-content: space-between;
  gap: 2rem;
  margin-top: -1rem;
}

.vista-cuento {
  width: 50%;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.cuento-card {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background-color: var(--vt-c-white-mute);
  border: 1px solid var(--color-text-input-fg-default);
}

.cuento-card {
  flex: 1;
  overflow-y: auto;
}

.vista-editar-aportacion {
  width: 50%;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.editor-card {
  flex: 1;
  width: 100%;
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;

  background-color: var(--vt-c-white-mute);
  border: 1px solid var(--color-text-input-fg-default);
}

.return-btn {
  margin: 0 0 1rem 0;
  padding: 0;
  font-size: 1rem;
}

.root-editar {
  background-color: var(--color-background-padding);
  width: 100%;
  height: 100%;
  padding: 2rem;
  padding-top: 1rem;
  display: flex;
  flex-direction: column;
}

.aportacion-autor {
  font-weight: bold;
  color: var(--color-text-black);
  margin: 0.5rem 0;
  text-align: center;
}
</style>

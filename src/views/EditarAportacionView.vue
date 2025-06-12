<template>
  <div class="root-editar">
    <AppNavbarWhite />
    <div class="d-inline-flex justify-between align-center mb-4">
      <v-img
        class="icon mr-2"
        src="/icons/edit_blue.svg"
        contain
        width="24"
        height="24"
      />
      <h1 class="text-h5 font-weight-bold w-100">
        {{ $t('edit_contribution.title') }}
      </h1>
      <ReturnBtn class="return-btn" @click="goToCuento">
        {{ $t('edit_contribution.return') }}
      </ReturnBtn>
    </div>
    <div class="editar-aportacion-view">
      <v-overlay :value="loading">
        <v-progress-circular indeterminate size="64"></v-progress-circular>
      </v-overlay>
      <!-- Sección con el cuento en su estado actual -->
      <div class="vista-cuento">
        {{ $t('edit_contribution.current_tale') }}
        <v-card class="cuento-card">
          <v-card-text>
            <div id="lectura" v-if="aportaciones.length === 0">
              <p class="no-aportaciones">
                {{ $t('edit_contribution.no_contributions') }}
              </p>
            </div>
            <div id="lectura" v-else>
              <v-list>
                <v-list-item v-for="(aportacion, idx) in aportaciones" :key="idx" class="aportacion-item">
                  <p
                    v-if="aportacion.es_autor"
                    class="aportacion-autor"
                  >
                    -- {{ $t('edit_contribution.contribution_start') }} --
                  </p>
                  <div v-html="aportacion.contenido"
                  class="text-wrap"
                  v-if="aportacion.contenido"
                  ></div>
                  <p
                    v-if="aportacion.es_autor"
                    class="aportacion-autor"
                  >
                    -- {{ $t('edit_contribution.contribution_end') }} --
                  </p>
                </v-list-item>
              </v-list>
            </div>
          </v-card-text>
        </v-card>
      </div>
      <!-- Sección con el texto a editar usando TipTap -->
      <div class="vista-editar-aportacion">
        <p>
          {{ $t('edit_contribution.edit') }}
        </p>
        <v-card class="editor-card">
          <v-card-text class="editor-card-text">
            <!-- Tip Tap Editor -->
            <template v-if="editor">
              <EditorMenuBar :editor="editor!" />

              <EditorContent
                id="editor"
                :editor="editor!"
              />

            </template>
          </v-card-text>
        </v-card>

        <!-- Botón para guardar cambios -->
        <div class="d-inline-flex justify-space-between">
          <p>{{ editor?.storage.characterCount.characters() }}/{{ CHARACTER_MAX }} {{ $t('edit_contribution.characters') }}</p>
          <div class="d-inline-flex justify-end">
            <BotonXs
              color_type="white_green"
              @click="guardarCambios"
              :disabled="loading"
            >
                {{ $t('edit_contribution.save_changes') }}
            </BotonXs>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import '../assets/base.css';

import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import DOMPurify from 'dompurify';
import { useI18n } from 'vue-i18n';

import AppNavbarWhite from '@/components/AppNavbarWhite.vue';
import ReturnBtn from '@/components/ReturnBtn.vue';
import BotonXs from '@/components/BotonSm.vue';
import EditorMenuBar from '@/components/EditorMenuBar.vue';

// TipTap
import { Color } from '@tiptap/extension-color'
import TextStyle from '@tiptap/extension-text-style'
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit';
import Heading from '@tiptap/extension-heading'
import Underline from '@tiptap/extension-underline';
import Placeholder from '@tiptap/extension-placeholder';
import TextAlign from '@tiptap/extension-text-align';
import CharacterCount from '@tiptap/extension-character-count';

function sanitizeHtml(html: string): string {
  return DOMPurify.sanitize(html, {
    ALLOWED_TAGS: ['b', 'i', 'u', 'strike', 'span', 'ul', 'ol', 'li', 'a', 'p', 'br', 'strong', 'em'],
    ALLOWED_ATTR: ['href', 'style'],
  });
}

export default defineComponent({
  name: 'EditarAportacionView',
  components: {
    AppNavbarWhite,
    ReturnBtn,
    BotonXs,
    EditorContent,
    EditorMenuBar,
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
    const editor = ref<Editor>();
    const es_creador = ref<boolean>(false);
    const aportaciones = ref([{
      contenido: '',
      es_autor: false,
    }]);
    const contenidoInicial = ref<string>('<p></p>'); // Default to empty content
    const CHARACTER_MAX= 8000;

    const router = useRouter();
    const { t } = useI18n();

    const initEditor = () => {
      editor.value = new Editor({
        editorProps: {
          attributes: {
            class: 'tiptap-editor h-100',
            style: 'overflow-y: auto; padding: 0.5rem; background-color: white; border: 1px solid #ccc; margin: 5px; border-radius: 5px; overflow-y: scroll;',
          },
        },

        extensions: [
          StarterKit,
          CharacterCount.configure({
            limit: CHARACTER_MAX,
          }),
          TextStyle,
          Color,
          Underline,
          Heading,
          TextAlign.configure({
            types: ['heading', 'paragraph'],
          }),
          Placeholder.configure({
            placeholder: t('edit_contribution.placeholder'),
            emptyEditorClass: 'ql-blank',
          }),
        ],
        content: contenidoInicial.value, // <-- now this has actual content
      });
    };

    const fetchAportacion = async () => {
      if (!props.id_cuento || !props.id_aportacion) {
        alert('Invalid parameters provided for fetching contribution.');
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
            contenido: sanitizeHtml(aport.contenido.trim()),
          }));
          es_creador.value = response.data.es_creador;

          // Parse contenido_autor safely
          try {
            contenidoInicial.value = sanitizeHtml(response.data.contenido_autor.trim());
          // eslint-disable-next-line @typescript-eslint/no-unused-vars
          } catch (parseError) {
            console.warn('Error parsing author content');
            contenidoInicial.value = '<p></p>'; // Default to empty content if parsing fails
          }

          loading.value = false;
        } else {
          alert('Could not fetch author contribution. Please try again later.');
          router.push('/mis_cuentos');
        }
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      } catch (error: any) {
        console.error('Fetch error:', error);
        switch (error.status) {
          case 400:
            alert('Could not fetch author contribution. Please check the provided parameters.');
            break;
          case 401:
            alert('You are not authorized to edit this contribution. Please log in again.');
            localStorage.removeItem('token');
            router.push('/');
            break;
          case 403:
            alert('You do not have permission to edit this contribution.');
            break;
          case 404:
            alert('Contribution not found. Please check the provided ID.');
            break;
          case 500:
            alert('Internal server error. Please try again later.');
            break;
          default:
            alert('An unexpected error occurred. Please try again later.');
            break;
        }
        router.push('/mis_cuentos');
      } finally {
        loading.value = false;
        initEditor();
      }
    };

    const guardarCambios = async () => {
      if (!editor.value) {
      console.error('TipTap editor instance is not initialized.');
      alert('Editor not initialized. Please try again later.');
      return;
      }

      const htmlContent = editor.value.getHTML();
      if (isHtmlEmpty(htmlContent)) {
      alert('The contribution content cannot be empty.');
      return;
      }
      if (htmlContent.length > CHARACTER_MAX) {
      alert(`The content exceeds the limit of ${CHARACTER_MAX} characters.`);
      return;
      }

      loading.value = true;

      try {
      const response = await axios.put('https://collabtalesserver.avaldez0.com/php/editar_aportacion.php',
        {
          id_cuento: props.id_cuento,
          id_aportacion: props.id_aportacion,
          contenido: htmlContent,
        },
        {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
          },
        }
      );

        if (response.status === 200) {
          alert(t('edit_contribution.success'));
          router.go(0);
        }
      } catch (error) {
        console.error('Error saving changes:', error);
        alert('Error saving changes. Please try again.');
        router.go(0);
      } finally {
      loading.value = false;
      }
    };

    const isHtmlEmpty = (html: string): boolean => {
      const temp = document.createElement('div');
      temp.innerHTML = html;
      return temp.innerText.trim() === '';
    }

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

    return {
      loading,
      guardarCambios,
      goToCuento,
      aportaciones,
      isHtmlEmpty,
      editor,
      CHARACTER_MAX,
    };
  },
});
</script>

<style scoped>
.editor-card-text {
  padding: 0;
  height: 100%;
}

.tiptap-editor {
  flex: 1;
  min-height: 100%;
  height: 100%;
  border: 1px solid #ccc;
  border-top: none;
}

#lectura {
  height: 100%;
  border: 1px solid #ccc;
  padding: 15px;
  overflow-y: auto;
  background: white;
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
  overflow-y: scroll;
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

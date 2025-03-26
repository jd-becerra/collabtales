<template>
  <v-container class="mt-5">
    <!-- Loading overlay -->
    <v-overlay :value="loading">
      <v-progress-circular indeterminate size="64"></v-progress-circular>
    </v-overlay>

    <!-- Sección con la aportación en su estado actual -->
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title>
            <h2>Tu Aportación hasta el momento:</h2>
          </v-card-title>
          <v-card-text id="lectura">
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Sección con el texto a editar usando Quill.js -->
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title>
            <h2>Editar Aportación:</h2>
          </v-card-title>
          <v-card-text>
            <!-- Toolbar for Quill.js -->
            <div id="toolbar">
              <select class="ql-font"></select>
              <select class="ql-size"></select>
              <button class="ql-bold"></button>
              <button class="ql-italic"></button>
              <button class="ql-underline"></button>
              <button class="ql-strike"></button>
              <select class="ql-color"></select>
              <select class="ql-background"></select>
              <button class="ql-list" value="ordered"></button>
              <button class="ql-list" value="bullet"></button>
              <select class="ql-align"></select>
              <button class="ql-link"></button>
            </div>
            <div id="editor"></div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Botón para guardar cambios -->
    <v-btn color="green" class="mt-4" @click="guardarCambios">Guardar Cambios</v-btn>
  </v-container>
</template>

<script lang="ts">
import axios from 'axios';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import 'quill/dist/quill.core.css';
import 'quill/dist/quill.bubble.css';

export default {
  name: 'EditarAportacion',
  data() {
    return {
      contenido: '',
      contenidoInicial: '',
      id_cuento: localStorage.getItem("id_cuento") || null,
      id_aportacion: localStorage.getItem("id_aportacion") || null,
      loading: false,
      quill: null as Quill | null, // Store the Quill instance
    };
  },
  mounted() {
    if (!this.id_cuento || !this.id_aportacion) {
      this.$router.push('/ver_cuentos');
      return;
    }
    this.loading = true;
    axios.get('/php/obtener_aportacion_individual.php', {
      params: {
        id_aportacion: this.id_aportacion,
      },
    })
      .then((response) => {
        if (response.data?.contenido !== undefined) {
          this.contenidoInicial = response.data.contenido;
          this.contenido = response.data.contenido;


          // Insert contenidoInicial into lectura
          const lecturaElement = document.getElementById('lectura');
          if (lecturaElement) {
            lecturaElement.innerHTML = this.contenidoInicial;
          }


          // Initialize Quill editor
          this.quill = new Quill('#editor', {
            modules: {
              toolbar: '#toolbar',
            },
            theme: 'snow',
          });

          // Insert the initial content
          this.quill.root.innerHTML = this.contenidoInicial;

          // Listen for text changes
          this.quill.on('text-change', () => {
            if (this.quill && this.quill.root) {
              this.contenido = this.quill.root.innerHTML;
            }
          });
        } else {
          console.error('Error: Response data is invalid or missing contenido.');
          this.$router.push('/ver_cuento');
        }
      })
      .catch((error) => {
        console.error('Error fetching aportación:', error);
        this.$router.push('/ver_cuento');
      })
      .finally(() => {
        this.loading = false;
      });
  },
  methods: {
    guardarCambios() {
      this.loading = true;
      axios.put(`/php/editar_aportacion/${this.id_cuento}`, {
        contenido: this.contenido,
      })
        .then(() => {
          this.$router.push('/ver_cuentos');
        })
        .catch((error) => {
          console.error('Error saving changes:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
  getQuillFormattedText() {
    return this.quill ? this.quill.getContents() : '';
  },
};
</script>

<style scoped>
#editor {
  height: 300px;
  max-height: 500px;
  min-height: 200px;
  border: 1px solid #ccc;
}
.ql-container {
  height: 100%;
}
.ql-toolbar {
  font-family: initial !important;
  width: 18px;
  height: 40px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-start;
  width: auto;
  max-width: 100%;
  padding: 5px;
  border: 1px solid #ccc;
}

.ql-toolbar svg {
  width: 16px;
  height: 16px;
}

.ql-picker-label,
.ql-picker-options {
  font-size: 14px;
}

.ql-picker-options {
  max-width: 200px;
}

.ql-toolbar button,
.ql-toolbar .ql-picker {
  min-width: 30px;
  height: 30px;
}

</style>

<template>
    <v-dialog v-model="showPublishModal" max-width="400">
      <v-card>
        <v-card-title>Publicar Cuento</v-card-title>
        <v-card-text>
          ¿Estás seguro de que deseas publicar este cuento? Una vez publicado, será visible para todos.
        </v-card-text>
        <v-card-actions>
          <v-btn color="green" @click="publicarCuento">Publicar</v-btn>
          <v-btn color="gray" @click="showPublishModal = false">Cancelar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-btn color="orange" class="mt-4" @click="showPublishModal = true">Publicar Cuento</v-btn>
  </template>

  <script lang="ts">
  import axios from 'axios';

  export default {
    data() {
      return {
        showPublishModal: false,
        id_cuento: localStorage.getItem("id_cuento") || null,
      };
    },
    methods: {
      async publicarCuento() {
        try {
          const response = await axios.post('/php/publicar_cuento.php',
          {
            id_cuento: this.id_cuento
          },
          {
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
            }
          });
          alert(response.data.mensaje);
          this.showPublishModal = false;
        } catch (error) {
          console.error("Error al publicar el cuento:", error);
        }
      }
    }
  };
  </script>

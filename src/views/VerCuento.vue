<script lang="ts">
import axios from 'axios';

export default {
  name: 'CuentoView',
  data() {
    return {
      cuento: {} as { id: number; nombre: string; descripcion: string } | null,
      aportaciones: [] as { id: number; texto: string }[],
      showDeleteCuentoPopup: false,
      showDeleteAportacionPopup: false,
      id_cuento: localStorage.getItem("id_cuento") || null,
    };
  },
  mounted() {
    if (!this.id_cuento) {
      console.error("No se encontró el ID del cuento en localStorage.");
      this.$router.push('/panel_inicio');
      return;
    }
    this.obtenerCuento();
    this.obtenerAportaciones();
  },
  methods: {
    async obtenerCuento() {
      try {
        const response = await axios.post('/php/obtener_vista_cuento.php', { id_cuento: this.id_cuento });
        this.cuento = response.data.length ? response.data[0] : null;
        console.log(this.cuento);
      } catch (error) {
        console.error("Error al obtener el cuento:", error);
      }
    },
    async obtenerAportaciones() {
      try {
        const response = await axios.get(`/php/get_aportaciones.php?id_cuento=${this.id_cuento}`);
        this.aportaciones = response.data;
      } catch (error) {
        console.error("Error al obtener las aportaciones:", error);
      }
    },
    async eliminarCuento() {
      try {
        await axios.post('/php/delete_cuento.php', { id_cuento: this.id_cuento });
        localStorage.removeItem("id_cuento");
        this.$router.push('/panel_inicio');
      } catch (error) {
        console.error("Error al eliminar el cuento:", error);
      }
    },
    async eliminarAportacion() {
      try {
        await axios.post('/php/delete_aportacion.php', { id_cuento: this.id_cuento });
        this.obtenerAportaciones();
      } catch (error) {
        console.error("Error al eliminar la aportación:", error);
      }
    },
  }
};
</script>

<template>
  <v-container class="vista-cuento">
    <!-- Botón para volver a la lista de cuentos -->
    <v-btn color="primary" :to="'/panel_inicio'">Mis cuentos</v-btn>
    
    <v-card class="my-4" v-if="cuento">
      <v-card-title>{{ cuento.nombre }}</v-card-title>
      <v-card-text>{{ cuento.descripcion }}</v-card-text>
    </v-card>
    
    <v-list>
      <v-list-item v-for="aportacion in aportaciones" :key="aportacion.id">
          <v-list-item-title>{{ aportacion.texto }}</v-list-item-title>
      </v-list-item>
    </v-list>
    
    <!-- Popup Eliminar Cuento -->
    <v-dialog v-model="showDeleteCuentoPopup" max-width="400">
      <v-card>
        <v-card-title>Eliminar Cuento</v-card-title>
        <v-card-text>
          ¿Estás seguro de que quieres eliminar este cuento?
          <br />Tu aportación y la de tus compañeros también serán eliminadas.
        </v-card-text>
        <v-card-actions>
          <v-btn color="red" @click="eliminarCuento">Eliminar</v-btn>
          <v-btn color="gray" @click="showDeleteCuentoPopup = false">Cancelar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Popup Eliminar Aportación -->
    <v-dialog v-model="showDeleteAportacionPopup" max-width="400">
      <v-card>
        <v-card-title>Abandonar cuento</v-card-title>
        <v-card-text>
          ¿Estás seguro de abandonar este cuento? Tu aportación será eliminada permanentemente.
        </v-card-text>
        <v-card-actions>
          <v-btn color="red" @click="eliminarAportacion">Abandonar</v-btn>
          <v-btn color="gray" @click="showDeleteAportacionPopup = false">Cancelar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<style scoped>
.vista-cuento {
  padding: 20px;
}
</style>

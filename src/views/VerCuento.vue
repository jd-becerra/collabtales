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
      } catch (error) {
        console.error("Error al obtener el cuento:", error);
      }
    },
    async obtenerAportaciones() {
      try {
        const response = await axios.get(`/php/obtener_aportaciones.php?id_cuento=${this.id_cuento}`);
        console.log("Id cuento:", this.id_cuento);
        console.log("Aportaciones:", response.data);
        this.aportaciones = response.data;
      } catch (error) {
        console.error("Error al obtener las aportaciones:", error);
      }
    },
    async eliminarCuento() {
      try {
        await axios.post('/php/eliminar_cuento.php', { id_cuento: this.id_cuento });
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
    <v-btn color="primary" class="mb-4" :to="'/panel_inicio'">📚 Volver a Mis Cuentos</v-btn>

    <v-card class="my-4 pa-4" elevation="6" v-if="cuento">
      <v-card-title class="text-h5 font-weight-bold">📖 Nombre Cuento: {{ cuento.nombre }}</v-card-title>
      <v-divider></v-divider>
      <v-card-text class="mt-2">
        <p class="text-body-1">Trata sobre:{{ cuento.descripcion }}</p>
      </v-card-text>
    </v-card>

    <v-card class="pa-4 mb-4 code-card" elevation="6">
      <v-card-title class="text-h6 font-weight-bold">🔑 Código para unirse</v-card-title>
      <v-card-text class="text-center text-h5 font-weight-bold green--text">
        {{ id_cuento }}
      </v-card-text>
    </v-card>

    <v-card class="pa-4 aportaciones-card" elevation="6">
      <v-card-title class="text-h6 font-weight-bold">✍️ Aportaciones</v-card-title>
      <v-divider></v-divider>
      <v-card-text class="mt-2">
        <v-list v-if="aportaciones.length > 0">
          <v-list-item v-for="aportacion in aportaciones" :key="aportacion.id" class="aportacion-item">
              <v-list-item-title class="text-body-1">{{ aportacion.texto }}</v-list-item-title>
          </v-list-item>
        </v-list>
        <p v-else class="no-aportaciones">⚠️ Actualmente no existen aportaciones en este cuento.</p>
      </v-card-text>
    </v-card>

    <!-- Botón para eliminar cuento -->
    <v-btn color="red" class="mt-4" @click="showDeleteCuentoPopup = true">🗑️ Eliminar Cuento</v-btn>

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
  max-width: 600px;
  margin: auto;
  padding: 20px;
}

.code-card {
  background-color: #e8f5e9;
  text-align: center;
  border-radius: 10px;
}

.aportaciones-card {
  width: 100%;
  max-height: 400px;
  max-width: 600px;
  background-color: #f3e5f5;
  overflow: auto;
}

.aportacion-item {
  max-width: 300px;
  margin: auto;
  background-color: #ffffff;
  padding: 8px;
  border-radius: 8px;
  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
}

.no-aportaciones {
  text-align: center;
  font-weight: bold;
  color: #757575;
  padding: 20px;
}
</style>

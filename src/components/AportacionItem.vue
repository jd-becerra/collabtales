<template>
  <div
  class="aportacion-item"
  :style="first_in_list ? 'margin-top: 0;' : 'margin-top: 1.5rem;'"
  >
    <p class="autor-header">Colaborador: {{ autor }} </p>
    <v-card flat class="aportacion-card">
      <v-card-text class="mt-2">
        <div v-html="contenido" v-if="contenido != ''"></div>
        <div v-else class="empty-contenido text-center">Esta aportación está vacía.</div>
      </v-card-text>
    </v-card>
  </div>
</template>

<script lang="ts" setup>
import '../assets/base.css'

defineProps<{
  id_cuento: number | null;
  id_aportacion: number | null;
  contenido: string;
  autor: string;
  first_in_list?: boolean;
}>();
</script>

<style scoped>
.aportacion-item {
  background-color: var(--vt-c-white-soft);
}

.autor-header {
  margin-left: 0.5rem;
}

.aportacion-card {
  border: 1px solid var(--color-border-default);
  border-radius: var(--border-radius-default);
  min-height: 10rem;
}

.empty-contenido {
  color: var(--vt-c-black-mute);
}
</style>

<template>
  <div
    class="aportacion-item"
    :style="first_in_list ? 'margin-top: 0;' : 'margin-top: 1.5rem;'"
  >
    <p class="aportacion-header">Tu aportación (Haz click para editarla)</p>
    <v-card flat class="aportacion-card" @click="goToAportacion">
      <v-card-text class="mt-2">
        <div v-html="contenido" v-if="contenido != ''"></div>
        <div v-else class="empty-contenido text-center">Tu aportación está vacía. Haz click para editarla.</div>
      </v-card-text>
      <div class="edit-btn-container">
        <v-btn
          class="edit-btn"
          @click="$emit('edit')">
          <v-img
            src="/icons/edit.svg"
            contain
            width="20"
            height="20"
          />
        </v-btn>
      </div>
    </v-card>
  </div>
</template>

<script lang="ts" setup>
import '../assets/base.css'

import { useRouter } from 'vue-router';

const router = useRouter();

const props = defineProps<{
  id_cuento: number | null;
  id_aportacion: number | null;
  contenido: string;
  first_in_list?: boolean;
}>();

const goToAportacion = () => {
  const id_cuento = props.id_cuento;
  const id_aportacion = props.id_aportacion;

  if (id_cuento && id_aportacion) {
    router.push(`/editar_aportacion/${id_cuento}/${id_aportacion}`);
  } else {
    alert("No hay aportación seleccionada.");
  }
}


</script>

<style scoped>
.aportacion-item {
  background-color: var(--vt-c-white-soft);
  min-width: 60vw;
}

.aportacion-header {
  font-weight: bold;
  color: var(--color-edit);
  margin-left: 0.5rem;
}

.aportacion-card {
  border: 2px solid var(--color-edit);
  border-radius: var(--border-radius-default);
  position: relative;
  cursor: pointer;
  min-height: 10rem;

  &:hover {
    background-color: var(--vt-c-white-mute);
  }
}

.edit-btn-container {
  display: flex;
  justify-content: flex-end;
  padding: 0.5rem;
  position: absolute;
  bottom: 0.5rem;
  right: 0.5rem;
}

.edit-btn {
  background-color: var(--color-edit);
}

.empty-contenido {
  color: var(--vt-c-black-mute);
}
</style>

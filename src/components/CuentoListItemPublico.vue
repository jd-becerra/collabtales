<template>
  <v-list-item class="cuento-item">
    <a @click="verCuentoPublico(id_cuento)" class="cuento-item-header text-h5">
      <v-img
        class="icon mr-2"
        src="/icons/book.svg"
        alt="Ícono para un cuento"
        contain
      />
      {{ nombre }}
    </a>
      <p v-if="autores && autores.length > 0" class="autores-header">
        <span>
          Autores:
        </span>
        <span> {{ autores.join('     |     ') }} </span>
      </p>
    <p v-else>
      Autores: Información no disponible
    </p>
    <p class="descripcion-header">
      {{ descripcion }}
    </p>
  </v-list-item>
  <div v-if="!ultimo" class="cuento-divider my-4"></div>
</template>

<script setup lang="ts">
import '../assets/base.css'
import { useRouter } from 'vue-router';

const router = useRouter();

const verCuentoPublico = (id_cuento: number) => {
  router.push('/ver_cuento_publico/' + id_cuento);
};

defineProps<{
  id_cuento: number;
  nombre: string;
  descripcion: string;
  autores?: string[];
  ultimo: boolean;
}>();
</script>

<style scoped>
.cuento-item {
  width: 100%;
  margin: 0;
}

.icon {
  width: 32px;
  height: 32px;
  flex-shrink: 0;
}

.cuento-item-header {
  font-size: 1.5rem;
  font-weight: 500;
  color: var(--color-text-blue);
  padding-left: 0;
  padding-top: 0;
  margin-left: 0;
  margin-top: 0;
  cursor: pointer;

  display: inline-flex;
  align-content: flex-start;
  align-items: center;
  flex-wrap: wrap;
}
.cuento-item-header:hover {
  background-color: transparent;
  color: var(--color-hover-blue);
}

.cuento-divider {
  border-top: 1px solid var(--color-text-input-border);
}

.autores-header {
  color: var(--color-text-input-fg-default);
  font-size: 0.8rem;
  margin-bottom: 0.5rem;
}

.descripcion-header {
  color: var(--color-text-input-fg-default);
  font-weight: 600;;
}
</style>

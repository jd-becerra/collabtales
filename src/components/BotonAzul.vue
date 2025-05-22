<template>
  <v-btn
    class="boton-azul"
  >
    <slot></slot>
  </v-btn>
</template>

<script setup lang="ts">
import '../assets/base.css'
</script>

<style scoped>
.boton-azul {
  background-color: var(--color-btn-blue-bg);
  color: var(--color-text-white);
  font-size: var(--font-size-md);
  font-weight: 500;
  border-radius: var(--border-radius-default);
  width: var(--input-width-lg);
  height: var(--input-height-lg);
  text-transform: none;
  font-family: 'Inter', sans-serif;
}

@media (max-width: 1366px) {
  .boton-azul {
    font-size: 1.2rem;
  }
}
</style>

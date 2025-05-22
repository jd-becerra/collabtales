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
  font-size: 1.2rem;
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius-default);
  transition: background-color 0.3s ease;
}
</style>

<template>
  <v-btn
    class="boton-blanco"
  >
    <slot></slot>
  </v-btn>
</template>

<script setup lang="ts">
import '../assets/base.css'
</script>

<style scoped>
.boton-blanco {
  background-color: var(--color-btn-white-bg);
  border: 1px solid var(--color-border-blue);
  color: var(--color-text-blue);
  font-size: var(--font-size-md);
  font-weight: 500;
  border-radius: var(--border-radius-default);
  width: var(--input-width-lg);
  height: var(--input-height-lg);
  text-transform: none;
  font-family: 'Inter', sans-serif;
}

@media (max-width: 1366px) {
  .boton-blanco {
    font-size: 1.2rem;
    height: 50px;
  }
}

@media (max-width: 1024px) {
  .boton-blanco {
    font-size: 1rem;
    width: var(--input-width-md);
  }
}

@media (max-width: 768px) {
  .boton-blanco {
    font-size: 0.9rem;
    width: var(--input-width-sm);
  }
}

@media (max-width: 600px) {
  .boton-blanco {
    font-size: 0.8rem;
    width: var(--input-width-md);
  }
}
</style>

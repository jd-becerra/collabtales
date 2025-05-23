<template>
  <v-app-bar app class="app-navbar" :color="getCSSBackgroundColor()" >
    <LogoCollabtalesSm/>
  </v-app-bar>
</template>

<script setup lang="ts">
import LogoCollabtalesSm from '@/components/icons/LogoCollabtalesSm.vue';

function getCSSBackgroundColor() {
  // Usamos esto porque por alguna razón el fondo sigue desapareciendo si lo cambiamos en <style>
  const color = getComputedStyle(document.documentElement).getPropertyValue('--color-background-padding');
  return color;
}
</script>



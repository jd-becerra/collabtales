<template>
  <v-app-bar app class="app-navbar" :color="getCSSBackgroundColor()" >
    <LogoCollabtalesSm/>

    <v-spacer></v-spacer>

    <LangBtn class="lang_navbar"/>
    <PerfilBtn/>
  </v-app-bar>
</template>

<script setup lang="ts">
import LogoCollabtalesSm from '@/components/icons/LogoCollabtalesSm.vue';
import PerfilBtn from '@/components/PerfilBtn.vue';
import LangBtn from '@/components/LangBtn.vue';

function getCSSBackgroundColor() {
  // Usamos esto porque por alguna razón el fondo sigue desapareciendo si lo cambiamos en <style>
  const color = getComputedStyle(document.documentElement).getPropertyValue('--color-background-padding');
  return color;
}
</script>

<style scoped>
.lang_navbar {
  margin-right: 1rem;
}
</style>


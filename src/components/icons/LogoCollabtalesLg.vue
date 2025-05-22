<template>
  <v-container class="d-flex flex-column justify-start">
    <h1 class="logo-collabtales">
      <span class="logo-collabtales-text">Collabtales</span>
    </h1>
    <h3 class="slogan-collabtales">
      <span class="logo-collabtales-text">¡Imagina, escribe, comparte, colabora!</span>
    </h3>
  </v-container>
</template>

<script lang="ts" setup>
import '../../assets/base.css'
</script>

<style scoped>
.logo-collabtales {
  font-family: 'Lucida Handwriting', 'cursive';
  font-size: 6.25rem;
  padding: 0;
  margin: 0; /* Reduce space below logo */
}
.slogan-collabtales {
  font-family: 'Kameron', sans-serif;
  font-size: 1.5rem;
  padding: 0;
  margin-top: -1rem; /* Remove extra space above slogan */
}
</style>

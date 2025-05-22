<template>
  <v-container class="d-flex flex-column justify-start logo-lg-container align-left">
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
.logo-lg-container {
  padding: 0;
  margin: 0;
  pointer-events: none;
}
.logo-collabtales {
  font-family: 'Lucida Handwriting', 'cursive';
  font-size: 6.25rem;
  padding: 0;
  padding-top: 4rem;
  margin: 0;
}
.slogan-collabtales {
  font-family: 'Kameron', sans-serif;
  font-size: 1.5rem;
  padding: 0;
  margin: 0;
  margin-top: -1.5rem;
}

.logo-collabtales-text {
  pointer-events: none;
  user-select: none;
}

/* Estilos responsivos para pantallas más pequeñas */
@media (max-width: 1366px) {
  .logo-collabtales {
    padding-top: 4rem;
    font-size: 4rem;
  }
  .slogan-collabtales {
    font-size: 1.2rem;
  }
}

@media (max-width: 1024px) {
  .logo-collabtales {
    padding-top: 3rem;
    font-size: 3rem;
  }
  .slogan-collabtales {
    font-size: 1rem;
  }
}

@media (max-width: 768px) {
  .logo-collabtales {
    padding-top: 2rem;
    font-size: 2.5rem;
  }
  .slogan-collabtales {
    font-size: 0.8rem;
  }
}

@media (max-width: 600px) {
  .logo-collabtales {
    padding-top: 2rem;
    font-size: 2rem;
  }
  .slogan-collabtales {
    font-size: 0.6rem;
  }
}
</style>

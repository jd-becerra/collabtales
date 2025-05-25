<template>
  <v-container class="d-flex flex-column justify-start logo-lg-container align-left">
    <h1 class="logo-collabtales">
      <span class="logo-collabtales-text">Collabtales</span><span class="copyright"> &copy;</span>
    </h1>
    <h2 class="slogan-collabtales">
      <span class="logo-collabtales-text">¡Imagina, escribe, comparte, colabora!</span>
    </h2>
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

  position: absolute;
  top: 10%;

}
.logo-collabtales {
  font-family: 'Lucida Handwriting', 'cursive';
  font-size: 6.25rem;
  padding: 0;
  margin: 0;
  text-align: left;
  margin-left: -0.5rem;
}
.copyright {
  font-family: 'Inter', sans-serif;
  font-size: 1.5rem;
}
.slogan-collabtales {
  font-family: 'Inter', sans-serif;
  font-size: 1.5rem;
  padding: 0;
  margin: 0;
  margin-top: -1.5rem;
}

.logo-collabtales-text {
  pointer-events: none;
  user-select: none;
}

@media (min-width: 1920px) {
  .logo-collabtales {
    font-size: 11rem;
  }
  .slogan-collabtales {
    font-size: 1.5rem;
    margin-top: -1.5rem;
  }
}

/* Estilos responsivos para pantallas más pequeñas */
@media (max-width: 1366px) {
  .logo-collabtales {
    font-size: 5.5rem;
  }
  .slogan-collabtales {
    font-size: 1.2rem;
    margin-top: -2.5rem;
  }
}

@media (max-width: 1024px) {
  .logo-collabtales {
    padding-top: 3rem;
    font-size: 3rem;
  }
  .slogan-collabtales {
    font-size: 1rem;
    margin-top: -1.2rem;
  }
}

@media (max-width: 768px) {
  .logo-collabtales {
    padding-top: 2rem;
    font-size: 2.5rem;
  }
  .slogan-collabtales {
    font-size: 0.8rem;
    margin-top: -1.0rem;
  }
}

@media (max-width: 600px) {
  .logo-collabtales {
    padding-top: 2rem;
    font-size: 2rem;
  }
  .slogan-collabtales {
    font-size: 0.6rem;
    margin-top: -0.5rem;
  }
}
</style>

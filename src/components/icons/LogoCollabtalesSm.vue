<template>
  <v-container class="logo-sm-container d-flex flex-row align-left justify-start" @click="goToInicio">
    <v-col class="logo-sm-img-container d-flex" cols="2">
      <v-img
      src="/books.ico"
        class="logo-collabtales"
        contain
        alt="Logo de Collabtales, con dos personas enmarcados por un libro de donde sale una planta."
      />
    </v-col>
    <v-col class="logo-sm-txt-container d-flex flex-column justify-start">
      <h1 class="logo-collabtales">
        <span class="logo-collabtales-text">Collabtales</span><span class="copyright"> &copy;</span>
      </h1>
      <h3 class="slogan-collabtales">
        <span class="logo-collabtales-text">¡Imagina, escribe, comparte, colabora!</span>
      </h3>
    </v-col>
  </v-container>
</template>

<script lang="ts" setup>
import '../../assets/base.css'
import { useRouter } from 'vue-router';

// Si hacemos click en el logo, nos lleva a la página de inicio
const router = useRouter();
function goToInicio() {
  router.push('/mis_cuentos');
}

</script>

<style scoped>
.logo-sm-container {
  width: 25%;
  height: 100%;
  padding: 0;
  margin: 0;
  pointer-events: all;
  cursor: pointer;
  margin-left: var(--gutter);
}

.logo-collabtales {
  font-family: 'Lucida Handwriting', 'cursive';
  font-size: 1.5rem;
}
.slogan-collabtales {
  font-family: 'Kameron', sans-serif;
  margin-top: -0.5rem;
  font-size: 0.5rem;
}
.copyright {
  font-family: 'Inter', sans-serif;
  font-size: 0.4rem;
}

.logo-sm-img-container {
  margin-right: 0;
  padding-right: 0;
  padding-left: 0;
  width: 100%;
  height: 100%;
  flex-shrink: 0;

}
.logo-sm-txt-container {
  width: 100%;
  height: 100%;
  user-select: none;

  margin-left: 0;
  padding-left: 0.1rem;
  margin-top: -0.2rem;

  display: flex;
  align-items: first baseline;
  justify-content: start;
}
</style>



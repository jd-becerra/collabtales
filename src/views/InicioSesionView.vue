<template>
  <BackgroundPadding />
  <v-container class="forms-column-container full-width fill-height ">
      <LogoCollabtalesLg />

      <div class="forms-container align-left justify-start">

          <FormularioRegistro
            v-if="showRegister"
            key="register"
            @show-login="showLoginForm"
          />
          <FormularioLogin v-else-if="showLogin" key="login"
            @show-register="showRegisterForm"
            @show-restore="showRestoreForm"
          />
          <FormularioRestaurar v-if="showRestore" key="restore"
            @show-register="showRegisterForm"
          />
      </div>
  </v-container>
  <v-img
    class="logo-image"
    src="/img/logo_inicio.png"
    position="center center"
    alt="Logo de Collabtales, con dos personas enmarcados por un libro de donde sale una planta."
  />
</template>

<script setup lang="ts">
import { ref } from 'vue';

// Componentes
import FormularioRegistro from '@/components/FormularioRegistro.vue';
import FormularioLogin from '@/components/FormularioLogin.vue';
import LogoCollabtalesLg from '@/components/icons/LogoCollabtalesLg.vue';
import FormularioRestaurar from '@/components/FormularioRestaurar.vue';
import BackgroundPadding from '@/components/BackgroundPadding.vue';

const showRegister = ref(false);
const showLogin = ref(true);
const showRestore = ref(false);

// Métodos para mostrar los formularios
function showRegisterForm() {
  showRegister.value = true;
  showLogin.value = false;
  showRestore.value = false;
}

function showLoginForm() {
  showRegister.value = false;
  showLogin.value = true;
  showRestore.value = false;
}

function showRestoreForm() {
  showRegister.value = false;
  showLogin.value = false;
  showRestore.value = true;
}
</script>

<style scoped>
.forms-column-container {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items:  start;
}

.logo-image {
  position: absolute;
  /* Put vertically in the center */
  top: 50%;
  right: 0;
  transform: translateY(-50%);

  width: 60%;
  height: 60%;
  padding: 0;
  margin: 0;

  border-radius: 5px;
}

</style>

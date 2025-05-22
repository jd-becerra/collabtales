<template>
  <BackgroundPadding />
  <v-container class="d-flex fill-height pa-0">
    <!-- A la izquierda tenemos el formulario y el logo con eslogan -->
    <v-col class="forms-column-container">
      <LogoCollabtalesLg />
      <div class="forms-container d-flex align-left justify-start">
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
            @show-login="showLoginForm"
          />
      </div>
    </v-col>

    <!-- A la derecha tenemos la imagen principal -->
    <v-col class="d-flex justify-end align-end">
        <v-img
          class="logo-image"
          src="/img/logo_inicio.png"
          alt="Logo de Collabtales, con dos personas enmarcados por un libro de donde sale una planta."
        />
    </v-col>
  </v-container>
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
.logo-image {
  width: 40%;
  height: 40%;

  max-width: 68%;
  max-height: 68%;

  margin-top: 20%;
  border-radius: 5px;
}

.forms-column-container {
  animation: fadeIn 0.5s ease-in-out;
}

.forms-container {
  margin-top: 30%;
  animation: slideY 0.5s ease-in-out;
}

@keyframes slideY {
  0% {
    transform: translateY(-20px);
  }
  100% {
    transform: translateY(0px);
  }
}

@keyframes fadeIn {
  0% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}

@media (max-width: 600px) {
  .logo-image {
    display: none;
  }
}

</style>

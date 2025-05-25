<template>
  <BackgroundPadding/>
  <div class="main-container">
    <!-- A la izquierda tenemos el formulario y el logo con eslogan -->
    <v-container class="forms-column-container">
      <LogoCollabtalesLg />
      <div class="forms-container">
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
    </v-container>

    <!-- A la derecha tenemos la imagen principal -->
    <v-container class="img-container">
        <v-img
          class="logo-image"
          src="/img/logo_inicio.png"
          alt="Logo de Collabtales, con dos personas enmarcados por un libro de donde sale una planta."
        />
    </v-container>
  </div>
</template>

<script setup lang="ts">
import '../assets/base.css';

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
.main-container {
  height: 100%;
  width: 100vw;

  padding: 2rem;

  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;

  align-content: center;

  gap: 0;

}

.logo-image {
  width: 65%;
  height: 65%;

  max-width: 65%;
  max-height: 65%;

  margin-top: 10%;
  border-radius: 5px;
}

.forms-column-container {
  animation: fadeIn 0.5s ease-in-out;
}

.forms-container {
  margin-top: 30%;
  animation: slideY 0.5s ease-in-out;
}

.img-container {
  display: flex;
  align-items: center;
  justify-content: center;
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

@media (max-width: 1024px) {
  .logo-image {
    display: none;
  }

  .forms-column-container {
    width: 100%;
    height: 100%;
    padding: 0;
    margin: 0;

    flex-direction: column;
  }
}

</style>

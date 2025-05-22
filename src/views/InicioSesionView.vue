<template>
  <v-container class="full-width fill-height">
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
</template>

<script setup lang="ts">
import { ref } from 'vue';

// Componentes
import FormularioRegistro from '@/components/FormularioRegistro.vue';
import FormularioLogin from '@/components/FormularioLogin.vue';
import LogoCollabtalesLg from '@/components/icons/LogoCollabtalesLg.vue';
import FormularioRestaurar from '@/components/FormularioRestaurar.vue';

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
</style>

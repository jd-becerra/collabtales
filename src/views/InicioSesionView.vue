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

    <!-- pop up de registro-->
    <v-dialog v-model="CamposVaciosPopup" max-width="400">
      <v-card>
        <v-card-title class = "text-red"> {{PopupValues.titulo}}</v-card-title>
        <v-card-text>
        {{PopupValues.error}}
        </v-card-text>
        <v-card-actions>
          <v-btn color="blue" @click="CamposVaciosPopup = false">Ok</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

      <!-- pop up de registro-->
  </v-container>
</template>



<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

// Componentes
import FormularioRegistro from '@/components/FormularioRegistro.vue';
import FormularioLogin from '@/components/FormularioLogin.vue';
import LogoCollabtalesLg from '@/components/icons/LogoCollabtalesLg.vue';
import FormularioRestaurar from '@/components/FormularioRestaurar.vue';

// Reactive state
const showRegister = ref(false);
const showLogin = ref(true);
const showRestore = ref(false);
const CamposVaciosPopup = ref(false);
const PopupValues = ref({titulo: "Error!", error:""})

// Metodo para mostrar pop ups

const showPopup = (titulos: string, errors: string) => {
  PopupValues.value.titulo = titulos;
  PopupValues.value.error = errors;
  CamposVaciosPopup.value = true;
};

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
/* Fondo con degradado */
.fill-height {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Estilo de la tarjeta */
.login-card {
  width: 400px;
  border-radius: 12px;
  background: white;
  box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.2);
}

/* Campos de entrada personalizados */
.custom-input {
  background: #f4f6f8;
  border-radius: 8px;
}

/* Botones estilizados */
.v-btn {
  font-weight: bold;
  transition: 0.3s ease-in-out;
}

.v-btn:hover {
  transform: scale(1.03);
}

.text-blue-darken-2 {
  color: #1976d2 !important;
}
</style>

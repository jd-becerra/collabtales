<template>
    <v-form disabled="true">
      <v-container class="login-fields d-flex flex-column">
        <TextInputSmDisabled
          :label="$t('profile.username')"
          v-model="localDatosAlumno.nombre"
          type="text"
          outlined
          class="custom-input"
          readonly
        />
        <TextInputSmDisabled
          :label="$t('profile.email')"
          v-model="localDatosAlumno.correo"
          type="text"
          outlined
          class="custom-input"
          readonly
        />
      </v-container>

      <v-container class="login-buttons text-right">
        <BotonWideXs @click="$emit('show-perfil-edicion')" class="mt-3">
          {{ $t('profile.edit_profile') }}
        </BotonWideXs>
      </v-container>
    </v-form>
</template>

<script lang="ts" setup>
import { ref, defineProps, watch } from 'vue';
// Componentes
import TextInputSmDisabled from './TextInputSmDisabled.vue';
import BotonWideXs from './BotonWideXs.vue';
defineEmits(['show-perfil-edicion']);

const props = defineProps<{
  datosAlumno: {
    id_alumno: string;
    nombre: string;
    correo: string;
  };
}>();

const localDatosAlumno = ref({ ...props.datosAlumno });

watch(
  () => props.datosAlumno,
  (newVal) => {
    localDatosAlumno.value = { ...newVal };
  },
  { deep: true, immediate: true } // 'deep' for nested objects, 'immediate' to run on initial mount
);
</script>

<style scoped>
.login-fields {
  margin-left: 0;
  padding-left: 0;
  gap: 0.5rem;
}
.custom-input {
  pointer-events: none;
}

.login-buttons {
  margin-left: 0;
  padding-left: 0;
  margin-top: 1.5rem;
}
.result-msg {
  font-size: 0.9rem;
  padding: 0;
  margin-top: -0.5rem;
}
.goto-restore {
  color: var( --color-text-blue);
  text-decoration: underline;
  cursor: pointer;
  margin-bottom: 2px;
}
</style>

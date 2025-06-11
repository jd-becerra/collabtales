<template>
  <v-btn
    class="lang-btn"
    @click="changeLanguage"
  >
    <span class="material-icons">
      <v-img
        src="/icons/translate.svg"
        width="24"
        height="24"
        class="mr-2"
      />
       {{ locale === 'es' ? 'ESPAÑOL' : 'ENGLISH' }}
    </span>
  </v-btn>
</template>

<script lang="ts" setup>
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { onMounted } from 'vue';

const { locale } = useI18n();
const router = useRouter();

function changeLanguage() {
  // Recargar la página para aplicar el cambio de idioma
   locale.value = locale.value === 'es' ? 'en' : 'es';
  localStorage.setItem('user-locale', locale.value);
  router.go(0);

}

onMounted(() => {
  // Cargar el idioma del localStorage al iniciar
  const savedLocale = localStorage.getItem('user-locale');
  if (savedLocale) {
    locale.value = savedLocale;
  }
});
</script>

<style scoped>
.lang-btn {
  background-color: white;
  border: 1px solid #ccc;
  color: black;
}

.material-icons {
  vertical-align: middle;
  display: inline-flex;
  align-items: center;
}
</style>

<template>
  <v-app-bar app class="app-navbar">
    <LogoCollabtalesSm/>

    <v-spacer></v-spacer>

    <LangBtn class="lang_navbar"/>
    <PerfilBtn/>
  </v-app-bar>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import LogoCollabtalesSm from '@/components/icons/LogoCollabtalesSm.vue';
import PerfilBtn from '@/components/PerfilBtn.vue';
import LangBtn from '@/components/LangBtn.vue';


const backgroundColor = ref('');

onMounted(() => {
  const color = getComputedStyle(document.documentElement).getPropertyValue('--color-navbar-white').trim();
  backgroundColor.value = color;
});

</script>

<style scoped>
.lang_navbar {
  margin-right: 1rem;
}
</style>

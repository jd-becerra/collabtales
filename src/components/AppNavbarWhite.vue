<template>
  <v-app-bar app class="app-navbar">
    <LogoCollabtalesSm/>
  </v-app-bar>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import LogoCollabtalesSm from '@/components/icons/LogoCollabtalesSm.vue';

const backgroundColor = ref('');

onMounted(() => {
  const color = getComputedStyle(document.documentElement).getPropertyValue('--color-navbar-white').trim();
  backgroundColor.value = color;
});
</script>

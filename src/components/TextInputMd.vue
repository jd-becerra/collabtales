<template>
  <input type="text" id="input" v-model="inputValue" />
  <label for="input">Input</label>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const inputValue = ref('');
</script>

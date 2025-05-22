<template>
  <div class="background-wrapper">
  </div>
</template>

<script lang="ts" setup>
import '../assets/base.css'
</script>

<style scoped>
.background-wrapper {
  background-color: var(--color-background-padding);
  width: 100%;
  height: var(--background-padding-height);
  margin: 0;
  padding: 0;

  position: fixed;
  top: 0;
  left: 0;
}
</style>

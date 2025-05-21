<template>
  <div class="background-wrapper">
    <div class="background-padding top"></div>
    <div class="main-content">
      <slot></slot>
    </div>
    <div class="background-padding bottom"></div>
  </div>
</template>

<script lang="ts" setup>
import '../assets/base.css'
</script>

<style scoped>
.background-wrapper {
  background-color: var(--color-background);
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.background-padding {
  background-color: var(--color-background-padding); /* or define a new --color-background-padding in base.css */
  height: 10vh;
  width: 100%;
}

.main-content {
  flex: 1;
  width: 100%;
  background-color: var(--color-background);
  display: flex;
  flex-direction: column;
}
</style>

<template>
  <div class="return-container">
    <button class="return-btn mb-2 ">
        <img
          src="/icons/chevron_left_black.svg"
          class="return-icon"
          contain
        />
      <slot></slot>
    </button>
  </div>
</template>

<script setup lang="ts">

</script>

<style scoped>
.return-container {
  display: flex;
  justify-content: right;

  width: 100%;
  padding: 0;
  margin: 0;
}

.return-btn {
  display: flex;
  justify-content: right;

  font-weight: bold;
  font-size: 1.2em;
  border: none;

  margin: 0;

}
.return-icon {
  height: 30px;
  width: 30px;
  padding: 0;
  margin: 0;
}
</style>

<template>
  <v-btn
    :variant="variant"
    :color="color"
    block
    @click="$emit('click')"
  >
    <v-icon start v-if="icon">{{ icon }}</v-icon>
    {{ label }}
  </v-btn>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  icon: { type: String, default: null },
  color: { type: String, default: 'primary' },
  variant: { type: String, default: 'outlined' }
})
</script>

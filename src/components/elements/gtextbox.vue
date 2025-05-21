<template>
  <v-text-field
    :label="label"
    :placeholder="placeholder"
    :type="type"
    :prepend-inner-icon="icon"
    :model-value="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    :append-icon="appendIcon"
    @click:append="$emit('toggle')"
    variant="outlined"
    density="compact"
  />
</template>

<script setup>
defineProps({
  label: String,
  placeholder: String,
  icon: String,
  type: { type: String, default: 'text' },
  modelValue: String,
  appendIcon: String
})
</script>

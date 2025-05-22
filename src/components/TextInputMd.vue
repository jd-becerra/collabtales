<template>
  <div class="text-input-md">
    <label v-if="label" class="text-caption font-weight-medium mb-1" :for="id">{{ label }}</label>
    <input
      :id="id"
      :type="type"
      class="text-input"
      :value="modelValue"
      :placeholder="placeholder"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement)?.value)"
    />
    <small v-if="small" class="text-caption font-weight-medium mb-1">
      {{ small }}
    </small>
  </div>
</template>

<script setup lang="ts">
import '../assets/base.css'

import { defineProps, defineEmits } from 'vue'

defineProps<{
  label?: string
  modelValue: string
  id?: string
  type?: string
  placeholder?: string
  small?: string
}>()

defineEmits(['update:modelValue'])
</script>

<style scoped>
.text-input-md {
  display: flex;
  flex-direction: column;
  margin-bottom: 1rem;
}

.text-input {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: var(--border-radius-default);
  font-size: var(--font-size-md);
  color: var(--color-text-input-fg-default);
  border-color: var(--color-border-default);
  width: var(--input-width-lg);
}
.text-input:focus {
  border-color: var(--vt-c-blue-dark);
  outline: none;
  font-family: var(--font-size-md);
}

.text-caption {
  margin-left: 0.5rem;
  color: var(--color-text-input-fg-label);
  font-size: var(--font-size-sm);
}

@media (max-width: 1366px) {
  .text-input {
    font-size: var(--font-size-sm);
  }
  .text-caption {
    font-size: var(--font-size-xs);
  }
}

</style>

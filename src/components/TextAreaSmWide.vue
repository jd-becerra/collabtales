<template>
  <div class="text-input-sm">
    <label v-if="label" class="text-caption font-weight-medium mb-1" :for="id">{{ label }}</label>
    <textarea
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
.text-input-sm {
  display: flex;
  flex-direction: column;
  padding: 0;
  margin: 0;
}

.text-input {
  padding: 0.5rem;
  border: 1px solid var(--color-border-default);
  border-radius: var(--border-radius-default);
  font-size: var(--font-size-sm);
  color: var(--color-text-black);
  border-color: var(--color-border-default);
  width: auto;
  height: var(--input-height-wide);
  background-color: var(--color-text-input-bg-default);
  resize: none;

  &::placeholder {
    color: var(--color-text-input-fg-default);
    word-wrap: break-word;
  }
  &:focus {
    border-color: var(--vt-c-blue-dark);
    outline: none;
  }
}

.text-caption {
  margin-left: 0.5rem;
  color: var(--color-text-input-fg-label);
  font-size: var(--font-size-sm);
}

</style>

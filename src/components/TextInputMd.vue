<template>
  <div class="text-input-md">
    <label v-if="label" class="text-caption font-weight-medium mb-1" :for="id">{{ label }}</label>
    <input
      :id="id"
      :type="type"
      class="text-input"
      :value="modelValue"
      :placeholder="placeholder"
      @input="onInput"
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

const emit = defineEmits(['update:modelValue'])

function onInput(event: Event) {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
}
</script>

<style scoped>
.text-input-md {
  display: flex;
  flex-direction: column;
  padding: 0;
  margin: 0;
}

.text-input {
  padding: 0.5rem;
  border: 1px solid var(--color-border-default);
  border-radius: var(--border-radius-default);
  font-size: var(--font-size-md);
  color: var(--color-text-black);
  border-color: var(--color-border-default);
  width: var(--input-width-md);
  height: var(--input-height-md);

  &::placeholder {
  color: var(--color-text-input-fg-default);
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

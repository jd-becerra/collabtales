<template>
  <div class="search-wrapper">
    <div class="text-input-md d-flex flex-row align-center" ref="input_wrapper">
      <div class="menu-icon" @click="focusSearch(true)">
        <v-img
          src="/icons/menu.svg"
          class="menu-icon-svg"
          width="30"
          height="30"
          ref="menu_icon"
        />
      </div>
      <input
        :id="id"
        :type="type"
        class="text-input"
        :value="modelValue"
        :placeholder="placeholder"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement)?.value)"
        @focus="focusSearch(true)"
        @focusout="focusSearch(false)"
        ref="text_input"
      />
      <btn class="search-btn"
        @click="focusSearch(true)"
        ref="search_icon"
        >
        <v-img
          src="/icons/search.svg"
          class="search-icon-svg"
          width="24"
          height="24"
        />
      </btn>
    </div>
  </div>
</template>

<script setup lang="ts">
import '../assets/base.css'
import { ref } from 'vue'

const text_input = ref<HTMLElement | null>(null)
const input_wrapper = ref<HTMLElement | null>(null)

defineEmits(['update:modelValue'])

const focusSearch = (shouldFocus: boolean) => {
  if (shouldFocus) {
    text_input.value?.focus()
    input_wrapper.value?.style.setProperty('border', '1px solid var(--vt-c-blue-dark)')
    input_wrapper.value?.style.setProperty('border-radius', 'var(--border-radius-default)')
  } else {
    text_input.value?.blur()
    input_wrapper.value?.style.setProperty('border', '1px solid var(--color-border-default)')
  }
}

defineProps<{
  modelValue: string
  id?: string
  type?: string
  placeholder?: string
  // We give an axios search function as a prop
  searchFunction?: (query: string) => void
}>()
</script>

<style scoped>
.text-input-md {
  border: 1px solid var(--color-border-default);
  border-radius: var(--border-radius-default);
  overflow: hidden;
  width: 330px;
  align-items: center;
}

.text-input {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  padding-left: 0.2rem;
  border: none;
  font-size: var(--font-size-md);

  width: auto;

  &:focus {
    outline: none;
  }
}

.text-caption {
  margin-left: 0.5rem;
  color: var(--color-text-input-fg-label);
  font-size: var(--font-size-sm);
}

.menu-icon,
.search-btn {
  padding: 0.5rem;
  cursor: pointer;
}

.text-input-md:focus-within .menu-icon,
.text-input-md:focus-within .text-input,
.text-input-md:focus-within .search-btn {
  border-color: 1px solid var(--vt-c-blue-dark); /* Or your desired color */
}

</style>

<template>
  <v-btn
    class="boton-md"
    :style="buttonStyle"
  >
    <slot></slot>
  </v-btn>
</template>

<script setup lang="ts">
import '../assets/base.css'
import { computed } from 'vue'

// Receive type of button to get colors
const props = defineProps<{
  color_type?: string
}>()

function getCSSVar(variable: string): string {
  const root = document.documentElement
  const style = getComputedStyle(root)
  return style.getPropertyValue(variable).trim()
}

const buttonStyle = computed(() => {
  let fg_color = getCSSVar('--color-text-blue')
  let bg_color = getCSSVar('--color-btn-white-bg')
  let border_color = getCSSVar('--color-border-blue')

  if (props.color_type === 'blue') {
    fg_color = getCSSVar('--color-text-white')
    bg_color = getCSSVar('--color-btn-blue-bg')
    border_color = 'transparent'
  } else if (props.color_type === 'white_green') {
    const green = getCSSVar('--color-save')
    fg_color = green
    border_color = green
  } else if (props.color_type === 'white_red') {
    const red = getCSSVar('--color-error')
    fg_color = red
    border_color = red
  } else if (props.color_type === 'white_purple') {
    const purple = getCSSVar('--color-edit')
    fg_color = purple
    border_color = purple
  }

  return {
    backgroundColor: bg_color,
    color: fg_color,
    border: `1px solid ${border_color}`
  }
})
</script>

<style scoped>
.boton-md {
  font-size: var(--font-size-md);
  font-weight: 500;
  border-radius: var(--border-radius-default);
  width: var(--input-width-md);
  height: var(--input-height-lg);
  text-transform: none;
  font-family: 'Inter', sans-serif;
}

@media (max-width: 1366px) {
  .boton-md {
    font-size: 1.2rem;
    height: 46px;
  }
}

@media (max-width: 1024px) {
  .boton-md {
    font-size: 1rem;
    width: var(--input-width-md);
  }
}

@media (max-width: 768px) {
  .boton-md {
    font-size: 0.9rem;
    width: var(--input-width-sm);
  }
}

@media (max-width: 600px) {
  .boton-md {
    font-size: 0.8rem;
    width: var(--input-width-md);
  }
}
</style>

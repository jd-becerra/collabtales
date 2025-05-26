<template>
  <v-btn
    class="boton-sm"
    :style="buttonStyle"
  >
   <div
    class="center-div"
    :style="{ gap: props.gap || '2rem' }"
   >
     <v-img
        v-if="icon_path"
        :src="icon_path"
        class="icon"
        contain
        :width="icon_size || '28px'"
        :height="icon_size || '28px'"
     />
      <slot class="boton-name"></slot>
   </div>
  </v-btn>
</template>

<script setup lang="ts">
import '../assets/base.css'
import { computed } from 'vue'

// Receive type of button to get colors
const props = defineProps<{
  color_type?: string,
  icon_path?: string,
  icon_size?: string,
  gap?: string
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
.boton-sm {
  font-size: var(--font-size-md);
  font-weight: 500;
  border-radius: var(--border-radius-default);
  width: var(--input-width-sm);
  height: var(--input-height-sm);
  text-transform: none;
  font-family: 'Inter', sans-serif;

  display: flex;
}

.center-div {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}
</style>

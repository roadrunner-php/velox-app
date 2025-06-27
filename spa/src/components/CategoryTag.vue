<script setup lang="ts">
import type { PluginCategory } from '@/api/pluginsApi.ts'
import type { Tag } from '@/components/FilterTags.vue'

const props = defineProps<{
  label: string
  value: string
  isActive: boolean
}>()

const emit = defineEmits<{
  (e: 'click', value: Tag | PluginCategory): void
}>()

function handleClick() {
  emit('click', {value: props.value, label: props.label})
}

function handleKeydown(event: KeyboardEvent) {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    handleClick()
  }
}
</script>

<template>
  <button
    @click="handleClick"
    @keydown="handleKeydown"
    class="px-2 py-0.5 rounded text-xs font-medium border transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
    :class="{
      'bg-blue-600 text-white border-blue-600 shadow-sm hover:bg-blue-700': props.isActive,
      'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400 hover:shadow-sm': !props.isActive,
    }"
    :aria-pressed="props.isActive"
    :aria-label="`${props.isActive ? 'Remove' : 'Add'} ${props.label} filter`"
    tabindex="0"
  >
    <span class="flex items-center gap-1">
      {{ props.label }}
    </span>
  </button>
</template>

<style scoped>
/* Ensure smooth transitions for all properties */
button {
  transition-property: background-color, border-color, color, box-shadow, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Subtle active state animation */
button:active {
  transform: scale(0.98);
}

/* Focus state improvements */
button:focus-visible {
  ring-offset-width: 1px;
}

/* Smooth checkmark animation */
span span {
  transition: opacity 0.15s ease-in-out;
}
</style>

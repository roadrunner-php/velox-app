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
  emit('click', { value: props.value, label: props.label })
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
      'bg-gray-700 text-gray-100 border-gray-700 hover:bg-gray-600 hover:border-gray-400 hover:shadow-sm':
        !props.isActive,
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

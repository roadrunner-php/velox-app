<script setup lang="ts">
const props = defineProps<{
  label: string
  value: string
  isActive: boolean
}>()

const emit = defineEmits<{
  (e: 'click', value: string): void
}>()

function handleClick() {
  emit('click', props.value)
}
</script>

<template>
  <button
    @click="handleClick"
    class="px-3 py-1 rounded-full text-sm font-medium border transition cursor-pointer"
    :class="{
      'bg-blue-600 text-white border-blue-600': props.isActive,
      'bg-gray-100 text-gray-800 border-gray-300 hover:bg-gray-200': !props.isActive,
    }"
  >
    {{ props.label }}
  </button>
</template>

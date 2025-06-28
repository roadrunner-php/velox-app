<template>
  <div class="text-center py-16">
    <!-- Icon -->
    <div class="text-slate-500 mb-6">
      <component :is="iconComponent" class="w-20 h-20 mx-auto" />
    </div>
    
    <!-- Title -->
    <h3 class="text-xl font-semibold text-white mb-3">
      {{ title }}
    </h3>
    
    <!-- Description -->
    <p class="text-slate-400 text-lg mb-6 max-w-md mx-auto leading-relaxed">
      {{ description }}
    </p>
    
    <!-- Action Button -->
    <button
      v-if="showActionButton"
      @click="$emit('action')"
      :disabled="actionDisabled"
      class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-slate-600 disabled:text-slate-400 disabled:cursor-not-allowed transition-colors duration-200 font-medium"
    >
      {{ actionText }}
    </button>

    <!-- Secondary Actions Slot -->
    <div v-if="$slots.actions" class="mt-4">
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, h } from 'vue'

interface Props {
  title?: string
  description?: string
  iconType?: 'search' | 'empty' | 'error' | 'filter' | 'custom'
  customIcon?: any
  showActionButton?: boolean
  actionText?: string
  actionDisabled?: boolean
}

interface Emits {
  (e: 'action'): void
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Nothing found',
  description: 'Try adjusting your search or filter criteria',
  iconType: 'empty',
  showActionButton: false,
  actionText: 'Take Action',
  actionDisabled: false
})

const emit = defineEmits<Emits>()

const iconComponent = computed(() => {
  if (props.iconType === 'custom' && props.customIcon) {
    return props.customIcon
  }

  const iconPaths = {
    search: "M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z",
    empty: "M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2m0 0V6a2 2 0 012-2h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V9M6 13v2a2 2 0 002 2h2",
    error: "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z",
    filter: "M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"
  }

  return () => h('svg', {
    fill: 'none',
    stroke: 'currentColor',
    viewBox: '0 0 24 24',
    'stroke-width': '1'
  }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      d: iconPaths[props.iconType] || iconPaths.empty
    })
  ])
})
</script>

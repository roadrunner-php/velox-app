<template>
  <div 
    v-if="isVisible"
    class="p-4 bg-gradient-to-r from-blue-900/20 to-blue-800/20 border border-blue-500/30 rounded-xl backdrop-blur-sm"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4 text-sm">
        <span class="font-semibold text-blue-200">
          {{ summary.manual }} {{ itemType }}{{ summary.manual === 1 ? '' : 's' }} selected
        </span>
        <span v-if="summary.dependencies > 0" class="text-blue-300">
          + {{ summary.dependencies }} {{ dependencyType }}{{ summary.dependencies === 1 ? 'y' : 'ies' }}
        </span>
        <span class="font-bold text-blue-100">
          = {{ summary.total }} total
        </span>
        <span v-if="summary.totalPlugins !== undefined" class="text-green-400">
          ({{ summary.totalPlugins }} plugins)
        </span>
      </div>

      <button
        v-if="showClearButton"
        @click="$emit('clearAll')"
        class="text-sm text-red-400 hover:text-red-300 font-medium transition-colors duration-200 bg-red-900/20 hover:bg-red-800/30 px-3 py-1 rounded-lg border border-red-500/30"
      >
        Clear All
      </button>
    </div>

    <!-- Selected Items Display -->
    <div v-if="showSelectedItems && selectedItems.length > 0" class="mt-4 flex flex-wrap gap-2">
      <span
        v-for="item in selectedItems"
        :key="item"
        class="bg-blue-800/40 text-blue-200 text-xs font-medium px-3 py-1 rounded-full border border-blue-600/40 backdrop-blur-sm"
      >
        {{ item }}
      </span>
    </div>

    <!-- Additional Details Slot -->
    <div v-if="$slots.details" class="mt-4">
      <slot name="details"></slot>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface SelectionSummary {
  manual: number
  dependencies?: number
  total: number
  totalPlugins?: number
}

interface Props {
  summary: SelectionSummary
  selectedItems?: string[]
  itemType?: string
  dependencyType?: string
  showClearButton?: boolean
  showSelectedItems?: boolean
}

interface Emits {
  (e: 'clearAll'): void
}

const props = withDefaults(defineProps<Props>(), {
  selectedItems: () => [],
  itemType: 'item',
  dependencyType: 'dependenc',
  showClearButton: true,
  showSelectedItems: true
})

const emit = defineEmits<Emits>()

const isVisible = computed(() => props.summary.total > 0)
</script>

<style scoped>
/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Focus styles for accessibility */
button:focus-visible {
  outline: 2px solid #ef4444;
  outline-offset: 2px;
}

/* Enhanced hover effects */
button:hover {
  transform: translateY(-1px);
}

button:active {
  transform: scale(0.98);
}

/* Gradient background animation */
.bg-gradient-to-r {
  background-size: 200% 200%;
  animation: gradient-shift 6s ease infinite;
}

@keyframes gradient-shift {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

/* Responsive design improvements */
@media (max-width: 640px) {
  .flex-wrap {
    gap: 0.25rem;
  }
  
  .text-xs {
    font-size: 0.7rem;
  }
}
</style>

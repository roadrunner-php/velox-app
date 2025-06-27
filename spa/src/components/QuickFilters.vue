<template>
  <div class="space-y-4">
    <!-- Quick Filters Row -->
    <div class="flex flex-wrap gap-2">
      <button
        v-for="filter in quickFilters"
        :key="filter.id"
        @click="toggleQuickFilter(filter.id)"
        :class="[
          'px-3 py-1.5 text-sm font-medium rounded-lg transition-all duration-200 border',
          isQuickFilterActive(filter.id) 
            ? 'bg-blue-600 text-white border-blue-500 shadow-lg shadow-blue-500/20'
            : 'bg-slate-800/60 text-slate-300 border-slate-600 hover:bg-slate-700 hover:text-white hover:border-slate-500'
        ]"
      >
        <span class="flex items-center gap-1.5">
          <component v-if="filter.icon" :is="filter.icon" class="w-3 h-3" />
          {{ filter.label }}
          <span v-if="filter.count !== undefined" class="text-xs opacity-75">
            ({{ filter.count }})
          </span>
        </span>
      </button>
    </div>

    <!-- Active Filters Summary -->
    <div v-if="hasActiveFilters" class="flex items-center justify-between p-3 bg-slate-800/40 border border-slate-600/50 rounded-lg">
      <div class="flex items-center gap-2 text-sm text-slate-300">
        <span class="font-medium text-white">Active filters:</span>
        <div class="flex flex-wrap gap-1">
          <span
            v-for="filter in activeFilterLabels"
            :key="filter"
            class="bg-slate-700/60 px-2 py-0.5 rounded text-xs"
          >
            {{ filter }}
          </span>
        </div>
      </div>
      
      <button
        @click="clearAllFilters"
        class="text-xs text-red-400 hover:text-red-300 font-medium transition-colors bg-red-900/20 hover:bg-red-800/30 px-2 py-1 rounded border border-red-500/30"
      >
        Clear All
      </button>
    </div>

    <!-- Custom Filter Slots -->
    <div v-if="$slots.customFilters" class="border-t border-slate-700/50 pt-4">
      <slot name="customFilters"></slot>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface QuickFilter {
  id: string
  label: string
  icon?: any
  count?: number
  active?: boolean
}

interface Props {
  quickFilters?: QuickFilter[]
  activeFilters?: string[]
}

interface Emits {
  (e: 'toggleFilter', filterId: string): void
  (e: 'clearAll'): void
}

const props = withDefaults(defineProps<Props>(), {
  quickFilters: () => [],
  activeFilters: () => []
})

const emit = defineEmits<Emits>()

const hasActiveFilters = computed(() => {
  return props.activeFilters.length > 0 || 
         props.quickFilters.some(f => f.active)
})

const activeFilterLabels = computed(() => {
  const quickFilterLabels = props.quickFilters
    .filter(f => f.active)
    .map(f => f.label)
  
  return [...quickFilterLabels, ...props.activeFilters]
})

function isQuickFilterActive(filterId: string): boolean {
  const filter = props.quickFilters.find(f => f.id === filterId)
  return filter?.active || false
}

function toggleQuickFilter(filterId: string) {
  emit('toggleFilter', filterId)
}

function clearAllFilters() {
  emit('clearAll')
}
</script>

<style scoped>
/* Enhanced button effects */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: scale(0.98);
}

/* Focus styles */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Shadow effects */
.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
}

.shadow-blue-500\/20 {
  box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.2), 0 4px 6px -2px rgba(59, 130, 246, 0.1);
}

/* Responsive design */
@media (max-width: 640px) {
  .flex-wrap {
    gap: 0.375rem;
  }
  
  .text-xs {
    font-size: 0.7rem;
  }
}

/* Animation for filter badges */
.bg-slate-700\/60 {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>

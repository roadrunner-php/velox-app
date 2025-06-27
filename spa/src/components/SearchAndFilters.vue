<template>
  <div class="space-y-4">
    <!-- Search and Source Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="relative flex-1 max-w-md">
        <input
          type="text"
          :value="searchQuery"
          @input="$emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
          :placeholder="searchPlaceholder"
          class="w-full px-4 py-3 bg-slate-800/60 border border-slate-600 text-white rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-slate-400 transition-all duration-200"
        />
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <div v-if="showSourceFilter" class="flex gap-2 text-sm">
        <button
          @click="$emit('update:sourceFilter', 'all')"
          :class="[
            'px-4 py-3 rounded-lg font-medium transition-all duration-200',
            sourceFilter === 'all'
              ? 'bg-blue-600 text-white border border-blue-500 shadow-lg shadow-blue-500/20'
              : 'bg-slate-800/60 text-slate-300 border border-slate-600 hover:bg-slate-700 hover:text-white hover:border-slate-500',
          ]"
        >
          All Sources
        </button>
        <button
          @click="$emit('update:sourceFilter', 'official')"
          :class="[
            'px-4 py-3 rounded-lg font-medium transition-all duration-200',
            sourceFilter === 'official'
              ? 'bg-blue-600 text-white border border-blue-500 shadow-lg shadow-blue-500/20'
              : 'bg-slate-800/60 text-slate-300 border border-slate-600 hover:bg-slate-700 hover:text-white hover:border-slate-500',
          ]"
        >
          Official
        </button>
        <button
          @click="$emit('update:sourceFilter', 'community')"
          :class="[
            'px-4 py-3 rounded-lg font-medium transition-all duration-200',
            sourceFilter === 'community'
              ? 'bg-blue-600 text-white border border-blue-500 shadow-lg shadow-blue-500/20'
              : 'bg-slate-800/60 text-slate-300 border border-slate-600 hover:bg-slate-700 hover:text-white hover:border-slate-500',
          ]"
        >
          Community
        </button>
      </div>
    </div>

    <!-- Active Filters Summary -->
    <div 
      v-if="showActiveFilters && (hasActiveFilters || resultCount !== null)"
      class="p-3 bg-slate-800/40 border border-slate-600/50 rounded-lg backdrop-blur-sm"
    >
      <div class="flex items-center justify-between">
        <div v-if="hasActiveFilters" class="flex items-center gap-3 text-sm text-slate-300">
          <span class="font-medium text-white">Active filters:</span>
          <div class="flex flex-wrap gap-2">
            <span v-if="activeCategories?.length" class="bg-slate-700/60 px-2 py-1 rounded text-xs">
              {{ categoriesLabel }}: {{ activeCategories.join(', ') }}
            </span>
            <span v-if="searchQuery" class="bg-slate-700/60 px-2 py-1 rounded text-xs">
              Search: "{{ searchQuery }}"
            </span>
            <span v-if="sourceFilter !== 'all'" class="bg-slate-700/60 px-2 py-1 rounded text-xs">
              Source: {{ sourceFilter }}
            </span>
          </div>
        </div>
        <span v-if="resultCount !== null" class="text-xs text-slate-400 font-medium">
          {{ resultCount }} {{ itemType }}{{ resultCount === 1 ? '' : 's' }} shown
        </span>
      </div>
    </div>

    <!-- Clear All Filters Button -->
    <div v-if="hasActiveFilters && showClearAll" class="flex justify-center">
      <button
        @click="$emit('clearAllFilters')"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium"
      >
        Clear All Filters
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  searchQuery: string
  sourceFilter?: 'all' | 'official' | 'community'
  activeCategories?: string[]
  searchPlaceholder?: string
  showSourceFilter?: boolean
  showActiveFilters?: boolean
  showClearAll?: boolean
  resultCount?: number | null
  itemType?: string
  categoriesLabel?: string
}

interface Emits {
  (e: 'update:searchQuery', value: string): void
  (e: 'update:sourceFilter', value: 'all' | 'official' | 'community'): void
  (e: 'clearAllFilters'): void
}

const props = withDefaults(defineProps<Props>(), {
  sourceFilter: 'all',
  activeCategories: () => [],
  searchPlaceholder: 'Search...',
  showSourceFilter: true,
  showActiveFilters: true,
  showClearAll: true,
  resultCount: null,
  itemType: 'item',
  categoriesLabel: 'Categories'
})

const emit = defineEmits<Emits>()

const hasActiveFilters = computed(() => {
  return !!(
    props.activeCategories?.length ||
    props.searchQuery ||
    props.sourceFilter !== 'all'
  )
})
</script>

<style scoped>
/* Enhanced focus styles */
input:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

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
}

/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Enhanced shadow effects */
.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
}

.shadow-blue-500\/20 {
  box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.2), 0 4px 6px -2px rgba(59, 130, 246, 0.1);
}
</style>

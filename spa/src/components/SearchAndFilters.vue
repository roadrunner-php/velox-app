<template>
  <div class="search-and-filters">
    <!-- Search and Source Filters -->
    <div class="search-and-filters__main">
      <div class="search-and-filters__search">
        <input
          type="text"
          :value="searchQuery"
          @input="$emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
          :placeholder="searchPlaceholder"
          class="search-and-filters__input"
        />
        <div class="search-and-filters__search-icon">
          <svg class="search-and-filters__search-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <div v-if="showSourceFilter" class="search-and-filters__source">
        <button
          @click="$emit('update:sourceFilter', 'all')"
          :class="[
            'search-and-filters__source-button',
            sourceFilter === 'all' ? 'search-and-filters__source-button--active' : 'search-and-filters__source-button--inactive'
          ]"
        >
          All Sources
        </button>
        <button
          @click="$emit('update:sourceFilter', 'official')"
          :class="[
            'search-and-filters__source-button',
            sourceFilter === 'official' ? 'search-and-filters__source-button--active' : 'search-and-filters__source-button--inactive'
          ]"
        >
          Official
        </button>
        <button
          @click="$emit('update:sourceFilter', 'community')"
          :class="[
            'search-and-filters__source-button',
            sourceFilter === 'community' ? 'search-and-filters__source-button--active' : 'search-and-filters__source-button--inactive'
          ]"
        >
          Community
        </button>
      </div>
    </div>

    <!-- Active Filters Summary -->
    <div 
      v-if="showActiveFilters && (hasActiveFilters || resultCount !== null)"
      class="search-and-filters__summary"
    >
      <div class="search-and-filters__summary-content">
        <div v-if="hasActiveFilters" class="search-and-filters__active-filters">
          <span class="search-and-filters__active-label">Active filters:</span>
          <div class="search-and-filters__active-list">
            <span v-if="activeCategories?.length" class="search-and-filters__active-tag">
              {{ categoriesLabel }}: {{ activeCategoriesLabel }}
            </span>
            <span v-if="searchQuery" class="search-and-filters__active-tag">
              Search: "{{ searchQuery }}"
            </span>
            <span v-if="sourceFilter !== 'all'" class="search-and-filters__active-tag">
              Source: {{ sourceFilter }}
            </span>
          </div>
        </div>
        <span v-if="resultCount !== null" class="search-and-filters__count">
          {{ resultCount }} {{ itemType }}{{ resultCount === 1 ? '' : 's' }} shown
        </span>
      </div>
    </div>

    <!-- Clear All Filters Button -->
    <div v-if="hasActiveFilters && showClearAll" class="search-and-filters__clear">
      <button
        @click="$emit('clearAllFilters')"
        class="search-and-filters__clear-button"
      >
        Clear All Filters
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { PluginCategory } from '@/api/pluginsApi.ts'
import type { Tag } from '@/components/FilterTags.vue'

interface Props {
  searchQuery: string
  sourceFilter?: 'all' | 'official' | 'community'
  activeCategories?: PluginCategory[] | Tag[]
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

const activeCategoriesLabel = computed(() => {
  return props.activeCategories
    .map(c => c.label)
    .join(', ')
})
</script>

<style scoped>
.search-and-filters {
  @apply space-y-4;
}

.search-and-filters__main {
  @apply flex flex-col sm:flex-row sm:items-center gap-4;
}

.search-and-filters__search {
  @apply relative flex-1 max-w-md;
}

.search-and-filters__input {
  @apply w-full px-4 py-3 bg-slate-800/60 border border-slate-600 text-white rounded-lg text-sm;
  @apply focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-slate-400;
  @apply transition-all duration-200;
}

.search-and-filters__search-icon {
  @apply absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none;
}

.search-and-filters__search-icon-svg {
  @apply w-4 h-4 text-slate-400;
}

.search-and-filters__source {
  @apply flex gap-2 text-sm;
}

.search-and-filters__source-button {
  @apply px-4 py-3 rounded-lg font-medium transition-all duration-200 border;
}

.search-and-filters__source-button--active {
  @apply bg-blue-600 text-white border-blue-500 shadow-lg shadow-blue-500/20;
}

.search-and-filters__source-button--inactive {
  @apply bg-slate-800/60 text-slate-300 border-slate-600 hover:bg-slate-700 hover:text-white hover:border-slate-500;
}

.search-and-filters__summary {
  @apply p-3 bg-slate-800/40 border border-slate-600/50 rounded-lg backdrop-blur-sm;
}

.search-and-filters__summary-content {
  @apply flex items-center justify-between;
}

.search-and-filters__active-filters {
  @apply flex items-center gap-3 text-sm text-slate-300;
}

.search-and-filters__active-label {
  @apply font-medium text-white;
}

.search-and-filters__active-list {
  @apply flex flex-wrap gap-2;
}

.search-and-filters__active-tag {
  @apply bg-slate-700/60 px-2 py-1 rounded text-xs;
}

.search-and-filters__count {
  @apply text-xs text-slate-400 font-medium;
}

.search-and-filters__clear {
  @apply flex justify-center;
}

.search-and-filters__clear-button {
  @apply px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium;
}
</style>

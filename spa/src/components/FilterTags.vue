<template>
  <div class="filter-tags">
    <!-- Filter Tags Section -->
    <div v-if="tags.length > 0">
      <div class="filter-tags__header">
        <h2 class="filter-tags__title">
          {{ title }}
        </h2>
        <button
          v-if="activeTags.length > 0 && showClearButton"
          @click="$emit('clearAll')"
          class="filter-tags__clear-button"
        >
          Clear All ({{ activeTags.length }})
        </button>
      </div>
      
      <div class="filter-tags__list">
        <CategoryTag
          v-for="tag in tags"
          :key="getTagValue(tag)"
          :label="getTagLabel(tag)"
          :value="getTagValue(tag)"
          :is-active="activeTags.some(t => t.value === tag.value)"
          @click="handleTagClick"
        />
      </div>
    </div>

    <!-- No Tags Available State -->
    <div v-else-if="showEmptyState" class="filter-tags__empty">
      <div class="filter-tags__empty-icon">
        <svg class="filter-tags__empty-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        </svg>
      </div>
      <p class="filter-tags__empty-text">
        {{ emptyStateMessage }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import CategoryTag from './CategoryTag.vue'
import type { PluginCategory } from '@/api/pluginsApi.ts'

export interface Tag {
  value: string
  label: string
}

interface Props {
  tags: PluginCategory[] | Tag[]
  activeTags: PluginCategory[] | Tag[]
  title?: string
  showClearButton?: boolean
  showEmptyState?: boolean
  emptyStateMessage?: string
  labelKey?: string
  valueKey?: string
}

interface Emits {
  (e: 'toggle', value: Tag | PluginCategory): void
  (e: 'clearAll'): void
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Filter by Categories',
  showClearButton: true,
  showEmptyState: true,
  emptyStateMessage: 'No tags available',
  labelKey: 'label',
  valueKey: 'value'
})

const emit = defineEmits<Emits>()

function getTagLabel(tag: Tag | PluginCategory): string {
  return tag.label || tag.value
}

function getTagValue(tag: Tag | PluginCategory): string {
  return tag.value
}

function handleTagClick(value: Tag | PluginCategory): void {
  emit('toggle', value)
}
</script>

<style scoped>
.filter-tags {
  @apply space-y-6;
}

.filter-tags__header {
  @apply flex items-center justify-between mb-4;
}

.filter-tags__title {
  @apply text-xl font-semibold text-white;
}

.filter-tags__clear-button {
  @apply text-sm text-slate-400 hover:text-white font-medium transition-colors duration-200;
  @apply bg-slate-800/40 hover:bg-slate-700/60 px-3 py-1 rounded-lg border border-slate-600/50;
}

.filter-tags__list {
  @apply flex flex-wrap gap-2;
}

.filter-tags__empty {
  @apply text-center py-8;
}

.filter-tags__empty-icon {
  @apply text-slate-500 mb-4;
}

.filter-tags__empty-icon-svg {
  @apply w-12 h-12 mx-auto;
}

.filter-tags__empty-text {
  @apply text-slate-400;
}
</style>

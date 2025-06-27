<template>
  <div class="space-y-6">
    <!-- Filter Tags Section -->
    <div v-if="tags.length > 0">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-white">
          {{ title }}
        </h2>
        <button
          v-if="activeTags.length > 0 && showClearButton"
          @click="$emit('clearAll')"
          class="text-sm text-slate-400 hover:text-white font-medium transition-colors duration-200 bg-slate-800/40 hover:bg-slate-700/60 px-3 py-1 rounded-lg border border-slate-600/50"
        >
          Clear All ({{ activeTags.length }})
        </button>
      </div>
      
      <div class="flex flex-wrap gap-2">
        <CategoryTag
          v-for="tag in tags"
          :key="tag"
          :label="getTagLabel(tag)"
          :value="tag"
          :is-active="activeTags.includes(tag)"
          @click="handleTagClick"
        />
      </div>
    </div>

    <!-- No Tags Available State -->
    <div v-else-if="showEmptyState" class="text-center py-8">
      <div class="text-slate-500 mb-4">
        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        </svg>
      </div>
      <p class="text-slate-400">
        {{ emptyStateMessage }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import CategoryTag from './CategoryTag.vue'

interface Tag {
  value: string
  label?: string
}

interface Props {
  tags: string[] | Tag[]
  activeTags: string[]
  title?: string
  showClearButton?: boolean
  showEmptyState?: boolean
  emptyStateMessage?: string
  labelKey?: string
  valueKey?: string
}

interface Emits {
  (e: 'toggle', value: string): void
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

function getTagLabel(tag: string | Tag): string {
  if (typeof tag === 'string') {
    return tag
  }
  return tag.label || tag.value
}

function getTagValue(tag: string | Tag): string {
  if (typeof tag === 'string') {
    return tag
  }
  return tag.value
}

function handleTagClick(value: string) {
  emit('toggle', value)
}
</script>

<style scoped>
/* Focus styles for accessibility */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Enhanced hover effects */
button:hover {
  transform: translateY(-1px);
}

button:active {
  transform: scale(0.98);
}

/* Responsive design */
@media (max-width: 640px) {
  .flex-wrap {
    gap: 0.375rem;
  }
  
  .text-xl {
    font-size: 1.125rem;
  }
}

/* Empty state animation */
.text-slate-500 svg {
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}
</style>

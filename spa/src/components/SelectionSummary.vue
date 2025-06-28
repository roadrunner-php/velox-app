<template>
  <div
    v-if="isVisible"
    class="selection-summary"
  >
    <div class="selection-summary__content">
      <div class="selection-summary__stats">
        <span class="selection-summary__stat selection-summary__stat--primary">
          {{ summary.manual }} {{ itemType }}{{ summary.manual === 1 ? '' : 's' }} selected
        </span>
        <span v-if="summary.dependencies > 0" class="selection-summary__stat selection-summary__stat--secondary">
          + {{ summary.dependencies }} {{ dependencyType
          }}{{ summary.dependencies === 1 ? 'y' : 'ies' }}
        </span>
        <span class="selection-summary__stat selection-summary__stat--total"> = {{ summary.total }} total </span>
        <span v-if="summary.totalPlugins !== undefined" class="selection-summary__stat selection-summary__stat--plugins">
          ({{ summary.totalPlugins }} plugins)
        </span>
      </div>

      <button
        v-if="showClearButton"
        @click="$emit('clearAll')"
        class="selection-summary__clear-button"
      >
        Clear All
      </button>
    </div>

    <!-- Selected Items Display -->
    <div v-if="showSelectedItems && selectedItems.length > 0" class="selection-summary__items">
      <span
        v-for="item in selectedItems"
        :key="item"
        class="selection-summary__item"
      >
        {{ item }}
      </span>
    </div>

    <!-- Additional Details Slot -->
    <div v-if="$slots.details" class="selection-summary__details">
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
  showSelectedItems: true,
})

const emit = defineEmits<Emits>()

const isVisible = computed(() => props.summary.total > 0)
</script>

<style scoped>
.selection-summary {
  @apply p-4 bg-gradient-to-r from-blue-900/20 to-blue-800/20 border border-blue-500/30 rounded-xl backdrop-blur-sm;
}

.selection-summary__content {
  @apply flex items-center justify-between;
}

.selection-summary__stats {
  @apply flex items-center gap-4 text-sm;
}

.selection-summary__stat {
  @apply font-semibold;
}

.selection-summary__stat--primary {
  @apply text-blue-200;
}

.selection-summary__stat--secondary {
  @apply text-blue-300;
}

.selection-summary__stat--total {
  @apply font-bold text-blue-100;
}

.selection-summary__stat--plugins {
  @apply text-green-400;
}

.selection-summary__clear-button {
  @apply text-sm text-red-400 hover:text-red-300 font-medium transition-colors duration-200;
  @apply bg-red-900/20 hover:bg-red-800/30 px-3 py-1 rounded-lg border border-red-500/30;
}

.selection-summary__items {
  @apply mt-4 flex flex-wrap gap-2;
}

.selection-summary__item {
  @apply bg-blue-800/40 text-blue-200 text-xs font-medium px-3 py-1 rounded-full border border-blue-600/40 backdrop-blur-sm;
}

.selection-summary__details {
  @apply mt-4;
}
</style>

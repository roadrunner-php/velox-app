<template>
  <div class="preset-card" :class="cardClasses" @click="handleCardClick" @mouseenter="isHovered = true" @mouseleave="isHovered = false" role="button" tabindex="0" :aria-label="`Preset ${preset.display_name || preset.name}, ${selectionText.toLowerCase()}`" @keydown.enter="handleToggleSelection" @keydown.space.prevent="handleToggleSelection">
    <div class="preset-card__content">
      <!-- Selection Status Badge -->
      <div
        v-if="isSelected"
        class="preset-card__selection-badge"
        :class="selectionIndicatorClasses"
      >
        {{ selectionText }}
      </div>

      <!-- Official/Community Badge -->
      <div
        class="preset-card__source-badge"
        :class="preset.is_official ? 'preset-card__source-badge--official' : 'preset-card__source-badge--community'"
      >
        <!-- Certificate icon for official presets -->
        <svg
          v-if="preset.is_official"
          class="preset-card__source-icon"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <!-- Globe icon for community presets -->
        <svg
          v-else
          class="preset-card__source-icon"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
        </svg>
        <span>{{ preset.is_official ? 'Official' : 'Community' }}</span>
      </div>

      <!-- Priority Badge -->
      <div class="preset-card__priority-badge" :class="priorityColor">
        {{ priorityText }}
      </div>

      <!-- Main Content -->
      <div class="preset-card__main">
        <!-- Preset Name -->
        <h3 class="preset-card__title">
          {{ preset.display_name || preset.name }}
        </h3>

        <!-- Preset Description -->
        <p class="preset-card__description">
          {{ preset.description || 'No description available' }}
        </p>

        <!-- Preset Details -->
        <div class="preset-card__details">
          <span class="preset-card__detail-tag preset-card__detail-tag--plugins">
            {{ pluginText }}
          </span>
          <span
            v-if="preset.tags?.length"
            class="preset-card__detail-tag preset-card__detail-tag--tags"
          >
            {{ preset.tags.length }} tags
          </span>
          <span class="preset-card__detail-tag preset-card__detail-tag--priority">
            Priority: {{ preset.priority }}
          </span>
        </div>

        <!-- Tags Display -->
        <div v-if="preset.tags?.length" class="preset-card__tags">
          <div class="preset-card__tags-list">
            <span
              v-for="tag in preset.tags.slice(0, 4)"
              :key="tag"
              class="preset-card__tag"
            >
              {{ tag }}
            </span>
            <span
              v-if="preset.tags.length > 4"
              class="preset-card__tag preset-card__tag--more"
            >
              +{{ preset.tags.length - 4 }} more
            </span>
          </div>
        </div>

        <!-- Plugins Section -->
        <div v-if="preset.plugins.length > 0" class="preset-card__plugins-section">
          <button
            @click.stop="handleLoadDependencies"
            class="preset-card__plugins-toggle"
            :disabled="isLoadingDependencies"
          >
            <span>{{ pluginText }}</span>
            <svg
              class="preset-card__plugins-icon"
              :class="{ 'preset-card__plugins-icon--expanded': showPluginDetails }"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
              />
            </svg>
          </button>

          <!-- Plugin Details -->
          <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-96"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 max-h-96"
            leave-to-class="opacity-0 max-h-0"
          >
            <div v-if="showPluginDetails" class="preset-card__plugin-details">
              <!-- Loading State -->
              <div
                v-if="isLoadingDependencies"
                class="preset-card__plugin-loading"
              >
                <div class="preset-card__plugin-spinner"></div>
                <span>Loading plugin details...</span>
              </div>

              <!-- Plugin List -->
              <div v-else class="preset-card__plugin-list">
                <div
                  v-for="pluginName in preset.plugins"
                  :key="pluginName"
                  class="preset-card__plugin-item"
                >
                  <div class="preset-card__plugin-info">
                    <div class="preset-card__plugin-indicator"></div>
                    <span class="preset-card__plugin-name">{{ pluginName }}</span>
                  </div>
                  <button
                    @click.stop="$emit('view-details', pluginName)"
                    class="preset-card__plugin-view-btn"
                  >
                    View Plugin
                  </button>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Selection Context -->
        <div v-if="selectionState === 'dependency' && selectedBy?.length" class="preset-card__context">
          <div class="preset-card__context-box preset-card__context-box--dependency">
            <span class="preset-card__context-label">Auto-selected:</span>
            Required by {{ selectedBy.join(', ') }}
          </div>
        </div>

        <div v-if="selectionState === 'conflict'" class="preset-card__context">
          <div class="preset-card__context-box preset-card__context-box--conflict">
            <span class="preset-card__context-label">Conflict detected:</span>
            This preset has conflicts with other selections
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="preset-card__actions">
      <button
        @click.stop="handleToggleSelection"
        class="preset-card__action-button"
        :class="isSelected ? 'preset-card__action-button--deselect' : 'preset-card__action-button--select'"
      >
        {{ isSelected ? 'Deselect' : 'Select' }}
      </button>

      <button
        @click.stop="handleViewDetails"
        class="preset-card__action-button preset-card__action-button--details"
      >
        Details
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import type { Preset } from '@/api/presetsApi'
import type { PresetSelectionState } from '@/types/preset'

interface Props {
  preset: Preset
  selectionState: PresetSelectionState
  isLoadingDependencies?: boolean
  showDependencies?: boolean
  selectedBy?: string[]
  requiredBy?: string[]
}

interface Emits {
  (e: 'toggle', name: string, includeDependencies: boolean): void
  (e: 'view-details', name: string): void
  (e: 'load-dependencies', name: string): void
}

const props = withDefaults(defineProps<Props>(), {
  selectionState: 'none',
  isLoadingDependencies: false,
  showDependencies: false,
  selectedBy: () => [],
  requiredBy: () => [],
})

const emit = defineEmits<Emits>()
const router = useRouter()

const showPluginDetails = ref(false)
const isHovered = ref(false)

// Computed properties for styling and state
const isSelected = computed(() => props.selectionState !== 'none')

const cardClasses = computed(() => {
  switch (props.selectionState) {
    case 'manual':
      return 'preset-card--selected'
    case 'dependency':
      return 'preset-card--dependency'
    case 'conflict':
      return 'preset-card--conflict'
    default:
      return 'preset-card--default'
  }
})

const selectionIndicatorClasses = computed(() => {
  switch (props.selectionState) {
    case 'manual':
      return 'preset-card__selection-badge--manual'
    case 'dependency':
      return 'preset-card__selection-badge--dependency'
    case 'conflict':
      return 'preset-card__selection-badge--conflict'
    default:
      return ''
  }
})

const selectionText = computed(() => {
  switch (props.selectionState) {
    case 'manual':
      return 'Selected'
    case 'dependency':
      return 'Auto-selected'
    case 'conflict':
      return 'Conflict'
    default:
      return 'Select'
  }
})

const pluginText = computed(() => {
  if (props.preset.plugin_count === 0) return 'No plugins'
  if (props.preset.plugin_count === 1) return '1 plugin'
  return `${props.preset.plugin_count} plugins`
})

const priorityText = computed(() => {
  if (props.preset.priority <= 10) return 'High priority'
  if (props.preset.priority <= 50) return 'Medium priority'
  return 'Low priority'
})

const priorityColor = computed(() => {
  if (props.preset.priority <= 10) return 'preset-card__priority-badge--high'
  if (props.preset.priority <= 50) return 'preset-card__priority-badge--medium'
  return 'preset-card__priority-badge--low'
})

// Event handlers
function handleCardClick(event: MouseEvent) {
  const target = event.target as HTMLElement

  // Don't trigger navigation if clicking on interactive elements
  if (target.closest('button') || target.closest('input') || target.closest('label')) {
    return
  }

  // For presets, we don't have detail pages, so just toggle selection
  handleToggleSelection(event)
}

function handleToggleSelection(event: MouseEvent) {
  event.stopPropagation()

  // For manual selections, include dependencies by default
  // For auto-selected dependencies, only toggle this specific preset
  const includeDependencies = props.selectionState !== 'dependency'
  emit('toggle', props.preset.name, includeDependencies)
}

function handleLoadDependencies() {
  if (!props.isLoadingDependencies) {
    emit('load-dependencies', props.preset.name)
  }
  showPluginDetails.value = !showPluginDetails.value
}

function handleViewDetails() {
  emit('view-details', props.preset.name)
}
</script>

<style scoped>
.preset-card {
  @apply relative p-6 rounded-2xl shadow-xl transition-all duration-300 cursor-pointer group border backdrop-blur-sm;
  @apply flex flex-col justify-between transform hover:-translate-y-1;
}

.preset-card--default {
  @apply bg-gradient-to-br from-slate-800/60 to-slate-900/40 border-slate-700/50;
  @apply hover:border-slate-600/70 hover:shadow-2xl hover:shadow-slate-900/50;
  @apply hover:bg-gradient-to-br hover:from-slate-800/80 hover:to-slate-900/60;
}

.preset-card--selected {
  @apply bg-gradient-to-br from-purple-900/40 to-purple-800/30 border-purple-500/50;
  @apply shadow-purple-500/20 hover:shadow-purple-500/30 hover:border-purple-400/70;
}

.preset-card--dependency {
  @apply bg-gradient-to-br from-green-900/40 to-green-800/30 border-green-500/50;
  @apply shadow-green-500/20 hover:shadow-green-500/30 hover:border-green-400/70;
}

.preset-card--conflict {
  @apply bg-gradient-to-br from-red-900/40 to-red-800/30 border-red-500/50;
  @apply shadow-red-500/20 hover:shadow-red-500/30 hover:border-red-400/70;
}

.preset-card__content {
  @apply flex-1;
}

.preset-card__selection-badge {
  @apply absolute -top-2 -right-2 px-3 py-1 text-xs font-bold rounded-full z-10 border border-white/10;
}

.preset-card__selection-badge--manual {
  @apply bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/30;
}

.preset-card__selection-badge--dependency {
  @apply bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-500/30;
}

.preset-card__selection-badge--conflict {
  @apply bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-500/30;
}

.preset-card__source-badge {
  @apply absolute top-3 left-3 text-xs font-medium z-10 flex items-center gap-1;
}

.preset-card__source-badge--official {
  @apply text-emerald-300;
}

.preset-card__source-badge--community {
  @apply text-gray-400;
}

.preset-card__source-icon {
  @apply w-6 h-6;
}

.preset-card__priority-badge {
  @apply absolute top-3 right-3 text-xs font-medium px-2 py-1 rounded-lg z-10 backdrop-blur-sm border;
}

.preset-card__priority-badge--high {
  @apply text-emerald-300 bg-emerald-900/20 border-emerald-500/30;
}

.preset-card__priority-badge--medium {
  @apply text-yellow-300 bg-yellow-900/20 border-yellow-500/30;
}

.preset-card__priority-badge--low {
  @apply text-slate-300 bg-slate-700/20 border-slate-600/30;
}

.preset-card__main {
  @apply mt-10;
}

.preset-card__title {
  @apply text-xl font-bold text-white mb-3 pr-8 group-hover:text-purple-300 transition-colors;
}

.preset-card__description {
  @apply text-slate-300 mb-4 leading-relaxed line-clamp-3 text-sm;
}

.preset-card__details {
  @apply flex flex-wrap gap-2 text-xs mb-4;
}

.preset-card__detail-tag {
  @apply px-2 py-1 rounded-lg border;
}

.preset-card__detail-tag--plugins {
  @apply bg-indigo-900/30 text-indigo-300 border-indigo-500/30;
}

.preset-card__detail-tag--tags {
  @apply bg-cyan-900/30 text-cyan-300 border-cyan-500/30;
}

.preset-card__detail-tag--priority {
  @apply bg-purple-900/30 text-purple-300 border-purple-500/30;
}

.preset-card__tags {
  @apply mb-4;
}

.preset-card__tags-list {
  @apply flex flex-wrap gap-2;
}

.preset-card__tag {
  @apply text-xs bg-slate-700/40 text-slate-300 px-2 py-1 rounded-lg border border-slate-600/30;
}

.preset-card__tag--more {
  @apply bg-slate-600/40 text-slate-400;
}

.preset-card__plugins-section {
  @apply mt-4;
}

.preset-card__plugins-toggle {
  @apply flex items-center gap-2 text-sm text-cyan-400 hover:text-cyan-300 transition-colors font-medium;
}

.preset-card__plugins-icon {
  @apply w-4 h-4 transition-transform duration-200;
}

.preset-card__plugins-icon--expanded {
  @apply rotate-180;
}

.preset-card__plugin-details {
  @apply mt-3 overflow-hidden;
}

.preset-card__plugin-loading {
  @apply flex items-center gap-2 text-sm text-slate-400;
}

.preset-card__plugin-spinner {
  @apply animate-spin rounded-full h-4 w-4 border-b-2 border-cyan-400;
}

.preset-card__plugin-list {
  @apply space-y-2 max-h-40 overflow-y-auto pr-2;
}

.preset-card__plugin-item {
  @apply flex items-center justify-between p-3 bg-slate-800/50 rounded-lg text-sm border border-slate-700/30;
  @apply hover:border-slate-600/50 transition-colors;
}

.preset-card__plugin-info {
  @apply flex items-center gap-2;
}

.preset-card__plugin-indicator {
  @apply w-2 h-2 bg-purple-400 rounded-full flex-shrink-0;
}

.preset-card__plugin-name {
  @apply font-medium text-white;
}

.preset-card__plugin-view-btn {
  @apply text-cyan-400 hover:text-cyan-300 text-xs font-medium transition-colors flex-shrink-0;
}

.preset-card__context {
  @apply mt-4;
}

.preset-card__context-box {
  @apply text-xs p-3 rounded-lg border;
}

.preset-card__context-box--dependency {
  @apply text-green-300 bg-green-900/20 border-green-500/30;
}

.preset-card__context-box--conflict {
  @apply text-red-300 bg-red-900/20 border-red-500/30;
}

.preset-card__context-label {
  @apply font-medium;
}

.preset-card__actions {
  @apply flex gap-3 mt-6;
}

.preset-card__action-button {
  @apply text-sm font-semibold rounded-xl transition-all duration-200 border;
}

.preset-card__action-button--select {
  @apply flex-1 px-4 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 text-white;
  @apply hover:from-purple-500 hover:to-purple-600 border-purple-500/50;
  @apply shadow-lg shadow-purple-500/20 hover:shadow-purple-500/30;
}

.preset-card__action-button--deselect {
  @apply flex-1 px-4 py-2.5 bg-slate-700/50 text-slate-300 hover:bg-slate-600/50;
  @apply border-slate-600/50 hover:border-slate-500/70;
}

.preset-card__action-button--details {
  @apply px-4 py-2.5 text-slate-300 border-slate-600/50 hover:bg-slate-700/30;
  @apply hover:border-slate-500/70 hover:text-white;
}
</style>

<template>
  <div class="plugin-card" :class="cardClasses" @click="handleCardClick" @mouseenter="isHovered = true" @mouseleave="isHovered = false" role="button" tabindex="0" :aria-label="`Plugin ${plugin.name}, ${selectionText.toLowerCase()}`" @keydown.enter="handleToggleSelection" @keydown.space.prevent="handleToggleSelection">
    <div class="plugin-card__content">
      <!-- Selection Status Badge -->
      <div
        v-if="isSelected"
        class="plugin-card__selection-badge"
        :class="selectionIndicatorClasses"
      >
        {{ selectionText }}
      </div>

      <!-- Official/Community Badge -->
      <div
        class="plugin-card__source-badge"
        :class="plugin.is_official ? 'plugin-card__source-badge--official' : 'plugin-card__source-badge--community'"
      >
        <!-- Certificate icon for official plugins -->
        <svg v-if="plugin.is_official" class="plugin-card__source-icon" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd"
          />
        </svg>
        <!-- Globe icon for community plugins -->
        <svg v-else class="plugin-card__source-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"
          />
        </svg>
        <span>{{ plugin.is_official ? 'Official' : 'Community' }}</span>
      </div>

      <!-- Main Content -->
      <div class="plugin-card__main">
        <!-- Plugin Name -->
        <h3 class="plugin-card__title">
          {{ plugin.name }}
        </h3>

        <!-- Plugin Description -->
        <p class="plugin-card__description">
          {{ plugin.description || 'No description available' }}
        </p>

        <!-- Plugin Details -->
        <div class="plugin-card__details">
          <span
            v-if="plugin.category"
            class="plugin-card__detail-tag plugin-card__detail-tag--category"
          >
            {{ plugin.category }}
          </span>
          <span class="plugin-card__detail-tag plugin-card__detail-tag--version">
            {{ plugin.version }}
          </span>
          <span class="plugin-card__detail-tag plugin-card__detail-tag--owner">
            {{ plugin.owner }}
          </span>
        </div>

        <!-- Dependencies Section -->
        <div v-if="plugin.dependencies.length > 0" class="plugin-card__dependencies-section">
          <button
            @click.stop="handleLoadDependencies"
            class="plugin-card__dependencies-toggle"
            :disabled="isLoadingDependencies"
          >
            <span>{{ dependencyText }}</span>
            <svg
              class="plugin-card__dependencies-icon"
              :class="{ 'plugin-card__dependencies-icon--expanded': showDependencyDetails }"
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

          <!-- Dependency Details -->
          <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-96"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 max-h-96"
            leave-to-class="opacity-0 max-h-0"
          >
            <div v-if="showDependencyDetails" class="plugin-card__dependency-details">
              <!-- Loading State -->
              <div
                v-if="isLoadingDependencies"
                class="plugin-card__dependency-loading"
              >
                <div class="plugin-card__dependency-spinner"></div>
                <span>Loading dependencies...</span>
              </div>

              <!-- Dependency List -->
              <div v-else-if="dependencies?.length" class="plugin-card__dependency-list">
                <div
                  v-for="dep in dependencies"
                  :key="dep.name"
                  class="plugin-card__dependency-item"
                >
                  <div class="plugin-card__dependency-info">
                    <span class="plugin-card__dependency-name">{{ dep.name }}</span>
                    <span class="plugin-card__dependency-version">{{ dep.version }}</span>
                  </div>
                </div>
              </div>

              <!-- Declared Dependencies (fallback) -->
              <div v-else class="plugin-card__dependency-list">
                <div
                  v-for="depName in plugin.dependencies"
                  :key="depName"
                  class="plugin-card__dependency-item"
                >
                  <span class="plugin-card__dependency-name">{{ depName }}</span>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Selection Context -->
        <div v-if="selectionState === 'dependency' && selectedBy?.length" class="plugin-card__context">
          <div class="plugin-card__context-box plugin-card__context-box--dependency">
            <span class="plugin-card__context-label">Auto-selected:</span>
            Required by {{ selectedBy.join(', ') }}
          </div>
        </div>

        <div v-if="selectionState === 'conflict'" class="plugin-card__context">
          <div class="plugin-card__context-box plugin-card__context-box--conflict">
            <span class="plugin-card__context-label">Conflict detected:</span>
            This plugin has dependency conflicts
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="plugin-card__actions">
      <button
        @click.stop="handleToggleSelection"
        class="plugin-card__action-button"
        :class="isSelected ? 'plugin-card__action-button--deselect' : 'plugin-card__action-button--select'"
      >
        {{ isSelected ? 'Deselect' : 'Select' }}
      </button>

      <button
        @click.stop="handleViewDetails"
        class="plugin-card__action-button plugin-card__action-button--details"
      >
        Details
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import type { Plugin } from '@/api/pluginsApi'
import type { PluginSelectionState } from '@/types/plugin'

interface Props {
  plugin: Plugin
  selectionState: PluginSelectionState
  dependencies?: Plugin[]
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
  dependencies: () => [],
  isLoadingDependencies: false,
  showDependencies: false,
  selectedBy: () => [],
  requiredBy: () => [],
})

const emit = defineEmits<Emits>()
const router = useRouter()

const showDependencyDetails = ref(false)
const isHovered = ref(false)

// Computed properties for styling and state
const isSelected = computed(() => props.selectionState !== 'none')

const cardClasses = computed(() => {
  switch (props.selectionState) {
    case 'manual':
      return 'plugin-card--selected'
    case 'dependency':
      return 'plugin-card--dependency'
    case 'conflict':
      return 'plugin-card--conflict'
    default:
      return 'plugin-card--default'
  }
})

const selectionIndicatorClasses = computed(() => {
  switch (props.selectionState) {
    case 'manual':
      return 'plugin-card__selection-badge--manual'
    case 'dependency':
      return 'plugin-card__selection-badge--dependency'
    case 'conflict':
      return 'plugin-card__selection-badge--conflict'
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

const dependencyText = computed(() => {
  if (props.plugin.dependencies.length === 0) return 'No dependencies'
  if (props.plugin.dependencies.length === 1) return '1 dependency'
  return `${props.plugin.dependencies.length} dependencies`
})

// Event handlers
function handleCardClick(event: MouseEvent) {
  const target = event.target as HTMLElement

  // Don't trigger navigation if clicking on interactive elements
  if (target.closest('button') || target.closest('input') || target.closest('label')) {
    return
  }

  emit('view-details', props.plugin.name)
}

function handleToggleSelection(event: MouseEvent) {
  event.stopPropagation()

  // For manual selections, include dependencies by default
  // For auto-selected dependencies, only toggle this specific plugin
  const includeDependencies = props.selectionState !== 'dependency'
  emit('toggle', props.plugin.name, includeDependencies)
}

function handleLoadDependencies() {
  if (!props.dependencies?.length && !props.isLoadingDependencies) {
    emit('load-dependencies', props.plugin.name)
  }
  showDependencyDetails.value = !showDependencyDetails.value
}

function handleViewDetails() {
  router.push(`/plugins/${props.plugin.name}`)
}

// Auto-load dependencies when hovering if not already loaded
watch(isHovered, (hovered) => {
  if (
    hovered &&
    props.plugin.dependencies.length > 0 &&
    !props.dependencies?.length &&
    !props.isLoadingDependencies
  ) {
    emit('load-dependencies', props.plugin.name)
  }
})
</script>

<style scoped>
.plugin-card {
  @apply relative p-6 rounded-2xl shadow-xl transition-all duration-300 cursor-pointer group border backdrop-blur-sm;
  @apply flex flex-col justify-between transform hover:-translate-y-1;
}

.plugin-card--default {
  @apply bg-gradient-to-br from-gray-800/60 to-gray-900/40 border-gray-700/50;
  @apply hover:border-gray-600/70 hover:shadow-2xl hover:shadow-gray-900/50;
  @apply hover:bg-gradient-to-br hover:from-gray-800/80 hover:to-gray-900/60;
}

.plugin-card--selected {
  @apply bg-gradient-to-br from-blue-900/40 to-blue-800/30 border-blue-500/50;
  @apply shadow-blue-500/20 hover:shadow-blue-500/30 hover:border-blue-400/70;
}

.plugin-card--dependency {
  @apply bg-gradient-to-br from-green-900/40 to-green-800/30 border-green-500/50;
  @apply shadow-green-500/20 hover:shadow-green-500/30 hover:border-green-400/70;
}

.plugin-card--conflict {
  @apply bg-gradient-to-br from-red-900/40 to-red-800/30 border-red-500/50;
  @apply shadow-red-500/20 hover:shadow-red-500/30 hover:border-red-400/70;
}

.plugin-card__content {
  @apply flex-1;
}

.plugin-card__selection-badge {
  @apply absolute -top-2 -right-2 px-3 py-1 text-xs font-bold rounded-full z-10 border border-white/10;
}

.plugin-card__selection-badge--manual {
  @apply bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/30;
}

.plugin-card__selection-badge--dependency {
  @apply bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-500/30;
}

.plugin-card__selection-badge--conflict {
  @apply bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-500/30;
}

.plugin-card__source-badge {
  @apply absolute top-3 left-3 text-xs font-medium z-10 flex items-center gap-1;
}

.plugin-card__source-badge--official {
  @apply text-emerald-300;
}

.plugin-card__source-badge--community {
  @apply text-gray-400;
}

.plugin-card__source-icon {
  @apply w-6 h-6;
}

.plugin-card__main {
  @apply mt-8;
}

.plugin-card__title {
  @apply text-xl font-bold text-white mb-3 pr-8 group-hover:text-blue-300 transition-colors;
}

.plugin-card__description {
  @apply text-gray-300 mb-4 leading-relaxed line-clamp-3 text-sm;
}

.plugin-card__details {
  @apply flex flex-wrap gap-2 text-xs mb-4;
}

.plugin-card__detail-tag {
  @apply px-2 py-1 rounded-lg border;
}

.plugin-card__detail-tag--category {
  @apply bg-gray-700/50 text-gray-300 border-gray-600/30;
}

.plugin-card__detail-tag--version {
  @apply bg-purple-900/30 text-purple-300 border-purple-500/30;
}

.plugin-card__detail-tag--owner {
  @apply bg-blue-900/30 text-blue-300 border-blue-500/30;
}

.plugin-card__dependencies-section {
  @apply mt-4;
}

.plugin-card__dependencies-toggle {
  @apply flex items-center gap-2 text-cyan-400 hover:text-cyan-300 transition-colors font-medium;
}

.plugin-card__dependencies-icon {
  @apply w-4 h-4 transition-transform duration-200;
}

.plugin-card__dependencies-icon--expanded {
  @apply rotate-180;
}

.plugin-card__dependency-details {
  @apply mt-3 overflow-hidden;
}

.plugin-card__dependency-loading {
  @apply flex items-center gap-2 text-sm text-gray-400;
}

.plugin-card__dependency-spinner {
  @apply animate-spin rounded-full h-4 w-4 border-b-2 border-cyan-400;
}

.plugin-card__dependency-list {
  @apply space-y-2;
}

.plugin-card__dependency-item {
  @apply flex items-center justify-between p-3 bg-gray-800/50 rounded-lg text-sm border border-gray-700/30;
  @apply hover:border-gray-600/50 transition-colors;
}

.plugin-card__dependency-info {
  @apply flex items-center gap-2;
}

.plugin-card__dependency-name {
  @apply font-medium text-white;
}

.plugin-card__dependency-version {
  @apply text-gray-400;
}

.plugin-card__context {
  @apply mt-4;
}

.plugin-card__context-box {
  @apply text-xs p-3 rounded-lg border;
}

.plugin-card__context-box--dependency {
  @apply text-green-300 bg-green-900/20 border-green-500/30;
}

.plugin-card__context-box--conflict {
  @apply text-red-300 bg-red-900/20 border-red-500/30;
}

.plugin-card__context-label {
  @apply font-medium;
}

.plugin-card__actions {
  @apply flex gap-3 mt-6;
}

.plugin-card__action-button {
  @apply px-6 py-1.5 text-sm font-semibold rounded-xl transition-all duration-200 border;
}

.plugin-card__action-button--select {
  @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-500 hover:to-blue-600;
  @apply border-blue-500/50 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30;
}

.plugin-card__action-button--deselect {
  @apply bg-gray-700/50 text-gray-300 hover:bg-gray-600/50 border-gray-600/50 hover:border-gray-500/70;
}

.plugin-card__action-button--details {
  @apply px-4 py-1.5 text-gray-300 border-gray-600/50 hover:bg-gray-700/30;
  @apply hover:border-gray-500/70 hover:text-white;
}
</style>

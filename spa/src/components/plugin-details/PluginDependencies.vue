<template>
  <div class="plugin-dependencies">
    <div class="plugin-dependencies-header">
      <h2 class="plugin-dependencies-title">
        <svg class="plugin-dependencies-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
          />
        </svg>
        Dependencies
        <span class="plugin-dependencies-count"> ({{ dependencyCount }}) </span>
      </h2>

      <div v-if="!expanded" class="plugin-dependencies-status">
        {{ dependencyStatusText }}
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-state">
      <div class="loading-content">
        <div class="loading-spinner"></div>
        <span>Analyzing dependencies...</span>
      </div>
    </div>

    <!-- No Dependencies State -->
    <div v-else-if="!hasDependencies" class="no-dependencies-state">
      <div class="no-dependencies-icon">
        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
      </div>
      <h3 class="no-dependencies-title">No Dependencies</h3>
      <p class="no-dependencies-description">This plugin is completely self-contained</p>
    </div>

    <!-- Dependencies Content -->
    <div v-else class="dependencies-content">
      <!-- Dependency Summary -->
      <div v-if="dependencies?.dependency_count" class="dependency-summary-grid">
        <div class="dependency-summary-item">
          <div class="dependency-summary-value dependency-summary-value--cyan">
            {{ dependencies.dependency_count.resolved }}
          </div>
          <div class="dependency-summary-label">Resolved</div>
        </div>
        <div class="dependency-summary-item">
          <div class="dependency-summary-value dependency-summary-value--blue">
            {{ dependencies.resolved_dependencies.length }}
          </div>
          <div class="dependency-summary-label">Direct</div>
        </div>
        <div class="dependency-summary-item">
          <div class="dependency-summary-value" :class="conflictCountClasses">
            {{ dependencies.conflicts.length }}
          </div>
          <div class="dependency-summary-label">Conflicts</div>
        </div>
      </div>

      <!-- Dependency Tree -->
      <div v-if="dependencies?.resolved_dependencies?.length > 0">
        <h3 class="dependency-tree-title">
          <svg class="dependency-tree-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            />
          </svg>
          Required Dependencies
        </h3>

        <div class="dependency-list">
          <div
            v-for="dependency in dependencies.resolved_dependencies || []"
            :key="dependency.name"
            class="dependency-item"
            :class="getDependencyClasses(dependency)"
          >
            <!-- Left: Dependency Info -->
            <div class="dependency-main-content">
              <!-- Connection Line -->
              <div class="dependency-connection-line">
                <div class="dependency-connection-indicator"></div>
              </div>

              <!-- Dependency Details -->
              <div class="dependency-details">
                <div class="dependency-header">
                  <h4 class="dependency-name">
                    {{ dependency.name }}
                  </h4>

                  <!-- Version Badge -->
                  <span class="dependency-badge dependency-badge--version">
                    {{ dependency.version }}
                  </span>

                  <!-- Official Badge -->
                  <span
                    v-if="dependency.is_official"
                    class="dependency-badge dependency-badge--official"
                  >
                    Official
                  </span>

                  <!-- Selection Status -->
                  <span
                    v-if="getSelectionState(dependency.name) !== 'none'"
                    class="dependency-badge dependency-badge--selection"
                    :class="getSelectionBadgeClasses(dependency.name)"
                  >
                    {{ getSelectionText(dependency.name) }}
                  </span>
                </div>

                <p class="dependency-description">
                  {{ dependency.description || 'No description available' }}
                </p>
              </div>
            </div>

            <!-- Right: Actions -->
            <div class="dependency-actions">
              <button
                @click="$emit('selectDependency', dependency.name)"
                class="dependency-action-button"
                :class="getSelectionButtonClasses(dependency.name)"
                :disabled="getSelectionState(dependency.name) === 'dependency'"
              >
                {{ getSelectionButtonText(dependency.name) }}
              </button>

              <button
                @click="$emit('loadDependencyDetails', dependency.name)"
                class="dependency-detail-button"
                title="View details"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Declared Dependencies (if different from resolved) -->
      <div v-if="hasDeclaredDependencies && showDeclaredDependencies">
        <h3 class="declared-dependencies-title">
          <svg
            class="declared-dependencies-icon"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          Declared Dependencies
        </h3>

        <div class="declared-dependencies-grid">
          <div
            v-for="depName in plugin.dependencies || []"
            :key="depName"
            class="declared-dependency-item"
            @click="$emit('loadDependencyDetails', depName)"
          >
            <div class="declared-dependency-name">{{ depName }}</div>
            <div class="declared-dependency-type">Declared</div>
          </div>
        </div>
      </div>

      <!-- Validation Status -->
      <div v-if="dependencies?.is_valid !== undefined" class="validation-status">
        <div class="validation-status-content" :class="validationStatusClasses">
          <svg class="validation-status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              v-if="dependencies.is_valid"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <div>
            <div class="validation-status-title">
              {{ dependencies.is_valid ? 'Dependencies Valid' : 'Dependency Issues Detected' }}
            </div>
            <div class="validation-status-description">
              {{
                dependencies.is_valid
                  ? 'All dependencies can be resolved without conflicts'
                  : 'Some dependencies have conflicts that need to be resolved'
              }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Plugin, PluginDependencyResponse } from '@/api/pluginsApi'
import { usePluginsStore } from '@/stores/usePluginsStore'

interface Props {
  plugin: Plugin
  dependencies: PluginDependencyResponse | null
  isLoading: boolean
  expanded?: boolean
}

interface Emits {
  (e: 'selectDependency', name: string): void

  (e: 'loadDependencyDetails', name: string): void
}

const props = withDefaults(defineProps<Props>(), {
  expanded: false,
})

const emit = defineEmits<Emits>()
const pluginsStore = usePluginsStore()

const hasDependencies = computed(() => {
  return !!(props.dependencies?.resolved_dependencies.length || props.plugin.dependencies?.length)
})

const hasDeclaredDependencies = computed(() => {
  return !!props.plugin.dependencies?.length
})

const dependencyCount = computed(() => {
  if (props.dependencies?.resolved_dependencies.length) {
    return props.dependencies.resolved_dependencies.length
  }
  return props.plugin.dependencies?.length || 0
})

const dependencyStatusText = computed(() => {
  if (props.isLoading) return 'Loading...'
  if (!hasDependencies.value) return 'Self-contained'
  if (props.dependencies?.conflicts.length) return 'Has conflicts'
  return 'All resolved'
})

const conflictCountClasses = computed(() => {
  const count = props.dependencies?.conflicts.length || 0
  return count > 0 ? 'text-red-400' : 'text-green-400'
})

const validationStatusClasses = computed(() => {
  if (props.dependencies?.is_valid) {
    return 'validation-status-content--valid'
  }
  return 'validation-status-content--invalid'
})

const showDeclaredDependencies = computed(() => {
  // Show declared dependencies if they're different from resolved ones
  if (!props.dependencies?.resolved_dependencies.length) return true

  const resolvedNames = props.dependencies.resolved_dependencies.map((d) => d.name)
  const declaredNames = props.plugin.dependencies || []

  return declaredNames.some((name) => !resolvedNames.includes(name))
})

function getSelectionState(dependencyName: string) {
  const info = pluginsStore.getSelectionInfo(dependencyName)
  return info?.state || 'none'
}

function getSelectionText(dependencyName: string) {
  const state = getSelectionState(dependencyName)
  switch (state) {
    case 'manual':
      return 'Selected'
    case 'dependency':
      return 'Auto-selected'
    case 'conflict':
      return 'Conflict'
    default:
      return ''
  }
}

function getSelectionBadgeClasses(dependencyName: string) {
  const state = getSelectionState(dependencyName)
  switch (state) {
    case 'manual':
      return 'dependency-badge--manual'
    case 'dependency':
      return 'dependency-badge--auto'
    case 'conflict':
      return 'dependency-badge--conflict'
    default:
      return ''
  }
}

function getSelectionButtonClasses(dependencyName: string) {
  const state = getSelectionState(dependencyName)
  const isSelected = state !== 'none'

  if (state === 'dependency') {
    return 'dependency-action-button--disabled'
  }

  if (isSelected) {
    return 'dependency-action-button--deselect'
  }

  return 'dependency-action-button--select'
}

function getSelectionButtonText(dependencyName: string) {
  const state = getSelectionState(dependencyName)

  switch (state) {
    case 'manual':
      return 'Deselect'
    case 'dependency':
      return 'Auto-selected'
    case 'conflict':
      return 'Resolve'
    default:
      return 'Select'
  }
}

function getDependencyClasses(dependency: Plugin) {
  const state = getSelectionState(dependency.name)

  switch (state) {
    case 'manual':
      return 'dependency-item--manual'
    case 'dependency':
      return 'dependency-item--auto'
    case 'conflict':
      return 'dependency-item--conflict'
    default:
      return ''
  }
}
</script>

<style scoped>
.plugin-dependencies {
  @apply bg-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 p-6;
}

.plugin-dependencies-header {
  @apply flex items-center justify-between mb-6;
}

.plugin-dependencies-title {
  @apply text-xl font-semibold text-white flex items-center gap-2;
}

.plugin-dependencies-title-icon {
  @apply w-5 h-5 text-cyan-400;
}

.plugin-dependencies-count {
  @apply text-sm font-normal text-gray-400;
}

.plugin-dependencies-status {
  @apply text-sm text-gray-400;
}

.loading-state {
  @apply flex items-center justify-center py-8;
}

.loading-content {
  @apply flex items-center gap-3 text-gray-400;
}

.loading-spinner {
  @apply animate-spin rounded-full h-6 w-6 border-b-2 border-cyan-400;
}

.no-dependencies-state {
  @apply text-center py-8;
}

.no-dependencies-icon {
  @apply text-green-400 mb-4;
}

.no-dependencies-title {
  @apply text-lg font-semibold text-white mb-2;
}

.no-dependencies-description {
  @apply text-gray-400;
}

.dependencies-content {
  @apply space-y-6;
}

.dependency-summary-grid {
  @apply grid grid-cols-1 sm:grid-cols-3 gap-4;
}

.dependency-summary-item {
  @apply text-center p-4 bg-gray-900/40 rounded-lg border border-gray-700/30;
}

.dependency-summary-value {
  @apply text-2xl font-bold;
}

.dependency-summary-value--cyan {
  @apply text-cyan-400;
}

.dependency-summary-value--blue {
  @apply text-blue-400;
}

.dependency-summary-label {
  @apply text-sm text-gray-400;
}

.dependency-tree-title {
  @apply text-lg font-medium text-white mb-4 flex items-center gap-2;
}

.dependency-tree-icon {
  @apply w-4 h-4 text-blue-400;
}

.dependency-list {
  @apply space-y-3;
}

.dependency-item {
  @apply relative flex items-center justify-between p-4 bg-gray-900/40 rounded-lg border border-gray-700/30 hover:border-gray-600/50 transition-all duration-200;
}

.dependency-item--manual {
  @apply border-blue-500/30 bg-blue-900/10;
}

.dependency-item--auto {
  @apply border-green-500/30 bg-green-900/10;
}

.dependency-item--conflict {
  @apply border-red-500/30 bg-red-900/10;
}

.dependency-main-content {
  @apply flex items-center gap-4 flex-1 min-w-0;
}

.dependency-connection-line {
  @apply w-8 flex justify-center;
}

.dependency-connection-indicator {
  @apply w-0.5 h-8 bg-gradient-to-b from-cyan-400/50 to-transparent;
}

.dependency-details {
  @apply flex-1 min-w-0;
}

.dependency-header {
  @apply flex items-center gap-3 mb-2;
}

.dependency-name {
  @apply font-semibold text-white group-hover:text-cyan-300 transition-colors;
}

.dependency-badge {
  @apply px-2 py-1 text-xs font-medium rounded-full border;
}

.dependency-badge--version {
  @apply bg-purple-900/30 text-purple-300 border-purple-500/30;
}

.dependency-badge--official {
  @apply bg-emerald-900/30 text-emerald-300 border-emerald-500/30;
}

.dependency-badge--selection {
  @apply font-bold;
}

.dependency-badge--manual {
  @apply bg-blue-900/30 text-blue-300 border-blue-500/30;
}

.dependency-badge--auto {
  @apply bg-green-900/30 text-green-300 border-green-500/30;
}

.dependency-badge--conflict {
  @apply bg-red-900/30 text-red-300 border-red-500/30;
}

.dependency-description {
  @apply text-sm text-gray-400 line-clamp-2;
}

.dependency-actions {
  @apply flex items-center gap-2 ml-4;
}

.dependency-action-button {
  @apply px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-200;
}

.dependency-action-button--select {
  @apply bg-blue-600/20 text-blue-300 hover:bg-blue-600/30 border-blue-500/30 hover:border-blue-400/50;
}

.dependency-action-button--deselect {
  @apply bg-gray-700/50 text-gray-300 hover:bg-gray-600/50 border-gray-600/50 hover:border-gray-500/70;
}

.dependency-action-button--disabled {
  @apply bg-gray-600/50 text-gray-400 border-gray-600/50 cursor-not-allowed;
}

.dependency-detail-button {
  @apply p-1.5 text-gray-400 hover:text-white border border-gray-600/50 rounded-lg hover:bg-gray-700/30 transition-all duration-200;
}

.declared-dependencies-title {
  @apply text-lg font-medium text-white mb-4 flex items-center gap-2;
}

.declared-dependencies-icon {
  @apply w-4 h-4 text-yellow-400;
}

.declared-dependencies-grid {
  @apply grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3;
}

.declared-dependency-item {
  @apply p-3 bg-gray-900/40 rounded-lg border border-gray-700/30 hover:border-gray-600/50 transition-colors cursor-pointer;
}

.declared-dependency-name {
  @apply font-medium text-white text-sm;
}

.declared-dependency-type {
  @apply text-xs text-gray-400 mt-1;
}

.validation-status {
  @apply mt-6;
}

.validation-status-content {
  @apply flex items-center gap-3 p-4 rounded-lg border;
}

.validation-status-content--valid {
  @apply bg-green-900/20 text-green-300 border-green-500/30;
}

.validation-status-content--invalid {
  @apply bg-red-900/20 text-red-300 border-red-500/30;
}

.validation-status-icon {
  @apply w-5 h-5 flex-shrink-0;
}

.validation-status-title {
  @apply font-medium;
}

.validation-status-description {
  @apply text-sm opacity-90;
}
</style>

<template>
  <div class="bg-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 p-6">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-semibold text-white flex items-center gap-2">
        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
          />
        </svg>
        Dependencies
        <span class="text-sm font-normal text-gray-400"> ({{ dependencyCount }}) </span>
      </h2>

      <div v-if="!expanded" class="text-sm text-gray-400">
        {{ dependencyStatusText }}
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center py-8">
      <div class="flex items-center gap-3 text-gray-400">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-cyan-400"></div>
        <span>Analyzing dependencies...</span>
      </div>
    </div>

    <!-- No Dependencies State -->
    <div v-else-if="!hasDependencies" class="text-center py-8">
      <div class="text-green-400 mb-4">
        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-white mb-2">No Dependencies</h3>
      <p class="text-gray-400">This plugin is completely self-contained</p>
    </div>

    <!-- Dependencies Content -->
    <div v-else class="space-y-6">
      <!-- Dependency Summary -->
      <div v-if="dependencies?.dependency_count" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="text-center p-4 bg-gray-900/40 rounded-lg border border-gray-700/30">
          <div class="text-2xl font-bold text-cyan-400">
            {{ dependencies.dependency_count.resolved }}
          </div>
          <div class="text-sm text-gray-400">Resolved</div>
        </div>
        <div class="text-center p-4 bg-gray-900/40 rounded-lg border border-gray-700/30">
          <div class="text-2xl font-bold text-blue-400">
            {{ dependencies.resolved_dependencies.length }}
          </div>
          <div class="text-sm text-gray-400">Direct</div>
        </div>
        <div class="text-center p-4 bg-gray-900/40 rounded-lg border border-gray-700/30">
          <div class="text-2xl font-bold" :class="conflictCountClasses">
            {{ dependencies.conflicts.length }}
          </div>
          <div class="text-sm text-gray-400">Conflicts</div>
        </div>
      </div>

      <!-- Dependency Tree -->
      <div v-if="dependencies?.resolved_dependencies?.length > 0">
        <h3 class="text-lg font-medium text-white mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            />
          </svg>
          Required Dependencies
        </h3>

        <div class="space-y-3">
          <div
            v-for="dependency in dependencies.resolved_dependencies || []"
            :key="dependency.name"
            class="group relative"
          >
            <div
              class="flex items-center justify-between p-4 bg-gray-900/40 rounded-lg border border-gray-700/30 hover:border-gray-600/50 transition-all duration-200"
              :class="getDependencyClasses(dependency)"
            >
              <!-- Left: Dependency Info -->
              <div class="flex items-center gap-4 flex-1 min-w-0">
                <!-- Connection Line -->
                <div class="w-8 flex justify-center">
                  <div class="w-0.5 h-8 bg-gradient-to-b from-cyan-400/50 to-transparent"></div>
                </div>

                <!-- Dependency Details -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-3 mb-2">
                    <h4
                      class="font-semibold text-white group-hover:text-cyan-300 transition-colors"
                    >
                      {{ dependency.name }}
                    </h4>

                    <!-- Version Badge -->
                    <span
                      class="px-2 py-1 text-xs font-medium bg-purple-900/30 text-purple-300 border border-purple-500/30 rounded-full"
                    >
                      {{ dependency.version }}
                    </span>

                    <!-- Official Badge -->
                    <span
                      v-if="dependency.is_official"
                      class="px-2 py-1 text-xs font-medium bg-emerald-900/30 text-emerald-300 border border-emerald-500/30 rounded-full"
                    >
                      Official
                    </span>

                    <!-- Selection Status -->
                    <span
                      v-if="getSelectionState(dependency.name) !== 'none'"
                      class="px-2 py-1 text-xs font-bold rounded-full"
                      :class="getSelectionBadgeClasses(dependency.name)"
                    >
                      {{ getSelectionText(dependency.name) }}
                    </span>
                  </div>

                  <p class="text-sm text-gray-400 line-clamp-2">
                    {{ dependency.description || 'No description available' }}
                  </p>
                </div>
              </div>

              <!-- Right: Actions -->
              <div class="flex items-center gap-2 ml-4">
                <button
                  @click="$emit('selectDependency', dependency.name)"
                  class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-200"
                  :class="getSelectionButtonClasses(dependency.name)"
                  :disabled="getSelectionState(dependency.name) === 'dependency'"
                >
                  {{ getSelectionButtonText(dependency.name) }}
                </button>

                <button
                  @click="$emit('loadDependencyDetails', dependency.name)"
                  class="p-1.5 text-gray-400 hover:text-white border border-gray-600/50 rounded-lg hover:bg-gray-700/30 transition-all duration-200"
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
      </div>

      <!-- Declared Dependencies (if different from resolved) -->
      <div v-if="hasDeclaredDependencies && showDeclaredDependencies">
        <h3 class="text-lg font-medium text-white mb-4 flex items-center gap-2">
          <svg
            class="w-4 h-4 text-yellow-400"
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

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
          <div
            v-for="depName in plugin.dependencies || []"
            :key="depName"
            class="p-3 bg-gray-900/40 rounded-lg border border-gray-700/30 hover:border-gray-600/50 transition-colors cursor-pointer"
            @click="$emit('loadDependencyDetails', depName)"
          >
            <div class="font-medium text-white text-sm">{{ depName }}</div>
            <div class="text-xs text-gray-400 mt-1">Declared</div>
          </div>
        </div>
      </div>

      <!-- Validation Status -->
      <div v-if="dependencies?.is_valid !== undefined" class="mt-6">
        <div class="flex items-center gap-3 p-4 rounded-lg border" :class="validationStatusClasses">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="font-medium">
              {{ dependencies.is_valid ? 'Dependencies Valid' : 'Dependency Issues Detected' }}
            </div>
            <div class="text-sm opacity-90">
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
    return 'bg-green-900/20 text-green-300 border-green-500/30'
  }
  return 'bg-red-900/20 text-red-300 border-red-500/30'
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
      return 'bg-blue-900/30 text-blue-300 border border-blue-500/30'
    case 'dependency':
      return 'bg-green-900/30 text-green-300 border border-green-500/30'
    case 'conflict':
      return 'bg-red-900/30 text-red-300 border border-red-500/30'
    default:
      return ''
  }
}

function getSelectionButtonClasses(dependencyName: string) {
  const state = getSelectionState(dependencyName)
  const isSelected = state !== 'none'

  if (state === 'dependency') {
    return 'bg-gray-600/50 text-gray-400 border-gray-600/50 cursor-not-allowed'
  }

  if (isSelected) {
    return 'bg-gray-700/50 text-gray-300 hover:bg-gray-600/50 border-gray-600/50 hover:border-gray-500/70'
  }

  return 'bg-blue-600/20 text-blue-300 hover:bg-blue-600/30 border-blue-500/30 hover:border-blue-400/50'
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
      return 'border-blue-500/30 bg-blue-900/10'
    case 'dependency':
      return 'border-green-500/30 bg-green-900/10'
    case 'conflict':
      return 'border-red-500/30 bg-red-900/10'
    default:
      return ''
  }
}
</script>

<style scoped>
/* Loading animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Line clamp for descriptions */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Enhanced transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Enhanced hover effects */
.group:hover .group-hover\:text-cyan-300 {
  color: #67e8f9;
}

button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: scale(0.98);
}

/* Focus states for accessibility */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Grid responsive improvements */
@media (max-width: 640px) {
  .sm\:grid-cols-3 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }

  .md\:grid-cols-3 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .lg\:grid-cols-4 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

/* Connection line styling */
.w-0\.5 {
  width: 0.125rem;
}

/* Enhanced border effects */
.border-blue-500\/30 {
  border-color: rgba(59, 130, 246, 0.3);
}

.border-green-500\/30 {
  border-color: rgba(34, 197, 94, 0.3);
}

.border-red-500\/30 {
  border-color: rgba(239, 68, 68, 0.3);
}

.bg-blue-900\/10 {
  background-color: rgba(30, 58, 138, 0.1);
}

.bg-green-900\/10 {
  background-color: rgba(20, 83, 45, 0.1);
}

.bg-red-900\/10 {
  background-color: rgba(127, 29, 29, 0.1);
}
</style>

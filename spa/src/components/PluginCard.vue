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
  const base = 'relative p-6 rounded-2xl shadow-xl transition-all duration-300 cursor-pointer group border backdrop-blur-sm'

  switch (props.selectionState) {
    case 'manual':
      return `${base} bg-gradient-to-br from-blue-900/40 to-blue-800/30 border-blue-500/50 shadow-blue-500/20 hover:shadow-blue-500/30 hover:border-blue-400/70`
    case 'dependency':
      return `${base} bg-gradient-to-br from-green-900/40 to-green-800/30 border-green-500/50 shadow-green-500/20 hover:shadow-green-500/30 hover:border-green-400/70`
    case 'conflict':
      return `${base} bg-gradient-to-br from-red-900/40 to-red-800/30 border-red-500/50 shadow-red-500/20 hover:shadow-red-500/30 hover:border-red-400/70`
    default:
      return `${base} bg-gradient-to-br from-gray-800/60 to-gray-900/40 border-gray-700/50 hover:border-gray-600/70 hover:shadow-2xl hover:shadow-gray-900/50 hover:bg-gradient-to-br hover:from-gray-800/80 hover:to-gray-900/60`
  }
})

const selectionIndicatorClasses = computed(() => {
  switch (props.selectionState) {
    case 'manual':
      return 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/30'
    case 'dependency':
      return 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-500/30'
    case 'conflict':
      return 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-500/30'
    default:
      return 'bg-gray-700 text-gray-300'
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

const hasConflicts = computed(() => props.selectionState === 'conflict')

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

<template>
  <div
    :class="cardClasses"
    class="flex flex-col justify-between transform hover:-translate-y-1"
    @click="handleCardClick"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
    role="button"
    tabindex="0"
    :aria-label="`Plugin ${plugin.name}, ${selectionText.toLowerCase()}`"
    @keydown.enter="handleToggleSelection"
    @keydown.space.prevent="handleToggleSelection"
  >
    <div class="flex-1">
      <!-- Selection Status Badge -->
      <div
        v-if="isSelected"
        class="absolute -top-2 -right-2 px-3 py-1 text-xs font-bold rounded-full z-10 border border-white/10"
        :class="selectionIndicatorClasses"
      >
        {{ selectionText }}
      </div>

      <!-- Official/Community Badge -->
      <div
        class="absolute top-3 left-3 text-xs font-medium px-2 py-1 rounded-lg z-10 backdrop-blur-sm border"
        :class="plugin.is_official 
          ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' 
          : 'bg-gray-600/20 text-gray-300 border-gray-600/30'"
      >
        <span v-if="plugin.is_official" class="mr-1">✅</span>
        <span v-else class="mr-1">🌐</span>
        {{ plugin.is_official ? 'Official' : 'Community' }}
      </div>

      <!-- Main Content -->
      <div class="mt-12 mb-4">
        <!-- Plugin Name -->
        <h3 class="text-xl font-bold text-white mb-3 pr-8 group-hover:text-blue-300 transition-colors">
          {{ plugin.name }}
        </h3>

        <!-- Plugin Description -->
        <p class="text-gray-300 mb-4 leading-relaxed line-clamp-3 text-sm">
          {{ plugin.description || 'No description available' }}
        </p>

        <!-- Plugin Details -->
        <div class="flex flex-wrap gap-2 text-xs mb-4">
          <span v-if="plugin.category" class="bg-gray-700/50 text-gray-300 px-2 py-1 rounded-lg border border-gray-600/30">
            {{ plugin.category }}
          </span>
          <span class="bg-purple-900/30 text-purple-300 px-2 py-1 rounded-lg border border-purple-500/30">
            {{ plugin.version }}
          </span>
          <span class="bg-blue-900/30 text-blue-300 px-2 py-1 rounded-lg border border-blue-500/30">
            {{ plugin.owner }}
          </span>
        </div>

        <!-- Dependencies Section -->
        <div v-if="plugin.dependencies.length > 0" class="mt-4">
          <button
            @click.stop="handleLoadDependencies"
            class="flex items-center gap-2 text-sm text-cyan-400 hover:text-cyan-300 transition-colors font-medium"
            :disabled="isLoadingDependencies"
          >
            <span>{{ dependencyText }}</span>
            <svg
              class="w-4 h-4 transition-transform duration-200"
              :class="{ 'rotate-180': showDependencyDetails }"
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
            <div v-if="showDependencyDetails" class="mt-3 overflow-hidden">
              <!-- Loading State -->
              <div v-if="isLoadingDependencies" class="flex items-center gap-2 text-sm text-gray-400">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-cyan-400"></div>
                <span>Loading dependencies...</span>
              </div>

              <!-- Dependency List -->
              <div v-else-if="dependencies?.length" class="space-y-2">
                <div
                  v-for="dep in dependencies"
                  :key="dep.name"
                  class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg text-sm border border-gray-700/30 hover:border-gray-600/50 transition-colors"
                >
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-white">{{ dep.name }}</span>
                    <span class="text-gray-400">{{ dep.version }}</span>
                  </div>
                  <button
                    @click.stop="$emit('view-details', dep.name)"
                    class="text-cyan-400 hover:text-cyan-300 text-xs font-medium transition-colors"
                  >
                    View
                  </button>
                </div>
              </div>

              <!-- Declared Dependencies (fallback) -->
              <div v-else class="space-y-2">
                <div
                  v-for="depName in plugin.dependencies"
                  :key="depName"
                  class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg text-sm border border-gray-700/30"
                >
                  <span class="font-medium text-white">{{ depName }}</span>
                  <button
                    @click.stop="$emit('view-details', depName)"
                    class="text-cyan-400 hover:text-cyan-300 text-xs font-medium transition-colors"
                  >
                    View
                  </button>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Selection Context -->
        <div v-if="selectionState === 'dependency' && selectedBy?.length" class="mt-4">
          <div class="text-xs text-green-300 bg-green-900/20 p-3 rounded-lg border border-green-500/30">
            <span class="font-medium">Auto-selected:</span>
            Required by {{ selectedBy.join(', ') }}
          </div>
        </div>

        <div v-if="selectionState === 'conflict'" class="mt-4">
          <div class="text-xs text-red-300 bg-red-900/20 p-3 rounded-lg border border-red-500/30">
            <span class="font-medium">Conflict detected:</span>
            This plugin has dependency conflicts
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-3 mt-6">
      <button
        @click.stop="handleToggleSelection"
        class="flex-1 px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 border"
        :class="
          isSelected
            ? 'bg-gray-700/50 text-gray-300 hover:bg-gray-600/50 border-gray-600/50 hover:border-gray-500/70'
            : 'bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-500 hover:to-blue-600 border-blue-500/50 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30'
        "
      >
        {{ isSelected ? 'Deselect' : 'Select' }}
      </button>

      <button
        @click.stop="handleViewDetails"
        class="px-4 py-2.5 text-sm font-semibold text-gray-300 border border-gray-600/50 rounded-xl hover:bg-gray-700/30 hover:border-gray-500/70 hover:text-white transition-all duration-200"
      >
        Details
      </button>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth transitions for dependency expansion */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Focus states for accessibility */
.group:focus-within {
  @apply ring-2 ring-blue-500/50 ring-offset-2 ring-offset-gray-900;
}

/* Enhanced hover effects */
.group:hover {
  transform: translateY(-4px);
}

/* Custom scrollbar for dependency list */
.space-y-2::-webkit-scrollbar {
  width: 4px;
}

.space-y-2::-webkit-scrollbar-track {
  background: #374151;
  border-radius: 2px;
}

.space-y-2::-webkit-scrollbar-thumb {
  background: #6b7280;
  border-radius: 2px;
}

.space-y-2::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Gradient animation for selected states */
.bg-gradient-to-br {
  background-size: 200% 200%;
  animation: gradient-shift 6s ease infinite;
}

@keyframes gradient-shift {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

/* Enhanced shadow effects */
.shadow-xl {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
}

/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}

/* Button hover glow effects */
.hover\:shadow-blue-500\/30:hover {
  box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3), 0 4px 6px -2px rgba(59, 130, 246, 0.2);
}

/* Card glow effects based on state */
.hover\:shadow-blue-500\/20:hover {
  box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.2), 0 10px 10px -5px rgba(59, 130, 246, 0.1);
}

.hover\:shadow-green-500\/20:hover {
  box-shadow: 0 20px 25px -5px rgba(34, 197, 94, 0.2), 0 10px 10px -5px rgba(34, 197, 94, 0.1);
}

.hover\:shadow-red-500\/20:hover {
  box-shadow: 0 20px 25px -5px rgba(239, 68, 68, 0.2), 0 10px 10px -5px rgba(239, 68, 68, 0.1);
}

.hover\:shadow-gray-900\/50:hover {
  box-shadow: 0 20px 25px -5px rgba(17, 24, 39, 0.5), 0 10px 10px -5px rgba(17, 24, 39, 0.3);
}
</style>

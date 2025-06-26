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
  const base =
    'relative p-6 rounded-2xl shadow-xl transition-all duration-300 cursor-pointer group border backdrop-blur-sm'

  switch (props.selectionState) {
    case 'manual':
      return `${base} bg-gradient-to-br from-purple-900/40 to-purple-800/30 border-purple-500/50 shadow-purple-500/20 hover:shadow-purple-500/30 hover:border-purple-400/70`
    case 'dependency':
      return `${base} bg-gradient-to-br from-green-900/40 to-green-800/30 border-green-500/50 shadow-green-500/20 hover:shadow-green-500/30 hover:border-green-400/70`
    case 'conflict':
      return `${base} bg-gradient-to-br from-red-900/40 to-red-800/30 border-red-500/50 shadow-red-500/20 hover:shadow-red-500/30 hover:border-red-400/70`
    default:
      return `${base} bg-gradient-to-br from-slate-800/60 to-slate-900/40 border-slate-700/50 hover:border-slate-600/70 hover:shadow-2xl hover:shadow-slate-900/50 hover:bg-gradient-to-br hover:from-slate-800/80 hover:to-slate-900/60`
  }
})

const selectionIndicatorClasses = computed(() => {
  switch (props.selectionState) {
    case 'manual':
      return 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/30'
    case 'dependency':
      return 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-500/30'
    case 'conflict':
      return 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-500/30'
    default:
      return 'bg-slate-700 text-slate-300'
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
  if (props.preset.priority <= 10) return 'text-emerald-300 bg-emerald-900/20 border-emerald-500/30'
  if (props.preset.priority <= 50) return 'text-yellow-300 bg-yellow-900/20 border-yellow-500/30'
  return 'text-slate-300 bg-slate-700/20 border-slate-600/30'
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

<template>
  <div
    :class="cardClasses"
    class="flex flex-col justify-between transform hover:-translate-y-1"
    @click="handleCardClick"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
    role="button"
    tabindex="0"
    :aria-label="`Preset ${preset.display_name || preset.name}, ${selectionText.toLowerCase()}`"
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
        class="absolute top-3 left-3 text-xs font-medium z-10 flex items-center gap-1"
        :class="preset.is_official
          ? 'text-emerald-300'
          : 'text-gray-400'"
      >
        <!-- Certificate icon for official presets -->
        <svg
          v-if="preset.is_official"
          class="w-6 h-6"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <!-- Globe icon for community presets -->
        <svg
          v-else
          class="w-6 h-6"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
        </svg>
        <span>{{ preset.is_official ? 'Official' : 'Community' }}</span>
      </div>

      <!-- Priority Badge -->
      <div
        class="absolute top-3 right-3 text-xs font-medium px-2 py-1 rounded-lg z-10 backdrop-blur-sm border"
        :class="priorityColor"
      >
        {{ priorityText }}
      </div>

      <!-- Main Content -->
      <div class="mt-10">
        <!-- Preset Name -->
        <h3
          class="text-xl font-bold text-white mb-3 pr-8 group-hover:text-purple-300 transition-colors"
        >
          {{ preset.display_name || preset.name }}
        </h3>

        <!-- Preset Description -->
        <p class="text-slate-300 mb-4 leading-relaxed line-clamp-3 text-sm">
          {{ preset.description || 'No description available' }}
        </p>

        <!-- Preset Details -->
        <div class="flex flex-wrap gap-2 text-xs mb-4">
          <span
            class="bg-indigo-900/30 text-indigo-300 px-2 py-1 rounded-lg border border-indigo-500/30"
          >
            {{ pluginText }}
          </span>
          <span
            v-if="preset.tags?.length"
            class="bg-cyan-900/30 text-cyan-300 px-2 py-1 rounded-lg border border-cyan-500/30"
          >
            {{ preset.tags.length }} tags
          </span>
          <span
            class="bg-purple-900/30 text-purple-300 px-2 py-1 rounded-lg border border-purple-500/30"
          >
            Priority: {{ preset.priority }}
          </span>
        </div>

        <!-- Tags Display -->
        <div v-if="preset.tags?.length" class="mb-4">
          <div class="flex flex-wrap gap-2">
            <span
              v-for="tag in preset.tags.slice(0, 4)"
              :key="tag"
              class="text-xs bg-slate-700/40 text-slate-300 px-2 py-1 rounded-lg border border-slate-600/30"
            >
              {{ tag }}
            </span>
            <span
              v-if="preset.tags.length > 4"
              class="text-xs bg-slate-600/40 text-slate-400 px-2 py-1 rounded-lg border border-slate-600/30"
            >
              +{{ preset.tags.length - 4 }} more
            </span>
          </div>
        </div>

        <!-- Plugins Section -->
        <div v-if="preset.plugins.length > 0" class="mt-4">
          <button
            @click.stop="handleLoadDependencies"
            class="flex items-center gap-2 text-sm text-cyan-400 hover:text-cyan-300 transition-colors font-medium"
            :disabled="isLoadingDependencies"
          >
            <span>{{ pluginText }}</span>
            <svg
              class="w-4 h-4 transition-transform duration-200"
              :class="{ 'rotate-180': showPluginDetails }"
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
            <div v-if="showPluginDetails" class="mt-3 overflow-hidden">
              <!-- Loading State -->
              <div
                v-if="isLoadingDependencies"
                class="flex items-center gap-2 text-sm text-slate-400"
              >
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-cyan-400"></div>
                <span>Loading plugin details...</span>
              </div>

              <!-- Plugin List -->
              <div v-else class="space-y-2 max-h-40 overflow-y-auto pr-2">
                <div
                  v-for="pluginName in preset.plugins"
                  :key="pluginName"
                  class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg text-sm border border-slate-700/30 hover:border-slate-600/50 transition-colors"
                >
                  <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-purple-400 rounded-full flex-shrink-0"></div>
                    <span class="font-medium text-white">{{ pluginName }}</span>
                  </div>
                  <button
                    @click.stop="$emit('view-details', pluginName)"
                    class="text-cyan-400 hover:text-cyan-300 text-xs font-medium transition-colors flex-shrink-0"
                  >
                    View Plugin
                  </button>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Selection Context -->
        <div v-if="selectionState === 'dependency' && selectedBy?.length" class="mt-4">
          <div
            class="text-xs text-green-300 bg-green-900/20 p-3 rounded-lg border border-green-500/30"
          >
            <span class="font-medium">Auto-selected:</span>
            Required by {{ selectedBy.join(', ') }}
          </div>
        </div>

        <div v-if="selectionState === 'conflict'" class="mt-4">
          <div class="text-xs text-red-300 bg-red-900/20 p-3 rounded-lg border border-red-500/30">
            <span class="font-medium">Conflict detected:</span>
            This preset has conflicts with other selections
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
            ? 'bg-slate-700/50 text-slate-300 hover:bg-slate-600/50 border-slate-600/50 hover:border-slate-500/70'
            : 'bg-gradient-to-r from-purple-600 to-purple-700 text-white hover:from-purple-500 hover:to-purple-600 border-purple-500/50 shadow-lg shadow-purple-500/20 hover:shadow-purple-500/30'
        "
      >
        {{ isSelected ? 'Deselect' : 'Select' }}
      </button>

      <button
        @click.stop="handleViewDetails"
        class="px-4 py-2.5 text-sm font-semibold text-slate-300 border border-slate-600/50 rounded-xl hover:bg-slate-700/30 hover:border-slate-500/70 hover:text-white transition-all duration-200"
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

/* Smooth transitions for plugin expansion */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Focus states for accessibility */
.group:focus-within {
  @apply ring-2 ring-purple-500/50 ring-offset-2 ring-offset-slate-900;
}

/* Enhanced hover effects */
.group:hover {
  transform: translateY(-4px);
}

/* Custom scrollbar for plugin list */
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
  0%,
  100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

/* Enhanced shadow effects */
.shadow-xl {
  box-shadow:
    0 20px 25px -5px rgba(0, 0, 0, 0.3),
    0 10px 10px -5px rgba(0, 0, 0, 0.2);
}

/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}

/* Button hover glow effects */
.hover\:shadow-purple-500\/30:hover {
  box-shadow:
    0 10px 15px -3px rgba(139, 92, 246, 0.3),
    0 4px 6px -2px rgba(139, 92, 246, 0.2);
}

/* Card glow effects based on state */
.hover\:shadow-purple-500\/20:hover {
  box-shadow:
    0 20px 25px -5px rgba(139, 92, 246, 0.2),
    0 10px 10px -5px rgba(139, 92, 246, 0.1);
}

.hover\:shadow-green-500\/20:hover {
  box-shadow:
    0 20px 25px -5px rgba(34, 197, 94, 0.2),
    0 10px 10px -5px rgba(34, 197, 94, 0.1);
}

.hover\:shadow-red-500\/20:hover {
  box-shadow:
    0 20px 25px -5px rgba(239, 68, 68, 0.2),
    0 10px 10px -5px rgba(239, 68, 68, 0.1);
}

.hover\:shadow-slate-900\/50:hover {
  box-shadow:
    0 20px 25px -5px rgba(15, 23, 42, 0.5),
    0 10px 10px -5px rgba(15, 23, 42, 0.3);
}

/* Enhanced plugin list scrolling */
.max-h-40 {
  max-height: 10rem;
}

/* Smooth scroll behavior */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #6b7280 #374151;
}

/* Tag animation on hover */
.group:hover .bg-slate-700\/40 {
  @apply bg-slate-600/50 border-slate-500/40;
}

/* Priority badge glow */
.bg-emerald-900\/20 {
  box-shadow: 0 0 10px rgba(16, 185, 129, 0.1);
}

.bg-yellow-900\/20 {
  box-shadow: 0 0 10px rgba(245, 158, 11, 0.1);
}
</style>

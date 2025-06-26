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
    'relative p-4 border-2 rounded-lg shadow-sm transition-all duration-200 cursor-pointer group'

  switch (props.selectionState) {
    case 'manual':
      return `${base} border-blue-500 bg-blue-50 shadow-md`
    case 'dependency':
      return `${base} border-green-500 bg-green-50 shadow-md`
    case 'conflict':
      return `${base} border-red-500 bg-red-50 shadow-md`
    default:
      return `${base} border-gray-300 bg-white hover:border-blue-300 hover:bg-blue-50 hover:shadow-md`
  }
})

const selectionIndicatorClasses = computed(() => {
  switch (props.selectionState) {
    case 'manual':
      return 'bg-blue-600 text-white'
    case 'dependency':
      return 'bg-green-600 text-white'
    case 'conflict':
      return 'bg-red-600 text-white'
    default:
      return 'bg-gray-300 text-gray-600'
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

const tagsText = computed(() => {
  if (!props.preset.tags || props.preset.tags.length === 0) return 'No tags'
  return props.preset.tags.join(', ')
})

const priorityText = computed(() => {
  if (props.preset.priority <= 10) return 'High priority'
  if (props.preset.priority <= 50) return 'Medium priority'
  return 'Low priority'
})

const priorityColor = computed(() => {
  if (props.preset.priority <= 10) return 'text-green-600 bg-green-100'
  if (props.preset.priority <= 50) return 'text-yellow-600 bg-yellow-100'
  return 'text-gray-600 bg-gray-100'
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
    class="flex flex-col justify-between"
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
        class="absolute -top-2 -right-2 px-2 py-1 text-xs font-bold rounded-full shadow-sm z-10"
        :class="selectionIndicatorClasses"
      >
        {{ selectionText }}
      </div>

      <!-- Official/Community Badge -->
      <div
        class="absolute top-2 left-2 text-xs font-medium px-2 py-0.5 rounded-full shadow-sm z-10"
        :class="preset.is_official ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
      >
        <span v-if="preset.is_official" class="mr-1">✅</span>
        <span v-else class="mr-1">🌐</span>
        {{ preset.is_official ? 'Official' : 'Community' }}
      </div>

      <!-- Priority Badge -->
      <div
        class="absolute top-2 right-12 text-xs font-medium px-2 py-0.5 rounded-full shadow-sm z-10"
        :class="priorityColor"
      >
        {{ priorityText }}
      </div>

      <!-- Main Content -->
      <div class="mt-8 mb-4">
        <!-- Preset Name -->
        <h3 class="text-lg font-semibold text-gray-900 mb-2 pr-8">
          {{ preset.display_name || preset.name }}
        </h3>

        <!-- Preset Description -->
        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
          {{ preset.description || 'No description available' }}
        </p>

        <!-- Preset Details -->
        <div class="flex flex-wrap gap-2 text-xs text-gray-500 mb-3">
          <span class="bg-gray-100 px-2 py-1 rounded">
            {{ pluginText }}
          </span>
          <span v-if="preset.tags?.length" class="bg-gray-100 px-2 py-1 rounded">
            Tags: {{ preset.tags.length }}
          </span>
          <span class="bg-gray-100 px-2 py-1 rounded"> Priority: {{ preset.priority }} </span>
        </div>

        <!-- Tags Display -->
        <div v-if="preset.tags?.length" class="mb-3">
          <div class="flex flex-wrap gap-1">
            <span
              v-for="tag in preset.tags.slice(0, 3)"
              :key="tag"
              class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full"
            >
              {{ tag }}
            </span>
            <span
              v-if="preset.tags.length > 3"
              class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full"
            >
              +{{ preset.tags.length - 3 }} more
            </span>
          </div>
        </div>

        <!-- Plugins Section -->
        <div v-if="preset.plugins.length > 0" class="mt-3">
          <button
            @click.stop="handleLoadDependencies"
            class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 transition-colors"
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
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-96"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 max-h-96"
            leave-to-class="opacity-0 max-h-0"
          >
            <div v-if="showPluginDetails" class="mt-2 overflow-hidden">
              <!-- Loading State -->
              <div
                v-if="isLoadingDependencies"
                class="flex items-center gap-2 text-sm text-gray-500"
              >
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                <span>Loading plugin details...</span>
              </div>

              <!-- Plugin List -->
              <div v-else class="space-y-1">
                <div
                  v-for="pluginName in preset.plugins"
                  :key="pluginName"
                  class="flex items-center justify-between p-2 bg-gray-50 rounded text-sm"
                >
                  <div class="flex items-center gap-2">
                    <span class="font-medium">{{ pluginName }}</span>
                  </div>
                  <button
                    @click.stop="$emit('view-details', pluginName)"
                    class="text-blue-600 hover:text-blue-700 text-xs"
                  >
                    View Plugin
                  </button>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Selection Context -->
        <div v-if="selectionState === 'dependency' && selectedBy?.length" class="mt-3">
          <p class="text-xs text-green-600 bg-green-50 p-2 rounded">
            <span class="font-medium">Auto-selected:</span>
            Required by {{ selectedBy.join(', ') }}
          </p>
        </div>

        <div v-if="selectionState === 'conflict'" class="mt-3">
          <p class="text-xs text-red-600 bg-red-50 p-2 rounded">
            <span class="font-medium">Conflict detected:</span>
            This preset has conflicts with other selections
          </p>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-2 mt-4">
      <button
        @click.stop="handleToggleSelection"
        class="flex-1 px-3 py-2 text-sm font-medium rounded-md transition-colors"
        :class="
          isSelected
            ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            : 'bg-blue-600 text-white hover:bg-blue-700'
        "
      >
        {{ isSelected ? 'Deselect' : 'Select' }}
      </button>

      <button
        @click.stop="handleViewDetails"
        class="px-3 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
      >
        Details
      </button>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
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
  @apply ring-2 ring-blue-500 ring-offset-2;
}

/* Custom scrollbar for plugin list */
.space-y-1::-webkit-scrollbar {
  width: 4px;
}

.space-y-1::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 2px;
}

.space-y-1::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 2px;
}

.space-y-1::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>

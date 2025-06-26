<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { usePresetsStore } from '@/stores/usePresetsStore'
import CategoryTag from '@/components/CategoryTag.vue'
import PresetCard from '@/components/PresetCard.vue'
import ConfigFormatSelector from '@/components/ConfigFormatSelector.vue'
import ConfigModal from '@/components/ConfigModal.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'
import BackButton from '@/components/BackButton.vue'

const presetStore = usePresetsStore()
const configFormat = ref<'toml' | 'json' | 'docker' | 'dockerfile' | ''>('')

const showModal = ref(false)
const showSelectionConfirm = ref(false)
const pendingSelection = ref<{ 
  name: string; 
  preview: { 
    toSelect: string[]
    conflicts: string[]
    newDependencies: string[]
    existingDependencies: string[]
    pluginSummary: {
      current: number
      new: number
      total: number
    }
  } 
} | null>(null)

const searchQuery = ref('')
const sourceFilter = ref<'all' | 'official' | 'community'>('all')
const activeTags = ref<string[]>([]) // Changed to support multiselect

onMounted(() => {
  presetStore.loadPresets()
})

const filteredPresets = computed(() => {
  return presetStore.presetsWithSelection.filter((p) => {
    const nameMatch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                     p.display_name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const sourceMatch =
      sourceFilter.value === 'all' ||
      (sourceFilter.value === 'official' && p.is_official) ||
      (sourceFilter.value === 'community' && !p.is_official)
    const tagsMatch =
      activeTags.value.length === 0 || p.tags?.some((tag) => activeTags.value.includes(tag))

    return nameMatch && sourceMatch && tagsMatch
  })
})

const uniqueTags = computed(() => {
  const tags = new Set<string>()
  presetStore.presets.forEach((p) => {
    p.tags?.forEach((tag) => tags.add(tag))
  })
  return Array.from(tags).sort()
})

const selectionSummary = computed(() => presetStore.selectionSummary)

function toggleTag(tag: string) {
  const index = activeTags.value.indexOf(tag)
  if (index === -1) {
    activeTags.value.push(tag)
  } else {
    activeTags.value.splice(index, 1)
  }
}

function clearAllTags() {
  activeTags.value = []
}

async function handlePresetToggle(name: string, includeDependencies: boolean) {
  const selection = presetStore.getSelectionInfo(name)
  
  if (!selection || selection.state === 'none') {
    // Preview the selection if it might have relationships/conflicts
    try {
      const preview = await presetStore.getSelectionPreview(name)
      
      // Check if we need confirmation:
      // 1. Are there new dependencies/relationships?
      // 2. Are there any conflicts?
      // 3. Does it add significant new plugins?
      const hasNewDependencies = preview.newDependencies.length > 1 // More than just the preset itself
      const hasConflicts = preview.conflicts.length > 0
      const hasSignificantPluginImpact = preview.pluginSummary.new > 5 // Arbitrary threshold
      
      if (hasNewDependencies || hasConflicts || hasSignificantPluginImpact) {
        pendingSelection.value = { name, preview }
        showSelectionConfirm.value = true
        return
      }
    } catch (e) {
      console.error('Failed to get selection preview:', e)
    }
  }
  
  // Direct toggle without confirmation
  await presetStore.togglePresetSelection(name, includeDependencies)
}

async function confirmSelection() {
  if (pendingSelection.value) {
    await presetStore.togglePresetSelection(pendingSelection.value.name, true)
    showSelectionConfirm.value = false
    pendingSelection.value = null
  }
}

function cancelSelection() {
  showSelectionConfirm.value = false
  pendingSelection.value = null
}

function handleViewDetails(name: string) {
  // For now, just show an alert with preset details
  const preset = presetStore.presets.find(p => p.name === name)
  if (preset) {
    alert(`Preset: ${preset.display_name}\nPlugins: ${preset.plugins.join(', ')}\nTags: ${preset.tags?.join(', ') || 'None'}`)
  }
}

async function handleLoadDependencies(name: string) {
  try {
    await presetStore.loadDependencies(name)
  } catch (e) {
    console.error('Failed to load preset relationships:', e)
  }
}

async function handleGenerate() {
  presetStore.error = null
  showModal.value = false

  try {
    await presetStore.generateConfig({
      presets: presetStore.allSelectedPresets,
      ...(configFormat.value && { format: configFormat.value }),
    })

    if (!presetStore.error) {
      showModal.value = true
    }
  } catch (e) {
    console.error(e)
  }
}

function clearAllSelections() {
  presetStore.clearAllSelections()
}
</script>

<template>
  <main class="p-6">
    <BackButton />
    
    <!-- Error Alert -->
    <Teleport to="body">
      <ErrorAlert v-if="presetStore.error" :message="presetStore.error" />
    </Teleport>

    <!-- Selection Confirmation Modal -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div
          v-if="showSelectionConfirm"
          class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/30"
        >
          <div class="bg-white p-6 rounded-lg w-full max-w-lg shadow-xl">
            <h3 class="text-lg font-semibold mb-4">Confirm Preset Selection</h3>
            
            <div v-if="pendingSelection" class="mb-4">
              
              <!-- Plugin Impact Summary -->
              <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                <h4 class="font-medium text-blue-900 mb-2">Plugin Impact</h4>
                <div class="text-sm text-blue-800">
                  <div class="flex justify-between">
                    <span>Current plugins:</span>
                    <span class="font-medium">{{ pendingSelection.preview.pluginSummary.current }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>New plugins:</span>
                    <span class="font-medium text-green-600">+{{ pendingSelection.preview.pluginSummary.new }}</span>
                  </div>
                  <div class="flex justify-between font-semibold border-t border-blue-200 mt-1 pt-1">
                    <span>Total plugins:</span>
                    <span>{{ pendingSelection.preview.pluginSummary.total }}</span>
                  </div>
                </div>
              </div>

              <!-- New Dependencies Section -->
              <div v-if="pendingSelection.preview.newDependencies.length > 1">
                <p class="text-sm text-gray-600 mb-3">
                  Selecting <strong>{{ pendingSelection.name }}</strong> will also select these related presets:
                </p>
                
                <div class="max-h-32 overflow-y-auto mb-4">
                  <ul class="text-sm space-y-1">
                    <li 
                      v-for="preset in pendingSelection.preview.newDependencies.filter(p => p !== pendingSelection.name)"
                      :key="preset"
                      class="flex items-center gap-2 p-2 bg-green-50 rounded"
                    >
                      <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                      {{ preset }}
                      <span class="text-xs text-green-600">(related preset)</span>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Existing Dependencies Section -->
              <div v-if="pendingSelection.preview.existingDependencies.length > 1">
                <p class="text-sm text-gray-500 mb-2">
                  These presets are already selected:
                </p>
                <div class="flex flex-wrap gap-1 mb-4">
                  <span 
                    v-for="preset in pendingSelection.preview.existingDependencies.filter(p => p !== pendingSelection.name)"
                    :key="preset"
                    class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full"
                  >
                    {{ preset }}
                  </span>
                </div>
              </div>

              <!-- Conflicts Section -->
              <div v-if="pendingSelection.preview.conflicts.length" class="mt-3">
                <p class="text-sm text-red-600 font-medium mb-2">⚠️ Potential conflicts:</p>
                <ul class="text-sm space-y-1">
                  <li 
                    v-for="conflict in pendingSelection.preview.conflicts"
                    :key="conflict"
                    class="flex items-center gap-2 p-2 bg-red-50 rounded"
                  >
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                    {{ conflict }}
                    <span class="text-xs text-red-600">(may conflict)</span>
                  </li>
                </ul>
              </div>
            </div>

            <div class="flex gap-3 justify-end">
              <button
                @click="cancelSelection"
                class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
              >
                Cancel
              </button>
              <button
                @click="confirmSelection"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors"
              >
                Confirm Selection
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold mb-2">RoadRunner Presets</h1>
      <p class="text-gray-600">Select predefined preset configurations for common use cases</p>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Search presets..."
        class="px-3 py-2 border rounded-md w-full sm:w-64 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
      />

      <div class="flex gap-2 text-sm">
        <button
          @click="sourceFilter = 'all'"
          :class="[
            'px-3 py-2 rounded-md font-medium transition-colors',
            sourceFilter === 'all'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
          ]"
        >
          All Sources
        </button>
        <button
          @click="sourceFilter = 'official'"
          :class="[
            'px-3 py-2 rounded-md font-medium transition-colors',
            sourceFilter === 'official'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
          ]"
        >
          Official
        </button>
        <button
          @click="sourceFilter = 'community'"
          :class="[
            'px-3 py-2 rounded-md font-medium transition-colors',
            sourceFilter === 'community'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
          ]"
        >
          Community
        </button>
      </div>
    </div>

    <!-- Tags -->
    <div v-if="uniqueTags.length" class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Filter by Tags</h2>
        <button
          v-if="activeTags.length > 0"
          @click="clearAllTags"
          class="text-sm text-gray-500 hover:text-gray-700 font-medium"
        >
          Clear All ({{ activeTags.length }})
        </button>
      </div>
      <div class="flex flex-wrap gap-2">
        <CategoryTag
          v-for="tag in uniqueTags"
          :key="tag"
          :label="tag"
          :value="tag"
          :is-active="activeTags.includes(tag)"
          @click="toggleTag"
        />
      </div>
    </div>

    <!-- Selection Summary -->
    <div 
      v-if="selectionSummary.total > 0" 
      class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4 text-sm">
          <span class="font-medium text-blue-900">
            {{ selectionSummary.manual }} preset{{ selectionSummary.manual === 1 ? '' : 's' }} selected
          </span>
          <span v-if="selectionSummary.dependencies > 0" class="text-blue-700">
            + {{ selectionSummary.dependencies }} related
          </span>
          <span class="font-semibold text-blue-900">
            = {{ selectionSummary.total }} total
          </span>
          <span class="text-green-600">
            ({{ selectionSummary.totalPlugins }} plugins)
          </span>
        </div>
        
        <button
          @click="clearAllSelections"
          class="text-sm text-red-600 hover:text-red-700 font-medium"
        >
          Clear All
        </button>
      </div>
      
      <!-- Selected Preset Names -->
      <div class="mt-3 flex flex-wrap gap-2">
        <span
          v-for="name in presetStore.manuallySelectedPresets"
          :key="name"
          class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full"
        >
          {{ name }}
        </span>
      </div>
    </div>

    <!-- Active Filters Summary -->
    <div 
      v-if="activeTags.length > 0 || searchQuery || sourceFilter !== 'all'"
      class="mb-4 p-3 bg-gray-50 rounded-lg"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-600">
          <span class="font-medium">Active filters:</span>
          <span v-if="activeTags.length > 0">
            Tags: {{ activeTags.join(', ') }}
          </span>
          <span v-if="searchQuery">
            Search: "{{ searchQuery }}"
          </span>
          <span v-if="sourceFilter !== 'all'">
            Source: {{ sourceFilter }}
          </span>
        </div>
        <span class="text-xs text-gray-500">
          {{ filteredPresets.length }} preset{{ filteredPresets.length === 1 ? '' : 's' }} shown
        </span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="presetStore.loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
      <p class="text-gray-600">Loading presets...</p>
    </div>

    <!-- Preset Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
      <PresetCard
        v-for="preset in filteredPresets"
        :key="preset.name"
        :preset="preset"
        :selection-state="preset.selectionState"
        :is-loading-dependencies="presetStore.isLoadingDependencies(preset.name)"
        :selected-by="preset.selectedBy"
        :required-by="preset.requiredBy"
        @toggle="handlePresetToggle"
        @view-details="handleViewDetails"
        @load-dependencies="handleLoadDependencies"
      />
    </div>

    <!-- Empty State -->
    <div v-if="!presetStore.loading && filteredPresets.length === 0" class="text-center py-12">
      <div class="text-gray-400 mb-4">
        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No presets found</h3>
      <p class="text-gray-600">Try adjusting your search or filter criteria</p>
    </div>
    <!-- Configuration Generation -->
    <div v-if="selectionSummary.total > 0" class="pt-6">
      <ConfigFormatSelector v-model="configFormat" class="mb-10" />

      <div class="flex items-end">
        <button
          @click="handleGenerate"
          :disabled="selectionSummary.total === 0"
          class="w-full px-6 py-3 text-white font-semibold rounded-lg transition-colors bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          Generate Configuration
        </button>
      </div>
    </div>

    <!-- Configuration Modal -->
    <ConfigModal 
      :show="showModal" 
      :text="presetStore.configOutput" 
      @close="showModal = false" 
    />
  </main>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

/* Loading animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>

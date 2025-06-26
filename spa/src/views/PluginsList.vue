<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { usePluginsStore } from '@/stores/usePluginsStore'
import CategoryTag from '@/components/CategoryTag.vue'
import PluginCard from '@/components/PluginCard.vue'
import ConfigFormatSelector from '@/components/ConfigFormatSelector.vue'
import ConfigModal from '@/components/ConfigModal.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'
import BackButton from '@/components/BackButton.vue'

const pluginStore = usePluginsStore()
const activeCategories = ref<string[]>([])
const configFormat = ref<'toml' | 'json' | 'docker' | 'dockerfile' | ''>('')

const searchQuery = ref('')
const sourceFilter = ref<'all' | 'official' | 'community'>('all')
const showModal = ref(false)
const showSelectionConfirm = ref(false)
const pendingSelection = ref<{
  name: string
  preview: {
    toSelect: string[]
    conflicts: string[]
    newDependencies: string[]
    existingDependencies: string[]
  }
} | null>(null)

onMounted(() => {
  pluginStore.loadCategories()
  pluginStore.loadPlugins()
})

const filteredPlugins = computed(() => {
  return pluginStore.pluginsWithSelection.filter((p) => {
    const categoryMatch = activeCategories.value.length === 0 || 
                         (p.category && activeCategories.value.includes(p.category))
    const sourceMatch =
      sourceFilter.value === 'all' ||
      (sourceFilter.value === 'official' && p.is_official) ||
      (sourceFilter.value === 'community' && !p.is_official)
    const searchMatch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    return categoryMatch && sourceMatch && searchMatch
  })
})

const selectionSummary = computed(() => {
  const manual = pluginStore.manuallySelectedPlugins
  const total = pluginStore.allSelectedPlugins
  const dependencies = total.length - manual.length

  return {
    manual: manual.length,
    dependencies,
    total: total.length,
  }
})

function toggleCategory(value: string) {
  const index = activeCategories.value.indexOf(value)
  if (index === -1) {
    activeCategories.value.push(value)
  } else {
    activeCategories.value.splice(index, 1)
  }
}

function clearAllCategories() {
  activeCategories.value = []
}

async function handlePluginToggle(name: string, includeDependencies: boolean) {
  const selection = pluginStore.getSelectionInfo(name)

  if (!selection || selection.state === 'none') {
    // Preview the selection if it has dependencies
    const plugin = pluginStore.plugins.find((p) => p.name === name)
    if (plugin?.dependencies.length > 0 && includeDependencies) {
      try {
        const preview = await pluginStore.getSelectionPreview(name)

        // Check if we need confirmation:
        // 1. Are there new dependencies that aren't already selected?
        // 2. Are there any conflicts?
        const hasNewDependencies = preview.newDependencies.length > 1 // More than just the plugin itself
        const hasConflicts = preview.conflicts.length > 0

        if (hasNewDependencies || hasConflicts) {
          pendingSelection.value = { name, preview }
          showSelectionConfirm.value = true
          return
        }
      } catch (e) {
        console.error('Failed to get selection preview:', e)
      }
    }
  }

  // Direct toggle without confirmation
  await pluginStore.togglePluginSelection(name, includeDependencies)
}

async function confirmSelection() {
  if (pendingSelection.value) {
    await pluginStore.togglePluginSelection(pendingSelection.value.name, true)
    showSelectionConfirm.value = false
    pendingSelection.value = null
  }
}

function cancelSelection() {
  showSelectionConfirm.value = false
  pendingSelection.value = null
}

function handleViewDetails(name: string) {
  // Navigation is handled by the PluginCard component
}

async function handleLoadDependencies(name: string) {
  try {
    await pluginStore.loadDependencies(name)
  } catch (e) {
    console.error('Failed to load dependencies:', e)
  }
}

async function handleGenerate() {
  pluginStore.error = null
  showModal.value = false

  try {
    await pluginStore.generateConfig({
      plugins: pluginStore.allSelectedPlugins,
      ...(configFormat.value && { format: configFormat.value }),
    })

    if (!pluginStore.error) {
      showModal.value = true
    }
  } catch (e) {
    console.error(e)
  }
}

function clearAllSelections() {
  pluginStore.clearAllSelections()
}
</script>

<template>
  <main class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-8">
    <!-- Error Alert -->
    <Teleport to="body">
      <ErrorAlert v-if="pluginStore.error" :message="pluginStore.error" />
    </Teleport>

    <!-- Selection Confirmation Modal -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div
          v-if="showSelectionConfirm"
          class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/60"
        >
          <div class="bg-slate-800 border border-slate-600 p-6 rounded-xl w-full max-w-md shadow-2xl mx-4">
            <h3 class="text-lg font-semibold mb-4 text-white">Confirm Plugin Selection</h3>

            <div v-if="pendingSelection" class="mb-6">
              <!-- New Dependencies Section -->
              <div v-if="pendingSelection.preview.newDependencies.length > 1" class="mb-4">
                <p class="text-sm text-slate-300 mb-3">
                  Selecting <strong class="text-white">{{ pendingSelection.name }}</strong> will also select these new dependencies:
                </p>

                <div class="max-h-32 overflow-y-auto mb-4 space-y-2">
                  <div
                    v-for="plugin in pendingSelection.preview.newDependencies.filter(
                      (p) => p !== pendingSelection.name,
                    )"
                    :key="plugin"
                    class="flex items-center gap-2 p-3 bg-green-900/20 border border-green-500/30 rounded-lg"
                  >
                    <div class="w-2 h-2 bg-green-400 rounded-full flex-shrink-0"></div>
                    <span class="text-white font-medium">{{ plugin }}</span>
                    <span class="text-xs text-green-400 ml-auto">(dependency)</span>
                  </div>
                </div>
              </div>

              <!-- Existing Dependencies Section -->
              <div v-if="pendingSelection.preview.existingDependencies.length > 0" class="mb-4">
                <p class="text-sm text-slate-400 mb-2">These dependencies are already selected:</p>
                <div class="flex flex-wrap gap-1 mb-3">
                  <span
                    v-for="plugin in pendingSelection.preview.existingDependencies.filter(
                      (p) => p !== pendingSelection.name,
                    )"
                    :key="plugin"
                    class="text-xs bg-slate-700/60 text-slate-300 px-2 py-1 rounded-full border border-slate-600/50"
                  >
                    {{ plugin }}
                  </span>
                </div>
              </div>

              <!-- Conflicts Section -->
              <div v-if="pendingSelection.preview.conflicts.length" class="mb-4">
                <p class="text-sm text-red-400 font-medium mb-2">⚠️ Potential conflicts detected:</p>
                <div class="space-y-2">
                  <div
                    v-for="conflict in pendingSelection.preview.conflicts"
                    :key="conflict"
                    class="flex items-center gap-2 p-3 bg-red-900/20 border border-red-500/30 rounded-lg"
                  >
                    <div class="w-2 h-2 bg-red-400 rounded-full flex-shrink-0"></div>
                    <span class="text-white font-medium">{{ conflict }}</span>
                    <span class="text-xs text-red-400 ml-auto">(conflict)</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex gap-3 justify-end">
              <button
                @click="cancelSelection"
                class="px-4 py-2 text-sm font-medium text-slate-300 bg-slate-700/60 border border-slate-600 rounded-lg hover:bg-slate-600 hover:text-white transition-all duration-200"
              >
                Cancel
              </button>
              <button
                @click="confirmSelection"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-500 rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-lg shadow-blue-500/20"
              >
                Confirm Selection
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-3 text-white">RoadRunner Plugins</h1>
      <p class="text-slate-300 text-lg">Select plugins to generate your custom RoadRunner configuration</p>
    </div>

    <!-- Search and Source Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
      <div class="relative flex-1 max-w-md">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search plugins..."
          class="w-full px-4 py-3 bg-slate-800/60 border border-slate-600 text-white rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-slate-400 transition-all duration-200"
        />
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <div class="flex gap-2 text-sm">
        <button
          @click="sourceFilter = 'all'"
          :class="[
            'px-4 py-3 rounded-lg font-medium transition-all duration-200',
            sourceFilter === 'all'
              ? 'bg-blue-600 text-white border border-blue-500 shadow-lg shadow-blue-500/20'
              : 'bg-slate-800/60 text-slate-300 border border-slate-600 hover:bg-slate-700 hover:text-white hover:border-slate-500',
          ]"
        >
          All Sources
        </button>
        <button
          @click="sourceFilter = 'official'"
          :class="[
            'px-4 py-3 rounded-lg font-medium transition-all duration-200',
            sourceFilter === 'official'
              ? 'bg-blue-600 text-white border border-blue-500 shadow-lg shadow-blue-500/20'
              : 'bg-slate-800/60 text-slate-300 border border-slate-600 hover:bg-slate-700 hover:text-white hover:border-slate-500',
          ]"
        >
          Official
        </button>
        <button
          @click="sourceFilter = 'community'"
          :class="[
            'px-4 py-3 rounded-lg font-medium transition-all duration-200',
            sourceFilter === 'community'
              ? 'bg-blue-600 text-white border border-blue-500 shadow-lg shadow-blue-500/20'
              : 'bg-slate-800/60 text-slate-300 border border-slate-600 hover:bg-slate-700 hover:text-white hover:border-slate-500',
          ]"
        >
          Community
        </button>
      </div>
    </div>

    <!-- Categories -->
    <div class="mb-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-white">Filter by Categories</h2>
        <button
          v-if="activeCategories.length > 0"
          @click="clearAllCategories"
          class="text-sm text-slate-400 hover:text-white font-medium transition-colors duration-200 bg-slate-800/40 hover:bg-slate-700/60 px-3 py-1 rounded-lg border border-slate-600/50"
        >
          Clear All ({{ activeCategories.length }})
        </button>
      </div>
      <div class="flex flex-wrap gap-2">
        <CategoryTag
          v-for="category in pluginStore.categories"
          :key="category.value"
          :label="category.label"
          :value="category.value"
          :is-active="activeCategories.includes(category.value)"
          @click="toggleCategory"
        />
      </div>
    </div>

    <!-- Selection Summary -->
    <div
      v-if="selectionSummary.total > 0"
      class="mb-8 p-4 bg-gradient-to-r from-blue-900/20 to-blue-800/20 border border-blue-500/30 rounded-xl backdrop-blur-sm"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4 text-sm">
          <span class="font-semibold text-blue-200">
            {{ selectionSummary.manual }} plugin{{ selectionSummary.manual === 1 ? '' : 's' }} selected
          </span>
          <span v-if="selectionSummary.dependencies > 0" class="text-blue-300">
            + {{ selectionSummary.dependencies }} dependenc{{ selectionSummary.dependencies === 1 ? 'y' : 'ies' }}
          </span>
          <span class="font-bold text-blue-100">
            = {{ selectionSummary.total }} total
          </span>
        </div>

        <button
          @click="clearAllSelections"
          class="text-sm text-red-400 hover:text-red-300 font-medium transition-colors duration-200 bg-red-900/20 hover:bg-red-800/30 px-3 py-1 rounded-lg border border-red-500/30"
        >
          Clear All
        </button>
      </div>

      <!-- Selected Plugin Names -->
      <div class="mt-4 flex flex-wrap gap-2">
        <span
          v-for="name in pluginStore.manuallySelectedPlugins"
          :key="name"
          class="bg-blue-800/40 text-blue-200 text-xs font-medium px-3 py-1 rounded-full border border-blue-600/40 backdrop-blur-sm"
        >
          {{ name }}
        </span>
      </div>
    </div>

    <!-- Active Filters Summary -->
    <div 
      v-if="activeCategories.length > 0 || searchQuery || sourceFilter !== 'all'"
      class="mb-6 p-3 bg-slate-800/40 border border-slate-600/50 rounded-lg backdrop-blur-sm"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 text-sm text-slate-300">
          <span class="font-medium text-white">Active filters:</span>
          <div class="flex flex-wrap gap-2">
            <span v-if="activeCategories.length > 0" class="bg-slate-700/60 px-2 py-1 rounded text-xs">
              Categories: {{ activeCategories.join(', ') }}
            </span>
            <span v-if="searchQuery" class="bg-slate-700/60 px-2 py-1 rounded text-xs">
              Search: "{{ searchQuery }}"
            </span>
            <span v-if="sourceFilter !== 'all'" class="bg-slate-700/60 px-2 py-1 rounded text-xs">
              Source: {{ sourceFilter }}
            </span>
          </div>
        </div>
        <span class="text-xs text-slate-400 font-medium">
          {{ filteredPlugins.length }} plugin{{ filteredPlugins.length === 1 ? '' : 's' }} shown
        </span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="pluginStore.loading" class="text-center py-16">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-400 mx-auto mb-6"></div>
      <p class="text-slate-300 text-lg">Loading plugins...</p>
    </div>

    <!-- Plugin Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
      <PluginCard
        v-for="plugin in filteredPlugins"
        :key="plugin.name"
        :plugin="plugin"
        :selection-state="plugin.selectionState"
        :dependencies="pluginStore.getCachedDependencies(plugin.name)?.resolved_dependencies"
        :is-loading-dependencies="pluginStore.isLoadingDependencies(plugin.name)"
        :selected-by="plugin.selectedBy"
        :required-by="plugin.requiredBy"
        @toggle="handlePluginToggle"
        @view-details="handleViewDetails"
        @load-dependencies="handleLoadDependencies"
      />
    </div>

    <!-- Empty State -->
    <div v-if="!pluginStore.loading && filteredPlugins.length === 0" class="text-center py-16">
      <div class="text-slate-500 mb-6">
        <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1"
            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2m0 0V6a2 2 0 012-2h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V9M6 13v2a2 2 0 002 2h2"
          />
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-white mb-3">No plugins found</h3>
      <p class="text-slate-400 text-lg">Try adjusting your search or filter criteria</p>
      <button
        v-if="activeCategories.length > 0 || searchQuery || sourceFilter !== 'all'"
        @click="() => { activeCategories = []; searchQuery = ''; sourceFilter = 'all'; }"
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
      >
        Clear All Filters
      </button>
    </div>

    <!-- Configuration Generation -->
    <div v-if="selectionSummary.total > 0" class="pt-8 border-t border-slate-700/50">
      <div class="mb-8">
        <h3 class="text-xl font-semibold text-white mb-2">Generate Configuration</h3>
        <p class="text-slate-400">Choose your preferred format and generate the configuration file</p>
      </div>
      
      <ConfigFormatSelector v-model="configFormat" class="mb-8" />

      <div class="flex items-center justify-center">
        <button
          @click="handleGenerate"
          :disabled="selectionSummary.total === 0"
          class="w-full max-w-md px-8 py-4 text-white font-bold text-lg rounded-xl transition-all duration-200 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 disabled:from-slate-600 disabled:to-slate-700 disabled:text-slate-400 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transform hover:scale-[1.02] active:scale-[0.98]"
        >
          <span class="flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Generate Configuration
          </span>
        </button>
      </div>
    </div>

    <!-- Configuration Modal -->
    <ConfigModal :show="showModal" :text="pluginStore.configOutput" @close="showModal = false" />
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

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Enhanced focus styles */
button:focus-visible,
input:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth hover transforms */
.transform {
  transform: translateZ(0);
}

/* Enhanced shadows */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

/* Gradient backgrounds */
.bg-gradient-to-r {
  background-image: linear-gradient(to right, var(--tw-gradient-stops));
}

/* Scrollbar styling */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgb(51 65 85);
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgb(107 114 128);
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgb(156 163 175);
}
</style>
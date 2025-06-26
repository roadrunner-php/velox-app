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
const activeCategories = ref<string[]>([]) // Changed from single category to array
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
          class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/30"
        >
          <div class="bg-white p-6 rounded-lg w-full max-w-md shadow-xl">
            <h3 class="text-lg font-semibold mb-4">Confirm Selection</h3>

            <div v-if="pendingSelection" class="mb-4">
              <!-- New Dependencies Section -->
              <div v-if="pendingSelection.preview.newDependencies.length > 1">
                <p class="text-sm text-gray-600 mb-3">
                  Selecting <strong>{{ pendingSelection.name }}</strong> will also select these new
                  dependencies:
                </p>

                <div class="max-h-32 overflow-y-auto mb-4">
                  <ul class="text-sm space-y-1">
                    <li
                      v-for="plugin in pendingSelection.preview.newDependencies.filter(
                        (p) => p !== pendingSelection.name,
                      )"
                      :key="plugin"
                      class="flex items-center gap-2 p-2 bg-green-50 rounded"
                    >
                      <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                      {{ plugin }}
                      <span class="text-xs text-green-600">(new dependency)</span>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Existing Dependencies Section -->
              <div v-if="pendingSelection.preview.existingDependencies.length > 0">
                <p class="text-sm text-gray-500 mb-2">These dependencies are already selected:</p>
                <div class="flex flex-wrap gap-1 mb-4">
                  <span
                    v-for="plugin in pendingSelection.preview.existingDependencies.filter(
                      (p) => p !== pendingSelection.name,
                    )"
                    :key="plugin"
                    class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full"
                  >
                    {{ plugin }}
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
      <h1 class="text-2xl font-bold mb-2">RoadRunner Plugins</h1>
      <p class="text-gray-600">Select plugins to generate your custom RoadRunner configuration</p>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Search plugins..."
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

    <!-- Categories -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Categories</h2>
        <button
          v-if="activeCategories.length > 0"
          @click="clearAllCategories"
          class="text-sm text-gray-500 hover:text-gray-700 font-medium"
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
      class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4 text-sm">
          <span class="font-medium text-blue-900">
            {{ selectionSummary.manual }} plugin{{
              selectionSummary.manual === 1 ? '' : 's'
            }}
            selected
          </span>
          <span v-if="selectionSummary.dependencies > 0" class="text-blue-700">
            + {{ selectionSummary.dependencies }} dependenc{{
              selectionSummary.dependencies === 1 ? 'y' : 'ies'
            }}
          </span>
          <span class="font-semibold text-blue-900"> = {{ selectionSummary.total }} total </span>
        </div>

        <button
          @click="clearAllSelections"
          class="text-sm text-red-600 hover:text-red-700 font-medium"
        >
          Clear All
        </button>
      </div>

      <!-- Selected Plugin Names -->
      <div class="mt-3 flex flex-wrap gap-2">
        <span
          v-for="name in pluginStore.manuallySelectedPlugins"
          :key="name"
          class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full"
        >
          {{ name }}
        </span>
      </div>
    </div>

    <!-- Active Filters Summary -->
    <div 
      v-if="activeCategories.length > 0 || searchQuery || sourceFilter !== 'all'"
      class="mb-4 p-3 bg-gray-50 rounded-lg"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-600">
          <span class="font-medium">Active filters:</span>
          <span v-if="activeCategories.length > 0">
            Categories: {{ activeCategories.join(', ') }}
          </span>
          <span v-if="searchQuery">
            Search: "{{ searchQuery }}"
          </span>
          <span v-if="sourceFilter !== 'all'">
            Source: {{ sourceFilter }}
          </span>
        </div>
        <span class="text-xs text-gray-500">
          {{ filteredPlugins.length }} plugin{{ filteredPlugins.length === 1 ? '' : 's' }} shown
        </span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="pluginStore.loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
      <p class="text-gray-600">Loading plugins...</p>
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
    <div v-if="!pluginStore.loading && filteredPlugins.length === 0" class="text-center py-12">
      <div class="text-gray-400 mb-4">
        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1"
            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2m0 0V6a2 2 0 012-2h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V9M6 13v2a2 2 0 002 2h2"
          />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No plugins found</h3>
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
</style>

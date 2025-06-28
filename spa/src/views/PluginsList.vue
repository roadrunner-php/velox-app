<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { usePluginsStore } from '@/stores/usePluginsStore'
import PluginCard from '@/components/PluginCard.vue'
import ConfigModal from '@/components/ConfigModal.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'

// New modular components
import SearchAndFilters from '@/components/SearchAndFilters.vue'
import FilterTags, { type Tag } from '@/components/FilterTags.vue'
import SelectionSummary from '@/components/SelectionSummary.vue'
import LoadingState from '@/components/LoadingState.vue'
import EmptyState from '@/components/EmptyState.vue'
import ConfigurationGeneration from '@/components/ConfigurationGeneration.vue'
import SelectionConfirmationModal from '@/components/SelectionConfirmationModal.vue'
import type { PluginCategory } from '@/api/pluginsApi.ts'

const pluginStore = usePluginsStore()
const activeCategories = ref<PluginCategory[] | Tag[]>([])
const configFormat = ref<'toml' | 'json' | 'docker' | 'dockerfile'>('toml')

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
    const categoryMatch =
      activeCategories.value.length === 0 ||
      (p.category && activeCategories.value.some(c => c.value === p.category))
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

// Filter management
function toggleCategory(category: PluginCategory) {
  const index = activeCategories.value.findIndex(c => c.value === category.value)
  if (index === -1) {
    activeCategories.value.push(category)
  } else {
    activeCategories.value.splice(index, 1)
  }
}

function clearAllCategories() {
  activeCategories.value = []
}

function clearAllFilters() {
  activeCategories.value = []
  searchQuery.value = ''
  sourceFilter.value = 'all'
}

// Plugin selection management
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

async function handleGenerate(format: typeof configFormat.value) {
  pluginStore.error = null
  showModal.value = false

  try {
    await pluginStore.generateConfig({
      plugins: pluginStore.allSelectedPlugins,
      format,
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
    <SelectionConfirmationModal
      :show="showSelectionConfirm"
      title="Confirm Plugin Selection"
      :item-name="pendingSelection?.name"
      :preview-data="pendingSelection?.preview"
      dependency-type="dependency"
      @confirm="confirmSelection"
      @cancel="cancelSelection"
    />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-3 text-white">RoadRunner Plugins</h1>
      <p class="text-slate-300 text-lg">
        Select plugins to generate your custom RoadRunner configuration
      </p>
    </div>

    <!-- Search and Filters -->
    <SearchAndFilters
      v-model:search-query="searchQuery"
      v-model:source-filter="sourceFilter"
      :active-categories="activeCategories"
      search-placeholder="Search plugins..."
      :result-count="filteredPlugins.length"
      item-type="plugin"
      categories-label="Categories"
      @clear-all-filters="clearAllFilters"
      class="mb-6"
    />

    <!-- Categories Filter -->
    <FilterTags
      :tags="pluginStore.categories"
      :active-tags="activeCategories"
      title="Filter by Categories"
      @toggle="toggleCategory"
      @clear-all="clearAllCategories"
      class="mb-8"
    />

    <!-- Selection Summary -->
    <SelectionSummary
      :summary="selectionSummary"
      :selected-items="pluginStore.manuallySelectedPlugins"
      item-type="plugin"
      dependency-type="dependenc"
      @clear-all="clearAllSelections"
      class="mb-8"
    />

    <!-- Loading State -->
    <LoadingState
      v-if="pluginStore.loading"
      message="Loading plugins..."
      subtitle="Please wait while we fetch the latest plugin information"
    />

    <!-- Plugin Grid -->
    <div
      v-else-if="filteredPlugins.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8"
    >
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
    <EmptyState
      v-else-if="!pluginStore.loading"
      title="No plugins found"
      description="Try adjusting your search or filter criteria to find the plugins you're looking for."
      icon-type="search"
      :show-action-button="activeCategories.length > 0 || searchQuery || sourceFilter !== 'all'"
      action-text="Clear All Filters"
      @action="clearAllFilters"
    />

    <!-- Configuration Generation -->
    <ConfigurationGeneration
      v-model="configFormat"
      :selection-count="selectionSummary.total"
      title="Generate Configuration"
      description="Choose your preferred format and generate the configuration file"
      button-text="Generate Configuration"
      @generate="handleGenerate"
    />

    <!-- Configuration Modal -->
    <ConfigModal :show="showModal" :text="pluginStore.configOutput" @close="showModal = false" />
  </main>
</template>

<style scoped>
/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Enhanced spacing for better visual hierarchy */
.mb-8 {
  margin-bottom: 2rem;
}

.mb-6 {
  margin-bottom: 1.5rem;
}

/* Grid layout improvements */
.grid {
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .grid {
    gap: 2rem;
  }
}

/* Focus states for accessibility */
*:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Responsive design improvements */
@media (max-width: 640px) {
  .px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .my-8 {
    margin-top: 1.5rem;
    margin-bottom: 1.5rem;
  }
}
</style>

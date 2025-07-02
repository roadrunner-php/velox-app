<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePresetsStore } from '@/stores/usePresetsStore'
import PresetCard from '@/components/PresetCard.vue'
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

const route = useRoute()
const router = useRouter()
const presetStore = usePresetsStore()

const configFormat = ref<'toml' | 'json' | 'docker' | 'dockerfile'>('toml')

const showModal = ref(false)
const showSelectionConfirm = ref(false)
const pendingSelection = ref<{
  name: string
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
const activeTags = ref<Tag[]>([])

onMounted(async () => {
  // Initialize URL sync
  presetStore.initUrlSync(route, router)
  
  // Load data
  await presetStore.loadPresets()
})

const filteredPresets = computed(() => {
  return presetStore.presetsWithSelection.filter((p) => {
    const nameMatch =
      p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.display_name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const sourceMatch =
      sourceFilter.value === 'all' ||
      (sourceFilter.value === 'official' && p.is_official) ||
      (sourceFilter.value === 'community' && !p.is_official)
    const tagsMatch =
      activeTags.value.length === 0 || p.tags?.some(tag => activeTags.value.some(t => t.value === tag))

    return nameMatch && sourceMatch && tagsMatch
  })
})

const uniqueTags = computed(() => {
  const tags = new Set<string>()
  presetStore.presets.forEach((p) => {
    p.tags?.forEach(tag => tags.add(tag))
  })
  return Array.from(tags).sort().map(tag => ({value: tag, label: tag}))
})

const selectionSummary = computed(() => presetStore.selectionSummary)

// Filter management
function toggleTag(tag: Tag) {
  const index = activeTags.value.findIndex(c => c.value === tag.value)
  if (index === -1) {
    activeTags.value.push(tag)
  } else {
    activeTags.value.splice(index, 1)
  }
}

function clearAllTags() {
  activeTags.value = []
}

function clearAllFilters() {
  activeTags.value = []
  searchQuery.value = ''
  sourceFilter.value = 'all'
}

// Preset selection management
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
  const preset = presetStore.presets.find((p) => p.name === name)
  if (preset) {
    alert(
      `Preset: ${preset.display_name}\nPlugins: ${preset.plugins.join(', ')}\nTags: ${preset.tags?.join(', ') || 'None'}`,
    )
  }
}

async function handleLoadDependencies(name: string) {
  try {
    await presetStore.loadDependencies(name)
  } catch (e) {
    console.error('Failed to load preset relationships:', e)
  }
}

async function handleGenerate(format: typeof configFormat.value) {
  presetStore.error = null
  showModal.value = false

  try {
    await presetStore.generateConfig({
      presets: presetStore.allSelectedPresets,
      format,
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
  <main class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-8">
    <!-- Error Alert -->
    <Teleport to="body">
      <ErrorAlert v-if="presetStore.error" :message="presetStore.error" />
    </Teleport>

    <!-- Selection Confirmation Modal -->
    <SelectionConfirmationModal
      :show="showSelectionConfirm"
      title="Confirm Preset Selection"
      :item-name="pendingSelection?.name"
      :preview-data="pendingSelection?.preview"
      dependency-type="related preset"
      @confirm="confirmSelection"
      @cancel="cancelSelection"
    />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-3 text-white">RoadRunner Presets</h1>
      <p class="text-slate-300 text-lg">
        Select predefined preset configurations for common use cases
      </p>
    </div>

    <!-- Search and Filters -->
    <SearchAndFilters
      v-model:search-query="searchQuery"
      v-model:source-filter="sourceFilter"
      :active-categories="activeTags"
      search-placeholder="Search presets..."
      :result-count="filteredPresets.length"
      item-type="preset"
      categories-label="Tags"
      @clear-all-filters="clearAllFilters"
      class="mb-6"
    />

    <!-- Tags Filter -->
    <FilterTags
      v-if="uniqueTags.length"
      :tags="uniqueTags"
      :active-tags="activeTags"
      title="Filter by Tags"
      @toggle="toggleTag"
      @clear-all="clearAllTags"
      class="mb-8"
    />

    <!-- Selection Summary -->
    <SelectionSummary
      :summary="selectionSummary"
      :selected-items="presetStore.manuallySelectedPresets"
      item-type="preset"
      dependency-type="related"
      @clear-all="clearAllSelections"
      class="mb-8"
    >
      <template #details>
        <div class="text-xs text-green-400 font-medium">
          Total plugins: {{ selectionSummary.totalPlugins }}
        </div>
      </template>
    </SelectionSummary>

    <!-- Loading State -->
    <LoadingState
      v-if="presetStore.loading"
      message="Loading presets..."
      subtitle="Please wait while we fetch the available preset configurations"
    />

    <!-- Preset Grid -->
    <div
      v-else-if="filteredPresets.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8"
    >
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
    <EmptyState
      v-else-if="!presetStore.loading"
      title="No presets found"
      description="Try adjusting your search or filter criteria to find the presets you're looking for."
      icon-type="filter"
      :show-action-button="activeTags.length > 0 || searchQuery || sourceFilter !== 'all'"
      action-text="Clear All Filters"
      @action="clearAllFilters"
    />

    <!-- Configuration Generation -->
    <ConfigurationGeneration
      v-model="configFormat"
      :selection-count="selectionSummary.total"
      title="Generate Configuration"
      description="Choose your preferred format and generate the configuration file from selected presets"
      button-text="Generate Configuration"
      @generate="handleGenerate"
    />

    <!-- Configuration Modal -->
    <ConfigModal :show="showModal" :text="presetStore.configOutput" @close="showModal = false" />
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

/* Enhanced visual separation */
.mb-8:last-child {
  margin-bottom: 0;
}

/* Grid responsiveness */
@media (max-width: 768px) {
  .grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }
}

@media (min-width: 1280px) {
  .xl\:grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>

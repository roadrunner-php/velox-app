<template>
  <div class="min-h-screen bg-gray-900">
    <!-- Loading State -->
    <LoadingState
      v-if="isLoading"
      message="Loading plugin details..."
      subtitle="Fetching plugin information and dependencies"
    />

    <!-- Error State -->
    <div v-else-if="error" class="max-w-4xl mx-auto px-4 py-16">
      <div class="text-center">
        <div class="text-red-400 mb-4">
          <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">Plugin Not Found</h1>
        <p class="text-gray-400 mb-6">{{ error }}</p>
        <router-link
          to="/plugins"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 19l-7-7 7-7"
            />
          </svg>
          Back to Plugins
        </router-link>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else-if="plugin" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Breadcrumb Navigation -->
      <nav class="flex items-center space-x-2 text-sm text-gray-400 mb-6">
        <router-link to="/plugins" class="hover:text-white transition-colors">
          Plugins
        </router-link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-white font-medium">{{ plugin.name }}</span>
      </nav>

      <!-- Plugin Header -->
      <PluginDetailsHeader
        :plugin="plugin"
        :selection-state="selectionState"
        :is-loading-dependencies="isLoadingDependencies"
        @toggle-selection="handleToggleSelection"
        @share="handleShare"
      />

      <PluginDependencies
        :plugin="plugin"
        :dependencies="dependencies"
        :is-loading="isLoadingDependencies"
        @select-dependency="handleSelectDependency"
        @load-dependency-details="handleLoadDependencyDetails"
      />
    </div>

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

    <!-- Config Generation Modal -->
    <ConfigModal :show="showConfigModal" :text="configOutput" @close="showConfigModal = false" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePluginsStore } from '@/stores/usePluginsStore'

// Components
import LoadingState from '@/components/LoadingState.vue'
import PluginDetailsHeader from '@/components/plugin-details/PluginDetailsHeader.vue'
import PluginDependencies from '@/components/plugin-details/PluginDependencies.vue'
import SelectionConfirmationModal from '@/components/SelectionConfirmationModal.vue'
import ConfigModal from '@/components/ConfigModal.vue'

const route = useRoute()
const router = useRouter()
const pluginsStore = usePluginsStore()

const showSelectionConfirm = ref(false)
const showConfigModal = ref(false)
const configOutput = ref('')
const pendingSelection = ref<{
  name: string
  preview: any
} | null>(null)

// Computed properties
const pluginName = computed(() => route.params.name as string)

const plugin = computed(() => pluginsStore.selectedPlugin)

const dependencies = computed(() => pluginsStore.dependencies)

const isLoading = computed(() => pluginsStore.loading || (!plugin.value && !error.value))

const isLoadingDependencies = computed(() => pluginsStore.isLoadingDependencies(pluginName.value))

const error = computed(() => pluginsStore.error)

const selectionState = computed(() => {
  const info = pluginsStore.getSelectionInfo(pluginName.value)
  return info?.state || 'none'
})

// Methods
async function loadPluginData(name: string) {
  try {
    // Load plugin details
    await pluginsStore.loadPlugin(name)

    // Load dependencies if plugin exists
    if (pluginsStore.selectedPlugin) {
      await pluginsStore.loadDependencies(name)
    }
  } catch (e) {
    console.error('Failed to load plugin data:', e)
  }
}

async function handleToggleSelection() {
  if (!plugin.value) return

  const currentSelection = pluginsStore.getSelectionInfo(plugin.value.name)

  if (!currentSelection || currentSelection.state === 'none') {
    // Check if we need confirmation for dependencies
    if (plugin.value.dependencies.length > 0) {
      try {
        const preview = await pluginsStore.getSelectionPreview(plugin.value.name)

        if (preview.newDependencies.length > 1 || preview.conflicts.length > 0) {
          pendingSelection.value = { name: plugin.value.name, preview }
          showSelectionConfirm.value = true
          return
        }
      } catch (e) {
        console.error('Failed to get selection preview:', e)
      }
    }
  }

  // Direct toggle
  await pluginsStore.togglePluginSelection(plugin.value.name, true)
}

async function confirmSelection() {
  if (pendingSelection.value) {
    await pluginsStore.togglePluginSelection(pendingSelection.value.name, true)
    showSelectionConfirm.value = false
    pendingSelection.value = null
  }
}

function cancelSelection() {
  showSelectionConfirm.value = false
  pendingSelection.value = null
}

async function handleSelectDependency(dependencyName: string) {
  await pluginsStore.togglePluginSelection(dependencyName, true)
}

function handleLoadDependencyDetails(dependencyName: string) {
  router.push(`/plugins/${dependencyName}`)
}

function handleShare() {
  // Copy plugin URL to clipboard
  const url = window.location.href
  navigator.clipboard.writeText(url).then(() => {
    // Show success message
    console.log('URL copied to clipboard')
  })
}

// Lifecycle hooks
onMounted(() => {
  loadPluginData(pluginName.value)
})

watch(
  () => route.params.name,
  (newName) => {
    if (newName) {
      loadPluginData(newName as string)
    }
  },
)
</script>

<style scoped>
/* Custom scrollbar for better UX */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #374151;
}

::-webkit-scrollbar-thumb {
  background: #6b7280;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Focus states for accessibility */
button:focus-visible,
a:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Enhanced tab styling */
nav button {
  position: relative;
}

nav button::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: transparent;
  transition: background-color 0.2s ease;
}

nav button.border-blue-500::after {
  background: #3b82f6;
}

/* Responsive improvements */
@media (max-width: 768px) {
  .grid-cols-1 {
    gap: 1.5rem;
  }

  .lg\:col-span-2,
  .lg\:col-span-1 {
    grid-column: span 1;
  }
}
</style>

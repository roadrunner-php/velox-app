<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { usePluginsStore } from '@/stores/usePluginsStore'
import CategoryTag from '@/components/CategoryTag.vue'
import EntityCard from '@/components/EntityCard.vue'
import ConfigFormatSelector from '@/components/ConfigFormatSelector.vue'
import ConfigModal from '@/components/ConfigModal.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'
import BackButton from '@/components/BackButton.vue'

const pluginStore = usePluginsStore()
const activeCategory = ref<string | null>(null)
const selectedPlugins = ref<string[]>([])
const configFormat = ref<'toml' | 'json' | 'docker' | 'dockerfile' | ''>('')

const searchQuery = ref('')
const sourceFilter = ref<'all' | 'official' | 'community'>('all')
const showModal = ref(false)

onMounted(() => {
  pluginStore.loadCategories()
  pluginStore.loadPlugins()
})

const filteredPlugins = computed(() => {
  return pluginStore.plugins.filter((p) => {
    const categoryMatch = !activeCategory.value || p.category === activeCategory.value
    const sourceMatch =
      sourceFilter.value === 'all' ||
      (sourceFilter.value === 'official' && p.is_official) ||
      (sourceFilter.value === 'community' && !p.is_official)
    const searchMatch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    return categoryMatch && sourceMatch && searchMatch
  })
})

function toggleCategory(value: string) {
  activeCategory.value = activeCategory.value === value ? null : value
}

function togglePlugin(name: string) {
  const i = selectedPlugins.value.indexOf(name)
  if (i === -1) {
    selectedPlugins.value.push(name)
  } else {
    selectedPlugins.value.splice(i, 1)
  }
}

async function handleGenerate() {
  pluginStore.error = null
  showModal.value = false

  try {
    await pluginStore.generateConfig({
      plugins: selectedPlugins.value,
      ...(configFormat.value && { format: configFormat.value }),
    })

    if (!pluginStore.error) {
      showModal.value = true
    }
  } catch (e) {
    console.error(e)
  }
}
</script>

<template>
  <main class="p-6">
    <BackButton />
    <Teleport to="body">
      <ErrorAlert v-if="pluginStore.error" :message="pluginStore.error" />
    </Teleport>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Search by name..."
        class="px-3 py-2 border rounded w-full sm:w-64 text-sm"
      />

      <div class="flex gap-2 text-sm">
        <button
          @click="sourceFilter = 'all'"
          :class="[
            'px-3 py-1 rounded',
            sourceFilter === 'all'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
          ]"
        >
          All
        </button>
        <button
          @click="sourceFilter = 'official'"
          :class="[
            'px-3 py-1 rounded',
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
            'px-3 py-1 rounded',
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
    <h2 class="text-xl font-bold mb-4">Plugin Categories</h2>
    <div class="flex flex-wrap gap-2 mb-6">
      <CategoryTag
        v-for="category in pluginStore.categories"
        :key="category.value"
        :label="category.label"
        :value="category.value"
        :is-active="activeCategory === category.value"
        @click="toggleCategory"
      />
    </div>

    <!-- Plugin List -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-6">
      <EntityCard
        v-for="plugin in filteredPlugins"
        :key="plugin.name"
        :entity="plugin"
        :type="'plugin'"
        :selected="selectedPlugins.includes(plugin.name)"
        @toggle="togglePlugin"
      />
    </div>

    <!-- Selected Plugins -->
    <div class="my-8">
      <h4 class="text-base font-semibold mb-2">Selected Plugins:</h4>

      <div v-if="selectedPlugins.length" class="flex flex-wrap gap-2">
        <span
          v-for="name in selectedPlugins"
          :key="name"
          class="bg-gray-100 text-sm text-gray-800 px-3 py-1 rounded-full flex items-center gap-1"
        >
          {{ name }}
          <button
            class="text-gray-500 hover:text-red-600"
            @click="togglePlugin(name)"
            title="Remove"
          >
            ✖
          </button>
        </span>
      </div>

      <div v-else class="text-sm text-gray-500 italic">No plugins selected</div>
    </div>
    <!-- Format + Button -->
    <div class="gap-4">
      <ConfigFormatSelector v-model="configFormat" class="mb-8" />
      <button
        :disabled="selectedPlugins.length === 0"
        @click="handleGenerate"
        class="px-4 py-2 rounded font-semibold transition text-white bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed"
      >
        Generate
      </button>
    </div>

    <ConfigModal :show="showModal" :text="pluginStore.configOutput" @close="showModal = false" />
  </main>
</template>

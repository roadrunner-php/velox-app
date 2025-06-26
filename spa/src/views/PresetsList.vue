<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { usePresetsStore } from '@/stores/usePresetsStore'
import CategoryTag from '@/components/CategoryTag.vue'
import EntityCard from '@/components/EntityCard.vue'
import ConfigFormatSelector from '@/components/ConfigFormatSelector.vue'
import ConfigModal from '@/components/ConfigModal.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'
import BackButton from '@/components/BackButton.vue'

const presetStore = usePresetsStore()
const selectedPresets = ref<string[]>([])
const configFormat = ref<'toml' | 'json' | 'docker' | 'dockerfile' | ''>('')

const showModal = ref(false)

const searchQuery = ref('')
const sourceFilter = ref<'all' | 'official' | 'community'>('all')
const activeTags = ref<string[]>([])

onMounted(() => {
  presetStore.loadPresets()
})

function togglePreset(name: string) {
  const i = selectedPresets.value.indexOf(name)
  if (i === -1) {
    selectedPresets.value.push(name)
  } else {
    selectedPresets.value.splice(i, 1)
  }
}

function toggleTag(tag: string) {
  const i = activeTags.value.indexOf(tag)
  if (i === -1) {
    activeTags.value.push(tag)
  } else {
    activeTags.value.splice(i, 1)
  }
}

const uniqueTags = computed(() => {
  const tags = new Set<string>()
  presetStore.presets.forEach((p) => {
    p.tags?.forEach((tag) => tags.add(tag))
  })
  return Array.from(tags)
})

const filteredPresets = computed(() => {
  return presetStore.presets.filter((p) => {
    const nameMatch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const sourceMatch =
      sourceFilter.value === 'all' ||
      (sourceFilter.value === 'official' && p.is_official) ||
      (sourceFilter.value === 'community' && !p.is_official)
    const tagsMatch =
      activeTags.value.length === 0 || p.tags?.some((tag) => activeTags.value.includes(tag))

    return nameMatch && sourceMatch && tagsMatch
  })
})

async function handleGenerate() {
  presetStore.error = null
  showModal.value = false

  try {
    await presetStore.generateConfig({
      presets: selectedPresets.value,
      ...(configFormat.value && { format: configFormat.value }),
    })

    if (!presetStore.error) {
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
      <ErrorAlert v-if="presetStore.error" :message="presetStore.error" />
    </Teleport>

    <h2 class="text-xl font-bold mb-4">Presets</h2>

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

    <!-- Tags -->
    <div v-if="uniqueTags.length" class="mb-6">
      <h2 class="text-xl font-bold mb-4">Filter by Tags:</h2>
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

    <!-- Presets -->
    <div v-if="presetStore.loading" class="text-gray-500">Loading...</div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-6">
      <EntityCard
        v-for="preset in filteredPresets"
        :key="preset.name"
        :entity="preset"
        :type="'preset'"
        :selected="selectedPresets.includes(preset.name)"
        @toggle="togglePreset"
      />
    </div>

    <!-- Selected -->
    <div class="mt-8">
      <h4 class="text-base font-semibold mb-2">Selected Presets:</h4>

      <div v-if="selectedPresets.length" class="flex flex-wrap gap-2">
        <span
          v-for="name in selectedPresets"
          :key="name"
          class="bg-gray-100 text-sm text-gray-800 px-3 py-1 rounded-full flex items-center gap-1"
        >
          {{ name }}
          <button
            class="text-gray-500 hover:text-red-600"
            @click.stop="togglePreset(name)"
            title="Remove"
          >
            ✖
          </button>
        </span>
      </div>

      <div v-else class="text-sm text-gray-500 italic">No presets selected</div>
    </div>

    <!-- Format + Button -->
    <div class="flex items-center gap-4 mt-8">
      <ConfigFormatSelector v-model="configFormat" />

      <button
        :disabled="selectedPresets.length === 0"
        @click="handleGenerate"
        class="px-4 py-2 rounded font-semibold transition text-white bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed"
      >
        Generate
      </button>
    </div>

    <!-- Modal -->
    <ConfigModal :show="showModal" :text="presetStore.configOutput" @close="showModal = false" />
  </main>
</template>

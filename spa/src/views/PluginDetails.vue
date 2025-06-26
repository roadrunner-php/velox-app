<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePluginsStore } from '@/stores/usePluginsStore'
import BackButton from '@/components/BackButton.vue'

const route = useRoute()
const pluginStore = usePluginsStore()

const pluginName = route.params.name as string

function loadData(name: string) {
  pluginStore.loadPlugin(name)
  pluginStore.loadDependencies(name)
}

onMounted(() => {
  loadData(pluginName)
})

watch(
  () => route.params.name,
  (newName) => {
    loadData(newName as string)
  },
)
</script>

<template>
  <main class="p-6 max-w-3xl mx-auto">
    <BackButton />
    <div v-if="!pluginStore.selectedPlugin" class="text-gray-500 text-sm">Loading...</div>

    <div v-else>
      <h1 class="text-2xl font-bold mb-1">
        {{ pluginStore.selectedPlugin?.name ?? 'Untitled' }}
      </h1>

      <p class="text-gray-700 mb-4">
        {{ pluginStore.selectedPlugin?.description ?? 'No description available' }}
      </p>

      <ul class="text-sm text-gray-800 space-y-2">
        <li><strong>Version:</strong> {{ pluginStore.selectedPlugin?.version ?? '—' }}</li>
        <li><strong>Full name:</strong> {{ pluginStore.selectedPlugin?.full_name ?? '—' }}</li>
        <li><strong>Category:</strong> {{ pluginStore.selectedPlugin?.category ?? '—' }}</li>
        <li><strong>Owner:</strong> {{ pluginStore.selectedPlugin?.owner ?? '—' }}</li>
        <li>
          <strong>Repository type:</strong> {{ pluginStore.selectedPlugin?.repository_type ?? '—' }}
        </li>
        <li><strong>Source:</strong> {{ pluginStore.selectedPlugin?.source ?? '—' }}</li>
        <li>
          <strong>Official:</strong>
          <span
            :class="pluginStore.selectedPlugin?.is_official ? 'text-green-600' : 'text-gray-500'"
          >
            {{ pluginStore.selectedPlugin?.is_official ? 'Yes' : 'No' }}
          </span>
        </li>
        <li>
          <strong>Repository:</strong>
          <a
            v-if="pluginStore.selectedPlugin?.repository_url"
            :href="pluginStore.selectedPlugin.repository_url"
            target="_blank"
            class="text-blue-600 hover:underline break-all"
          >
            {{ pluginStore.selectedPlugin.repository_url }}
          </a>
          <span v-else>—</span>
        </li>
        <li><strong>Folder:</strong> {{ pluginStore.selectedPlugin?.folder ?? '—' }}</li>
        <li><strong>Replace:</strong> {{ pluginStore.selectedPlugin?.replace ?? '—' }}</li>

        <li v-if="pluginStore.selectedPlugin?.dependencies?.length">
          <strong>Declared dependencies:</strong>
          <ul class="list-disc ml-5">
            <li v-for="dep in pluginStore.selectedPlugin.dependencies" :key="dep">
              {{ dep }}
            </li>
          </ul>
        </li>
      </ul>

      <!-- Dependencies -->
      <div v-if="pluginStore.dependencies" class="mt-8">
        <h2 class="text-lg font-bold mb-2">Dependencies (resolved)</h2>
        <ul class="list-disc ml-5 text-sm text-gray-700">
          <li v-for="dep in pluginStore.dependencies.resolved_dependencies" :key="dep.name">
            {{ dep.name }} — {{ dep.description }}
          </li>
        </ul>

        <div v-if="pluginStore.dependencies.conflicts.length" class="mt-6">
          <h3 class="font-semibold text-red-600 mb-2">Conflicts:</h3>
          <ul class="list-disc ml-5 text-sm text-red-700">
            <li v-for="conflict in pluginStore.dependencies.conflicts" :key="conflict.plugin">
              <strong>{{ conflict.plugin }}</strong> — {{ conflict.message }}
              <span v-if="conflict.conflicting_plugins.length">
                (conflicts with: {{ conflict.conflicting_plugins.join(', ') }})
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const props = defineProps<{
  entity: {
    name: string
    description: string
    category?: string | null
    display_name?: string
    plugin_count?: number
    tags?: string[]
    is_official?: boolean
  }
  selected: boolean
  type: 'plugin' | 'preset'
}>()

const emit = defineEmits<{
  (e: 'toggle', name: string): void
}>()

const router = useRouter()

function handleCardClick(event: MouseEvent) {
  const target = event.target as HTMLElement

  if (target.closest('label')) return

  if (props.type === 'plugin') {
    router.push(`/plugins/${props.entity.name}`)
  }
}

function toggleSelection() {
  emit('toggle', props.entity.name)
}
</script>

<template>
  <div
    class="p-4 border rounded-lg shadow hover:shadow-md transition relative cursor-pointer"
    :class="{ 'border-blue-500 bg-blue-50': selected }"
    @click="toggleSelection"
  >
    <!-- Метка: Official / Community -->
    <div
      class="absolute top-2 left-2 text-xs font-medium px-2 py-0.5 rounded bg-gray-100 text-gray-800"
    >
      <span v-if="entity.is_official">✅ Официальный</span>
      <span v-else>🌐 Community</span>
    </div>

    <!-- Чекбокс -->
    <label class="absolute top-2 right-2 cursor-pointer">
      <input type="checkbox" class="cursor-pointer" :checked="selected" />
    </label>

    <!-- Заголовок -->
    <div class="flex justify-between items-start mb-2 mt-6">
      <h3 class="text-lg font-semibold" @click="handleCardClick">
        {{ entity.display_name || entity.name }}
      </h3>
    </div>

    <!-- Описание -->
    <p class="text-sm text-gray-600 mb-2">{{ entity.description }}</p>

    <!-- Детали -->
    <div class="text-xs text-gray-500">
      <template v-if="type === 'plugin'">
        Категория: <strong>{{ entity.category || '—' }}</strong>
      </template>
      <template v-else>
        {{ entity.plugin_count }} плагинов, теги: {{ entity.tags?.join(', ') || '—' }}
      </template>
    </div>
  </div>
</template>

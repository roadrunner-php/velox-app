<script setup lang="ts">
const props = defineProps<{
  modelValue?: 'toml' | 'json' | 'docker' | 'dockerfile' | ''
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: 'toml' | 'json' | 'docker' | 'dockerfile' | ''): void
}>()
</script>

<template>
  <label class="text-sm">
    Формат:
    <select
      :value="modelValue"
      @change="
        emit(
          'update:modelValue',
          ($event.target as HTMLSelectElement).value as
            | 'toml'
            | 'json'
            | 'docker'
            | 'dockerfile'
            | '',
        )
      "
      class="ml-2 border rounded p-1 text-sm"
    >
      <option value="">Выберите формат</option>
      <option value="toml">TOML</option>
      <option value="json">JSON</option>
      <option value="docker">Docker</option>
      <option value="dockerfile">Dockerfile</option>
    </select>
  </label>
</template>

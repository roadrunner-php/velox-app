<script setup lang="ts">
const props = defineProps<{
  modelValue?: 'toml' | 'json' | 'docker'
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: 'toml' | 'json' | 'docker'): void
}>()
</script>

<template>
  <label class="text-sm">
    Format:
    <select
      :value="modelValue"
      @change="
        emit(
          'update:modelValue',
          ($event.target as HTMLSelectElement).value as 'toml' | 'json' | 'docker',
        )
      "
      class="ml-2 border rounded p-1 text-sm"
    >
      <option value="toml" selected>TOML</option>
      <option value="json">JSON</option>
      <option value="docker">Docker</option>
    </select>
  </label>
</template>

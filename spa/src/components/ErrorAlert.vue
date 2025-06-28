<template>
  <transition name="fade">
    <div
      v-if="visible"
      class="error-alert"
      role="alert"
    >
      <strong class="error-alert__label">Error:</strong>
      <span class="error-alert__message">{{ message }}</span>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'

const props = defineProps<{
  message: string
}>()

const visible = ref(false)

onMounted(() => {
  visible.value = true
  setTimeout(() => {
    visible.value = false
  }, 5000)
})
</script>

<style scoped>
.error-alert {
  @apply fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg max-w-sm;
}

.error-alert__label {
  @apply font-bold;
}

.error-alert__message {
  @apply block sm:inline ml-1;
}
</style>

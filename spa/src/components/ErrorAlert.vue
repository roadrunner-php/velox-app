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

<template>
  <transition name="fade">
    <div
      v-if="visible"
      class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg max-w-sm"
      role="alert"
    >
      <strong class="font-bold">Error:</strong>
      <span class="block sm:inline ml-1">{{ message }}</span>
    </div>
  </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

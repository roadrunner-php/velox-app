<template>
  <div class="min-h-screen flex flex-col relative">
    <Loader />

    <header class="bg-gray-800 text-white w-full">
      <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-lg font-bold">Velox UI</h1>
      </div>
    </header>

    <main class="flex-1 w-full overflow-hidden">
      <div class="max-w-[1024px] mx-auto px-4 py-6 relative">
        <Transition :name="transitionName" mode="out-in">
          <RouterView :key="$route.fullPath" />
        </Transition>
      </div>
    </main>

    <footer class="bg-gray-100 text-sm text-gray-600 w-full">
      <div class="max-w-6xl mx-auto px-4 py-4 text-center">© 2025 Velox UI</div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
import { computed } from 'vue'
import { navigationDirection } from '@/navigation'
import Loader from '@/components/Loader.vue'

const transitionName = computed(() =>
  navigationDirection.value === 'forward' ? 'slide-left' : 'slide-right',
)
</script>

<style scoped>
.slide-left-enter-active,
.slide-right-enter-active,
.slide-left-leave-active,
.slide-right-leave-active {
  transition:
    transform 0.3s ease,
    opacity 0.3s ease;
  position: absolute;
  width: 100%;
}

.slide-left-enter-from {
  transform: translateX(50%);
  opacity: 0;
}
.slide-left-leave-to {
  transform: translateX(-50%);
  opacity: 0;
}

.slide-right-enter-from {
  transform: translateX(-50%);
  opacity: 0;
}
.slide-right-leave-to {
  transform: translateX(50%);
  opacity: 0;
}
</style>

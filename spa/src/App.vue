<template>
  <div class="min-h-screen flex flex-col relative">
    <header class="bg-gray-800 text-white w-full">
      <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-lg font-bold">Velox UI</h1>
      </div>
    </header>

    <main class="flex-1 w-full overflow-hidden">
      <div class="max-w-[1024px] mx-auto px-4 py-6 relative">
        <RouterView :key="$route.fullPath" />
      </div>
    </main>

    <footer class="bg-gray-100 text-sm text-gray-600 w-full">
      <div class="max-w-6xl mx-auto px-4 py-4 text-center">© 2025 Velox UI</div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
</script>
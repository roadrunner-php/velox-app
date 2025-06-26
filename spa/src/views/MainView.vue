<template>
  <main class="p-6">
    <h1 class="text-2xl font-bold mb-4">Welcome to Configuration Builder</h1>
    <p class="text-gray-600 mb-4">Choose what you want to generate:</p>

    <div class="flex gap-4">
      <RouterLink to="/plugins" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Go to Plugins
      </RouterLink>

      <RouterLink to="/presets" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
        Go to Presets
      </RouterLink>
    </div>
  </main>
</template>

<script setup lang="ts"></script>

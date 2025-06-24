<template>
  <main class="p-6">
    <h1 class="text-2xl font-bold mb-4">Добро пожаловать в конфигуратор</h1>
    <p class="text-gray-600 mb-4">Выберите, что вы хотите сгенерировать:</p>

    <div class="flex gap-4">
      <RouterLink to="/plugins" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Перейти к плагинам
      </RouterLink>

      <RouterLink to="/presets" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
        Перейти к пресетам
      </RouterLink>
    </div>
  </main>
</template>

<script setup lang="ts"></script>

<template>
  <footer class="bg-gray-900 border-t border-gray-800 text-gray-400 w-full">
    <div class="max-w-6xl mx-auto px-4 py-6">
      <div
        class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0"
      >
        <div class="flex items-center space-x-2">
          <div
            class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded-md flex items-center justify-center"
          >
            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z"
              />
            </svg>
          </div>
          <span class="text-sm">© 2025 Velox UI - RoadRunner Configuration Builder</span>
        </div>

        <div class="flex items-center space-x-6 text-sm">
          <a
            href="https://docs.roadrunner.dev/docs/customization/build"
            target="_blank"
            rel="noopener noreferrer"
            class="hover:text-white transition-colors duration-200"
            >Documentation</a
          >
          <a
            href="https://github.com/roadrunner-php/velox-app"
            target="_blank"
            rel="noopener noreferrer"
            class="hover:text-white transition-colors duration-200"
            >GitHub</a
          >
          <a
            href="https://t.me/spiralphp/4983"
            target="_blank"
            rel="noopener noreferrer"
            class="hover:text-white transition-colors duration-200"
            >Support</a
          >
        </div>
      </div>
    </div>
  </footer>
</template>
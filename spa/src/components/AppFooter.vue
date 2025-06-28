<template>
  <footer class="bg-gray-900 border-t border-gray-800 text-gray-400 w-full">
    <div class="max-w-6xl mx-auto px-4 py-6">
      <div>
        <h2 class="text-lg font-semibold mb-6 text-white text-center">
          We are Buggregator developers
        </h2>

        <div class="flex flex-wrap gap-5 justify-center">
          <a
            v-for="user in users"
            :key="user.name"
            :href="user.github"
            target="_blank"
            class="group flex flex-col items-center justify-between bg-gray-800 hover:bg-gray-700 rounded-lg px-5 py-4 transition transform hover:-translate-y-1 hover:shadow-lg w-56 h-44"
          >
            <div
              class="border-2 border-gray-400 group-hover:border-transparent rounded-full p-1 transition bg-gradient-to-br group-hover:from-blue-500 group-hover:to-purple-600"
            >
              <img :src="user.avatar" alt="avatar" class="w-16 h-16 rounded-full" loading="lazy" />
            </div>

            <div class="text-center">
              <div class="text-white font-medium group-hover:text-blue-400 transition">
                {{ user.name }}
              </div>
              <div class="text-sm text-gray-400">{{ user.role }}</div>
            </div>
          </a>
        </div>
      </div>
      <div
        class="mt-8 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0"
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
            class="hover:text-white transition-colors duration-200"
            >Documentation</a
          >
          <a
            href="https://github.com/roadrunner-php/velox-app"
            class="hover:text-white transition-colors duration-200"
            >GitHub</a
          >
          <a
            href="https://t.me/spiralphp/4983"
            class="hover:text-white transition-colors duration-200"
            >Support</a
          >
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
const users = [
  {
    name: 'Pavel Buchnev',
    role: 'Creator of Buggregator',
    github: 'https://github.com/butschster',
    avatar: 'https://avatars.githubusercontent.com/u/773481?v=4',
  },
  {
    name: 'Nickolay Arbuzov',
    role: 'Frontend part',
    github: 'https://github.com/nickolayArbuzov',
    avatar: 'https://avatars.githubusercontent.com/u/56513866?v=4',
  },
]
</script>

<template>
  <div class="min-h-screen flex flex-col relative bg-gray-900">
    <!-- Header -->
    <header
      class="bg-gray-900 border-b border-gray-800 text-white w-full sticky top-0 z-50 backdrop-blur-sm"
    >
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <div class="flex items-center space-x-3">
          <!-- Logo/Icon -->
          <div
            class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center"
          >
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z"
              />
            </svg>
          </div>
          <RouterLink
            to="/"
            class="text-xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent"
            :class="{ 'text-blue-400': $route.path === '/' }"
          >
            Velox UI
          </RouterLink>
        </div>

        <!-- Navigation -->
        <nav class="hidden md:flex items-center space-x-6">
          <RouterLink
            to="/"
            class="text-gray-300 hover:text-white transition-colors duration-200 font-medium"
            :class="{ 'text-blue-400': $route.path === '/' }"
          >
            Home
          </RouterLink>
          <RouterLink
            to="/plugins"
            class="text-gray-300 hover:text-white transition-colors duration-200 font-medium"
            :class="{ 'text-blue-400': $route.path.startsWith('/plugins') }"
          >
            Plugins
          </RouterLink>
          <RouterLink
            to="/presets"
            class="text-gray-300 hover:text-white transition-colors duration-200 font-medium"
            :class="{ 'text-blue-400': $route.path.startsWith('/presets') }"
          >
            Presets
          </RouterLink>
        </nav>

        <!-- Mobile menu button -->
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="md:hidden p-2 rounded-lg hover:bg-gray-800 transition-colors"
          :class="{ 'bg-gray-800': mobileMenuOpen }"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              v-if="!mobileMenuOpen"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <!-- Mobile Navigation -->
      <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 max-h-0"
        enter-to-class="opacity-100 max-h-96"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="opacity-100 max-h-96"
        leave-to-class="opacity-0 max-h-0"
      >
        <div
          v-if="mobileMenuOpen"
          class="md:hidden border-t border-gray-800 bg-gray-900/95 backdrop-blur-sm overflow-hidden"
        >
          <div class="px-4 py-2 space-y-1">
            <RouterLink
              to="/"
              @click="mobileMenuOpen = false"
              class="block px-3 py-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 transition-colors duration-200"
              :class="{ 'text-blue-400 bg-gray-800': $route.path === '/' }"
            >
              Home
            </RouterLink>
            <RouterLink
              to="/plugins"
              @click="mobileMenuOpen = false"
              class="block px-3 py-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 transition-colors duration-200"
              :class="{ 'text-blue-400 bg-gray-800': $route.path.startsWith('/plugins') }"
            >
              Plugins
            </RouterLink>
            <RouterLink
              to="/presets"
              @click="mobileMenuOpen = false"
              class="block px-3 py-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 transition-colors duration-200"
              :class="{ 'text-blue-400 bg-gray-800': $route.path.startsWith('/presets') }"
            >
              Presets
            </RouterLink>
          </div>
        </div>
      </transition>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full overflow-hidden bg-gray-900 relative">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-5">
        <div
          class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-blue-500/10 to-purple-500/10"
        ></div>
        <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
        <div
          class="absolute bottom-1/4 left-1/4 w-96 h-96 bg-purple-500/5 rounded-full blur-3xl"
        ></div>
      </div>

      <div class="relative z-10">
        <RouterView :key="$route.fullPath" />
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-gray-800 text-gray-400 w-full">
      <div class="max-w-6xl mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
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
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { RouterView, RouterLink } from 'vue-router'

const mobileMenuOpen = ref(false)

// Close mobile menu when clicking outside
const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

// Close mobile menu on route change
const onRouteChange = () => {
  mobileMenuOpen.value = false
}
</script>

<style scoped>
/* Ensure smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Mobile menu animations */
.max-h-0 {
  max-height: 0;
}

.max-h-96 {
  max-height: 24rem;
}

/* Backdrop blur for mobile menu */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}

/* Active link styles */
.router-link-active {
  color: #60a5fa !important;
}

/* Gradient text animation */
.bg-clip-text {
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Sticky header with backdrop blur */
.sticky {
  position: sticky;
  backdrop-filter: blur(8px);
}

/* Focus states for accessibility */
button:focus,
a:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Custom scrollbar for the main content */
main::-webkit-scrollbar {
  width: 6px;
}

main::-webkit-scrollbar-track {
  background: #1f2937;
}

main::-webkit-scrollbar-thumb {
  background: #4b5563;
  border-radius: 3px;
}

main::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>

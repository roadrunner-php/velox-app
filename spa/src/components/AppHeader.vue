<template>
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
          :class="['text-xl font-bold', route.path === '/' ? 'text-blue-400' : 'text-gray-300']"
        >
          Velox UI
        </RouterLink>
      </div>

      <!-- Navigation -->
      <nav class="hidden md:flex items-center space-x-6">
        <RouterLink
          to="/"
          :class="[
            'hover:text-white transition-colors duration-200 font-medium',
            route.path === '/' ? 'text-blue-400' : 'text-gray-300',
          ]"
        >
          Home
        </RouterLink>
        <RouterLink
          to="/plugins"
          :class="[
            'hover:text-white transition-colors duration-200 font-medium',
            route.path.startsWith('/plugins') ? 'text-blue-400' : 'text-gray-300',
          ]"
        >
          Plugins
        </RouterLink>
        <RouterLink
          to="/presets"
          :class="[
            'hover:text-white transition-colors duration-200 font-medium',
            route.path.startsWith('/presets') ? 'text-blue-400' : 'text-gray-300',
          ]"
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
            :class="[
              'block px-3 py-2 rounded-md hover:text-white hover:bg-gray-800 transition-colors duration-200',
              route.path === '/' ? 'text-blue-400 bg-gray-800' : 'text-gray-300',
            ]"
          >
            Home
          </RouterLink>
          <RouterLink
            to="/plugins"
            @click="mobileMenuOpen = false"
            :class="[
              'block px-3 py-2 rounded-md hover:text-white hover:bg-gray-800 transition-colors duration-200',
              route.path.startsWith('/plugins') ? 'text-blue-400 bg-gray-800' : 'text-gray-300',
            ]"
          >
            Plugins
          </RouterLink>
          <RouterLink
            to="/presets"
            @click="mobileMenuOpen = false"
            :class="[
              'block px-3 py-2 rounded-md hover:text-white hover:bg-gray-800 transition-colors duration-200',
              route.path.startsWith('/presets') ? 'text-blue-400 bg-gray-800' : 'text-gray-300',
            ]"
          >
            Presets
          </RouterLink>
        </div>
      </div>
    </transition>
  </header>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

const mobileMenuOpen = ref(false)
const route = useRoute()
</script>

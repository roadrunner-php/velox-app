<template>
  <header class="app-header">
    <div class="app-header__container">
      <div class="app-header__brand">
        <!-- Logo/Icon -->
        <div class="app-header__logo">
          <svg class="app-header__logo-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          :class="['app-header__title', route.path === '/' ? 'app-header__title--active' : '']"
        >
          Velox UI
        </RouterLink>
      </div>

      <!-- Navigation -->
      <nav class="app-header__nav">
        <RouterLink
          to="/"
          :class="[
            'app-header__nav-link',
            route.path === '/' ? 'app-header__nav-link--active' : '',
          ]"
        >
          Home
        </RouterLink>
        <RouterLink
          to="/plugins"
          :class="[
            'app-header__nav-link',
            route.path.startsWith('/plugins') ? 'app-header__nav-link--active' : '',
          ]"
        >
          Plugins
        </RouterLink>
        <RouterLink
          to="/presets"
          :class="[
            'app-header__nav-link',
            route.path.startsWith('/presets') ? 'app-header__nav-link--active' : '',
          ]"
        >
          Presets
        </RouterLink>
      </nav>

      <!-- Mobile menu button -->
      <button
        @click="mobileMenuOpen = !mobileMenuOpen"
        class="app-header__mobile-button"
        :class="{ 'app-header__mobile-button--active': mobileMenuOpen }"
      >
        <svg class="app-header__mobile-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
      <div v-if="mobileMenuOpen" class="app-header__mobile-menu">
        <div class="app-header__mobile-menu-content">
          <RouterLink
            to="/"
            @click="mobileMenuOpen = false"
            :class="[
              'app-header__mobile-link',
              route.path === '/' ? 'app-header__mobile-link--active' : '',
            ]"
          >
            Home
          </RouterLink>
          <RouterLink
            to="/plugins"
            @click="mobileMenuOpen = false"
            :class="[
              'app-header__mobile-link',
              route.path.startsWith('/plugins') ? 'app-header__mobile-link--active' : '',
            ]"
          >
            Plugins
          </RouterLink>
          <RouterLink
            to="/presets"
            @click="mobileMenuOpen = false"
            :class="[
              'app-header__mobile-link',
              route.path.startsWith('/presets') ? 'app-header__mobile-link--active' : '',
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

<style scoped>
.app-header {
  @apply bg-gray-900 border-b border-gray-800 text-white w-full sticky top-0 z-50 backdrop-blur-sm;
}

.app-header__container {
  @apply max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center;
}

.app-header__brand {
  @apply flex items-center space-x-3;
}

.app-header__logo {
  @apply w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center;
}

.app-header__logo-icon {
  @apply w-5 h-5 text-white;
}

.app-header__title {
  @apply text-xl font-bold text-gray-300 transition-colors duration-200;
}

.app-header__title--active {
  @apply text-blue-400;
}

.app-header__nav {
  @apply hidden md:flex items-center space-x-6;
}

.app-header__nav-link {
  @apply hover:text-white transition-colors duration-200 font-medium text-gray-300;
}

.app-header__nav-link--active {
  @apply text-blue-400;
}

.app-header__mobile-button {
  @apply md:hidden p-2 rounded-lg hover:bg-gray-800 transition-colors;
}

.app-header__mobile-button--active {
  @apply bg-gray-800;
}

.app-header__mobile-icon {
  @apply w-6 h-6;
}

.app-header__mobile-menu {
  @apply md:hidden border-t border-gray-800 bg-gray-900/95 backdrop-blur-sm overflow-hidden;
}

.app-header__mobile-menu-content {
  @apply px-4 py-2 space-y-1;
}

.app-header__mobile-link {
  @apply block px-3 py-2 rounded-md hover:text-white hover:bg-gray-800 transition-colors duration-200 text-gray-300;
}

.app-header__mobile-link--active {
  @apply text-blue-400 bg-gray-800;
}
</style>

<template>
  <div class="min-h-screen flex flex-col relative bg-gray-900">
    <AppHeader />
    <main class="flex-1 w-full overflow-hidden bg-gray-900 relative">
      <BackgroundPattern />
      <div class="relative z-10">
        <RouterView :key="$route.fullPath" />
      </div>
    </main>
    <StickyConfiguration />
    <ContributorsSection />
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
import AppHeader from './components/AppHeader.vue'
import AppFooter from './components/AppFooter.vue'
import ContributorsSection from './components/contributors/ContributorsSection.vue'
import BackgroundPattern from './components/BackgroundPattern.vue'
import StickyConfiguration from './components/StickyConfiguration.vue'
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

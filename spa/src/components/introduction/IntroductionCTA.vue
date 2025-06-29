<template>
  <section class="introduction-cta">
    <div class="introduction-cta__container">
      <h2 class="introduction-cta__title">Ready to Build Your Custom Server?</h2>
      <p class="introduction-cta__subtitle">
        Start by selecting the plugins you need, or choose from our battle-tested presets
      </p>
      <div class="introduction-cta__buttons">
        <RouterLink
          to="/plugins"
          class="introduction-cta__button introduction-cta__button--primary"
        >
          🔧 Custom Build
        </RouterLink>
        <RouterLink
          to="/presets"
          class="introduction-cta__button introduction-cta__button--secondary"
        >
          ⚡ Quick Start
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
interface Props {
  title?: string
  subtitle?: string
  primaryButtonText?: string
  primaryButtonTo?: string
  secondaryButtonText?: string
  secondaryButtonTo?: string
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Ready to Build Your Custom Server?',
  subtitle: 'Start by selecting the plugins you need, or choose from our battle-tested presets',
  primaryButtonText: '🔧 Custom Build',
  primaryButtonTo: '/plugins',
  secondaryButtonText: '⚡ Quick Start',
  secondaryButtonTo: '/presets'
})
</script>

<style scoped>
.introduction-cta {
  @apply bg-gradient-to-r from-gray-900 via-black to-gray-900 border-t border-gray-800/50 py-20;
}

.introduction-cta__container {
  @apply max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8;
}

.introduction-cta__title {
  @apply text-3xl font-bold text-white mb-6;
}

.introduction-cta__subtitle {
  @apply text-xl text-gray-300 mb-8;
}

.introduction-cta__buttons {
  @apply flex flex-col sm:flex-row gap-4 justify-center;
}

.introduction-cta__button {
  @apply inline-flex items-center gap-2 px-8 py-4 font-bold text-lg rounded-full;
  @apply transition-all duration-300 transform hover:scale-105 shadow-lg;
}

.introduction-cta__button--primary {
  @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white;
  @apply hover:from-blue-500 hover:to-blue-600 shadow-blue-500/20;
}

.introduction-cta__button--secondary {
  @apply bg-gradient-to-r from-purple-600 to-purple-700 text-white;
  @apply hover:from-purple-500 hover:to-purple-600 shadow-purple-500/20;
}
</style>

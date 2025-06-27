<template>
  <div class="absolute inset-0 overflow-hidden">
    <!-- Gradient overlay -->
    <div
      class="absolute inset-0 bg-gradient-to-br from-slate-900/90 via-purple-900/20 to-indigo-900/90"
    ></div>

    <!-- Particle Field -->
    <ParticleField
      :particle-count="particleCount"
      :colors="particleColors"
      :disabled="animationsDisabled"
    />

    <!-- Geometric Patterns -->
    <GeometricPatterns :opacity="patternOpacity" :disabled="animationsDisabled" />

    <!-- Floating Blobs -->
    <div class="absolute inset-0">
      <div
        class="absolute top-20 left-20 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl animate-pulse"
        :class="{ 'animate-none': animationsDisabled }"
      ></div>
      <div
        class="absolute bottom-20 right-20 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse delay-1000"
        :class="{ 'animate-none': animationsDisabled }"
      ></div>
      <div
        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-blue-500/5 to-purple-500/5 rounded-full blur-3xl animate-spin-slow"
        :class="{ 'animate-none': animationsDisabled }"
      ></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import ParticleField from './ParticleField.vue'
import GeometricPatterns from './GeometricPatterns.vue'

interface Props {
  particleCount?: number
  particleColors?: string[]
  patternOpacity?: number
  animationsDisabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  particleCount: 30,
  particleColors: () => ['#3b82f6', '#8b5cf6', '#06b6d4'],
  patternOpacity: 0.2,
  animationsDisabled: false,
})
</script>

<style scoped>
@keyframes spin-slow {
  from {
    transform: translate(-50%, -50%) rotate(0deg);
  }
  to {
    transform: translate(-50%, -50%) rotate(360deg);
  }
}

.animate-spin-slow {
  animation: spin-slow 20s linear infinite;
}

.delay-1000 {
  animation-delay: 1s;
}

/* Respect reduced motion preferences */
@media (prefers-reduced-motion: reduce) {
  .animate-pulse,
  .animate-spin-slow {
    animation: none;
  }
}
</style>

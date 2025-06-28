<template>
  <div class="animated-background">
    <!-- Gradient overlay -->
    <div class="animated-background-overlay"></div>

    <!-- Particle Field -->
    <ParticleField
      :particle-count="particleCount"
      :colors="particleColors"
      :disabled="animationsDisabled"
    />

    <!-- Geometric Patterns -->
    <GeometricPatterns :opacity="patternOpacity" :disabled="animationsDisabled" />

    <!-- Floating Blobs -->
    <div class="floating-blobs">
      <div
        class="floating-blob floating-blob--blue"
        :class="{ 'animate-none': animationsDisabled }"
      ></div>
      <div
        class="floating-blob floating-blob--purple"
        :class="{ 'animate-none': animationsDisabled }"
      ></div>
      <div
        class="floating-blob floating-blob--center"
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
.animated-background {
  @apply absolute inset-0 overflow-hidden;
}

.animated-background-overlay {
  @apply absolute inset-0 bg-gradient-to-br from-slate-900/90 via-purple-900/20 to-indigo-900/90;
}

.floating-blobs {
  @apply absolute inset-0;
}

.floating-blob {
  @apply absolute rounded-full blur-3xl animate-pulse;
}

.floating-blob--blue {
  @apply top-20 left-20 w-72 h-72 bg-blue-500/10;
}

.floating-blob--purple {
  @apply bottom-20 right-20 w-96 h-96 bg-purple-500/10 delay-1000;
}

.floating-blob--center {
  @apply top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-blue-500/5 to-purple-500/5;
}
</style>

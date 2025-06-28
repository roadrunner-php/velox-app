<template>
  <section class="statistics-section" :class="sectionClasses">
    <div class="statistics-container">
      <!-- Section Header -->
      <div v-if="title || subtitle" class="statistics-header">
        <h2 v-if="title" class="statistics-title">
          {{ title }}
        </h2>
        <p v-if="subtitle" class="statistics-subtitle">
          {{ subtitle }}
        </p>
      </div>

      <!-- Statistics Grid -->
      <div class="statistics-grid">
        <StatCounter
          v-for="stat in statistics"
          :key="stat.id || stat.label"
          :value="stat.value"
          :label="stat.label"
          :prefix="stat.prefix"
          :suffix="stat.suffix"
          :color="stat.color"
          :size="counterSize"
          :duration="animationDuration"
          :delay="stat.delay || 0"
          :animate-on-mount="animateOnMount"
        />
      </div>

      <!-- Additional Content Slot -->
      <div v-if="$slots.content" class="statistics-additional-content">
        <slot name="content"></slot>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import StatCounter from '../ui/StatCounter.vue'

interface Statistic {
  id?: string
  value: number
  label: string
  prefix?: string
  suffix?: string
  color?: 'blue' | 'green' | 'purple' | 'cyan' | 'yellow'
  delay?: number
}

interface Props {
  title?: string
  subtitle?: string
  statistics?: Statistic[]
  variant?: 'default' | 'transparent' | 'dark'
  counterSize?: 'sm' | 'md' | 'lg'
  animationDuration?: number
  animateOnMount?: boolean
  startAnimation?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  statistics: () => [
    {
      id: 'plugins',
      value: 50,
      label: 'Available Plugins',
      suffix: '+',
      color: 'blue',
      delay: 0,
    },
    {
      id: 'presets',
      value: 12,
      label: 'Ready Presets',
      suffix: '+',
      color: 'green',
      delay: 200,
    },
    {
      id: 'deployments',
      value: 25,
      label: 'Deployments',
      suffix: 'K+',
      color: 'purple',
      delay: 400,
    },
    {
      id: 'performance',
      value: 85,
      label: 'Faster Setup',
      suffix: '%',
      color: 'cyan',
      delay: 600,
    },
  ],
  variant: 'default',
  counterSize: 'md',
  animationDuration: 2000,
  animateOnMount: true,
  startAnimation: false,
})

const sectionClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gray-900/80 backdrop-blur-sm border-t border-gray-800/50',
    transparent: 'bg-transparent',
    dark: 'bg-gray-900 border-t border-gray-800',
  }

  return variantClasses[props.variant]
})

// Expose methods for manual animation control
defineExpose({
  startAnimation: () => {
    // This would be implemented if we need manual control
    // For now, animations are handled by individual StatCounter components
  },
  resetCounters: () => {
    // Reset all counters - could be implemented if needed
  },
})
</script>

<style scoped>
.statistics-section {
  @apply py-16 relative;
}

.statistics-container {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8;
}

.statistics-header {
  @apply text-center mb-12;
}

.statistics-title {
  @apply text-2xl sm:text-3xl font-bold text-white mb-4;
}

.statistics-subtitle {
  @apply text-gray-300 max-w-2xl mx-auto;
}

.statistics-grid {
  @apply grid grid-cols-2 lg:grid-cols-4 gap-8;
}

.statistics-additional-content {
  @apply mt-12;
}
</style>

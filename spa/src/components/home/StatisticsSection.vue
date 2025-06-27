<template>
  <section 
    class="py-16 relative"
    :class="sectionClasses"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div v-if="title || subtitle" class="text-center mb-12">
        <h2 v-if="title" class="text-2xl sm:text-3xl font-bold text-white mb-4">
          {{ title }}
        </h2>
        <p v-if="subtitle" class="text-gray-300 max-w-2xl mx-auto">
          {{ subtitle }}
        </p>
      </div>

      <!-- Statistics Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
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
      <div v-if="$slots.content" class="mt-12">
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
      delay: 0
    },
    {
      id: 'presets',
      value: 12,
      label: 'Ready Presets',
      suffix: '+',
      color: 'green',
      delay: 200
    },
    {
      id: 'deployments',
      value: 25,
      label: 'Deployments',
      suffix: 'K+',
      color: 'purple',
      delay: 400
    },
    {
      id: 'performance',
      value: 85,
      label: 'Faster Setup',
      suffix: '%',
      color: 'cyan',
      delay: 600
    }
  ],
  variant: 'default',
  counterSize: 'md',
  animationDuration: 2000,
  animateOnMount: true,
  startAnimation: false
})

const sectionClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gray-900/80 backdrop-blur-sm border-t border-gray-800/50',
    transparent: 'bg-transparent',
    dark: 'bg-gray-900 border-t border-gray-800'
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
  }
})
</script>

<style scoped>
/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Grid responsive improvements */
.grid {
  gap: 2rem;
}

@media (max-width: 640px) {
  .grid {
    gap: 1.5rem;
  }
  
  .grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1024px) {
  .lg\:grid-cols-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

/* Section animations */
.section-animate {
  animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Enhanced visual separation */
.border-t {
  border-top-width: 1px;
}

/* Focus management for accessibility */
.grid:focus-within {
  outline: 2px solid #3b82f6;
  outline-offset: 4px;
  border-radius: 0.5rem;
}

/* Responsive text sizing */
@media (max-width: 640px) {
  .text-2xl {
    font-size: 1.5rem;
  }
  
  .text-3xl {
    font-size: 1.875rem;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .section-animate {
    animation: none;
  }
}
</style>

<template>
  <div class="text-center group">
    <div
      class="transition-transform duration-300 group-hover:scale-110 mb-2"
      :class="[valueClasses, animationClass]"
    >
      {{ displayValue }}{{ suffix }}
    </div>
    <div class="text-gray-400 font-medium">
      {{ label }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'

interface Props {
  value: number
  label: string
  prefix?: string
  suffix?: string
  duration?: number
  delay?: number
  color?: 'blue' | 'green' | 'purple' | 'cyan' | 'yellow'
  size?: 'sm' | 'md' | 'lg'
  animateOnMount?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  prefix: '',
  suffix: '',
  duration: 300,
  delay: 0,
  color: 'blue',
  size: 'md',
  animateOnMount: true,
})

const displayValue = ref(0)
const isAnimating = ref(false)

const valueClasses = computed(() => {
  const sizeClasses = {
    sm: 'text-2xl',
    md: 'text-4xl',
    lg: 'text-5xl',
  }

  const colorClasses = {
    blue: 'text-blue-400',
    green: 'text-green-400',
    purple: 'text-purple-400',
    cyan: 'text-cyan-400',
    yellow: 'text-yellow-400',
  }

  return ['font-bold', sizeClasses[props.size], colorClasses[props.color]]
})

const animationClass = computed(() => {
  return isAnimating.value ? 'animate-pulse' : ''
})

const formattedDisplayValue = computed(() => {
  return props.prefix + Math.floor(displayValue.value) + props.suffix
})

function animateCounter() {
  if (!props.animateOnMount || displayValue.value === props.value) {
    return
  }

  isAnimating.value = true
  const steps = 120 // 60 FPS
  const stepDuration = props.duration / steps
  const increment = props.value / steps

  let currentStep = 0

  const animate = () => {
    currentStep++
    const progress = currentStep / steps

    // Easing function for smooth animation (ease-out-quart)
    const easeOutQuart = 1 - Math.pow(1 - progress, 4)

    displayValue.value = parseInt(props.value * easeOutQuart)

    if (currentStep < steps) {
      setTimeout(animate, stepDuration)
    } else {
      displayValue.value = parseInt(props.value)
      isAnimating.value = false
    }
  }

  setTimeout(() => {
    animate()
  }, props.delay)
}

// Watch for value changes to re-animate
watch(
  () => props.value,
  (newValue, oldValue) => {
    if (newValue !== oldValue) {
      displayValue.value = 0
      animateCounter()
    }
  },
)

onMounted(() => {
  if (props.animateOnMount) {
    animateCounter()
  } else {
    displayValue.value = props.value
  }
})

// Expose method to manually trigger animation
defineExpose({
  animate: animateCounter,
  reset: () => {
    displayValue.value = 0
    isAnimating.value = false
  },
})
</script>

<style scoped>
/* Smooth transitions */
.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Pulse animation during counting */
.animate-pulse {
  animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}

/* Hover effect */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

/* Focus state for accessibility */
.group:focus-within {
  outline: 2px solid currentColor;
  outline-offset: 2px;
  border-radius: 0.5rem;
}

/* Text shadow for better contrast */
.text-blue-400,
.text-green-400,
.text-purple-400,
.text-cyan-400,
.text-yellow-400 {
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* Responsive sizing */
@media (max-width: 640px) {
  .text-4xl {
    font-size: 2.5rem;
  }

  .text-5xl {
    font-size: 3rem;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .transition-transform,
  .animate-pulse {
    transition: none;
    animation: none;
  }

  .group:hover .group-hover\:scale-110 {
    transform: none;
  }
}
</style>

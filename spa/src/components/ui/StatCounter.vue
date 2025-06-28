<template>
  <div class="stat-counter">
    <div
      class="stat-counter-value"
      :class="[valueClasses, animationClass]"
    >
      {{ displayValue }}{{ suffix }}
    </div>
    <div class="stat-counter-label">
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

  return ['stat-counter-value-base', sizeClasses[props.size], colorClasses[props.color]]
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
.stat-counter {
  @apply text-center;
}

.stat-counter-value {
  @apply transition-transform duration-300 group-hover:scale-110 mb-2;
}

.stat-counter-value-base {
  @apply font-bold;
}

.stat-counter-label {
  @apply text-gray-400 font-medium;
}
</style>

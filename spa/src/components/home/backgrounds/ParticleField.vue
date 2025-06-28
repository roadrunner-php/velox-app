<template>
  <div class="particle-field">
    <div
      v-for="particle in particles"
      :key="particle.id"
      class="particle"
      :class="[sizeClass, disabled ? 'animate-none' : '']"
      :style="{
        left: particle.x + '%',
        top: particle.y + '%',
        backgroundColor: particle.color,
        animationDelay: particle.delay + 's',
        animationDuration: particle.duration + 's',
        opacity: particle.opacity,
      }"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface Particle {
  id: number
  x: number
  y: number
  color: string
  delay: number
  duration: number
  opacity: number
}

interface Props {
  count?: number
  colors?: string[]
  size?: 'sm' | 'md' | 'lg'
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  count: 30,
  colors: () => ['#3b82f6', '#8b5cf6', '#06b6d4'],
  size: 'sm',
  disabled: false,
})

const particles = ref<Particle[]>([])

const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-1 h-1'
    case 'md':
      return 'w-2 h-2'
    case 'lg':
      return 'w-3 h-3'
    default:
      return 'w-1 h-1'
  }
})

function generateParticles() {
  particles.value = Array.from({ length: props.count }, (_, i) => ({
    id: i,
    x: Math.random() * 100,
    y: Math.random() * 100,
    color: props.colors[Math.floor(Math.random() * props.colors.length)] + '30', // Add transparency
    delay: Math.random() * 3,
    duration: 2 + Math.random() * 2,
    opacity: 0.3 + Math.random() * 0.4,
  }))
}

onMounted(() => {
  generateParticles()
})
</script>

<style scoped>
.particle-field {
  @apply absolute inset-0;
}

.particle {
  @apply absolute rounded-full animate-pulse;
}
</style>

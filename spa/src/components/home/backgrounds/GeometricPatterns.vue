<template>
  <div class="geometric-patterns" :style="{ opacity: opacity }">
    <svg
      class="geometric-patterns-svg"
      viewBox="0 0 1200 800"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden="true"
    >
      <defs>
        <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop
            offset="0%"
            :style="`stop-color:${gradientStart};stop-opacity:${gradientOpacity}`"
          />
          <stop
            offset="100%"
            :style="`stop-color:${gradientEnd};stop-opacity:${gradientOpacity * 0.3}`"
          />
        </linearGradient>

        <!-- Additional gradient variations -->
        <linearGradient id="lineGradient2" x1="100%" y1="0%" x2="0%" y2="100%">
          <stop offset="0%" :style="`stop-color:${gradientEnd};stop-opacity:${gradientOpacity}`" />
          <stop
            offset="100%"
            :style="`stop-color:${gradientStart};stop-opacity:${gradientOpacity * 0.3}`"
          />
        </linearGradient>
      </defs>

      <g class="geometric-lines" :class="{ 'animate-none': disabled }">
        <!-- Main pattern lines -->
        <line
          v-for="line in mainLines"
          :key="`main-${line.id}`"
          :x1="line.x1"
          :y1="line.y1"
          :x2="line.x2"
          :y2="line.y2"
          stroke="url(#lineGradient)"
          :stroke-width="line.width"
          :opacity="line.opacity"
        />

        <!-- Secondary pattern lines -->
        <line
          v-for="line in secondaryLines"
          :key="`secondary-${line.id}`"
          :x1="line.x1"
          :y1="line.y1"
          :x2="line.x2"
          :y2="line.y2"
          stroke="url(#lineGradient2)"
          :stroke-width="line.width"
          :opacity="line.opacity"
        />
      </g>
    </svg>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface GeometricLine {
  id: number
  x1: number
  y1: number
  x2: number
  y2: number
  width: number
  opacity: number
}

interface Props {
  opacity?: number
  gradientStart?: string
  gradientEnd?: string
  gradientOpacity?: number
  disabled?: boolean
  density?: 'low' | 'medium' | 'high'
}

const props = withDefaults(defineProps<Props>(), {
  opacity: 0.2,
  gradientStart: '#3b82f6',
  gradientEnd: '#8b5cf6',
  gradientOpacity: 0.3,
  disabled: false,
  density: 'medium',
})

const mainLines = computed<GeometricLine[]>(() => {
  const baseLines = [
    { id: 1, x1: 0, y1: 400, x2: 300, y2: 200, width: 1, opacity: 0.8 },
    { id: 2, x1: 900, y1: 600, x2: 1200, y2: 300, width: 1, opacity: 0.8 },
    { id: 3, x1: 100, y1: 100, x2: 500, y2: 400, width: 1, opacity: 0.6 },
    { id: 4, x1: 700, y1: 100, x2: 1100, y2: 500, width: 1, opacity: 0.6 },
  ]

  if (props.density === 'high') {
    return [
      ...baseLines,
      { id: 5, x1: 200, y1: 300, x2: 600, y2: 100, width: 0.5, opacity: 0.4 },
      { id: 6, x1: 800, y1: 700, x2: 1000, y2: 200, width: 0.5, opacity: 0.4 },
      { id: 7, x1: 50, y1: 600, x2: 400, y2: 700, width: 0.5, opacity: 0.4 },
    ]
  }

  if (props.density === 'low') {
    return baseLines.slice(0, 2)
  }

  return baseLines
})

const secondaryLines = computed<GeometricLine[]>(() => {
  if (props.density === 'low') return []

  return [
    { id: 1, x1: 400, y1: 0, x2: 600, y2: 300, width: 0.5, opacity: 0.3 },
    { id: 2, x1: 500, y1: 800, x2: 800, y2: 500, width: 0.5, opacity: 0.3 },
    { id: 3, x1: 0, y1: 200, x2: 200, y2: 600, width: 0.5, opacity: 0.3 },
  ]
})
</script>

<style scoped>
.geometric-patterns {
  @apply absolute inset-0;
}

.geometric-patterns-svg {
  @apply w-full h-full;
}

.geometric-lines {
  @apply animate-pulse;
}
</style>

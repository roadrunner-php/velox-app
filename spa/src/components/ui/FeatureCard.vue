<template>
  <div
    class="group relative overflow-hidden transition-all duration-500 transform hover:-translate-y-2"
    :class="[cardClasses, sizeClasses]"
  >
    <!-- Top accent line -->
    <div class="absolute top-0 left-0 w-full h-1" :class="accentClasses"></div>

    <!-- Card Content -->
    <div :class="paddingClasses">
      <!-- Icon Section -->
      <div
        class="flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg"
        :class="[iconContainerClasses, iconSizeClasses]"
      >
        <!-- Slot for custom icon -->
        <slot name="icon">
          <!-- Default icon if none provided -->
          <svg
            class="text-white"
            :class="iconClasses"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="defaultIconPath"
            />
          </svg>
        </slot>
      </div>

      <!-- Title -->
      <h3 class="font-bold text-white mb-4" :class="titleSizeClasses">
        {{ title }}
      </h3>

      <!-- Description -->
      <p class="text-gray-300 leading-relaxed" :class="descriptionSizeClasses">
        {{ description }}
      </p>

      <!-- Link/Button Section -->
      <div v-if="linkText && (href || to)" class="mt-6">
        <component
          :is="linkComponent"
          :href="href"
          :to="to"
          class="inline-flex items-center justify-center w-full px-6 py-3 font-semibold rounded-xl transition-all duration-300 group-hover:shadow-lg border"
          :class="linkClasses"
          :target="href ? '_blank' : undefined"
          :rel="href ? 'noopener noreferrer' : undefined"
        >
          {{ linkText }}
          <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 7l5 5m0 0l-5 5m5-5H6"
            />
          </svg>
        </component>
      </div>

      <!-- Badge/Tag Section -->
      <div v-if="badge" class="absolute top-3 right-3 z-10">
        <span
          class="px-2 py-1 text-xs font-medium rounded-full backdrop-blur-sm border"
          :class="badgeClasses"
        >
          {{ badge }}
        </span>
      </div>
    </div>

    <!-- Hover overlay effect -->
    <div
      class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
      :class="hoverOverlayClasses"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  title: string
  description: string
  variant?: 'default' | 'blue' | 'purple' | 'green' | 'yellow' | 'pink' | 'indigo'
  size?: 'sm' | 'md' | 'lg'
  href?: string
  to?: string | object
  linkText?: string
  badge?: string
  defaultIcon?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'md',
  defaultIcon: 'star',
})

const linkComponent = computed(() => {
  return props.to ? 'RouterLink' : 'a'
})

const cardClasses = computed(() => {
  const base = 'bg-gray-800/40 backdrop-blur-sm rounded-2xl shadow-2xl border border-gray-700/30'

  const variantClasses = {
    default: 'hover:shadow-gray-900/50 hover:border-gray-600/70',
    blue: 'hover:shadow-blue-500/10 hover:border-blue-500/30',
    purple: 'hover:shadow-purple-500/10 hover:border-purple-500/30',
    green: 'hover:shadow-green-500/10 hover:border-green-500/30',
    yellow: 'hover:shadow-yellow-500/10 hover:border-yellow-500/30',
    pink: 'hover:shadow-pink-500/10 hover:border-pink-500/30',
    indigo: 'hover:shadow-indigo-500/10 hover:border-indigo-500/30',
  }

  return `${base} ${variantClasses[props.variant]}`
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'max-w-sm'
    case 'lg':
      return 'max-w-lg'
    default:
      return 'max-w-md'
  }
})

const paddingClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'p-6'
    case 'lg':
      return 'p-10'
    default:
      return 'p-8'
  }
})

const iconContainerClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gradient-to-br from-gray-500 to-gray-600',
    blue: 'bg-gradient-to-br from-blue-500 to-blue-600',
    purple: 'bg-gradient-to-br from-purple-500 to-purple-600',
    green: 'bg-gradient-to-br from-green-400 to-green-600',
    yellow: 'bg-gradient-to-br from-yellow-400 to-orange-500',
    pink: 'bg-gradient-to-br from-pink-400 to-pink-600',
    indigo: 'bg-gradient-to-br from-indigo-400 to-indigo-600',
  }

  return `${variantClasses[props.variant]} rounded-2xl`
})

const iconSizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-16 h-16'
    case 'lg':
      return 'w-24 h-24'
    default:
      return 'w-20 h-20'
  }
})

const iconClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-8 h-8'
    case 'lg':
      return 'w-12 h-12'
    default:
      return 'w-10 h-10'
  }
})

const titleSizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'text-lg'
    case 'lg':
      return 'text-2xl'
    default:
      return 'text-xl'
  }
})

const descriptionSizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'text-sm'
    case 'lg':
      return 'text-base'
    default:
      return 'text-sm'
  }
})

const accentClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gradient-to-r from-gray-500 to-gray-600',
    blue: 'bg-gradient-to-r from-blue-500 to-blue-600',
    purple: 'bg-gradient-to-r from-purple-500 to-purple-600',
    green: 'bg-gradient-to-r from-green-400 to-green-600',
    yellow: 'bg-gradient-to-r from-yellow-400 to-orange-500',
    pink: 'bg-gradient-to-r from-pink-400 to-pink-600',
    indigo: 'bg-gradient-to-r from-indigo-400 to-indigo-600',
  }

  return variantClasses[props.variant]
})

const linkClasses = computed(() => {
  const variantClasses = {
    default:
      'bg-gradient-to-r from-gray-600 to-gray-700 text-white hover:from-gray-500 hover:to-gray-600 border-gray-500/30',
    blue: 'bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-500 hover:to-blue-600 border-blue-500/30',
    purple:
      'bg-gradient-to-r from-purple-600 to-purple-700 text-white hover:from-purple-500 hover:to-purple-600 border-purple-500/30',
    green:
      'bg-gradient-to-r from-green-600 to-green-700 text-white hover:from-green-500 hover:to-green-600 border-green-500/30',
    yellow:
      'bg-gradient-to-r from-yellow-600 to-orange-600 text-white hover:from-yellow-500 hover:to-orange-500 border-yellow-500/30',
    pink: 'bg-gradient-to-r from-pink-600 to-pink-700 text-white hover:from-pink-500 hover:to-pink-600 border-pink-500/30',
    indigo:
      'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white hover:from-indigo-500 hover:to-indigo-600 border-indigo-500/30',
  }

  return variantClasses[props.variant]
})

const badgeClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gray-700/60 text-gray-300 border-gray-600/50',
    blue: 'bg-blue-900/20 text-blue-300 border-blue-500/30',
    purple: 'bg-purple-900/20 text-purple-300 border-purple-500/30',
    green: 'bg-green-900/20 text-green-300 border-green-500/30',
    yellow: 'bg-yellow-900/20 text-yellow-300 border-yellow-500/30',
    pink: 'bg-pink-900/20 text-pink-300 border-pink-500/30',
    indigo: 'bg-indigo-900/20 text-indigo-300 border-indigo-500/30',
  }

  return variantClasses[props.variant]
})

const hoverOverlayClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gradient-to-br from-gray-800/80 to-gray-900/60',
    blue: 'bg-gradient-to-br from-blue-800/20 to-blue-900/10',
    purple: 'bg-gradient-to-br from-purple-800/20 to-purple-900/10',
    green: 'bg-gradient-to-br from-green-800/20 to-green-900/10',
    yellow: 'bg-gradient-to-br from-yellow-800/20 to-orange-900/10',
    pink: 'bg-gradient-to-br from-pink-800/20 to-pink-900/10',
    indigo: 'bg-gradient-to-br from-indigo-800/20 to-indigo-900/10',
  }

  return variantClasses[props.variant]
})

const defaultIconPath = computed(() => {
  const iconPaths = {
    star: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
    lightning: 'M13 10V3L4 14h7v7l9-11h-7z',
    check: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    heart:
      'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
    cog: 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4',
  }

  return iconPaths[props.defaultIcon as keyof typeof iconPaths] || iconPaths.star
})
</script>
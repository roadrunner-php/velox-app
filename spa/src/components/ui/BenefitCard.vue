<template>
  <div class="text-center group">
    <!-- Icon Container -->
    <div 
      class="mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg"
      :class="[
        iconContainerClasses,
        sizeClasses
      ]"
    >
      <!-- Slot for custom icon -->
      <slot name="icon">
        <!-- Default icon based on type -->
        <svg 
          class="text-white"
          :class="iconSizeClasses"
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
    <h3 
      class="font-bold text-white mb-4"
      :class="titleSizeClasses"
    >
      {{ title }}
    </h3>

    <!-- Description -->
    <p 
      class="text-gray-300 leading-relaxed"
      :class="descriptionSizeClasses"
    >
      {{ description }}
    </p>

    <!-- Additional Content Slot -->
    <div v-if="$slots.content" class="mt-4">
      <slot name="content"></slot>
    </div>

    <!-- Action Button -->
    <div v-if="actionText && (actionHref || actionTo)" class="mt-6">
      <component
        :is="actionComponent"
        :href="actionHref"
        :to="actionTo"
        class="inline-flex items-center text-sm font-medium transition-colors duration-200 hover:underline"
        :class="actionClasses"
        :target="actionHref ? '_blank' : undefined"
        :rel="actionHref ? 'noopener noreferrer' : undefined"
      >
        {{ actionText }}
        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M13 7l5 5m0 0l-5 5m5-5H6" 
          />
        </svg>
      </component>
    </div>

    <!-- Badge -->
    <div v-if="badge" class="mt-4">
      <span 
        class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full border"
        :class="badgeClasses"
      >
        {{ badge }}
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  title: string
  description: string
  iconType?: 'speed' | 'security' | 'optimization' | 'experience' | 'enterprise' | 'format' | 'custom'
  variant?: 'yellow' | 'green' | 'purple' | 'blue' | 'pink' | 'indigo'
  size?: 'sm' | 'md' | 'lg'
  actionText?: string
  actionHref?: string
  actionTo?: string | object
  badge?: string
}

const props = withDefaults(defineProps<Props>(), {
  iconType: 'speed',
  variant: 'blue',
  size: 'md'
})

const actionComponent = computed(() => {
  return props.actionTo ? 'RouterLink' : 'a'
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm': return 'w-16 h-16'
    case 'lg': return 'w-24 h-24'
    default: return 'w-20 h-20'
  }
})

const iconSizeClasses = computed(() => {
  switch (props.size) {
    case 'sm': return 'w-8 h-8'
    case 'lg': return 'w-12 h-12'
    default: return 'w-10 h-10'
  }
})

const titleSizeClasses = computed(() => {
  switch (props.size) {
    case 'sm': return 'text-lg'
    case 'lg': return 'text-2xl'
    default: return 'text-xl'
  }
})

const descriptionSizeClasses = computed(() => {
  switch (props.size) {
    case 'sm': return 'text-sm'
    case 'lg': return 'text-base'
    default: return 'text-sm'
  }
})

const iconContainerClasses = computed(() => {
  const variantClasses = {
    yellow: 'bg-gradient-to-br from-yellow-400 to-orange-500 shadow-yellow-500/20',
    green: 'bg-gradient-to-br from-green-400 to-green-600 shadow-green-500/20',
    purple: 'bg-gradient-to-br from-purple-400 to-purple-600 shadow-purple-500/20',
    blue: 'bg-gradient-to-br from-blue-400 to-blue-600 shadow-blue-500/20',
    pink: 'bg-gradient-to-br from-pink-400 to-pink-600 shadow-pink-500/20',
    indigo: 'bg-gradient-to-br from-indigo-400 to-indigo-600 shadow-indigo-500/20'
  }

  return `${variantClasses[props.variant]} rounded-2xl flex items-center justify-center`
})

const actionClasses = computed(() => {
  const variantClasses = {
    yellow: 'text-yellow-400 hover:text-yellow-300',
    green: 'text-green-400 hover:text-green-300',
    purple: 'text-purple-400 hover:text-purple-300',
    blue: 'text-blue-400 hover:text-blue-300',
    pink: 'text-pink-400 hover:text-pink-300',
    indigo: 'text-indigo-400 hover:text-indigo-300'
  }

  return variantClasses[props.variant]
})

const badgeClasses = computed(() => {
  const variantClasses = {
    yellow: 'bg-yellow-900/20 text-yellow-300 border-yellow-500/30',
    green: 'bg-green-900/20 text-green-300 border-green-500/30',
    purple: 'bg-purple-900/20 text-purple-300 border-purple-500/30',
    blue: 'bg-blue-900/20 text-blue-300 border-blue-500/30',
    pink: 'bg-pink-900/20 text-pink-300 border-pink-500/30',
    indigo: 'bg-indigo-900/20 text-indigo-300 border-indigo-500/30'
  }

  return variantClasses[props.variant]
})

const defaultIconPath = computed(() => {
  const iconPaths = {
    speed: 'M13 10V3L4 14h7v7l9-11h-7z', // Lightning bolt
    security: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', // Check circle
    optimization: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', // Server
    experience: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', // Heart
    enterprise: 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4', // Adjustments
    format: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9', // Globe
    custom: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' // Star
  }

  return iconPaths[props.iconType]
})
</script>
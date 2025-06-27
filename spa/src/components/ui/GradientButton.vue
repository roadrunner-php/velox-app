<template>
  <component 
    :is="tag"
    :to="to"
    :href="href"
    :disabled="disabled"
    :class="buttonClasses"
    :aria-label="ariaLabel"
    @click="handleClick"
  >
    <span class="flex items-center justify-center gap-2">
      <!-- Loading Spinner -->
      <div 
        v-if="loading"
        class="animate-spin rounded-full border-b-2 border-current"
        :class="spinnerSizeClass"
      ></div>
      
      <!-- Icon Slot -->
      <slot name="icon" v-if="!loading && $slots.icon" />
      
      <!-- Default Icon -->
      <span v-else-if="!loading && icon" class="text-lg">{{ icon }}</span>
      
      <!-- Text Content -->
      <span>{{ loading ? loadingText : text }}</span>
      
      <!-- Arrow Icon -->
      <svg 
        v-if="showArrow && !loading" 
        class="transition-transform duration-200 group-hover:translate-x-1"
        :class="iconSizeClass"
        fill="none" 
        stroke="currentColor" 
        viewBox="0 0 24 24"
      >
        <path 
          stroke-linecap="round" 
          stroke-linejoin="round" 
          stroke-width="2" 
          d="M13 7l5 5m0 0l-5 5m5-5H6" 
        />
      </svg>
    </span>
  </component>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  text: string
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost'
  size?: 'sm' | 'md' | 'lg'
  href?: string
  to?: string | object
  disabled?: boolean
  loading?: boolean
  loadingText?: string
  icon?: string
  showArrow?: boolean
  fullWidth?: boolean
  ariaLabel?: string
}

interface Emits {
  (e: 'click', event: MouseEvent): void
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  disabled: false,
  loading: false,
  loadingText: 'Loading...',
  showArrow: true,
  fullWidth: false
})

const emit = defineEmits<Emits>()

const tag = computed(() => {
  if (props.to) return 'RouterLink'
  if (props.href) return 'a'
  return 'button'
})

const baseClasses = 'group inline-flex items-center justify-center font-bold rounded-full transition-all duration-300 transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed'

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-500 hover:to-blue-600 focus:ring-blue-500 border border-blue-500/30 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30'
    case 'secondary':
      return 'bg-gradient-to-r from-purple-600 to-purple-700 text-white hover:from-purple-500 hover:to-purple-600 focus:ring-purple-500 border border-purple-500/30 shadow-lg shadow-purple-500/20 hover:shadow-purple-500/30'
    case 'outline':
      return 'bg-transparent border-2 border-purple-500/70 text-purple-400 hover:bg-purple-500 hover:text-white hover:border-purple-400 focus:ring-purple-500 shadow-lg shadow-purple-500/10 hover:shadow-purple-500/25'
    case 'ghost':
      return 'bg-gray-800/60 backdrop-blur-sm text-gray-300 border border-gray-700/50 hover:bg-gray-700 hover:text-white hover:border-gray-600/50 focus:ring-gray-500'
    default:
      return ''
  }
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'px-4 py-2 text-sm'
    case 'md':
      return 'px-8 py-4 text-lg'
    case 'lg':
      return 'px-10 py-5 text-xl'
    default:
      return 'px-8 py-4 text-lg'
  }
})

const widthClass = computed(() => props.fullWidth ? 'w-full' : 'min-w-[200px]')

const spinnerSizeClass = computed(() => {
  switch (props.size) {
    case 'sm': return 'w-3 h-3'
    case 'md': return 'w-5 h-5'
    case 'lg': return 'w-6 h-6'
    default: return 'w-5 h-5'
  }
})

const iconSizeClass = computed(() => {
  switch (props.size) {
    case 'sm': return 'w-4 h-4'
    case 'md': return 'w-5 h-5'
    case 'lg': return 'w-6 h-6'
    default: return 'w-5 h-5'
  }
})

const buttonClasses = computed(() => [
  baseClasses,
  variantClasses.value,
  sizeClasses.value,
  widthClass.value,
  {
    'hover:scale-105': !props.disabled && !props.loading,
    'active:scale-95': !props.disabled && !props.loading
  }
])

function handleClick(event: MouseEvent) {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

<style scoped>
/* Enhanced hover effects */
.group:hover:not(:disabled) {
  transform: translateY(-1px) scale(1.05);
}

.group:active:not(:disabled) {
  transform: scale(0.95);
}

/* Loading animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Focus styles for accessibility */
.focus\:ring-2:focus {
  box-shadow: 0 0 0 2px var(--tw-ring-color);
}

.focus\:ring-offset-2:focus {
  box-shadow: 
    0 0 0 2px var(--tw-ring-offset-color),
    0 0 0 4px var(--tw-ring-color);
}

/* Gradient background animation */
.bg-gradient-to-r {
  background-size: 200% 200%;
  animation: gradient-shift 6s ease infinite;
}

@keyframes gradient-shift {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Enhanced shadow effects */
.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
}

/* Glow effect on hover */
.hover\:shadow-blue-500\/30:hover {
  box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.3), 0 10px 10px -5px rgba(59, 130, 246, 0.2);
}

.hover\:shadow-purple-500\/30:hover {
  box-shadow: 0 20px 25px -5px rgba(139, 92, 246, 0.3), 0 10px 10px -5px rgba(139, 92, 246, 0.2);
}

.hover\:shadow-purple-500\/25:hover {
  box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.25);
}

/* Transition for smooth interactions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Respect reduced motion preferences */
@media (prefers-reduced-motion: reduce) {
  .group {
    transform: none !important;
  }
  
  .group:hover {
    transform: none !important;
  }
  
  .bg-gradient-to-r {
    animation: none;
  }
}
</style>

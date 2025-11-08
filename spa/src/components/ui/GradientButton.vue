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
    <span class="gradient-button-content">
      <!-- Loading Spinner -->
      <div v-if="loading" class="gradient-button-spinner" :class="spinnerSizeClass"></div>

      <!-- Icon Slot -->
      <slot name="icon" v-if="!loading && $slots.icon" />

      <!-- Default Icon -->
      <span v-else-if="!loading && icon" class="gradient-button-emoji">{{ icon }}</span>

      <!-- Text Content -->
      <span>{{ loading ? loadingText : text }}</span>

      <!-- Arrow Icon -->
      <svg
        v-if="showArrow && !loading"
        class="gradient-button-arrow"
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
  fullWidth: false,
})

const emit = defineEmits<Emits>()

const tag = computed(() => {
  if (props.to) return 'RouterLink'
  if (props.href) return 'a'
  return 'button'
})

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'gradient-button--primary'
    case 'secondary':
      return 'gradient-button--secondary'
    case 'outline':
      return 'gradient-button--outline'
    case 'ghost':
      return 'gradient-button--ghost'
    default:
      return 'gradient-button--primary'
  }
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'gradient-button--sm'
    case 'md':
      return 'gradient-button--md'
    case 'lg':
      return 'gradient-button--lg'
    default:
      return 'gradient-button--md'
  }
})

const widthClass = computed(() => (props.fullWidth ? 'w-full' : 'min-w-[200px]'))

const spinnerSizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-3 h-3'
    case 'md':
      return 'w-5 h-5'
    case 'lg':
      return 'w-6 h-6'
    default:
      return 'w-5 h-5'
  }
})

const iconSizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-4 h-4'
    case 'md':
      return 'w-5 h-5'
    case 'lg':
      return 'w-6 h-6'
    default:
      return 'w-5 h-5'
  }
})

const buttonClasses = computed(() => [
  'gradient-button-base',
  variantClasses.value,
  sizeClasses.value,
  widthClass.value,
  {
    'gradient-button--hover': !props.disabled && !props.loading,
    'gradient-button--disabled': props.disabled || props.loading,
  },
])

function handleClick(event: MouseEvent) {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

<style scoped>
.gradient-button-base {
  @apply cursor-pointer inline-flex items-center justify-center font-bold rounded-full transition-all duration-300 transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900;
}

.gradient-button--hover:not(.gradient-button--disabled) {
  @apply hover:scale-105 active:scale-95;
}

.gradient-button--disabled {
  @apply opacity-50 cursor-not-allowed;
}

.gradient-button--primary {
  @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-500 hover:to-blue-600 focus:ring-blue-500 border border-blue-500/30 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30;
}

.gradient-button--secondary {
  @apply bg-gradient-to-r from-purple-600 to-purple-700 text-white hover:from-purple-500 hover:to-purple-600 focus:ring-purple-500 border border-purple-500/30 shadow-lg shadow-purple-500/20 hover:shadow-purple-500/30;
}

.gradient-button--outline {
  @apply bg-transparent border-2 border-purple-500/70 text-purple-400 hover:bg-purple-500 hover:text-white hover:border-purple-400 focus:ring-purple-500 shadow-lg shadow-purple-500/10 hover:shadow-purple-500/25;
}

.gradient-button--ghost {
  @apply bg-gray-800/60 backdrop-blur-sm text-gray-300 border border-gray-700/50 hover:bg-gray-700 hover:text-white hover:border-gray-600/50 focus:ring-gray-500;
}

.gradient-button--sm {
  @apply px-4 py-2 text-sm;
}

.gradient-button--md {
  @apply px-8 py-4 text-lg;
}

.gradient-button--lg {
  @apply px-10 py-5 text-xl;
}

.gradient-button-content {
  @apply flex items-center justify-center gap-2;
}

.gradient-button-spinner {
  @apply animate-spin rounded-full border-b-2 border-current;
}

.gradient-button-emoji {
  @apply text-lg;
}

.gradient-button-arrow {
  @apply transition-transform duration-200 group-hover:translate-x-1;
}
</style>

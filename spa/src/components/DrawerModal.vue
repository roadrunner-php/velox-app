<template>
  <div 
    class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    @click="handleBackdropClick"
  >
    <div 
      ref="drawerRef"
      class="bg-slate-800 border border-slate-600 rounded-xl shadow-2xl w-full max-w-md transform transition-all duration-300"
      :class="[
        position === 'right' ? 'slide-right' : 'slide-left',
        show ? 'translate-x-0 opacity-100' : (position === 'right' ? 'translate-x-full opacity-0' : '-translate-x-full opacity-0')
      ]"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-slate-600">
        <h3 class="text-lg font-semibold text-white">
          {{ title }}
        </h3>
        <button
          @click="$emit('close')"
          class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-slate-700/50"
          aria-label="Close"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 max-h-96 overflow-y-auto">
        <slot></slot>
      </div>

      <!-- Footer Actions -->
      <div v-if="$slots.actions || actions?.length" class="p-6 pt-0">
        <slot name="actions">
          <div class="flex gap-3 justify-end">
            <button
              v-for="action in actions"
              :key="action.id"
              @click="$emit('action', action.id)"
              :disabled="action.disabled"
              :class="[
                'px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                action.variant === 'primary' ? 
                  'bg-blue-600 text-white hover:bg-blue-700 disabled:bg-slate-600' :
                action.variant === 'danger' ?
                  'bg-red-600 text-white hover:bg-red-700 disabled:bg-slate-600' :
                  'bg-slate-700/60 text-slate-300 border border-slate-600 hover:bg-slate-600 hover:text-white'
              ]"
            >
              <span class="flex items-center gap-2">
                <component v-if="action.icon" :is="action.icon" class="w-4 h-4" />
                {{ action.label }}
                <div v-if="action.loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-current"></div>
              </span>
            </button>
          </div>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'

interface Action {
  id: string
  label: string
  variant?: 'primary' | 'secondary' | 'danger'
  icon?: any
  disabled?: boolean
  loading?: boolean
}

interface Props {
  show: boolean
  title: string
  position?: 'left' | 'right'
  closeOnBackdrop?: boolean
  actions?: Action[]
}

interface Emits {
  (e: 'close'): void
  (e: 'action', actionId: string): void
}

const props = withDefaults(defineProps<Props>(), {
  position: 'right',
  closeOnBackdrop: true,
  actions: () => []
})

const emit = defineEmits<Emits>()

const drawerRef = ref<HTMLElement | null>(null)

function handleBackdropClick() {
  if (props.closeOnBackdrop) {
    emit('close')
  }
}

function handleEscapeKey(event: KeyboardEvent) {
  if (event.key === 'Escape' && props.show) {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleEscapeKey)
  // Prevent body scroll when drawer is open
  if (props.show) {
    document.body.style.overflow = 'hidden'
  }
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleEscapeKey)
  document.body.style.overflow = ''
})
</script>

<style scoped>
/* Backdrop blur */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Slide animations */
.slide-right {
  transform-origin: right center;
}

.slide-left {
  transform-origin: left center;
}

/* Enhanced shadow */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
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

/* Focus styles */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Hover effects */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: scale(0.98);
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgb(51 65 85);
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgb(107 114 128);
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgb(156 163 175);
}

/* Mobile adjustments */
@media (max-width: 640px) {
  .max-w-md {
    max-width: calc(100% - 2rem);
  }
}
</style>

<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div
        v-if="show"
        class="config-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title"
      >
        <transition name="modal-content">
          <div ref="modalRef" class="config-modal__content">
            <!-- Close button in top-right corner -->
            <button 
              class="config-modal__close-button"
              @click="emit('close')"
              aria-label="Close modal"
            >
              <svg class="config-modal__close-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <!-- Modal header -->
            <div class="config-modal__header">
              <h3 id="modal-title" class="config-modal__title">
                Generated Configuration
              </h3>
              <p class="config-modal__subtitle">
                Your custom RoadRunner configuration is ready
              </p>
            </div>

            <!-- Configuration content -->
            <div class="config-modal__body">
              <div class="config-modal__textarea-container">
                <textarea 
                  class="config-modal__textarea" 
                  readonly
                  :value="text"
                  aria-label="Generated configuration content"
                ></textarea>
                
                <!-- Copy overlay button -->
                <button 
                  class="config-modal__copy-overlay"
                  @click="copyToClipboard"
                  :class="{ 'config-modal__copy-overlay--copied': copied }"
                >
                  <svg v-if="!copied" class="config-modal__copy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  <svg v-else class="config-modal__copy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span class="config-modal__copy-text">
                    {{ copied ? 'Copied!' : 'Copy to Clipboard' }}
                  </span>
                </button>
              </div>
            </div>

            <!-- Modal footer -->
            <div class="config-modal__footer">
              <div class="config-modal__footer-info">
<!--                <span class="config-modal__char-count">-->
<!--                  {{ text.length.toLocaleString() }} characters-->
<!--                </span>-->
              </div>

              <div class="config-modal__footer-actions">
                <button 
                  class="config-modal__action-button config-modal__action-button--secondary"
                  @click="downloadConfig"
                >
                  <svg class="config-modal__action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Download
                </button>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps<{
  show: boolean
  text: string
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

const copied = ref(false)
const modalRef = ref<HTMLElement | null>(null)

async function copyToClipboard() {
  try {
    await navigator.clipboard.writeText(props.text)
    copied.value = true
    setTimeout(() => (copied.value = false), 2000)
  } catch (e) {
    // Fallback for older browsers
    const textArea = document.createElement('textarea')
    textArea.value = props.text
    document.body.appendChild(textArea)
    textArea.select()
    try {
      document.execCommand('copy')
      copied.value = true
      setTimeout(() => (copied.value = false), 2000)
    } catch (err) {
      console.error('Failed to copy text:', err)
    }
    document.body.removeChild(textArea)
  }
}

function downloadConfig() {
  const blob = new Blob([props.text], { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'velox.toml'
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

function handleOutsideClick(event: MouseEvent) {
  if (modalRef.value && !modalRef.value.contains(event.target as Node)) {
    emit('close')
  }
}

function handleEscapeKey(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleOutsideClick)
  document.addEventListener('keydown', handleEscapeKey)
  // Focus management
  if (modalRef.value) {
    modalRef.value.focus()
  }
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleOutsideClick)
  document.removeEventListener('keydown', handleEscapeKey)
})
</script>

<style scoped>
.config-modal {
  @apply fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-md;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
}

.config-modal__content {
  @apply w-full max-w-6xl h-[90vh] max-h-[800px] relative shadow-2xl outline-none;
  @apply bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl border border-gray-700/50;
  @apply flex flex-col overflow-hidden;
}

.config-modal__close-button {
  @apply absolute top-4 right-4 z-10 p-2 rounded-full transition-all duration-200;
  @apply text-gray-400 hover:text-white hover:border-gray-500/70;
}

.config-modal__close-icon {
  @apply w-5 h-5;
}

.config-modal__header {
  @apply p-6 pb-4 border-b border-gray-700/50;
  @apply bg-gradient-to-r from-gray-900/50 to-gray-800/50;
}

.config-modal__title {
  @apply text-2xl font-bold text-white mb-2;
}

.config-modal__subtitle {
  @apply text-gray-400 text-sm;
}

.config-modal__body {
  @apply flex-1 overflow-hidden;
}

.config-modal__textarea-container {
  @apply relative h-full overflow-hidden;
  @apply bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700/50;
}

.config-modal__textarea {
  @apply w-full h-full p-4 bg-gray-900 text-gray-100 font-mono text-sm leading-relaxed;
  @apply resize-none outline-none overflow-auto;
  @apply scrollbar-thin scrollbar-track-gray-800 scrollbar-thumb-gray-600;
}

.config-modal__copy-overlay {
  @apply absolute top-4 right-4 px-4 py-2 rounded-lg transition-all duration-200;
  @apply bg-blue-600/90 hover:bg-blue-500/90 text-white text-sm font-medium;
  @apply border border-blue-500/50 hover:border-blue-400/70;
  @apply backdrop-blur-sm shadow-lg hover:shadow-xl hover:shadow-blue-500/20;
  @apply flex items-center gap-2 opacity-80 hover:opacity-100;
}

.config-modal__copy-overlay--copied {
  @apply bg-green-600/90 border-green-500/50 hover:bg-green-500/90;
}

.config-modal__copy-icon {
  @apply w-4 h-4;
}

.config-modal__copy-text {
  @apply hidden sm:block;
}

.config-modal__footer {
  @apply p-6 pt-4 border-t border-gray-700/50 flex items-center justify-between;
  @apply bg-gradient-to-r from-gray-900/50 to-gray-800/50;
}

.config-modal__footer-info {
  @apply text-sm text-gray-400;
}

.config-modal__char-count {
  @apply font-mono;
}

.config-modal__footer-actions {
  @apply flex items-center gap-3;
}

.config-modal__action-button {
  @apply px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200;
  @apply flex items-center gap-2 border shadow-lg hover:shadow-xl;
}

.config-modal__action-button--secondary {
  @apply bg-gray-700/50 hover:bg-gray-600/50 text-gray-300 hover:text-white;
  @apply border-gray-600/50 hover:border-gray-500/70;
}

.config-modal__action-button--primary {
  @apply bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600;
  @apply text-white border-blue-500/50 hover:border-blue-400/70;
  @apply shadow-blue-500/20 hover:shadow-blue-500/30;
}

.config-modal__action-icon {
  @apply w-4 h-4;
}

/* Transition animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
  @apply transition-all duration-300;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  @apply opacity-0;
}

.modal-content-enter-active,
.modal-content-leave-active {
  @apply transition-all duration-300;
}

.modal-content-enter-from,
.modal-content-leave-to {
  @apply opacity-0 scale-95 translate-y-4;
}

/* Scrollbar styling */
.scrollbar-thin {
  scrollbar-width: thin;
}

.scrollbar-track-gray-800::-webkit-scrollbar-track {
  @apply bg-gray-800;
}

.scrollbar-thumb-gray-600::-webkit-scrollbar-thumb {
  @apply bg-gray-600 rounded-full;
}

.scrollbar-thumb-gray-600::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-500;
}

/* Responsive design */
@media (max-width: 640px) {
  .config-modal {
    @apply p-2;
  }
  
  .config-modal__content {
    @apply h-[95vh] max-h-none rounded-xl;
  }
  
  .config-modal__header,
  .config-modal__body,
  .config-modal__footer {
    @apply p-4;
  }
  
  .config-modal__title {
    @apply text-xl;
  }
  
  .config-modal__footer {
    @apply flex-col gap-3 items-stretch;
  }
  
  .config-modal__footer-actions {
    @apply w-full justify-center;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .config-modal__textarea {
    color-scheme: dark;
  }
}

/* Focus styles for accessibility */
.config-modal__close-button:focus-visible,
.config-modal__copy-overlay:focus-visible,
.config-modal__action-button:focus-visible {
  @apply ring-2 ring-blue-500 ring-offset-2 ring-offset-gray-900;
}
</style>

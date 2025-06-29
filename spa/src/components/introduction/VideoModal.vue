<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div
        v-if="show"
        class="video-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="video-modal-title"
      >
        <transition name="modal-content">
          <div ref="modalRef" class="video-modal__content">
            <!-- Close button -->
            <button 
              class="video-modal__close-button"
              @click="emit('close')"
              aria-label="Close video modal"
            >
              <svg class="video-modal__close-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <!-- Modal header -->
            <div class="video-modal__header">
              <h3 id="video-modal-title" class="video-modal__title">
                Velox in Action
              </h3>
              <p class="video-modal__subtitle">
                See how to build custom RoadRunner binaries in under 5 minutes
              </p>
            </div>

            <!-- Video content -->
            <div class="video-modal__body">
              <div class="video-modal__iframe-container">
                <iframe
                  :src="videoEmbedUrl"
                  class="video-modal__iframe"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen
                  :title="videoTitle"
                ></iframe>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

interface Props {
  show: boolean
  videoUrl?: string
  videoTitle?: string
}

const props = withDefaults(defineProps<Props>(), {
  videoUrl: 'https://www.youtube.com/watch?v=sddi_lh7ePo',
  videoTitle: 'Velox in Action - Build Custom RoadRunner Binaries'
})

const emit = defineEmits<{
  (e: 'close'): void
}>()

const modalRef = ref<HTMLElement | null>(null)

const videoEmbedUrl = computed(() => {
  // Convert YouTube watch URL to embed URL
  const url = new URL(props.videoUrl)
  if (url.hostname.includes('youtube.com')) {
    const videoId = url.searchParams.get('v')
    return `https://www.youtube.com/embed/${videoId}?autoplay=1`
  }
  return props.videoUrl
})

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
.video-modal {
  @apply fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-md;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
}

.video-modal__content {
  @apply w-full max-w-5xl h-[90vh] max-h-[675px] relative shadow-2xl outline-none;
  @apply bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl border border-gray-700/50;
  @apply flex flex-col overflow-hidden;
}

.video-modal__close-button {
  @apply absolute top-4 right-4 z-10 p-2 rounded-full transition-all duration-200;
  @apply text-gray-400 hover:text-white hover:bg-gray-800/50;
}

.video-modal__close-icon {
  @apply w-5 h-5;
}

.video-modal__header {
  @apply p-6 pb-4 border-b border-gray-700/50;
  @apply bg-gradient-to-r from-gray-900/50 to-gray-800/50;
}

.video-modal__title {
  @apply text-2xl font-bold text-white mb-2;
}

.video-modal__subtitle {
  @apply text-gray-400 text-sm;
}

.video-modal__body {
  @apply flex-1 overflow-hidden;
}

.video-modal__iframe-container {
  @apply w-full h-full overflow-hidden bg-black;
}

.video-modal__iframe {
  @apply w-full h-full;
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

/* Focus styles for accessibility */
.video-modal__close-button:focus-visible {
  @apply ring-2 ring-blue-500 ring-offset-2 ring-offset-gray-900;
}

/* Responsive design */
@media (max-width: 640px) {
  .video-modal {
    @apply p-2;
  }
  
  .video-modal__content {
    @apply h-[95vh] max-h-none rounded-xl;
  }
  
  .video-modal__header,
  .video-modal__body {
    @apply p-4;
  }
  
  .video-modal__title {
    @apply text-xl;
  }
}
</style>

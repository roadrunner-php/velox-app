<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div
        v-if="show"
        class="config-modal"
      >
        <transition name="modal-content">
          <div ref="modalRef" class="config-modal__content">
            <h3 class="config-modal__title">Generated Configuration</h3>

            <textarea class="config-modal__textarea" readonly>{{
              text
            }}</textarea>

            <div class="config-modal__actions">
              <button class="config-modal__copy-btn" @click="copyToClipboard">
                {{ copied ? 'Copied!' : 'Copy' }}
              </button>

              <button class="config-modal__close-btn" @click="emit('close')">
                ✖ Close
              </button>
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
    setTimeout(() => (copied.value = false), 1500)
  } catch (e) {
    alert('Failed to copy text.')
  }
}

function handleOutsideClick(event: MouseEvent) {
  if (modalRef.value && !modalRef.value.contains(event.target as Node)) {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleOutsideClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleOutsideClick)
})
</script>

<style scoped>
.config-modal {
  @apply fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/30;
}

.config-modal__content {
  @apply bg-gray-900 p-6 rounded-lg w-full max-w-4xl relative shadow-xl;
}

.config-modal__title {
  @apply text-lg font-semibold mb-4;
}

.config-modal__textarea {
  @apply w-full h-80 p-2 border rounded font-mono text-sm mb-4 bg-gray-900;
}

.config-modal__actions {
  @apply flex justify-between items-center;
}

.config-modal__copy-btn {
  @apply text-sm text-blue-600 hover:underline;
}

.config-modal__close-btn {
  @apply text-sm text-gray-500 hover:text-black;
}
</style>

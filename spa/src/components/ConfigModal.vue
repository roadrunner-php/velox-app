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

<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/30"
      >
        <transition name="modal-content">
          <div ref="modalRef" class="bg-white p-6 rounded-lg w-full max-w-4xl relative shadow-xl">
            <h3 class="text-lg font-semibold mb-4">Generated Configuration</h3>

            <textarea class="w-full h-80 p-2 border rounded font-mono text-sm mb-4" readonly>{{
              text
            }}</textarea>

            <div class="flex justify-between items-center">
              <button class="text-sm text-blue-600 hover:underline" @click="copyToClipboard">
                {{ copied ? 'Copied!' : 'Copy' }}
              </button>

              <button class="text-sm text-gray-500 hover:text-black" @click="emit('close')">
                ✖ Close
              </button>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </Teleport>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-content-enter-active,
.modal-content-leave-active {
  transition:
    transform 0.3s ease,
    opacity 0.3s ease;
}

.modal-content-enter-from {
  opacity: 0;
  transform: scale(0.95);
}

.modal-content-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>

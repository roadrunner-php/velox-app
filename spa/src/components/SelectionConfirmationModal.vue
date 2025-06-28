<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/60"
        @click="handleBackdropClick"
      >
        <transition name="modal-content">
          <div 
            ref="modalRef" 
            class="bg-slate-800 border border-slate-600 p-6 rounded-xl w-full max-w-lg shadow-2xl mx-4"
            @click.stop
          >
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-white">
                {{ title }}
              </h3>
              <button
                @click="$emit('cancel')"
                class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-slate-700/50"
                aria-label="Close modal"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Content -->
            <div class="mb-6">
              <slot name="content">
                <p class="text-slate-300 mb-4">
                  {{ description }}
                </p>
              </slot>

              <!-- Preview Data -->
              <div v-if="previewData">
                <!-- Plugin Impact Summary -->
                <div v-if="previewData.pluginSummary" class="mb-4 p-3 bg-blue-900/20 border border-blue-500/30 rounded-lg">
                  <h4 class="font-medium text-blue-300 mb-2">Plugin Impact</h4>
                  <div class="text-sm text-blue-200">
                    <div class="flex justify-between">
                      <span>Current plugins:</span>
                      <span class="font-medium">{{ previewData.pluginSummary.current }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span>New plugins:</span>
                      <span class="font-medium text-green-400">+{{ previewData.pluginSummary.new }}</span>
                    </div>
                    <div class="flex justify-between font-semibold border-t border-blue-400/30 mt-1 pt-1">
                      <span>Total plugins:</span>
                      <span>{{ previewData.pluginSummary.total }}</span>
                    </div>
                  </div>
                </div>

                <!-- New Dependencies Section -->
                <div v-if="previewData.newDependencies?.length > 1" class="mb-4">
                  <p class="text-sm text-slate-300 mb-3">
                    Selecting <strong class="text-white">{{ itemName }}</strong> will also select:
                  </p>
                  
                  <div class="max-h-32 overflow-y-auto mb-4 space-y-2">
                    <div
                      v-for="item in previewData.newDependencies.filter(dep => dep !== itemName)"
                      :key="item"
                      class="flex items-center gap-2 p-3 bg-green-900/20 border border-green-500/30 rounded-lg"
                    >
                      <div class="w-2 h-2 bg-green-400 rounded-full flex-shrink-0"></div>
                      <span class="text-white font-medium">{{ item }}</span>
                      <span class="text-xs text-green-400 ml-auto">({{ dependencyType }})</span>
                    </div>
                  </div>
                </div>

                <!-- Existing Dependencies Section -->
                <div v-if="previewData.existingDependencies?.length > 0" class="mb-4">
                  <p class="text-sm text-slate-400 mb-2">Already selected:</p>
                  <div class="flex flex-wrap gap-1 mb-3">
                    <span
                      v-for="item in previewData.existingDependencies.filter(dep => dep !== itemName)"
                      :key="item"
                      class="text-xs bg-slate-700/60 text-slate-300 px-2 py-1 rounded-full border border-slate-600/50"
                    >
                      {{ item }}
                    </span>
                  </div>
                </div>

                <!-- Conflicts Section -->
                <div v-if="previewData.conflicts?.length" class="mb-4">
                  <p class="text-sm text-red-400 font-medium mb-2">⚠️ Potential conflicts:</p>
                  <div class="space-y-2">
                    <div
                      v-for="conflict in previewData.conflicts"
                      :key="conflict"
                      class="flex items-center gap-2 p-3 bg-red-900/20 border border-red-500/30 rounded-lg"
                    >
                      <div class="w-2 h-2 bg-red-400 rounded-full flex-shrink-0"></div>
                      <span class="text-white font-medium">{{ conflict }}</span>
                      <span class="text-xs text-red-400 ml-auto">(conflict)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 justify-end">
              <button
                @click="$emit('cancel')"
                :disabled="isLoading"
                class="px-4 py-2 text-sm font-medium text-slate-300 bg-slate-700/60 border border-slate-600 rounded-lg hover:bg-slate-600 hover:text-white transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ cancelText }}
              </button>
              <button
                @click="$emit('confirm')"
                :disabled="isLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-500 rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <div v-if="isLoading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                {{ isLoading ? 'Processing...' : confirmText }}
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

interface PreviewData {
  toSelect?: string[]
  conflicts?: string[]
  newDependencies?: string[]
  existingDependencies?: string[]
  pluginSummary?: {
    current: number
    new: number
    total: number
  }
}

interface Props {
  show: boolean
  title?: string
  description?: string
  itemName?: string
  previewData?: PreviewData | null
  dependencyType?: string
  confirmText?: string
  cancelText?: string
  isLoading?: boolean
  closeOnBackdrop?: boolean
}

interface Emits {
  (e: 'confirm'): void
  (e: 'cancel'): void
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Confirm Selection',
  description: 'Please review the following changes:',
  itemName: '',
  previewData: null,
  dependencyType: 'dependency',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  isLoading: false,
  closeOnBackdrop: true
})

const emit = defineEmits<Emits>()

const modalRef = ref<HTMLElement | null>(null)

function handleBackdropClick() {
  if (props.closeOnBackdrop && !props.isLoading) {
    emit('cancel')
  }
}

function handleEscapeKey(event: KeyboardEvent) {
  if (event.key === 'Escape' && props.show && !props.isLoading) {
    emit('cancel')
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleEscapeKey)
  // Focus management
  if (props.show && modalRef.value) {
    modalRef.value.focus()
  }
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleEscapeKey)
})
</script>

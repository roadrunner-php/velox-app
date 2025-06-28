<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div
        v-if="show"
        class="selection-confirmation-modal"
        @click="handleBackdropClick"
      >
        <transition name="modal-content">
          <div 
            ref="modalRef" 
            class="selection-confirmation-modal__content"
            @click.stop
          >
            <!-- Header -->
            <div class="selection-confirmation-modal__header">
              <h3 class="selection-confirmation-modal__title">
                {{ title }}
              </h3>
              <button
                @click="$emit('cancel')"
                class="selection-confirmation-modal__close"
                aria-label="Close modal"
              >
                <svg class="selection-confirmation-modal__close-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Content -->
            <div class="selection-confirmation-modal__body">
              <slot name="content">
                <p class="selection-confirmation-modal__description">
                  {{ description }}
                </p>
              </slot>

              <!-- Preview Data -->
              <div v-if="previewData">
                <!-- Plugin Impact Summary -->
                <div v-if="previewData.pluginSummary" class="selection-confirmation-modal__summary">
                  <h4 class="selection-confirmation-modal__summary-title">Plugin Impact</h4>
                  <div class="selection-confirmation-modal__summary-stats">
                    <div class="selection-confirmation-modal__stat">
                      <span class="selection-confirmation-modal__stat-label">Current plugins:</span>
                      <span class="selection-confirmation-modal__stat-value">{{ previewData.pluginSummary.current }}</span>
                    </div>
                    <div class="selection-confirmation-modal__stat">
                      <span class="selection-confirmation-modal__stat-label">New plugins:</span>
                      <span class="selection-confirmation-modal__stat-value selection-confirmation-modal__stat-value--new">+{{ previewData.pluginSummary.new }}</span>
                    </div>
                    <div class="selection-confirmation-modal__stat selection-confirmation-modal__stat--total">
                      <span class="selection-confirmation-modal__stat-label">Total plugins:</span>
                      <span class="selection-confirmation-modal__stat-value">{{ previewData.pluginSummary.total }}</span>
                    </div>
                  </div>
                </div>

                <!-- New Dependencies Section -->
                <div v-if="previewData.newDependencies?.length > 1" class="selection-confirmation-modal__section">
                  <p class="selection-confirmation-modal__section-text">
                    Selecting <strong class="selection-confirmation-modal__highlight">{{ itemName }}</strong> will also select:
                  </p>
                  
                  <div class="selection-confirmation-modal__dependencies">
                    <div
                      v-for="item in previewData.newDependencies.filter(dep => dep !== itemName)"
                      :key="item"
                      class="selection-confirmation-modal__dependency selection-confirmation-modal__dependency--new"
                    >
                      <div class="selection-confirmation-modal__dependency-indicator selection-confirmation-modal__dependency-indicator--new"></div>
                      <span class="selection-confirmation-modal__dependency-name">{{ item }}</span>
                      <span class="selection-confirmation-modal__dependency-type">({{ dependencyType }})</span>
                    </div>
                  </div>
                </div>

                <!-- Existing Dependencies Section -->
                <div v-if="previewData.existingDependencies?.length > 0" class="selection-confirmation-modal__section">
                  <p class="selection-confirmation-modal__section-label">Already selected:</p>
                  <div class="selection-confirmation-modal__existing-tags">
                    <span
                      v-for="item in previewData.existingDependencies.filter(dep => dep !== itemName)"
                      :key="item"
                      class="selection-confirmation-modal__existing-tag"
                    >
                      {{ item }}
                    </span>
                  </div>
                </div>

                <!-- Conflicts Section -->
                <div v-if="previewData.conflicts?.length" class="selection-confirmation-modal__section">
                  <p class="selection-confirmation-modal__conflicts-title">⚠️ Potential conflicts:</p>
                  <div class="selection-confirmation-modal__conflicts">
                    <div
                      v-for="conflict in previewData.conflicts"
                      :key="conflict"
                      class="selection-confirmation-modal__dependency selection-confirmation-modal__dependency--conflict"
                    >
                      <div class="selection-confirmation-modal__dependency-indicator selection-confirmation-modal__dependency-indicator--conflict"></div>
                      <span class="selection-confirmation-modal__dependency-name">{{ conflict }}</span>
                      <span class="selection-confirmation-modal__dependency-type">(conflict)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="selection-confirmation-modal__actions">
              <button
                @click="$emit('cancel')"
                :disabled="isLoading"
                class="selection-confirmation-modal__button selection-confirmation-modal__button--cancel"
                :class="{
                  'selection-confirmation-modal__button--disabled': isLoading
                }"
              >
                {{ cancelText }}
              </button>
              <button
                @click="$emit('confirm')"
                :disabled="isLoading"
                class="selection-confirmation-modal__button selection-confirmation-modal__button--confirm"
                :class="{
                  'selection-confirmation-modal__button--disabled': isLoading
                }"
              >
                <div v-if="isLoading" class="selection-confirmation-modal__spinner"></div>
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

<style scoped>
.selection-confirmation-modal {
  @apply fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/60;
}

.selection-confirmation-modal__content {
  @apply bg-slate-800 border border-slate-600 p-6 rounded-xl w-full max-w-lg shadow-2xl mx-4;
}

.selection-confirmation-modal__header {
  @apply flex items-center justify-between mb-6;
}

.selection-confirmation-modal__title {
  @apply text-lg font-semibold text-white;
}

.selection-confirmation-modal__close {
  @apply text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-slate-700/50;
}

.selection-confirmation-modal__close-icon {
  @apply w-5 h-5;
}

.selection-confirmation-modal__body {
  @apply mb-6;
}

.selection-confirmation-modal__description {
  @apply text-slate-300 mb-4;
}

.selection-confirmation-modal__summary {
  @apply mb-4 p-3 bg-blue-900/20 border border-blue-500/30 rounded-lg;
}

.selection-confirmation-modal__summary-title {
  @apply font-medium text-blue-300 mb-2;
}

.selection-confirmation-modal__summary-stats {
  @apply text-sm text-blue-200;
}

.selection-confirmation-modal__stat {
  @apply flex justify-between;
}

.selection-confirmation-modal__stat--total {
  @apply font-semibold border-t border-blue-400/30 mt-1 pt-1;
}

.selection-confirmation-modal__stat-label {
  /* Base styles applied via parent */
}

.selection-confirmation-modal__stat-value {
  @apply font-medium;
}

.selection-confirmation-modal__stat-value--new {
  @apply text-green-400;
}

.selection-confirmation-modal__section {
  @apply mb-4;
}

.selection-confirmation-modal__section-text {
  @apply text-sm text-slate-300 mb-3;
}

.selection-confirmation-modal__section-label {
  @apply text-sm text-slate-400 mb-2;
}

.selection-confirmation-modal__highlight {
  @apply text-white;
}

.selection-confirmation-modal__dependencies {
  @apply max-h-32 overflow-y-auto mb-4 space-y-2;
}

.selection-confirmation-modal__dependency {
  @apply flex items-center gap-2 p-3 rounded-lg border;
}

.selection-confirmation-modal__dependency--new {
  @apply bg-green-900/20 border-green-500/30;
}

.selection-confirmation-modal__dependency--conflict {
  @apply bg-red-900/20 border-red-500/30;
}

.selection-confirmation-modal__dependency-indicator {
  @apply w-2 h-2 rounded-full flex-shrink-0;
}

.selection-confirmation-modal__dependency-indicator--new {
  @apply bg-green-400;
}

.selection-confirmation-modal__dependency-indicator--conflict {
  @apply bg-red-400;
}

.selection-confirmation-modal__dependency-name {
  @apply text-white font-medium;
}

.selection-confirmation-modal__dependency-type {
  @apply text-xs ml-auto;
}

.selection-confirmation-modal__dependency--new .selection-confirmation-modal__dependency-type {
  @apply text-green-400;
}

.selection-confirmation-modal__dependency--conflict .selection-confirmation-modal__dependency-type {
  @apply text-red-400;
}

.selection-confirmation-modal__existing-tags {
  @apply flex flex-wrap gap-1 mb-3;
}

.selection-confirmation-modal__existing-tag {
  @apply text-xs bg-slate-700/60 text-slate-300 px-2 py-1 rounded-full border border-slate-600/50;
}

.selection-confirmation-modal__conflicts-title {
  @apply text-sm text-red-400 font-medium mb-2;
}

.selection-confirmation-modal__conflicts {
  @apply space-y-2;
}

.selection-confirmation-modal__actions {
  @apply flex gap-3 justify-end;
}

.selection-confirmation-modal__button {
  @apply px-4 py-2 text-sm font-medium rounded-lg border transition-all duration-200 flex items-center gap-2;
}

.selection-confirmation-modal__button--cancel {
  @apply text-slate-300 bg-slate-700/60 border-slate-600 hover:bg-slate-600 hover:text-white;
}

.selection-confirmation-modal__button--confirm {
  @apply text-white bg-blue-600 border-blue-500 hover:bg-blue-700;
  @apply shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30;
}

.selection-confirmation-modal__button--disabled {
  @apply opacity-50 cursor-not-allowed;
}

.selection-confirmation-modal__spinner {
  @apply animate-spin rounded-full h-4 w-4 border-b-2 border-white;
}
</style>

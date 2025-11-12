<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="platform-selector" @click.self="handleClose">
        <div class="platform-selector__content" role="dialog" aria-modal="true">
          <!-- Header -->
          <div class="platform-selector__header">
            <h2 class="platform-selector__title">Select Target Platform</h2>
            <p class="platform-selector__description">
              Choose the operating system and architecture for your RoadRunner binary
            </p>
            <button
              @click="handleClose"
              class="platform-selector__close"
              aria-label="Close modal"
            >
              <svg class="platform-selector__close-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Platform Grid -->
          <div class="platform-selector__grid">
            <div
              v-for="option in platformOptions"
              :key="`${option.os}-${option.arch}`"
              class="platform-selector__option"
              :class="{
                'platform-selector__option--selected': isSelected(option),
              }"
              @click="selectPlatform(option)"
              @keydown="handleKeydown($event, option)"
              tabindex="0"
              role="button"
            >
              <!-- Popular Badge -->
              <div v-if="option.popular" class="platform-selector__badge">
                Popular
              </div>

              <!-- Selected Indicator -->
              <div v-if="isSelected(option)" class="platform-selector__indicator">
                <svg class="platform-selector__indicator-icon" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>

              <!-- Platform Info -->
              <div class="platform-selector__platform-header">
                <span class="platform-selector__icon">{{ option.icon }}</span>
                <h3 class="platform-selector__label">{{ option.label }}</h3>
              </div>
              <p class="platform-selector__platform-description">
                {{ option.description }}
              </p>
            </div>
          </div>

          <!-- Current Platform Detection -->
          <div v-if="detectedPlatform" class="platform-selector__detection">
            <svg class="platform-selector__detection-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="platform-selector__detection-text">
              Detected platform: <strong>{{ detectedPlatform.label }}</strong>
            </span>
          </div>

          <!-- Actions -->
          <div class="platform-selector__actions">
            <button
              @click="handleClose"
              class="platform-selector__button platform-selector__button--secondary"
              :disabled="isGenerating"
            >
              Cancel
            </button>
            <button
              @click="handleGenerate"
              class="platform-selector__button platform-selector__button--primary"
              :disabled="!selectedPlatform || isGenerating"
            >
              <div v-if="isGenerating" class="platform-selector__spinner"></div>
              <svg v-else class="platform-selector__button-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              {{ isGenerating ? 'Generating...' : 'Generate Binary' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { PLATFORM_OPTIONS, detectPlatform, type PlatformOption } from '@/api/binaryApi'

interface Props {
  show: boolean
  isGenerating?: boolean
}

interface Emits {
  (e: 'close'): void
  (e: 'generate', platform: PlatformOption): void
}

const props = withDefaults(defineProps<Props>(), {
  isGenerating: false,
})

const emit = defineEmits<Emits>()

const platformOptions = PLATFORM_OPTIONS
const selectedPlatform = ref<PlatformOption | null>(null)

// Detect current platform
const detected = detectPlatform()
const detectedPlatform = computed(() => {
  return platformOptions.find(p => p.os === detected.os && p.arch === detected.arch)
})

// Auto-select detected platform when modal opens
watch(() => props.show, (show) => {
  if (show && detectedPlatform.value && !selectedPlatform.value) {
    selectedPlatform.value = detectedPlatform.value
  }
})

function isSelected(option: PlatformOption): boolean {
  return selectedPlatform.value?.os === option.os && 
         selectedPlatform.value?.arch === option.arch
}

function selectPlatform(option: PlatformOption): void {
  selectedPlatform.value = option
}

function handleKeydown(event: KeyboardEvent, option: PlatformOption): void {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    selectPlatform(option)
  }
}

function handleClose(): void {
  if (!props.isGenerating) {
    emit('close')
  }
}

function handleGenerate(): void {
  if (selectedPlatform.value && !props.isGenerating) {
    emit('generate', selectedPlatform.value)
  }
}
</script>

<style scoped>
/* Modal Overlay */
.platform-selector {
  @apply fixed inset-0 z-50 flex items-center justify-center p-4;
  @apply bg-black/70 backdrop-blur-sm;
}

/* Modal Content */
.platform-selector__content {
  @apply relative w-full max-w-4xl max-h-[90vh] overflow-y-auto;
  @apply bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl shadow-2xl;
  @apply border border-gray-700/50;
  @apply p-6 md:p-8;
}

/* Header */
.platform-selector__header {
  @apply relative mb-8;
}

.platform-selector__title {
  @apply text-2xl md:text-3xl font-bold text-white mb-2;
}

.platform-selector__description {
  @apply text-gray-300 text-sm md:text-base;
}

.platform-selector__close {
  @apply absolute top-0 right-0 p-2 text-gray-400 hover:text-white;
  @apply transition-colors duration-200 rounded-lg hover:bg-gray-800/50;
}

.platform-selector__close-icon {
  @apply w-6 h-6;
}

/* Platform Grid */
.platform-selector__grid {
  @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6;
}

.platform-selector__option {
  @apply relative p-4 border-2 rounded-xl cursor-pointer transition-all duration-200;
  @apply bg-gradient-to-br from-gray-800/60 to-gray-900/40;
  @apply border-gray-700/50 hover:border-gray-600/70;
  @apply hover:shadow-xl hover:shadow-gray-900/50;
  @apply focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900;
}

.platform-selector__option--selected {
  @apply border-blue-500/50 bg-gradient-to-br from-blue-900/40 to-blue-800/30;
  @apply shadow-blue-500/20;
}

.platform-selector__badge {
  @apply absolute -top-2 -right-2 bg-gradient-to-r from-green-500 to-green-600;
  @apply text-white text-xs font-bold px-2 py-1 rounded-full;
  @apply shadow-lg shadow-green-500/30 border border-white/10;
}

.platform-selector__indicator {
  @apply absolute top-3 right-3 w-5 h-5;
  @apply bg-gradient-to-r from-blue-500 to-blue-600 rounded-full;
  @apply flex items-center justify-center shadow-lg shadow-blue-500/30;
}

.platform-selector__indicator-icon {
  @apply w-3 h-3 text-white;
}

.platform-selector__platform-header {
  @apply flex items-center gap-3 mb-2;
}

.platform-selector__icon {
  @apply text-2xl;
}

.platform-selector__label {
  @apply font-semibold text-white text-lg;
}

.platform-selector__platform-description {
  @apply text-sm text-gray-300;
}

/* Detection Info */
.platform-selector__detection {
  @apply flex items-center gap-2 p-3 rounded-lg;
  @apply bg-blue-900/20 border border-blue-500/30 mb-6;
}

.platform-selector__detection-icon {
  @apply w-5 h-5 text-blue-400 flex-shrink-0;
}

.platform-selector__detection-text {
  @apply text-sm text-blue-300;
}

/* Actions */
.platform-selector__actions {
  @apply flex gap-3 justify-end;
}

.platform-selector__button {
  @apply px-6 py-3 rounded-lg font-semibold transition-all duration-200;
  @apply flex items-center gap-2;
  @apply focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900;
}

.platform-selector__button--secondary {
  @apply bg-gray-700 hover:bg-gray-600 text-white;
  @apply focus:ring-gray-500;
}

.platform-selector__button--primary {
  @apply bg-gradient-to-r from-blue-600 to-blue-700;
  @apply hover:from-blue-500 hover:to-blue-600;
  @apply text-white shadow-lg shadow-blue-500/20;
  @apply focus:ring-blue-500;
  @apply disabled:from-gray-600 disabled:to-gray-700 disabled:text-gray-400;
  @apply disabled:cursor-not-allowed disabled:shadow-none;
}

.platform-selector__button-icon {
  @apply w-5 h-5;
}

.platform-selector__spinner {
  @apply animate-spin rounded-full h-5 w-5 border-b-2 border-white;
}

/* Modal Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active .platform-selector__content,
.modal-leave-active .platform-selector__content {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .platform-selector__content,
.modal-leave-to .platform-selector__content {
  transform: scale(0.95);
  opacity: 0;
}

/* Scrollbar */
.platform-selector__content::-webkit-scrollbar {
  @apply w-2;
}

.platform-selector__content::-webkit-scrollbar-track {
  @apply bg-gray-800 rounded-full;
}

.platform-selector__content::-webkit-scrollbar-thumb {
  @apply bg-gray-600 rounded-full hover:bg-gray-500;
}
</style>

<template>
  <Teleport to="#sticky-configuration">
    <div class="configuration-generation__wrapper">
    <div v-if="isVisible" class="configuration-generation">
      <div class="configuration-generation__header">
        <h3 class="configuration-generation__title">
          {{ title }}
        </h3>
        <p class="configuration-generation__description">
          {{ description }}
        </p>
      </div>

      <!-- Configuration Format Selector -->
      <ConfigFormatSelector
        v-model="internalFormat"
        :disabled="disabled"
        class="configuration-generation__format-selector"
      />

      <!-- Additional Options Slot -->
      <div v-if="$slots.options" class="configuration-generation__options">
        <slot name="options"></slot>
      </div>

      <!-- Generate Button -->
      <div class="configuration-generation__button-wrapper">
        <button
          @click="handleGenerate"
          :disabled="disabled || isGenerating"
          class="configuration-generation__button"
          :class="{
            'configuration-generation__button--loading': isGenerating,
            'configuration-generation__button--disabled': disabled || isGenerating
          }"
        >
          <span class="configuration-generation__button-content">
            <!-- Loading Spinner -->
            <div
              v-if="isGenerating"
              class="configuration-generation__spinner"
            ></div>
            <!-- Download Icon -->
            <svg v-else class="configuration-generation__download-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            {{ isGenerating ? 'Generating...' : buttonText }}
          </span>
        </button>
      </div>

      <!-- Status Messages -->
      <div v-if="statusMessage" class="configuration-generation__status">
        <p
          class="configuration-generation__status-text"
          :class="`configuration-generation__status-text--${statusType}`"
        >
          {{ statusMessage }}
        </p>
      </div>

      <!-- Progress Indicator -->
      <div v-if="showProgress && progress !== null" class="configuration-generation__progress">
        <div class="configuration-generation__progress-bar">
          <div
            class="configuration-generation__progress-fill"
            :style="{ width: `${Math.min(100, Math.max(0, progress))}%` }"
          ></div>
        </div>
        <div class="configuration-generation__progress-text">
          {{ Math.round(progress) }}% complete
        </div>
      </div>

      <!-- Help Text -->
      <div v-if="helpText" class="configuration-generation__help">
        <p class="configuration-generation__help-text">
          {{ helpText }}
        </p>
      </div>
    </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import ConfigFormatSelector from './ConfigFormatSelector.vue'

type ConfigFormat = 'toml' | 'json' | 'docker' | 'dockerfile'

interface Props {
  modelValue?: ConfigFormat
  selectionCount?: number
  title?: string
  description?: string
  buttonText?: string
  disabled?: boolean
  isGenerating?: boolean
  statusMessage?: string
  statusType?: 'success' | 'error' | 'info' | 'warning'
  showProgress?: boolean
  progress?: number | null
  helpText?: string
}

interface Emits {
  (e: 'update:modelValue', value: ConfigFormat): void
  (e: 'generate', format: ConfigFormat): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: 'toml',
  selectionCount: 0,
  title: 'Generate Configuration',
  description: 'Choose your preferred format and generate the configuration file',
  buttonText: 'Generate Configuration',
  disabled: false,
  isGenerating: false,
  statusMessage: '',
  statusType: 'info',
  showProgress: false,
  progress: null,
  helpText: ''
})

const emit = defineEmits<Emits>()

const internalFormat = ref(props.modelValue)

const isVisible = computed(() => props.selectionCount > 0)

// Watch for external format changes
watch(() => props.modelValue, (newValue) => {
  internalFormat.value = newValue
})

// Watch for internal format changes and emit
watch(internalFormat, (newValue) => {
  emit('update:modelValue', newValue)
})

function handleGenerate() {
  if (!props.disabled && !props.isGenerating) {
    emit('generate', internalFormat.value)
  }
}
</script>

<style scoped>
.configuration-generation__wrapper {
  @apply border-t border-slate-700/50 mx-auto;
}

.configuration-generation {
  @apply p-8 max-w-7xl mx-auto;
}

.configuration-generation__header {
  @apply mb-8;
}

.configuration-generation__title {
  @apply text-xl font-semibold text-white mb-2;
}

.configuration-generation__description {
  @apply text-slate-400;
}

.configuration-generation__format-selector {
  @apply mb-8;
}

.configuration-generation__options {
  @apply mb-8;
}

.configuration-generation__button-wrapper {
  @apply flex items-center justify-center;
}

.configuration-generation__button {
  @apply w-full max-w-md px-8 py-4 text-white font-bold text-lg rounded-xl transition-all duration-200;
  @apply bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600;
  @apply focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900;
  @apply shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30;
  @apply transform hover:scale-[1.02] active:scale-[0.98];
}

.configuration-generation__button--disabled {
  @apply from-slate-600 to-slate-700 text-slate-400 cursor-not-allowed;
  @apply hover:scale-100 hover:shadow-none;
}

.configuration-generation__button-content {
  @apply flex items-center justify-center gap-2;
}

.configuration-generation__spinner {
  @apply animate-spin rounded-full h-5 w-5 border-b-2 border-white;
}

.configuration-generation__download-icon {
  @apply w-5 h-5;
}

.configuration-generation__status {
  @apply mt-4 text-center;
}

.configuration-generation__status-text {
  @apply text-sm font-medium;
}

.configuration-generation__status-text--success {
  @apply text-green-400;
}

.configuration-generation__status-text--error {
  @apply text-red-400;
}

.configuration-generation__status-text--info {
  @apply text-blue-400;
}

.configuration-generation__status-text--warning {
  @apply text-yellow-400;
}

.configuration-generation__progress {
  @apply mt-4;
}

.configuration-generation__progress-bar {
  @apply bg-slate-700/50 rounded-full h-2 overflow-hidden max-w-md mx-auto;
}

.configuration-generation__progress-fill {
  @apply bg-gradient-to-r from-blue-500 to-blue-600 h-full transition-all duration-500 ease-out rounded-full;
}

.configuration-generation__progress-text {
  @apply text-xs text-slate-400 mt-2 text-center;
}

.configuration-generation__help {
  @apply mt-6 text-center;
}

.configuration-generation__help-text {
  @apply text-xs text-slate-500 max-w-md mx-auto leading-relaxed;
}
</style>

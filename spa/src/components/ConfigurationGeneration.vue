<template>
  <div v-if="isVisible" class="pt-8 border-t border-slate-700/50">
    <div class="mb-8">
      <h3 class="text-xl font-semibold text-white mb-2">
        {{ title }}
      </h3>
      <p class="text-slate-400">
        {{ description }}
      </p>
    </div>
    
    <!-- Configuration Format Selector -->
    <ConfigFormatSelector 
      v-model="internalFormat" 
      :disabled="disabled"
      class="mb-8" 
    />

    <!-- Additional Options Slot -->
    <div v-if="$slots.options" class="mb-8">
      <slot name="options"></slot>
    </div>

    <!-- Generate Button -->
    <div class="flex items-center justify-center">
      <button
        @click="handleGenerate"
        :disabled="disabled || isGenerating"
        class="w-full max-w-md px-8 py-4 text-white font-bold text-lg rounded-xl transition-all duration-200 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 disabled:from-slate-600 disabled:to-slate-700 disabled:text-slate-400 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transform hover:scale-[1.02] active:scale-[0.98]"
      >
        <span class="flex items-center justify-center gap-2">
          <!-- Loading Spinner -->
          <div 
            v-if="isGenerating"
            class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"
          ></div>
          <!-- Download Icon -->
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          {{ isGenerating ? 'Generating...' : buttonText }}
        </span>
      </button>
    </div>

    <!-- Status Messages -->
    <div v-if="statusMessage" class="mt-4 text-center">
      <p 
        class="text-sm font-medium"
        :class="{
          'text-green-400': statusType === 'success',
          'text-red-400': statusType === 'error',
          'text-blue-400': statusType === 'info',
          'text-yellow-400': statusType === 'warning'
        }"
      >
        {{ statusMessage }}
      </p>
    </div>

    <!-- Progress Indicator -->
    <div v-if="showProgress && progress !== null" class="mt-4">
      <div class="bg-slate-700/50 rounded-full h-2 overflow-hidden max-w-md mx-auto">
        <div 
          class="bg-gradient-to-r from-blue-500 to-blue-600 h-full transition-all duration-500 ease-out rounded-full"
          :style="{ width: `${Math.min(100, Math.max(0, progress))}%` }"
        ></div>
      </div>
      <div class="text-xs text-slate-400 mt-2 text-center">
        {{ Math.round(progress) }}% complete
      </div>
    </div>

    <!-- Help Text -->
    <div v-if="helpText" class="mt-6 text-center">
      <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
        {{ helpText }}
      </p>
    </div>
  </div>
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
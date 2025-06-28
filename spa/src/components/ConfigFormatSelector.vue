<template>
  <div class="config-format-selector">
    <div
      class="config-format-selector__grid"
      role="radiogroup"
      aria-label="Configuration format selection"
    >
      <div
        v-for="option in formatOptions"
        :key="option.value"
        class="config-format-selector__option"
        :class="{
          'config-format-selector__option--disabled': option.disabled,
        }"
        @click="!option.disabled && selectFormat(option.value)"
        @keydown="handleKeydown($event, option.value)"
        tabindex="0"
        role="radio"
        :aria-checked="selectedValue === option.value"
        :aria-disabled="option.disabled"
      >
        <!-- Card -->
        <div
          class="config-format-selector__card"
          :class="{
            'config-format-selector__card--selected':
              selectedValue === option.value && !option.disabled,
            'config-format-selector__card--unselected':
              selectedValue !== option.value && !option.disabled,
            'config-format-selector__card--disabled': option.disabled,
            'config-format-selector__card--focusable': !option.disabled,
          }"
        >
          <!-- Recommended badge -->
          <div v-if="option.recommended && !disabled" class="config-format-selector__badge">
            Recommended
          </div>

          <!-- Selected indicator -->
          <div v-if="selectedValue === option.value" class="config-format-selector__indicator">
            <svg
              class="config-format-selector__indicator-icon"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </div>

          <!-- Format icon and label -->
          <div class="config-format-selector__header">
            <span
              class="config-format-selector__icon"
              role="img"
              :aria-label="`${option.label} icon`"
            >
              {{ option.icon }}
            </span>
            <h3 class="config-format-selector__title">
              {{ option.label }}
            </h3>
          </div>

          <!-- Description -->
          <p class="config-format-selector__description">
            {{ option.description }}
          </p>

          <!-- Hover indicator -->
          <div
            v-if="!option.disabled"
            class="config-format-selector__hover-ring"
            :class="{
              'config-format-selector__hover-ring--selected': selectedValue === option.value,
            }"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

type ConfigFormat = 'toml' | 'json' | 'docker' | 'dockerfile'

interface FormatOption {
  value: ConfigFormat
  label: string
  description: string
  icon: string
  recommended?: boolean
}

interface Props {
  modelValue?: ConfigFormat
  disabled?: boolean
}

interface Emits {
  (e: 'update:modelValue', value: ConfigFormat): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: 'toml',
  disabled: false,
})

const emit = defineEmits<Emits>()

const formatOptions: FormatOption[] = [
  {
    value: 'toml',
    label: 'TOML',
    description: 'Human-readable configuration format (recommended)',
    icon: '📄',
  },
  {
    value: 'dockerfile',
    label: 'Dockerfile',
    description: 'Complete Dockerfile with RoadRunner setup',
    icon: '🌊',
  },
  {
    value: 'binary',
    label: 'Binary',
    description: 'Will be soon available',
    icon: '📦',
    recommended: true,
    disabled: true,
  },
]

const selectedValue = computed({
  get: () => props.modelValue,
  set: (value: ConfigFormat) => emit('update:modelValue', value),
})

function selectFormat(format: ConfigFormat) {
  if (!props.disabled) {
    selectedValue.value = format
  }
}

function handleKeydown(event: KeyboardEvent, format: ConfigFormat) {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    selectFormat(format)
  }
}
</script>

<style scoped>
.config-format-selector {
  @apply w-full;
}

.config-format-selector__label {
  @apply block text-sm font-medium text-white mb-3;
}

.config-format-selector__grid {
  @apply grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-3 gap-3;
}

.config-format-selector__option {
  @apply relative cursor-pointer;
}

.config-format-selector__option--disabled {
  @apply cursor-not-allowed opacity-50;
}

.config-format-selector__card {
  @apply relative p-4 border-2 rounded-lg transition-all duration-200 h-full flex flex-col;
  @apply backdrop-blur-sm shadow-xl;
}

.config-format-selector__card--selected {
  @apply border-blue-500/50 bg-gradient-to-br from-blue-900/40 to-blue-800/30;
  @apply shadow-blue-500/20 hover:shadow-blue-500/30 hover:border-blue-400/70;
}

.config-format-selector__card--unselected {
  @apply border-gray-700/50 bg-gradient-to-br from-gray-800/60 to-gray-900/40;
  @apply hover:border-gray-600/70 hover:shadow-2xl hover:shadow-gray-900/50;
  @apply hover:bg-gradient-to-br hover:from-gray-800/80 hover:to-gray-900/60;
}

.config-format-selector__card--disabled {
  @apply border-gray-800/50 bg-gradient-to-br from-gray-900/40 to-gray-800/30;
}

.config-format-selector__card--focusable {
  @apply focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2 focus-within:ring-offset-gray-900;
}

.config-format-selector__badge {
  @apply absolute -top-2 -right-2 bg-gradient-to-r from-green-500 to-green-600 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg shadow-green-500/30 border border-white/10;
  z-index: 2;
}

.config-format-selector__indicator {
  @apply absolute top-3 right-3 w-5 h-5 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg shadow-blue-500/30;
}

.config-format-selector__indicator-icon {
  @apply w-3 h-3 text-white;
}

.config-format-selector__header {
  @apply flex items-center gap-3 mb-2;
}

.config-format-selector__icon {
  @apply text-2xl;
}

.config-format-selector__title {
  @apply font-semibold text-white text-lg;
}

.config-format-selector__description {
  @apply text-sm text-gray-300 flex-1;
}

.config-format-selector__hover-ring {
  @apply absolute inset-0 rounded-lg ring-2 ring-transparent transition-all duration-200 pointer-events-none;
}

.config-format-selector__hover-ring--selected {
  @apply ring-blue-500/30;
}
</style>

<template>
  <div class="config-format-selector">
    <label class="config-format-selector__label">
      Configuration Format
    </label>

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
          'config-format-selector__option--disabled': disabled
        }"
        @click="selectFormat(option.value)"
        @keydown="handleKeydown($event, option.value)"
        tabindex="0"
        role="radio"
        :aria-checked="selectedValue === option.value"
        :aria-disabled="disabled"
      >
        <!-- Hidden radio input for form compatibility -->
        <input
          type="radio"
          :name="`config-format-${Math.random()}`"
          :value="option.value"
          :checked="selectedValue === option.value"
          :disabled="disabled"
          class="sr-only"
          @change="selectFormat(option.value)"
        />

        <!-- Card -->
        <div
          class="config-format-selector__card"
          :class="{
            'config-format-selector__card--selected': selectedValue === option.value && !disabled,
            'config-format-selector__card--unselected': selectedValue !== option.value && !disabled,
            'config-format-selector__card--disabled': disabled,
            'config-format-selector__card--focusable': !disabled
          }"
        >
          <!-- Recommended badge -->
          <div
            v-if="option.recommended && !disabled"
            class="config-format-selector__badge"
          >
            Recommended
          </div>

          <!-- Selected indicator -->
          <div
            v-if="selectedValue === option.value"
            class="config-format-selector__indicator"
          >
            <svg class="config-format-selector__indicator-icon" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </div>

          <!-- Format icon and label -->
          <div class="config-format-selector__header">
            <span class="config-format-selector__icon" role="img" :aria-label="`${option.label} icon`">
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
            v-if="!disabled"
            class="config-format-selector__hover-ring"
            :class="{
              'config-format-selector__hover-ring--selected': selectedValue === option.value
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
  disabled: false
})

const emit = defineEmits<Emits>()

const formatOptions: FormatOption[] = [
  {
    value: 'toml',
    label: 'TOML',
    description: 'Human-readable configuration format (recommended)',
    icon: '📄',
    recommended: true
  },
  {
    value: 'json',
    label: 'JSON',
    description: 'Machine-readable format for programmatic use',
    icon: '🔧'
  },
  {
    value: 'dockerfile',
    label: 'Dockerfile',
    description: 'Complete Dockerfile with RoadRunner setup',
    icon: '📦'
  }
]

const selectedValue = computed({
  get: () => props.modelValue,
  set: (value: ConfigFormat) => emit('update:modelValue', value)
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
  @apply block text-sm font-medium text-gray-700 mb-3;
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
}

.config-format-selector__card--selected {
  @apply border-blue-600 bg-blue-50 shadow-md;
}

.config-format-selector__card--unselected {
  @apply border-gray-300 bg-white hover:border-blue-300 hover:bg-blue-50 hover:shadow-sm;
}

.config-format-selector__card--disabled {
  @apply border-gray-200 bg-gray-50;
}

.config-format-selector__card--focusable {
  @apply focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2;
}

.config-format-selector__badge {
  @apply absolute -top-2 -right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm;
  z-index: 2;
}

.config-format-selector__indicator {
  @apply absolute top-3 right-3 w-5 h-5 bg-blue-600 rounded-full flex items-center justify-center;
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
  @apply font-semibold text-gray-900 text-lg;
}

.config-format-selector__description {
  @apply text-sm text-gray-600 flex-1;
}

.config-format-selector__hover-ring {
  @apply absolute inset-0 rounded-lg ring-2 ring-transparent group-hover:ring-blue-300 transition-all duration-200 pointer-events-none;
}

.config-format-selector__hover-ring--selected {
  @apply ring-blue-600;
}
</style>

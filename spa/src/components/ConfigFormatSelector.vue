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

<template>
  <div class="w-full">
    <label class="block text-sm font-medium text-gray-700 mb-3">
      Configuration Format
    </label>

    <div
      class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-3 gap-3"
      role="radiogroup"
      aria-label="Configuration format selection"
    >
      <div
        v-for="option in formatOptions"
        :key="option.value"
        class="relative cursor-pointer group"
        :class="{
          'cursor-not-allowed opacity-50': disabled
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
          class="relative p-4 border-2 rounded-lg transition-all duration-200 h-full flex flex-col"
          :class="{
            'border-blue-600 bg-blue-50 shadow-md': selectedValue === option.value && !disabled,
            'border-gray-300 bg-white hover:border-blue-300 hover:bg-blue-50 hover:shadow-sm': selectedValue !== option.value && !disabled,
            'border-gray-200 bg-gray-50': disabled,
            'focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2': !disabled
          }"
        >
          <!-- Recommended badge -->
          <div
            v-if="option.recommended && !disabled"
            class="absolute -top-2 -right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm"
          >
            Recommended
          </div>

          <!-- Selected indicator -->
          <div
            v-if="selectedValue === option.value"
            class="absolute top-3 right-3 w-5 h-5 bg-blue-600 rounded-full flex items-center justify-center"
          >
            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </div>

          <!-- Format icon and label -->
          <div class="flex items-center gap-3 mb-2">
            <span class="text-2xl" role="img" :aria-label="`${option.label} icon`">
              {{ option.icon }}
            </span>
            <h3 class="font-semibold text-gray-900 text-lg">
              {{ option.label }}
            </h3>
          </div>

          <!-- Description -->
          <p class="text-sm text-gray-600 flex-1">
            {{ option.description }}
          </p>

          <!-- Hover indicator -->
          <div
            v-if="!disabled"
            class="absolute inset-0 rounded-lg ring-2 ring-transparent group-hover:ring-blue-300 transition-all duration-200 pointer-events-none"
            :class="{
              'ring-blue-600': selectedValue === option.value
            }"
          />
        </div>
      </div>
    </div>
  </div>
</template>
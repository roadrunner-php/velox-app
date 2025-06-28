<template>
  <section class="py-20 relative overflow-hidden" :class="sectionClasses">
    <!-- Background Pattern -->
    <div v-if="showBackground" class="absolute inset-0 opacity-5">
      <div
        class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-blue-500/10 to-purple-500/10"
      ></div>
      <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
      <div
        class="absolute bottom-1/4 left-1/4 w-96 h-96 bg-purple-500/5 rounded-full blur-3xl"
      ></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
          {{ title }}
        </h2>
        <p v-if="subtitle" class="text-xl text-gray-300 max-w-3xl mx-auto">
          {{ subtitle }}
        </p>
      </div>

      <!-- Process Steps Grid -->
      <div class="grid md:grid-cols-3 gap-8 mt-16">
        <div v-for="(step, index) in steps" :key="step.id || index" class="text-center group">
          <!-- Step Number Badge -->
          <div
            class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg"
            :class="getStepClasses(index)"
          >
            <span class="text-2xl font-bold text-white">{{ index + 1 }}</span>
          </div>

          <!-- Step Content -->
          <h3 class="text-xl font-bold text-white mb-4">
            {{ step.title }}
          </h3>
          <p class="text-gray-300 leading-relaxed">
            {{ step.description }}
          </p>

          <!-- Additional step content -->
          <div v-if="step.features && step.features.length" class="mt-4">
            <ul class="text-sm text-gray-400 space-y-1">
              <li
                v-for="feature in step.features"
                :key="feature"
                class="flex items-center justify-center"
              >
                <span class="w-1 h-1 bg-gray-400 rounded-full mr-2"></span>
                {{ feature }}
              </li>
            </ul>
          </div>

          <!-- Step Action -->
          <div v-if="step.actionText && (step.actionHref || step.actionTo)" class="mt-6">
            <component
              :is="step.actionTo ? 'RouterLink' : 'a'"
              :href="step.actionHref"
              :to="step.actionTo"
              class="inline-flex items-center text-sm font-medium transition-colors duration-200 hover:underline"
              :class="getActionClasses(index)"
            >
              {{ step.actionText }}
              <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 7l5 5m0 0l-5 5m5-5H6"
                />
              </svg>
            </component>
          </div>
        </div>
      </div>

      <!-- Bottom CTA -->
      <div v-if="ctaText && (ctaHref || ctaTo)" class="text-center mt-16">
        <GradientButton :text="ctaText" :href="ctaHref" :to="ctaTo" variant="primary" size="lg" />
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import GradientButton from '../ui/GradientButton.vue'

interface ProcessStep {
  id?: string
  title: string
  description: string
  features?: string[]
  actionText?: string
  actionHref?: string
  actionTo?: string | object
}

interface Props {
  title?: string
  subtitle?: string
  steps?: ProcessStep[]
  showBackground?: boolean
  variant?: 'default' | 'dark' | 'blue'
  ctaText?: string
  ctaHref?: string
  ctaTo?: string | object
}

const props = withDefaults(defineProps<Props>(), {
  title: 'How Velox Works',
  steps: () => [
    {
      id: 'select',
      title: 'Select & Configure',
    },
    {
      id: 'generate',
      title: 'Generate Config',
    },
    {
      id: 'deploy',
      title: 'Deploy & Scale',
    },
  ],
  showBackground: true,
  variant: 'default',
})

const sectionClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gradient-to-b from-gray-900 to-black',
    dark: 'bg-gray-900',
    blue: 'bg-gradient-to-b from-blue-900 to-indigo-900',
  }

  return variantClasses[props.variant]
})

const stepColors = [
  {
    bg: 'bg-gradient-to-br from-blue-500 to-blue-600',
    shadow: 'shadow-blue-500/20',
    action: 'text-blue-400 hover:text-blue-300',
  },
  {
    bg: 'bg-gradient-to-br from-purple-500 to-purple-600',
    shadow: 'shadow-purple-500/20',
    action: 'text-purple-400 hover:text-purple-300',
  },
  {
    bg: 'bg-gradient-to-br from-cyan-500 to-cyan-600',
    shadow: 'shadow-cyan-500/20',
    action: 'text-cyan-400 hover:text-cyan-300',
  },
]

function getStepClasses(index: number) {
  const colorIndex = index % stepColors.length
  const colors = stepColors[colorIndex]
  return `${colors.bg} ${colors.shadow}`
}

function getActionClasses(index: number) {
  const colorIndex = index % stepColors.length
  const colors = stepColors[colorIndex]
  return colors.action
}
</script>

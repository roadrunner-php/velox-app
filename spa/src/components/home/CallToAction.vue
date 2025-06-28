<template>
  <section class="py-20 relative" :class="sectionClasses">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
        {{ title }}
      </h2>

      <p v-if="subtitle" class="text-xl text-gray-300 mb-8 leading-relaxed">
        {{ subtitle }}
      </p>

      <!-- Additional Content Slot -->
      <div v-if="$slots.content" class="mb-8">
        <slot name="content"></slot>
      </div>

      <!-- CTA Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
        <GradientButton
          v-for="cta in ctas"
          :key="cta.text"
          :text="cta.text"
          :icon="cta.icon"
          :variant="cta.variant || 'primary'"
          :size="cta.size || 'lg'"
          :href="cta.href"
          :to="cta.to"
          :loading="cta.loading"
          :disabled="cta.disabled"
        />
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import GradientButton from '../ui/GradientButton.vue'

interface CtaButton {
  text: string
  icon?: string
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost'
  size?: 'sm' | 'md' | 'lg'
  href?: string
  to?: string | object
  loading?: boolean
  disabled?: boolean
}

interface AdditionalLink {
  text: string
  href?: string
  to?: string | object
}

interface TrustSignal {
  text: string
  icon?: 'users' | 'shield' | 'star'
}

interface Props {
  title?: string
  subtitle?: string
  ctas?: CtaButton[]
  supportingText?: string
  additionalLinks?: AdditionalLink[]
  trustSignal?: TrustSignal
  variant?: 'default' | 'dark' | 'gradient'
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Ready to Build Something Amazing?',
  subtitle:
    'Join thousands of developers who have streamlined their RoadRunner deployments with Velox',
  ctas: () => [
    {
      text: '🔧 Custom Build',
      variant: 'primary',
      to: '/plugins',
    },
    {
      text: '⚡ Quick Start',
      variant: 'secondary',
      to: '/presets',
    },
  ],
  variant: 'default',
})

const sectionClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gradient-to-r from-gray-900 via-black to-gray-900 border-t border-gray-800/50',
    dark: 'bg-gray-900 border-t border-gray-800',
    gradient: 'bg-gradient-to-r from-blue-900 via-purple-900 to-indigo-900',
  }

  return variantClasses[props.variant]
})
</script>

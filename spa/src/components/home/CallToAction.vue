<template>
  <section 
    class="py-20 relative"
    :class="sectionClasses"
  >
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
  subtitle: 'Join thousands of developers who have streamlined their RoadRunner deployments with Velox',
  ctas: () => [
    {
      text: '🔧 Custom Build',
      variant: 'primary',
      to: '/plugins'
    },
    {
      text: '⚡ Quick Start',
      variant: 'secondary',
      to: '/presets'
    }
  ],
  variant: 'default'
})

const sectionClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gradient-to-r from-gray-900 via-black to-gray-900 border-t border-gray-800/50',
    dark: 'bg-gray-900 border-t border-gray-800',
    gradient: 'bg-gradient-to-r from-blue-900 via-purple-900 to-indigo-900'
  }

  return variantClasses[props.variant]
})
</script>

<style scoped>
/* Section spacing */
.py-20 {
  padding-top: 5rem;
  padding-bottom: 5rem;
}

/* Enhanced spacing */
.mb-6 {
  margin-bottom: 1.5rem;
}

.mb-8 {
  margin-bottom: 2rem;
}

.mt-8 {
  margin-top: 2rem;
}

.mt-12 {
  margin-top: 3rem;
}

/* Button group styling */
.flex.gap-4 {
  gap: 1rem;
}

@media (max-width: 640px) {
  .flex.gap-4 {
    gap: 0.75rem;
    flex-direction: column;
    align-items: center;
  }
}

/* Link styling */
.text-gray-400 {
  transition-property: color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

.hover\:text-white:hover {
  color: #ffffff;
}

.hover\:underline:hover {
  text-decoration: underline;
}

/* Trust signal styling */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .py-20 {
    padding-top: 3rem;
    padding-bottom: 3rem;
  }
  
  .text-3xl {
    font-size: 1.875rem;
  }
  
  .text-4xl {
    font-size: 2.25rem;
  }
  
  .px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  .gap-6 {
    gap: 1rem;
  }
  
  .flex-wrap {
    flex-direction: column;
    align-items: center;
  }
}

/* Enhanced visual hierarchy */
.max-w-4xl {
  max-width: 56rem;
}

.max-w-2xl {
  max-width: 42rem;
}

/* Focus states for accessibility */
a:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
  border-radius: 0.25rem;
}

/* Section entrance animation */
.section-animate {
  animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Trust signal animation */
.trust-signal-animate {
  animation: fadeIn 1s ease-out 0.6s both;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Enhanced border styling */
.border-t {
  border-top-width: 1px;
}

/* Improved text contrast */
.text-white {
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .section-animate,
  .trust-signal-animate {
    animation: none;
  }
  
  .text-gray-400 {
    transition: none;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .text-gray-300 {
    color: #ffffff;
  }
  
  .text-gray-400 {
    color: #e5e7eb;
  }
  
  .border-gray-800\/50 {
    border-color: #ffffff;
  }
}
</style>

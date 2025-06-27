<template>
  <section 
    class="py-20 relative overflow-hidden"
    :class="sectionClasses"
  >
    <!-- Background Pattern -->
    <div v-if="showBackground" class="absolute inset-0 opacity-10">
      <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-blue-400/10 to-purple-400/10"></div>
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

      <!-- Benefits Grid -->
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <BenefitCard
          v-for="benefit in benefits"
          :key="benefit.id || benefit.title"
          :title="benefit.title"
          :description="benefit.description"
          :icon-type="benefit.iconType"
          :variant="benefit.variant"
          :size="cardSize"
          :action-text="benefit.actionText"
          :action-href="benefit.actionHref"
          :action-to="benefit.actionTo"
          :badge="benefit.badge"
        >
          <!-- Custom icon slot if provided -->
          <template v-if="benefit.customIcon" #icon>
            <component :is="benefit.customIcon" />
          </template>

          <!-- Custom content slot if provided -->
          <template v-if="benefit.content" #content>
            <div v-html="benefit.content"></div>
          </template>
        </BenefitCard>
      </div>

      <!-- Additional Content Slot -->
      <div v-if="$slots.content" class="mt-16">
        <slot name="content"></slot>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BenefitCard from '../ui/BenefitCard.vue'
import GradientButton from '../ui/GradientButton.vue'

interface Benefit {
  id?: string
  title: string
  description: string
  iconType?: 'speed' | 'security' | 'optimization' | 'experience' | 'enterprise' | 'format'
  variant?: 'yellow' | 'green' | 'purple' | 'blue' | 'pink' | 'indigo'
  actionText?: string
  actionHref?: string
  actionTo?: string | object
  badge?: string
  customIcon?: any
  content?: string
}

interface BottomCta {
  text: string
  variant?: 'primary' | 'secondary' | 'outline'
  size?: 'sm' | 'md' | 'lg'
  href?: string
  to?: string | object
  icon?: string
}

interface Props {
  title?: string
  subtitle?: string
  benefits?: Benefit[]
  cardSize?: 'sm' | 'md' | 'lg'
  showBackground?: boolean
  showBottomCta?: boolean
  bottomCtaText?: string
  bottomCtas?: BottomCta[]
  variant?: 'default' | 'dark' | 'gradient'
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Why Choose Velox?',
  benefits: () => [
    {
      id: 'speed',
      title: 'Lightning Fast Setup',
      description: 'Deploy production-ready RoadRunner servers in minutes, not hours. Automated dependency resolution eliminates configuration headaches.',
      iconType: 'speed',
      variant: 'yellow'
    },
    {
      id: 'security',
      title: 'Zero Conflicts',
      description: 'Intelligent conflict detection prevents incompatible plugin combinations. Our validation engine ensures stable, reliable configurations every time.',
      iconType: 'security',
      variant: 'green'
    },
    {
      id: 'optimization',
      title: 'Optimized Binaries',
      description: 'Generate lean, purpose-built binaries containing only what you need. Reduce memory footprint and improve startup performance significantly.',
      iconType: 'optimization',
      variant: 'purple'
    },
    {
      id: 'enterprise',
      title: 'Enterprise Ready',
      description: 'Battle-tested presets used in production by teams worldwide. Includes monitoring, security, and scalability features out of the box.',
      iconType: 'enterprise',
      variant: 'blue'
    },
    {
      id: 'experience',
      title: 'Developer Experience',
      description: 'Intuitive interface designed for developers, by developers. Clear documentation, helpful tooltips, and smart defaults make configuration effortless.',
      iconType: 'experience',
      variant: 'pink'
    },
    {
      id: 'format',
      title: 'Multi-Format Output',
      description: 'Generate configurations in TOML, JSON, or complete Dockerfiles. Perfect integration with your existing CI/CD pipelines and deployment workflows.',
      iconType: 'format',
      variant: 'indigo'
    }
  ],
  cardSize: 'md',
  showBackground: true,
  showBottomCta: true,
  bottomCtaText: 'Ready to experience the future of RoadRunner configuration?',
  bottomCtas: () => [
    {
      text: '🔧 Start Building',
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
    default: 'bg-gradient-to-br from-black via-gray-900 to-black',
    dark: 'bg-gray-900',
    gradient: 'bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900'
  }

  return variantClasses[props.variant]
})
</script>

<style scoped>
/* Grid layout responsive improvements */
.grid {
  gap: 2rem;
}

@media (min-width: 768px) {
  .md\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1024px) {
  .lg\:grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

/* Section spacing */
.py-20 {
  padding-top: 5rem;
  padding-bottom: 5rem;
}

.mb-16 {
  margin-bottom: 4rem;
}

.mt-16 {
  margin-top: 4rem;
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
  
  .gap-8 {
    gap: 1.5rem;
  }
  
  .mb-16 {
    margin-bottom: 2rem;
  }
  
  .mt-16 {
    margin-top: 2rem;
  }
}

/* Enhanced focus management */
.grid:focus-within {
  outline: 2px solid #3b82f6;
  outline-offset: 4px;
  border-radius: 1rem;
}

/* Section entrance animation */
.section-animate {
  animation: staggerFadeIn 0.8s ease-out;
}

@keyframes staggerFadeIn {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Stagger child animations */
.grid > *:nth-child(1) { animation-delay: 0.1s; }
.grid > *:nth-child(2) { animation-delay: 0.2s; }
.grid > *:nth-child(3) { animation-delay: 0.3s; }
.grid > *:nth-child(4) { animation-delay: 0.4s; }
.grid > *:nth-child(5) { animation-delay: 0.5s; }
.grid > *:nth-child(6) { animation-delay: 0.6s; }

/* Enhanced visual hierarchy */
.text-gray-300 {
  line-height: 1.6;
}

/* Button group improvements */
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

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
  .section-animate,
  .grid > * {
    animation: none;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .text-gray-300 {
    color: #ffffff;
  }
  
  .bg-gradient-to-br {
    background: #000000;
  }
}
</style>

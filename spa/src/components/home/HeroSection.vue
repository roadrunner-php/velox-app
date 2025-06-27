<template>
  <section 
    class="relative overflow-hidden"
    :class="sectionClasses"
  >
    <!-- Background -->
    <AnimatedBackground 
      v-if="showBackground"
      :particle-count="30"
      :pattern-opacity="0.1"
      :animations-disabled="reducedMotion"
    />

    <!-- Content Container -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
      <div class="text-center">
        <!-- Main Headline with Animation -->
        <h1 
          class="text-4xl sm:text-6xl lg:text-7xl font-bold text-white mb-6"
          :class="headlineAnimation"
        >
          {{ mainTitle }}
          <span
            class="bg-gradient-to-r from-blue-400 via-purple-400 to-cyan-400 bg-clip-text text-transparent animate-gradient-x"
          >
            {{ highlightTitle }}
          </span>
        </h1>

        <!-- Subtitle -->
        <p
          class="text-xl sm:text-2xl text-gray-300 my-16 max-w-4xl mx-auto leading-relaxed"
          :class="subtitleAnimation"
        >
          {{ subtitle }}
          <span v-if="subtitleHighlight" class="text-cyan-400 font-semibold">
            {{ subtitleHighlight }}
          </span>
        </p>

        <!-- CTA Buttons -->
        <div
          class="flex flex-col sm:flex-row gap-4 justify-center items-center"
          :class="ctaAnimation"
        >
          <GradientButton
            :text="primaryCta.text"
            :icon="primaryCta.icon"
            variant="primary"
            size="lg"
            :to="primaryCta.to"
            :href="primaryCta.href"
          />

          <GradientButton
            :text="secondaryCta.text"
            :icon="secondaryCta.icon"
            variant="outline"
            size="lg"
            :to="secondaryCta.to"
            :href="secondaryCta.href"
          />
        </div>

        <!-- Key Benefits Pills -->
        <div
          class="flex flex-wrap justify-center gap-3 mt-12"
          :class="benefitsAnimation"
        >
          <span
            v-for="benefit in benefits"
            :key="benefit"
            class="px-4 py-2 bg-gray-800/60 backdrop-blur-sm text-gray-300 rounded-full text-sm font-medium border border-gray-700/50 hover:border-gray-600/50 transition-colors"
          >
            {{ benefit }}
          </span>
        </div>
      </div>
    </div>

    <!-- Scroll Indicator -->
    <div 
      v-if="showScrollIndicator"
      class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce"
    >
      <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M19 14l-7 7m0 0l-7-7m7 7V3"
        />
      </svg>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import AnimatedBackground from './backgrounds/AnimatedBackground.vue'
import GradientButton from '../ui/GradientButton.vue'

interface CtaButton {
  text: string
  icon?: string
  to?: string
  href?: string
}

interface Props {
  mainTitle?: string
  highlightTitle?: string
  subtitle?: string
  subtitleHighlight?: string
  primaryCta?: CtaButton
  secondaryCta?: CtaButton
  benefits?: string[]
  showBackground?: boolean
  showScrollIndicator?: boolean
  variant?: 'default' | 'dark' | 'gradient'
  reducedMotion?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  mainTitle: 'Build Your Perfect',
  highlightTitle: 'RoadRunner Server',
  subtitle: 'Create optimized, lightweight server configurations with our intelligent plugin system and proven presets.',
  subtitleHighlight: 'Deploy faster, run leaner, scale better.',
  primaryCta: () => ({
    text: '🚀 Start Building',
    to: '/plugins'
  }),
  secondaryCta: () => ({
    text: '⚡ Quick Presets',
    to: '/presets'
  }),
  benefits: () => [
    '✅ Zero Configuration Conflicts',
    '⚡ Instant Deployment',
    '🔧 Auto Dependency Resolution',
    '📦 Optimized Binaries'
  ],
  showBackground: true,
  showScrollIndicator: true,
  variant: 'default',
  reducedMotion: false
})

const sectionClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900',
    dark: 'bg-gradient-to-br from-gray-900 via-black to-gray-900',
    gradient: 'bg-gradient-to-br from-blue-900 via-purple-900 to-indigo-900'
  }

  return variantClasses[props.variant]
})

const headlineAnimation = computed(() => 
  props.reducedMotion ? '' : 'animate-fade-in-up'
)

const subtitleAnimation = computed(() => 
  props.reducedMotion ? '' : 'animate-fade-in-up animation-delay-200'
)

const ctaAnimation = computed(() => 
  props.reducedMotion ? '' : 'animate-fade-in-up animation-delay-400'
)

const benefitsAnimation = computed(() => 
  props.reducedMotion ? '' : 'animate-fade-in-up animation-delay-600'
)
</script>

<style scoped>
/* Custom animations */
@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes gradient-x {
  0%, 100% {
    background-size: 200% 200%;
    background-position: left center;
  }
  50% {
    background-size: 200% 200%;
    background-position: right center;
  }
}

.animate-fade-in-up {
  animation: fade-in-up 0.8s ease-out forwards;
}

.animation-delay-200 {
  animation-delay: 0.2s;
}

.animation-delay-400 {
  animation-delay: 0.4s;
}

.animation-delay-600 {
  animation-delay: 0.6s;
}

.animate-gradient-x {
  animation: gradient-x 3s ease infinite;
  background-size: 200% 200%;
}

/* Text gradient */
.bg-clip-text {
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .text-4xl {
    font-size: 2.5rem;
  }

  .text-6xl {
    font-size: 3.5rem;
  }

  .text-7xl {
    font-size: 4rem;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .animate-fade-in-up,
  .animate-gradient-x,
  .animate-bounce {
    animation: none;
  }
}

/* Enhanced hover effects for benefits */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>

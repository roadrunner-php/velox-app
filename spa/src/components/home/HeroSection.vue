<template>
  <section class="hero-section" :class="sectionClasses">
    <!-- Background -->
    <AnimatedBackground
      v-if="showBackground"
      :particle-count="30"
      :pattern-opacity="0.1"
      :animations-disabled="reducedMotion"
    />

    <!-- Content Container -->
    <div class="hero-container">
      <div class="hero-content">
        <!-- Main Headline with Animation -->
        <h1
          class="hero-title"
          :class="headlineAnimation"
        >
          {{ mainTitle }}
          <span class="hero-title-highlight">
            {{ highlightTitle }}
          </span>
        </h1>

        <!-- Subtitle -->
        <p
          class="hero-subtitle"
          :class="subtitleAnimation"
        >
          {{ subtitle }}
          <span v-if="subtitleHighlight" class="hero-subtitle-highlight">
            {{ subtitleHighlight }}
          </span>
        </p>

        <!-- CTA Buttons -->
        <div
          class="hero-cta-buttons"
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

        <!-- Getting Started Link -->
        <div class="hero-getting-started" :class="benefitsAnimation">
          <RouterLink
            to="/introduction"
            class="hero-getting-started-link"
          >
            📖 New to Velox? Check out our step-by-step guide
            <svg class="hero-getting-started-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </RouterLink>
        </div>

        <!-- Key Benefits Pills -->
        <div class="hero-benefits" :class="benefitsAnimation">
          <span
            v-for="benefit in benefits"
            :key="benefit"
            class="hero-benefit-pill"
          >
            {{ benefit }}
          </span>
        </div>
      </div>
    </div>

    <!-- Scroll Indicator -->
    <div
      v-if="showScrollIndicator"
      class="hero-scroll-indicator"
    >
      <svg class="hero-scroll-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
  subtitle:
    'Create optimized, lightweight server configurations with our intelligent plugin system and proven presets.',
  subtitleHighlight: 'Deploy faster, run leaner, scale better.',
  primaryCta: () => ({
    text: '🚀 Start Building',
    to: '/plugins',
  }),
  secondaryCta: () => ({
    text: '⚡ Quick Presets',
    to: '/presets',
  }),
  benefits: () => [
    '✅ Zero Configuration Conflicts',
    '⚡ Instant Deployment',
    '🔧 Auto Dependency Resolution',
    '📦 Optimized Binaries',
  ],
  showBackground: true,
  showScrollIndicator: true,
  variant: 'default',
  reducedMotion: false,
})

const sectionClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900',
    dark: 'bg-gradient-to-br from-gray-900 via-black to-gray-900',
    gradient: 'bg-gradient-to-br from-blue-900 via-purple-900 to-indigo-900',
  }

  return variantClasses[props.variant]
})

const headlineAnimation = computed(() => (props.reducedMotion ? '' : 'animate-fade-in-up'))

const subtitleAnimation = computed(() =>
  props.reducedMotion ? '' : 'animate-fade-in-up animation-delay-200',
)

const ctaAnimation = computed(() =>
  props.reducedMotion ? '' : 'animate-fade-in-up animation-delay-400',
)

const benefitsAnimation = computed(() =>
  props.reducedMotion ? '' : 'animate-fade-in-up animation-delay-600',
)
</script>

<style scoped>
.hero-section {
  @apply relative overflow-hidden;
}

.hero-container {
  @apply relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32;
}

.hero-content {
  @apply text-center;
}

.hero-title {
  @apply text-4xl sm:text-6xl lg:text-7xl font-bold text-white mb-6;
}

.hero-title-highlight {
  @apply bg-gradient-to-r from-blue-400 via-purple-400 to-cyan-400 bg-clip-text text-transparent;
}

.hero-subtitle {
  @apply text-xl sm:text-2xl text-gray-300 my-16 max-w-4xl mx-auto leading-relaxed;
}

.hero-subtitle-highlight {
  @apply text-cyan-400 font-semibold;
}

.hero-cta-buttons {
  @apply flex flex-col sm:flex-row gap-4 justify-center items-center mb-8;
}

.hero-getting-started {
  @apply mb-8;
}

.hero-getting-started-link {
  @apply inline-flex items-center gap-2 px-4 py-2 text-cyan-400 hover:text-cyan-300 font-medium transition-colors duration-200 rounded-lg hover:bg-gray-800/30 border border-cyan-500/20 hover:border-cyan-400/40;
}

.hero-getting-started-icon {
  @apply w-4 h-4;
}

.hero-benefits {
  @apply flex flex-wrap justify-center gap-3;
}

.hero-benefit-pill {
  @apply px-4 py-2 bg-gray-800/60 backdrop-blur-sm text-gray-300 rounded-full text-sm font-medium border border-gray-700/50 hover:border-gray-600/50 transition-colors;
}

.hero-scroll-indicator {
  @apply absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce;
}

.hero-scroll-icon {
  @apply w-6 h-6 text-gray-400;
}
</style>

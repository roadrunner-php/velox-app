<template>
  <section 
    class="feature-comparison-section"
    :class="sectionClasses"
  >
    <div class="feature-comparison-container">
      <!-- Section Header -->
      <div class="feature-comparison-header">
        <h2 class="feature-comparison-title">
          {{ title }}
        </h2>
        <p v-if="subtitle" class="feature-comparison-subtitle">
          {{ subtitle }}
        </p>
      </div>

      <!-- Feature Comparison Grid -->
      <div class="feature-comparison-grid">
        <!-- Plugins Card -->
        <FeatureCard
          title="Custom Plugins"
          description="Hand-pick exactly what you need. Build lean, optimized servers with precise plugin selection and automatic dependency resolution."
          variant="blue"
          size="lg"
          :link-text="pluginsCard.linkText"
          :link-href="pluginsCard.linkHref"
          :link-to="pluginsCard.linkTo"
          badge="Granular Control"
        >
          <template #icon>
            <svg class="feature-card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 11.172V5l-1-1z"
              />
            </svg>
          </template>

          <template #content>
            <ul class="feature-list">
              <li 
                v-for="feature in pluginsFeatures"
                :key="feature"
                class="feature-item feature-item--blue"
              >
                <span class="feature-bullet feature-bullet--blue"></span>
                {{ feature }}
              </li>
            </ul>
          </template>
        </FeatureCard>

        <!-- Presets Card -->
        <FeatureCard
          title="Quick Presets"
          description="Get started instantly with battle-tested configurations. Perfect for common use cases with enterprise-grade reliability."
          variant="purple"
          size="lg"
          :link-text="presetsCard.linkText"
          :link-href="presetsCard.linkHref"
          :link-to="presetsCard.linkTo"
          badge="Instant Deploy"
        >
          <template #icon>
            <svg class="feature-card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z"
              />
            </svg>
          </template>

          <template #content>
            <ul class="feature-list">
              <li 
                v-for="feature in presetsFeatures"
                :key="feature"
                class="feature-item feature-item--purple"
              >
                <span class="feature-bullet feature-bullet--purple"></span>
                {{ feature }}
              </li>
            </ul>
          </template>
        </FeatureCard>
      </div>

      <!-- Bottom CTA -->
      <div v-if="showBottomCta" class="feature-comparison-cta">
        <p class="feature-comparison-cta-text">
          {{ bottomCtaText }}
        </p>
        <div class="feature-comparison-cta-buttons">
          <GradientButton
            text="Try Plugins"
            variant="primary"
            :to="'/plugins'"
          />
          <GradientButton
            text="Browse Presets"
            variant="secondary"
            :to="'/presets'"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import FeatureCard from '../ui/FeatureCard.vue'
import GradientButton from '../ui/GradientButton.vue'

interface CardConfig {
  linkText?: string
  linkHref?: string
  linkTo?: string | object
}

interface Props {
  title?: string
  subtitle?: string
  pluginsCard?: CardConfig
  presetsCard?: CardConfig
  pluginsFeatures?: string[]
  presetsFeatures?: string[]
  showBottomCta?: boolean
  bottomCtaText?: string
  variant?: 'default' | 'dark' | 'gradient'
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Choose Your Approach',
  subtitle: 'Whether you prefer granular control or quick deployment, we\'ve got you covered',
  pluginsCard: () => ({
    linkText: 'Explore Plugins',
    linkTo: '/plugins'
  }),
  presetsCard: () => ({
    linkText: 'Browse Presets',
    linkTo: '/presets'
  }),
  pluginsFeatures: () => [
    'Granular control over features',
    'Minimal resource footprint',
    'Smart dependency management',
    'Perfect for custom architectures'
  ],
  presetsFeatures: () => [
    'Production-ready templates',
    'Zero configuration needed',
    'Best practices included',
    'Instant deployment ready'
  ],
  showBottomCta: true,
  bottomCtaText: 'Not sure which approach to take? Try both and see what works best for your project.',
  variant: 'default'
})

const sectionClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gradient-to-b from-gray-900 to-black',
    dark: 'bg-black',
    gradient: 'bg-gradient-to-b from-blue-900 to-purple-900'
  }

  return variantClasses[props.variant]
})
</script>

<style scoped>
.feature-comparison-section {
  @apply py-20 relative overflow-hidden;
}

.feature-comparison-container {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8;
}

.feature-comparison-header {
  @apply text-center mb-16;
}

.feature-comparison-title {
  @apply text-3xl sm:text-4xl font-bold text-white mb-4;
}

.feature-comparison-subtitle {
  @apply text-xl text-gray-300 max-w-3xl mx-auto;
}

.feature-comparison-grid {
  @apply grid lg:grid-cols-2 gap-8 max-w-6xl mx-auto;
}

.feature-card-icon {
  @apply w-8 h-8 text-white;
}

.feature-list {
  @apply space-y-3 mb-8 text-left;
}

.feature-item {
  @apply flex items-center text-gray-300;
}

.feature-bullet {
  @apply w-2 h-2 rounded-full mr-3 flex-shrink-0;
}

.feature-bullet--blue {
  @apply bg-blue-400;
}

.feature-bullet--purple {
  @apply bg-purple-400;
}

.feature-comparison-cta {
  @apply text-center mt-16;
}

.feature-comparison-cta-text {
  @apply text-gray-300 mb-6;
}

.feature-comparison-cta-buttons {
  @apply flex flex-col sm:flex-row gap-4 justify-center;
}
</style>

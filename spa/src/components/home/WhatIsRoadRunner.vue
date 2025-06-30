<template>
  <section class="what-is-roadrunner-section" :class="sectionClasses">
    <!-- Background Pattern -->
    <div v-if="showBackground" class="roadrunner-background">
      <div class="roadrunner-gradient"></div>
      <div class="roadrunner-blob roadrunner-blob--blue"></div>
      <div class="roadrunner-blob roadrunner-blob--cyan"></div>
    </div>

    <div class="roadrunner-container">
      <!-- Section Header -->
      <div class="roadrunner-header">
        <h2 class="roadrunner-title">
          {{ title }}
        </h2>
        <p v-if="subtitle" class="roadrunner-subtitle">
          {{ subtitle }}
        </p>
      </div>

      <!-- Main Content Grid -->
      <div class="roadrunner-content-grid">
        <!-- Text Content -->
        <div class="roadrunner-text-content">
          <div class="roadrunner-description">
            <p class="roadrunner-description-text">
              {{ description }}
            </p>
          </div>

          <!-- Key Features -->
          <div class="roadrunner-features">
            <h3 class="roadrunner-features-title">Why RoadRunner?</h3>
            <div class="roadrunner-features-grid">
              <div
                v-for="feature in features"
                :key="feature.id"
                class="roadrunner-feature"
                :class="{ 'roadrunner-feature--highlight': feature.highlight }"
              >
                <div class="roadrunner-feature-icon">
                  <component :is="getFeatureIcon(feature.icon)" />
                </div>
                <div class="roadrunner-feature-content">
                  <h4 class="roadrunner-feature-title">{{ feature.title }}</h4>
                  <p class="roadrunner-feature-description">{{ feature.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Visual Diagram -->
        <div class="roadrunner-visual">
          <div class="roadrunner-diagram">
            <!-- Architecture Diagram -->
            <div class="architecture-diagram">
              <div class="diagram-layer">
                <div class="diagram-box diagram-box--client">
                  <span class="diagram-icon">🌐</span>
                  <span class="diagram-label">HTTP Requests</span>
                </div>
                <div class="diagram-arrow">↓</div>
              </div>

              <div class="diagram-layer">
                <div class="diagram-box diagram-box--roadrunner">
                  <span class="diagram-icon">⚡</span>
                  <span class="diagram-label">RoadRunner Server</span>
                  <span class="diagram-sublabel">Go-powered HTTP server</span>
                </div>
                <div class="diagram-arrow">↓</div>
              </div>

              <div class="diagram-layer">
                <div class="diagram-box diagram-box--php">
                  <span class="diagram-icon">🐘</span>
                  <span class="diagram-label">PHP Workers</span>
                  <span class="diagram-sublabel">Persistent processes</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom CTA -->
      <div v-if="showCta" class="roadrunner-cta">
        <div class="roadrunner-cta-content">
          <h3 class="roadrunner-cta-title">Want to learn more?</h3>
        </div>
        <div class="roadrunner-cta-buttons">
          <GradientButton
            text="📚 Read the Docs"
            href="https://roadrunner.dev/docs"
            variant="secondary"
            size="md"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import GradientButton from '../ui/GradientButton.vue'

interface Feature {
  id: string
  title: string
  description: string
  icon: string
  highlight?: boolean
}

interface Props {
  title?: string
  subtitle?: string
  description?: string
  features?: Feature[]
  showBackground?: boolean
  showCta?: boolean
  variant?: 'default' | 'dark' | 'gradient'
}

const props = withDefaults(defineProps<Props>(), {
  title: 'What is RoadRunner?',
  subtitle: 'The high-performance PHP application server that changes everything',
  description:
    'RoadRunner is a modern application server written in Go that serves PHP applications with unprecedented performance. Unlike traditional PHP-FPM or Apache setups, RoadRunner maintains persistent worker processes, eliminating the overhead of bootstrapping your application on every request.',
  features: () => [
    {
      id: 'performance',
      title: 'Lightning Performance',
      description: 'Up to 10x faster than traditional setups with persistent workers',
      icon: 'lightning',
      highlight: true,
    },
    {
      id: 'plugins',
      title: 'Plugin Ecosystem',
      description: 'Extensible architecture with HTTP, gRPC, Queue, and more',
      icon: 'plugins',
    },
    {
      id: 'microservices',
      title: 'Microservices Ready',
      description: 'Built-in support for modern distributed architectures',
      icon: 'microservices',
    },
  ],
  showBackground: true,
  showCta: true,
  variant: 'default',
})

const sectionClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gradient-to-br from-slate-900 via-blue-900/20 to-slate-900',
    dark: 'bg-gray-900',
    gradient: 'bg-gradient-to-br from-blue-900 via-cyan-900/30 to-indigo-900',
  }

  return variantClasses[props.variant]
})

function getFeatureIcon(icon: string) {
  const iconMap = {
    lightning: () => '⚡',
    memory: () => '🧠',
    plugins: () => '🔧',
    microservices: () => '🏗️',
  }

  return iconMap[icon as keyof typeof iconMap] || (() => '🔥')
}
</script>

<style scoped>
.what-is-roadrunner-section {
  @apply py-20 relative overflow-hidden;
}

.roadrunner-background {
  @apply absolute inset-0 opacity-10;
}

.roadrunner-gradient {
  @apply absolute top-0 left-0 w-full h-full bg-gradient-to-r from-blue-400/20 to-cyan-400/20;
}

.roadrunner-blob {
  @apply absolute w-96 h-96 rounded-full blur-3xl;
}

.roadrunner-blob--blue {
  @apply top-1/4 left-1/4 bg-blue-500/5;
}

.roadrunner-blob--cyan {
  @apply bottom-1/4 right-1/4 bg-cyan-500/5;
}

.roadrunner-container {
  @apply relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8;
}

.roadrunner-header {
  @apply text-center mb-16;
}

.roadrunner-title {
  @apply text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4;
}

.roadrunner-subtitle {
  @apply text-xl text-cyan-300 max-w-3xl mx-auto font-medium;
}

.roadrunner-content-grid {
  @apply grid lg:grid-cols-2 gap-16 items-start;
}

.roadrunner-text-content {
  @apply space-y-8;
}

.roadrunner-description-text {
  @apply text-lg text-gray-300 leading-relaxed;
}

.roadrunner-features-title {
  @apply text-2xl font-bold text-white mb-6;
}

.roadrunner-features-grid {
  @apply space-y-6;
}

.roadrunner-feature {
  @apply flex items-start space-x-4 p-4 rounded-lg bg-gray-800/30 backdrop-blur-sm border border-gray-700/50 hover:border-gray-600/50 transition-colors duration-200;
}

.roadrunner-feature--highlight {
  @apply bg-blue-500/10 border-blue-500/30;
}

.roadrunner-feature-icon {
  @apply text-2xl flex-shrink-0;
}

.roadrunner-feature-title {
  @apply text-lg font-semibold text-white mb-1;
}

.roadrunner-feature-description {
  @apply text-gray-300 text-sm;
}

.roadrunner-stats {
  @apply flex flex-wrap gap-6 pt-6 border-t border-gray-700/50;
}

.roadrunner-stat {
  @apply text-center;
}

.roadrunner-stat-value {
  @apply block text-2xl font-bold text-cyan-400;
}

.roadrunner-stat-label {
  @apply text-sm text-gray-400;
}

.roadrunner-visual {
  @apply lg:pl-8;
}

.roadrunner-diagram {
  @apply space-y-8;
}

.architecture-diagram {
  @apply bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-700/50;
}

.diagram-layer {
  @apply flex flex-col items-center space-y-4 mb-6 last:mb-0;
}

.diagram-box {
  @apply px-6 py-4 rounded-xl text-center min-w-[200px] border-2 transition-transform duration-200 hover:scale-105;
}

.diagram-box--client {
  @apply bg-green-500/10 border-green-500/30 text-green-300;
}

.diagram-box--roadrunner {
  @apply bg-blue-500/10 border-blue-500/30 text-blue-300;
}

.diagram-box--php {
  @apply bg-purple-500/10 border-purple-500/30 text-purple-300;
}

.diagram-icon {
  @apply text-2xl block mb-2;
}

.diagram-label {
  @apply font-semibold block;
}

.diagram-sublabel {
  @apply text-sm opacity-80;
}

.diagram-arrow {
  @apply text-2xl text-gray-400;
}

.roadrunner-benefits-callout {
  @apply bg-gradient-to-br from-cyan-500/10 to-blue-500/10 rounded-2xl p-6 border border-cyan-500/20;
}

.benefits-callout-title {
  @apply text-lg font-semibold text-cyan-300 mb-4;
}

.benefits-callout-list {
  @apply space-y-2;
}

.benefits-callout-item {
  @apply flex items-center text-gray-300 text-sm;
}

.benefits-callout-bullet {
  @apply w-2 h-2 bg-cyan-400 rounded-full mr-3 flex-shrink-0;
}

.roadrunner-cta {
  @apply text-center mt-16 pt-12 border-t border-gray-700/50;
}

.roadrunner-cta-content {
  @apply mb-8;
}

.roadrunner-cta-title {
  @apply text-2xl font-bold text-white mb-4;
}

.roadrunner-cta-subtitle {
  @apply text-gray-300 max-w-2xl mx-auto;
}

.roadrunner-cta-buttons {
  @apply flex flex-col sm:flex-row gap-4 justify-center items-center;
}
</style>

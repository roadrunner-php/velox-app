<template>
  <section class="use-cases-section" :class="sectionClasses">
    <!-- Background Pattern -->
    <div v-if="showBackground" class="use-cases-background">
      <div class="use-cases-gradient"></div>
    </div>

    <div class="use-cases-container">
      <!-- Section Header -->
      <div class="use-cases-header">
        <h2 class="use-cases-title">
          {{ title }}
        </h2>
        <p v-if="subtitle" class="use-cases-subtitle">
          {{ subtitle }}
        </p>
      </div>

      <!-- Use Cases Grid -->
      <div class="use-cases-grid">
        <!-- TOML Configuration Use Case -->
        <UseCaseCard
          :use-case="tomlUseCase"
          class="use-case-card--toml"
        />

        <!-- Docker Use Case -->
        <UseCaseCard
          :use-case="dockerUseCase"
          class="use-case-card--docker"
        />
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import UseCaseCard from '../ui/UseCaseCard.vue'
import GradientButton from '../ui/GradientButton.vue'

interface UseCase {
  id: string
  title: string
  description: string
  icon: string
  codePreview: string
  actionText: string
  actionTo: string
  variant: 'toml' | 'docker'
  benefits: string[]
  workflow: string[]
}

interface Props {
  title?: string
  subtitle?: string
  showBackground?: boolean
  variant?: 'default' | 'dark' | 'gradient'
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Deployment Use Cases',
  subtitle: 'Generate the exact configuration format your workflow needs',
  showBackground: true,
  variant: 'default',
})

const sectionClasses = computed(() => {
  const variantClasses = {
    default: 'bg-gradient-to-b from-black via-gray-900 to-black',
    dark: 'bg-gray-900',
    gradient: 'bg-gradient-to-b from-purple-900 via-blue-900 to-indigo-900',
  }

  return variantClasses[props.variant]
})

const tomlUseCase: UseCase = {
  id: 'toml-config',
  title: 'Generate TOML Config',
  description: 'Perfect for local development and traditional server deployments. Get a clean, readable configuration file that works with any RoadRunner installation.',
  icon: '📄',
  variant: 'toml',
  actionText: 'Generate TOML Config',
  actionTo: '/plugins?format=toml',
  codePreview: `# Generated RoadRunner Configuration
version = "2023.3"

[server]
command = "php worker.php"
pool.num_workers = 4

[http]
address = "0.0.0.0:8080"
middleware = ["gzip", "headers"]

[logs]
mode = "development"
level = "debug"

[metrics]
address = "127.0.0.1:2112"`,
  workflow: [
    'Select plugins in Velox',
    'Configure settings',
    'Download .rr.yaml file',
    'Run with RoadRunner binary'
  ]
}

const dockerUseCase: UseCase = {
  id: 'dockerfile',
  title: 'Generate Dockerfile',
  description: 'Ideal for CI/CD pipelines and containerized deployments. Get a complete Dockerfile that builds your custom RoadRunner binary with selected plugins.',
  icon: '🐳',
  variant: 'docker',
  actionText: 'Generate Dockerfile',
  actionTo: '/plugins?format=dockerfile',
  codePreview: `# Generated Dockerfile
FROM ghcr.io/roadrunner-server/velox:latest as velox

ENV CGO_ENABLED=0

# Build custom RoadRunner binary
RUN vx build -c /config.toml -o ./rr

FROM php:8.2-cli
COPY --from=velox /usr/local/bin/rr /usr/local/bin/rr
COPY --from=velox /config.yaml ./
COPY . /app

WORKDIR /app
EXPOSE 8080

CMD ["./rr", "serve"]`,
  workflow: [
    'Select plugins in Velox',
    'Choose Docker output',
    'Download Dockerfile',
    'Build and deploy container'
  ]
}
</script>

<style scoped>
.use-cases-section {
  @apply py-20 relative overflow-hidden;
}

.use-cases-background {
  @apply absolute inset-0 opacity-10;
}

.use-cases-gradient {
  @apply absolute top-0 left-0 w-full h-full bg-gradient-to-br from-orange-500/10 via-purple-500/10 to-blue-500/10;
}

.use-cases-container {
  @apply relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8;
}

.use-cases-header {
  @apply text-center mb-16;
}

.use-cases-title {
  @apply text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4;
}

.use-cases-subtitle {
  @apply text-xl text-gray-300 max-w-3xl mx-auto;
}

.use-cases-grid {
  @apply grid lg:grid-cols-2 gap-8 mb-16;
}

.use-case-card--toml {
  @apply hover:shadow-orange-500/10;
}

.use-case-card--docker {
  @apply hover:shadow-blue-500/10;
}

.use-cases-info {
  @apply space-y-12;
}

.use-cases-info-content {
  @apply text-center;
}

.use-cases-info-title {
  @apply text-2xl font-bold text-white mb-4;
}

.use-cases-info-description {
  @apply text-lg text-gray-300 max-w-3xl mx-auto leading-relaxed;
}

.use-cases-comparison {
  @apply grid md:grid-cols-2 gap-8;
}

.comparison-item {
  @apply bg-gray-800/50 backdrop-blur-sm rounded-xl p-6 border border-gray-700/50;
}

.comparison-title {
  @apply text-lg font-semibold text-white mb-4;
}

.comparison-list {
  @apply space-y-2;
}

.comparison-list li {
  @apply flex items-center text-gray-300 text-sm;
}

.comparison-list li::before {
  @apply content-['✓'] text-green-400 font-bold mr-3;
}

.use-cases-cta {
  @apply flex flex-col sm:flex-row gap-4 justify-center items-center;
}
</style>

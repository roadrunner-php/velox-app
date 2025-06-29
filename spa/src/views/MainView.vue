<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-900">
    <!-- Hero Section -->
    <HeroSection
      main-title="Build Your Perfect"
      highlight-title="RoadRunner Server"
      subtitle="Create optimized, lightweight server configurations with our intelligent plugin system and proven presets."
      subtitle-highlight="Deploy faster, run leaner, scale better."
      :primary-cta="{
        text: '🚀 Start Building',
        to: '/plugins',
      }"
      :secondary-cta="{
        text: '⚡ Quick Presets',
        to: '/presets',
      }"
      :benefits="[
        '✅ Zero Configuration Conflicts',
        '⚡ Instant Deployment',
        '🔧 Auto Dependency Resolution',
        '📦 Optimized Binaries',
      ]"
    />

    <!-- What is RoadRunner Section -->
    <WhatIsRoadRunner
      title="What is RoadRunner?"
      subtitle="The high-performance PHP application server that changes everything"
      :show-background="true"
      :show-cta="true"
      variant="default"
    />

    <!-- Process Steps Section -->
    <ProcessSteps
      title="How Velox Works"
      subtitle="Three simple steps to deploy your optimized RoadRunner server"
      :steps="[
        {
          id: 'select',
          title: 'Select & Configure',
          description:
            'Choose plugins or start with proven presets. Our intelligent system automatically resolves dependencies and prevents conflicts.',
        },
        {
          id: 'generate',
          title: 'Generate Config',
          description:
            'Get production-ready configurations in your preferred format. TOML for traditional deployments, Dockerfiles for containers.',
        },
        {
          id: 'deploy',
          title: 'Deploy & Scale',
          description:
            'Deploy with confidence using battle-tested configurations. Monitor performance and scale seamlessly.',
        },
      ]"
    />

    <!-- Use Cases Section -->
    <UseCases
      title="Deployment Use Cases"
      subtitle="Generate the exact configuration format your workflow needs"
      :show-background="true"
      variant="default"
    />

    <!-- Statistics Section -->
    <StatisticsSection
      variant="transparent"
      title="Trusted by Developers Worldwide"
      :statistics="[
        {
          id: 'plugins',
          value: 50,
          label: 'Available Plugins',
          suffix: '+',
          color: 'blue',
          delay: 0,
        },
        {
          id: 'presets',
          value: 12,
          label: 'Ready Presets',
          suffix: '+',
          color: 'green',
          delay: 200,
        },
        {
          id: 'deployments',
          value: 25,
          label: 'Deployments',
          suffix: 'K+',
          color: 'purple',
          delay: 400,
        },
        {
          id: 'performance',
          value: 85,
          label: 'Faster Setup',
          suffix: '%',
          color: 'cyan',
          delay: 600,
        },
      ]"
    />

    <!-- Feature Comparison Section -->
    <FeatureComparison
      title="Choose Your Approach"
      subtitle="Whether you prefer granular control or quick deployment, we've got you covered"
      :show-bottom-cta="true"
      bottom-cta-text="Not sure which approach to take? Try both and see what works best for your project."
      variant="default"
    />

    <!-- Benefits Grid Section -->
    <BenefitsGrid
      title="Why Choose Velox?"
      subtitle="Experience the difference of intelligent configuration management"
      :show-background="true"
      :show-bottom-cta="true"
      bottom-cta-text="Ready to experience the future of RoadRunner configuration?"
      variant="default"
    />

    <!-- Call to Action Section -->
    <CallToAction
      title="Ready to Build Something Amazing?"
      subtitle="Join thousands of developers who have streamlined their RoadRunner deployments with Velox"
      :ctas="[
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
      ]"
      variant="default"
    />
  </div>
</template>

<script setup lang="ts">
import HeroSection from '@/components/home/HeroSection.vue'
import WhatIsRoadRunner from '@/components/home/WhatIsRoadRunner.vue'
import ProcessSteps from '@/components/home/ProcessSteps.vue'
import UseCases from '@/components/home/UseCases.vue'
import StatisticsSection from '@/components/home/StatisticsSection.vue'
import FeatureComparison from '@/components/home/FeatureComparison.vue'
import BenefitsGrid from '@/components/home/BenefitsGrid.vue'
import CallToAction from '@/components/home/CallToAction.vue'
</script>

<style scoped>
/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Enhanced gradient backgrounds */
.bg-gradient-to-br {
  background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}

/* Performance optimizations */
.transform {
  transform: translateZ(0);
}

/* Focus states for accessibility */
.focus\:ring-2:focus {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

.focus\:ring-offset-2:focus {
  box-shadow:
    0 0 0 2px #000,
    0 0 0 4px rgba(59, 130, 246, 0.5);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .min-h-screen {
    min-height: 100vh;
  }
}

/* Enhanced shadow effects for dark theme */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  html {
    scroll-behavior: auto;
  }

  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .bg-gradient-to-br {
    background: #000000;
  }
}

/* Print styles */
@media print {
  .bg-gradient-to-br {
    background: white !important;
    color: black !important;
  }
}

/* Section spacing and flow */
section {
  @apply relative;
}

/* Ensure proper section transitions */
section + section {
  @apply border-t border-gray-800/20;
}

/* Optimize background patterns */
.bg-gradient-to-br::before {
  content: '';
  @apply absolute inset-0 opacity-5 pointer-events-none;
}

/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  @apply w-2;
}

::-webkit-scrollbar-track {
  @apply bg-gray-900;
}

::-webkit-scrollbar-thumb {
  @apply bg-gray-600 rounded-full;
}

::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-500;
}
</style>

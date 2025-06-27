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

    <!-- Process Steps Section -->
    <ProcessSteps title="How Velox Works" />

    <!-- Statistics Section -->
    <StatisticsSection variant="transparent" />

    <!-- Benefits Grid Section -->
    <BenefitsGrid />

    <!-- Call to Action Section -->
    <CallToAction />
  </div>
</template>

<script setup lang="ts">
import HeroSection from '@/components/home/HeroSection.vue'
import ProcessSteps from '@/components/home/ProcessSteps.vue'
import StatisticsSection from '@/components/home/StatisticsSection.vue'
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
</style>

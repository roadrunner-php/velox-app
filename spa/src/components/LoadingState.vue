<template>
  <div class="loading-state">
    <!-- Loading Spinner -->
    <div class="loading-state__spinner"></div>

    <!-- Loading Message -->
    <p class="loading-state__message">
      {{ message }}
    </p>

    <!-- Optional Subtitle -->
    <p v-if="subtitle" class="loading-state__subtitle">
      {{ subtitle }}
    </p>

    <!-- Progress Bar (Optional) -->
    <div v-if="showProgress && progress !== null" class="loading-state__progress">
      <div class="loading-state__progress-bar">
        <div
          class="loading-state__progress-fill"
          :style="{ width: `${Math.min(100, Math.max(0, progress))}%` }"
        ></div>
      </div>
      <div class="loading-state__progress-text">{{ Math.round(progress) }}% complete</div>
    </div>

    <!-- Loading Steps (Optional) -->
    <div v-if="steps.length > 0" class="loading-state__steps">
      <div class="loading-state__steps-list">
        <div
          v-for="(step, index) in steps"
          :key="index"
          class="loading-state__step"
          :class="`loading-state__step--${step.status}`"
        >
          <!-- Step Icon -->
          <div class="loading-state__step-icon">
            <!-- Complete -->
            <svg
              v-if="step.status === 'complete'"
              class="loading-state__step-icon-complete"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"
              />
            </svg>
            <!-- Active -->
            <div v-else-if="step.status === 'active'" class="loading-state__step-spinner"></div>
            <!-- Pending -->
            <div v-else class="loading-state__step-pending"></div>
          </div>

          <!-- Step Text -->
          <span :class="{ 'loading-state__step-text--active': step.status === 'active' }">
            {{ step.text }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface LoadingStep {
  text: string
  status: 'pending' | 'active' | 'complete'
}

interface Props {
  message?: string
  subtitle?: string
  showProgress?: boolean
  progress?: number | null
  steps?: LoadingStep[]
}

withDefaults(defineProps<Props>(), {
  message: 'Loading...',
  subtitle: '',
  showProgress: false,
  progress: null,
  steps: () => [],
})
</script>

<style scoped>
.loading-state {
  @apply text-center py-16;
}

.loading-state__spinner {
  @apply animate-spin rounded-full h-12 w-12 border-b-2 border-blue-400 mx-auto mb-6;
}

.loading-state__message {
  @apply text-slate-300 text-lg mb-2;
}

.loading-state__subtitle {
  @apply text-slate-500 text-sm;
}

.loading-state__progress {
  @apply mt-6 max-w-xs mx-auto;
}

.loading-state__progress-bar {
  @apply bg-slate-700/50 rounded-full h-2 overflow-hidden;
}

.loading-state__progress-fill {
  @apply bg-gradient-to-r from-blue-500 to-blue-600 h-full transition-all duration-500 ease-out rounded-full;
}

.loading-state__progress-text {
  @apply text-xs text-slate-400 mt-2;
}

.loading-state__steps {
  @apply mt-8 max-w-md mx-auto;
}

.loading-state__steps-list {
  @apply space-y-3;
}

.loading-state__step {
  @apply flex items-center gap-3 text-sm;
}

.loading-state__step--complete {
  @apply text-green-400;
}

.loading-state__step--active {
  @apply text-blue-400;
}

.loading-state__step--pending {
  @apply text-slate-500;
}

.loading-state__step-icon {
  @apply flex-shrink-0;
}

.loading-state__step-icon-complete {
  @apply w-5 h-5;
}

.loading-state__step-spinner {
  @apply w-5 h-5 border-2 border-current rounded-full animate-spin border-t-transparent;
}

.loading-state__step-pending {
  @apply w-5 h-5 border-2 border-current rounded-full opacity-50;
}

.loading-state__step-text--active {
  @apply font-medium;
}
</style>

<template>
  <div class="use-case-card" :class="cardClasses">
    <!-- Card Header -->
    <div class="use-case-header">
      <div class="use-case-icon" :class="iconClasses">
        {{ useCase.icon }}
      </div>
      <div class="use-case-header-content">
        <h3 class="use-case-title">{{ useCase.title }}</h3>
        <p class="use-case-description">{{ useCase.description }}</p>
      </div>
    </div>

    <!-- Code Preview -->
    <div class="use-case-code-preview">
      <div class="code-preview-header">
        <div class="code-preview-dots">
          <span class="code-dot code-dot--red"></span>
          <span class="code-dot code-dot--yellow"></span>
          <span class="code-dot code-dot--green"></span>
        </div>
        <span class="code-preview-title">{{ getCodeTitle() }}</span>
      </div>
      <div class="code-preview-content">
        <pre class="code-preview-text"><code>{{ useCase.codePreview }}</code></pre>
      </div>
    </div>

    <!-- Benefits Section -->
    <div class="use-case-benefits" v-if="useCase.benefits">
      <h4 class="benefits-title">Key Benefits</h4>
      <ul class="benefits-list">
        <li
          v-for="benefit in useCase.benefits"
          :key="benefit"
          class="benefit-item"
        >
          <span class="benefit-bullet" :class="bulletClasses"></span>
          {{ benefit }}
        </li>
      </ul>
    </div>

    <!-- Workflow Steps -->
    <div class="use-case-workflow" v-if="useCase.workflow">
      <h4 class="workflow-title">How it works</h4>
      <ol class="workflow-steps">
        <li
          v-for="(step, index) in useCase.workflow"
          :key="step"
          class="workflow-step"
        >
          <span class="workflow-number" :class="numberClasses">{{ index + 1 }}</span>
          {{ step }}
        </li>
      </ol>
    </div>

    <!-- Action Button -->
    <div class="use-case-action">
      <GradientButton
        :text="useCase.actionText"
        :to="useCase.actionTo"
        :variant="useCase.variant === 'toml' ? 'secondary' : 'primary'"
        size="lg"
        class="w-full"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import GradientButton from './GradientButton.vue'

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
  useCase: UseCase
}

const props = defineProps<Props>()

const cardClasses = computed(() => {
  const variantClasses = {
    toml: 'border-orange-500/30 hover:border-orange-400/50 bg-gradient-to-br from-orange-500/5 to-amber-500/5',
    docker: 'border-blue-500/30 hover:border-blue-400/50 bg-gradient-to-br from-blue-500/5 to-cyan-500/5',
  }

  return variantClasses[props.useCase.variant]
})

const iconClasses = computed(() => {
  const variantClasses = {
    toml: 'bg-gradient-to-br from-orange-500 to-amber-500 shadow-orange-500/20',
    docker: 'bg-gradient-to-br from-blue-500 to-cyan-500 shadow-blue-500/20',
  }

  return variantClasses[props.useCase.variant]
})

const bulletClasses = computed(() => {
  const variantClasses = {
    toml: 'bg-orange-400',
    docker: 'bg-blue-400',
  }

  return variantClasses[props.useCase.variant]
})

const numberClasses = computed(() => {
  const variantClasses = {
    toml: 'bg-orange-500/20 text-orange-300 border-orange-500/30',
    docker: 'bg-blue-500/20 text-blue-300 border-blue-500/30',
  }

  return variantClasses[props.useCase.variant]
})

function getCodeTitle() {
  return props.useCase.variant === 'toml' ? '.rr.yaml' : 'Dockerfile'
}
</script>

<style scoped>
.use-case-card {
  @apply bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border-2 transition-all duration-300 hover:shadow-2xl hover:scale-[1.02] space-y-6;
}

.use-case-header {
  @apply flex items-start space-x-4;
}

.use-case-icon {
  @apply w-12 h-12 rounded-xl flex items-center justify-center text-2xl font-semibold text-white shadow-lg flex-shrink-0;
}

.use-case-header-content {
  @apply flex-1;
}

.use-case-title {
  @apply text-xl font-bold text-white mb-2;
}

.use-case-description {
  @apply text-gray-300 leading-relaxed;
}

.use-case-code-preview {
  @apply bg-gray-900/80 rounded-xl overflow-hidden border border-gray-700/50;
}

.code-preview-header {
  @apply flex items-center justify-between px-4 py-2 bg-gray-800/50 border-b border-gray-700/50;
}

.code-preview-dots {
  @apply flex space-x-2;
}

.code-dot {
  @apply w-3 h-3 rounded-full;
}

.code-dot--red {
  @apply bg-red-400;
}

.code-dot--yellow {
  @apply bg-yellow-400;
}

.code-dot--green {
  @apply bg-green-400;
}

.code-preview-title {
  @apply text-sm text-gray-400 font-mono;
}

.code-preview-content {
  @apply p-4 overflow-x-auto;
}

.code-preview-text {
  @apply text-sm text-gray-300 font-mono leading-relaxed whitespace-pre;
}

.use-case-benefits {
  @apply space-y-3;
}

.benefits-title {
  @apply text-lg font-semibold text-white;
}

.benefits-list {
  @apply space-y-2;
}

.benefit-item {
  @apply flex items-center text-gray-300 text-sm;
}

.benefit-bullet {
  @apply w-2 h-2 rounded-full mr-3 flex-shrink-0;
}

.use-case-workflow {
  @apply space-y-3;
}

.workflow-title {
  @apply text-lg font-semibold text-white;
}

.workflow-steps {
  @apply space-y-2;
}

.workflow-step {
  @apply flex items-center text-gray-300 text-sm;
}

.workflow-number {
  @apply w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold mr-3 flex-shrink-0 border;
}

.use-case-action {
  @apply pt-2;
}

/* Code syntax highlighting for different file types */
.code-preview-text {
  /* TOML highlighting */
  --toml-key: #f59e0b;
  --toml-string: #10b981;
  --toml-number: #3b82f6;
  --toml-comment: #6b7280;
  
  /* Docker highlighting */
  --docker-keyword: #8b5cf6;
  --docker-string: #10b981;
  --docker-comment: #6b7280;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .use-case-header {
    @apply flex-col space-x-0 space-y-4;
  }
  
  .use-case-icon {
    @apply self-start;
  }
  
  .code-preview-content {
    @apply p-3;
  }
  
  .code-preview-text {
    @apply text-xs;
  }
}
</style>

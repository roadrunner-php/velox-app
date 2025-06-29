<template>
  <section class="getting-started-steps">
    <div class="getting-started-steps__container">
      <div class="getting-started-steps__content">
        <div
          v-for="(step, index) in steps"
          :key="step.id"
          :ref="(el) => (stepRefs[step.id] = el)"
          class="getting-started-steps__step"
          :class="{ 'getting-started-steps__step--active': currentStep === step.id }"
          :data-step="step.id"
        >
          <!-- Step Header -->
          <div class="getting-started-steps__step-header">
            <!-- Step Number -->
            <div class="getting-started-steps__step-number" :class="getStepNumberClasses(step.id)">
              <span class="getting-started-steps__step-number-text">{{ step.id }}</span>
            </div>

            <!-- Step Content -->
            <div class="getting-started-steps__step-content">
              <div>
                <div class="getting-started-steps__step-title-row">
                  <span class="getting-started-steps__step-icon">{{ step.icon }}</span>
                  <h2 class="getting-started-steps__step-title">{{ step.title }}</h2>
                  <div
                    v-if="step.difficulty"
                    class="getting-started-steps__difficulty-badge"
                    :class="getDifficultyClasses(step.difficulty)"
                  >
                    {{ step.difficulty }}
                  </div>
                </div>
                <p class="getting-started-steps__step-description">{{ step.description }}</p>
                <p class="getting-started-steps__step-details">{{ step.content }}</p>

                <!-- Additional Tips -->
                <div
                  v-if="step.tips"
                  class="getting-started-steps__tips"
                >
                  <div class="getting-started-steps__tips-content">
                    <svg
                      class="getting-started-steps__tips-icon"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                    <div>
                      <p class="getting-started-steps__tips-title">💡 Pro Tip</p>
                      <p class="getting-started-steps__tips-text">{{ step.tips }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Button -->
              <div v-if="step.actionText" class="getting-started-steps__action">
                <component
                  :is="step.external ? 'a' : 'RouterLink'"
                  :href="step.external ? step.actionLink : undefined"
                  :to="step.external ? undefined : step.actionLink"
                  :target="step.external ? '_blank' : undefined"
                  :rel="step.external ? 'noopener noreferrer' : undefined"
                  class="getting-started-steps__action-button"
                >
                  {{ step.actionText }}
                  <svg
                    v-if="step.external"
                    class="getting-started-steps__action-icon"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                    />
                  </svg>
                  <svg v-else class="getting-started-steps__action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13 7l5 5m0 0l-5 5m5-5H6"
                    />
                  </svg>
                </component>
              </div>

              <!-- Code Example -->
              <div v-if="step.codeExample" class="getting-started-steps__code-example">
                <div class="getting-started-steps__code-header" @click="toggleCodeExpansion(step.id)">
                  <h3 class="getting-started-steps__code-title">{{ step.codeExample.title }}</h3>
                  <button class="getting-started-steps__code-toggle">
                    <svg
                      class="getting-started-steps__code-toggle-icon"
                      :class="{ 'getting-started-steps__code-toggle-icon--expanded': expandedCode[step.id] }"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                      />
                    </svg>
                  </button>
                </div>

                <transition
                  enter-active-class="transition-all duration-300 ease-out"
                  enter-from-class="opacity-0 max-h-0"
                  enter-to-class="opacity-100 max-h-screen"
                  leave-active-class="transition-all duration-300 ease-in"
                  leave-from-class="opacity-100 max-h-screen"
                  leave-to-class="opacity-0 max-h-0"
                >
                  <div v-show="expandedCode[step.id]" class="getting-started-steps__code-content">
                    <div class="getting-started-steps__code-block">
                      <div class="getting-started-steps__code-block-header">
                        <span class="getting-started-steps__code-language">{{ step.codeExample.language }}</span>
                        <button
                          @click="copyToClipboard(step.codeExample.code, step.id)"
                          class="getting-started-steps__copy-button"
                          :class="{ 'getting-started-steps__copy-button--copied': copiedStep === step.id }"
                        >
                          <svg
                            v-if="copiedStep !== step.id"
                            class="getting-started-steps__copy-icon"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                            />
                          </svg>
                          <svg
                            v-else
                            class="getting-started-steps__copy-icon"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"
                            />
                          </svg>
                          {{ copiedStep === step.id ? 'Copied!' : 'Copy' }}
                        </button>
                      </div>
                      <pre class="getting-started-steps__code-pre"><code class="getting-started-steps__code-text">{{ step.codeExample.code }}</code></pre>
                    </div>
                  </div>
                </transition>
              </div>
            </div>
          </div>

          <!-- Step Connector -->
          <div v-if="index < steps.length - 1" class="getting-started-steps__connector"></div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'

interface StepCodeExample {
  title: string
  language: string
  code: string
}

interface Step {
  id: number
  title: string
  description: string
  icon: string
  difficulty: string
  content: string
  tips?: string
  actionText?: string
  actionLink?: string
  external?: boolean
  codeExample?: StepCodeExample
}

interface Props {
  steps?: Step[]
}

const props = withDefaults(defineProps<Props>(), {
  steps: () => [
    {
      id: 1,
      title: 'Select Required Plugins',
      description: 'Choose the plugins you need for your RoadRunner server',
      icon: '🔧',
      difficulty: 'Easy',
      content:
        'Browse our plugin catalog and select the ones that match your requirements. Our intelligent dependency resolver will automatically include any required dependencies.',
      tips: "Start with presets if you're unsure which plugins to choose. They're pre-configured for common use cases.",
      actionText: 'Browse Plugins',
      actionLink: '/plugins',
      external: false,
    },
    {
      id: 2,
      title: 'Generate Configuration',
      description: 'Create your custom RoadRunner configuration file',
      icon: '⚙️',
      difficulty: 'Easy',
      content:
        "Once you've selected your plugins, generate a configuration file in your preferred format. We support TOML, JSON, and Dockerfile formats.",
      tips: "TOML format is recommended for most use cases as it's more readable and widely supported.",
      external: false,
      codeExample: {
        title: 'Example .velox.toml configuration:',
        language: 'toml',
        code: `[roadrunner]
ref = "v2025.1.1"

[log]
level = "info"
mode = "production"

[github]
[github.token]
token = "\${RT_TOKEN}"

[github.plugins]
[github.plugins.http]
ref = "v5.2.7"
owner = "roadrunner-server"
repository = "http"

[github.plugins.server]
ref = "v5.2.9"
owner = "roadrunner-server"
repository = "server"

[github.plugins.lock]
ref = "v5.1.8"
owner = "roadrunner-server"
repository = "lock"

[github.plugins.otel]
ref = "v5.1.8"
owner = "roadrunner-server"
repository = "otel"

[github.plugins.status]
ref = "v5.1.8"
owner = "roadrunner-server"
repository = "status"

[github.plugins.metrics]
ref = "v5.1.8"
owner = "roadrunner-server"
repository = "metrics"`,
      },
    },
    {
      id: 3,
      title: 'Download Velox',
      description: 'Get the Velox binary builder tool',
      icon: '📦',
      difficulty: 'Easy',
      content:
        'Download the Velox binary for your operating system. Velox is the tool that compiles your custom RoadRunner binary with only the plugins you selected.',
      tips: 'Choose the correct binary for your OS. Linux users might need to make the binary executable with chmod +x.',
      actionText: 'Download Velox',
      actionLink: 'https://github.com/roadrunner-server/velox/releases',
      external: true,
    },
    {
      id: 4,
      title: 'Install Go',
      description: 'Set up the Go programming language',
      icon: '🐹',
      difficulty: 'Medium',
      content:
        "Velox requires Go 1.22 or later to compile your RoadRunner binary. If you don't have Go installed, download it from the official website.",
      tips: 'Make sure to add Go to your PATH environment variable. You can verify installation with: go version',
      actionText: 'Download Go',
      actionLink: 'https://go.dev/doc/install',
      external: true,
      codeExample: {
        title: 'Verify Go installation:',
        language: 'bash',
        code: `# Check if Go is installed and version
go version

# Should output something like:
# go version go1.24.4 linux/amd64`,
      },
    },
    {
      id: 5,
      title: 'Build Your Binary',
      description: 'Put it all together and compile your custom RoadRunner',
      icon: '🚀',
      difficulty: 'Medium',
      content:
        'Place your generated .velox.toml configuration file in your project directory and run the Velox build command. The tool will download all required plugins and compile your custom binary.',
      tips: 'The build process may take a few minutes on first run as it downloads dependencies. Subsequent builds will be faster.',
      codeExample: {
        title: 'Build commands:',
        language: 'bash',
        code: `# Set your GitHub token (required for downloading plugins)
export RT_TOKEN=your_github_token_here

# Run the build (this will create a 'rr' binary)
./vx build -c .velox.toml

# For Windows
set RT_TOKEN=your_github_token_here
vx.exe build -c .velox.toml

# Run your custom RoadRunner server
./rr serve -c .rr.yaml

# Check your custom binary
./rr --version`,
      },
    },
  ]
})

const currentStep = ref(1)
const copiedStep = ref<number | null>(null)
const expandedCode = ref<Record<number, boolean>>({})
const stepRefs = ref<Record<number, HTMLElement | null>>({})

// Initialize expanded states
onMounted(() => {
  props.steps.forEach((step) => {
    if (step.codeExample) {
      expandedCode.value[step.id] = false
    }
  })

  // Set up intersection observer for step tracking
  setupIntersectionObserver()
})

function setupIntersectionObserver() {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const stepId = parseInt(entry.target.getAttribute('data-step') || '1')
          currentStep.value = stepId
        }
      })
    },
    { threshold: 0.3, rootMargin: '-20% 0px -20% 0px' },
  )

  // Observe all step elements
  Object.values(stepRefs.value).forEach((el) => {
    if (el) {
      observer.observe(el)
    }
  })

  onBeforeUnmount(() => {
    observer.disconnect()
  })
}

function toggleCodeExpansion(stepId: number) {
  expandedCode.value[stepId] = !expandedCode.value[stepId]
}

async function copyToClipboard(text: string, stepId: number) {
  try {
    await navigator.clipboard.writeText(text)
    copiedStep.value = stepId
    setTimeout(() => {
      copiedStep.value = null
    }, 2000)
  } catch (err) {
    // Fallback for older browsers
    const textArea = document.createElement('textarea')
    textArea.value = text
    document.body.appendChild(textArea)
    textArea.select()
    try {
      document.execCommand('copy')
      copiedStep.value = stepId
      setTimeout(() => {
        copiedStep.value = null
      }, 2000)
    } catch (e) {
      console.error('Failed to copy text:', e)
    }
    document.body.removeChild(textArea)
  }
}

function getStepNumberClasses(stepId: number) {
  const isActive = currentStep.value === stepId
  const isCompleted = currentStep.value > stepId

  if (isCompleted) {
    return 'getting-started-steps__step-number--completed'
  } else if (isActive) {
    return 'getting-started-steps__step-number--active'
  } else {
    return 'getting-started-steps__step-number--pending'
  }
}

function getDifficultyClasses(difficulty: string) {
  switch (difficulty.toLowerCase()) {
    case 'easy':
      return 'getting-started-steps__difficulty-badge--easy'
    case 'medium':
      return 'getting-started-steps__difficulty-badge--medium'
    case 'hard':
      return 'getting-started-steps__difficulty-badge--hard'
    default:
      return 'getting-started-steps__difficulty-badge--medium'
  }
}
</script>

<style scoped>
.getting-started-steps {
  @apply max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20;
}

.getting-started-steps__container {
  @apply space-y-16;
}

.getting-started-steps__step {
  @apply relative;
}

.getting-started-steps__step--active {
  @apply transform scale-[1.02] transition-transform duration-300;
}

.getting-started-steps__step-header {
  @apply flex items-start gap-6 mb-8;
}

.getting-started-steps__step-number {
  @apply w-16 h-16 rounded-2xl flex items-center justify-center font-bold text-xl border-2;
  @apply transition-all duration-300 flex-shrink-0;
}

.getting-started-steps__step-number--pending {
  @apply bg-gray-800/60 border-gray-700 text-gray-400;
}

.getting-started-steps__step-number--active {
  @apply bg-gradient-to-br from-blue-500 to-blue-600 border-blue-400 text-white;
  @apply shadow-lg shadow-blue-500/30;
}

.getting-started-steps__step-number--completed {
  @apply bg-gradient-to-br from-green-500 to-green-600 border-green-400 text-white;
  @apply shadow-lg shadow-green-500/30;
}

.getting-started-steps__step-number-text {
  @apply transition-transform duration-300;
}

.getting-started-steps__step-content {
  @apply flex-1 flex flex-col gap-6;
}

.getting-started-steps__step-title-row {
  @apply flex items-center gap-4 mb-4;
}

.getting-started-steps__step-icon {
  @apply text-3xl;
}

.getting-started-steps__step-title {
  @apply text-2xl font-bold text-white;
}

.getting-started-steps__difficulty-badge {
  @apply px-2 py-1 text-xs font-medium rounded-lg;
}

.getting-started-steps__difficulty-badge--easy {
  @apply bg-green-900/30 text-green-300 border border-green-500/30;
}

.getting-started-steps__difficulty-badge--medium {
  @apply bg-yellow-900/30 text-yellow-300 border border-yellow-500/30;
}

.getting-started-steps__difficulty-badge--hard {
  @apply bg-red-900/30 text-red-300 border border-red-500/30;
}

.getting-started-steps__step-description {
  @apply text-lg text-gray-300 mb-6;
}

.getting-started-steps__step-details {
  @apply text-gray-400 leading-relaxed;
}

.getting-started-steps__tips {
  @apply mt-4 p-4 bg-blue-900/20 border border-blue-500/30 rounded-lg;
}

.getting-started-steps__tips-content {
  @apply flex items-start gap-2;
}

.getting-started-steps__tips-icon {
  @apply w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0;
}

.getting-started-steps__tips-title {
  @apply text-blue-300 font-medium mb-1;
}

.getting-started-steps__tips-text {
  @apply text-blue-200 text-sm;
}

.getting-started-steps__action {
  /* No additional styling needed */
}

.getting-started-steps__action-button {
  @apply inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700;
  @apply text-white cursor-pointer font-semibold rounded-xl hover:from-blue-500 hover:to-blue-600;
  @apply transition-all duration-200 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30;
  @apply transform hover:scale-105;
}

.getting-started-steps__action-icon {
  @apply w-4 h-4;
}

.getting-started-steps__code-example {
  @apply bg-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 overflow-hidden;
}

.getting-started-steps__code-header {
  @apply flex items-center justify-between p-4 border-b border-gray-700/30;
  @apply cursor-pointer hover:bg-gray-800/30 transition-colors;
}

.getting-started-steps__code-title {
  @apply font-medium text-white;
}

.getting-started-steps__code-toggle {
  @apply p-2 text-gray-400 hover:text-white transition-colors rounded-lg hover:bg-gray-700/50;
}

.getting-started-steps__code-toggle-icon {
  @apply w-5 h-5 transition-transform duration-200;
}

.getting-started-steps__code-toggle-icon--expanded {
  @apply rotate-180;
}

.getting-started-steps__code-content {
  @apply overflow-hidden;
}

.getting-started-steps__code-block {
  @apply relative;
}

.getting-started-steps__code-block-header {
  @apply flex items-center justify-between px-4 py-2 bg-gray-900/60 border-b border-gray-700/30;
}

.getting-started-steps__code-language {
  @apply text-xs font-medium text-gray-400 uppercase tracking-wider;
}

.getting-started-steps__copy-button {
  @apply flex items-center gap-2 px-3 py-1 text-xs font-medium rounded-lg transition-all duration-200;
  @apply text-gray-400 hover:text-white hover:bg-gray-700/50;
}

.getting-started-steps__copy-button--copied {
  @apply text-green-400 bg-green-900/20 border border-green-500/30;
}

.getting-started-steps__copy-icon {
  @apply w-4 h-4;
}

.getting-started-steps__code-pre {
  @apply p-4 overflow-x-auto;
}

.getting-started-steps__code-text {
  @apply text-sm text-gray-300 font-mono leading-relaxed whitespace-pre;
}

.getting-started-steps__connector {
  @apply absolute left-8 top-16 w-0.5 h-16 bg-gradient-to-b from-gray-600 to-transparent;
}

/* Scrollbar styling */
.getting-started-steps__code-pre::-webkit-scrollbar {
  height: 8px;
}

.getting-started-steps__code-pre::-webkit-scrollbar-track {
  @apply bg-gray-800;
}

.getting-started-steps__code-pre::-webkit-scrollbar-thumb {
  @apply bg-gray-600 rounded-full;
}

.getting-started-steps__code-pre::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-500;
}

/* Responsive improvements */
@media (max-width: 768px) {
  .getting-started-steps__step-number {
    @apply w-12 h-12 text-lg;
  }
}
</style>

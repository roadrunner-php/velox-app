<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-900">
    <!-- Hero Section -->
    <section class="relative overflow-hidden py-20">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-5">
        <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
        <div
          class="absolute bottom-1/4 left-1/4 w-96 h-96 bg-purple-500/5 rounded-full blur-3xl"
        ></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-6xl font-bold text-white mb-6">
          Getting Started with
          <span
            class="bg-gradient-to-r from-blue-400 via-purple-400 to-cyan-400 bg-clip-text text-transparent"
          >
            Velox
          </span>
        </h1>
        <p class="text-xl text-gray-300 max-w-3xl mx-auto mb-8">
          Follow these simple steps to build your custom RoadRunner binary with exactly the plugins
          you need
        </p>
        
        <!-- Video Link Section -->
        <div class="max-w-2xl mx-auto mb-12">
          <a
            href="https://www.youtube.com/watch?v=sddi_lh7ePo"
            target="_blank"
            rel="noopener noreferrer"
            class="group inline-flex items-center gap-4 p-6 bg-gradient-to-r from-red-600/20 to-red-700/20 border border-red-500/30 rounded-2xl hover:from-red-600/30 hover:to-red-700/30 hover:border-red-400/50 transition-all duration-300 transform hover:scale-105"
          >
            <div class="relative">
              <!-- YouTube Play Button -->
              <div class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center group-hover:bg-red-500 transition-colors">
                <svg class="w-6 h-6 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M8 5v14l11-7z"/>
                </svg>
              </div>
              <!-- Pulse animation -->
              <div class="absolute inset-0 w-16 h-16 bg-red-600 rounded-full animate-ping opacity-20"></div>
            </div>
            <div class="text-left">
              <h3 class="text-lg font-semibold text-white mb-1 group-hover:text-red-200 transition-colors">
                Watch Velox in Action
              </h3>
              <p class="text-gray-400 text-sm group-hover:text-gray-300 transition-colors">
                See how to build custom RoadRunner binaries in under 5 minutes
              </p>
            </div>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
          </a>
        </div>
      </div>
    </section>

    <!-- Steps Section -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
      <div class="space-y-16">
        <div
          v-for="(step, index) in steps"
          :key="step.id"
          :ref="(el) => (stepRefs[step.id] = el)"
          class="step-container"
          :class="{ 'step-container--active': currentStep === step.id }"
          :data-step="step.id"
        >
          <!-- Step Header -->
          <div class="flex items-start gap-6 mb-8">
            <!-- Step Number -->
            <div class="step-number" :class="getStepNumberClasses(step.id)">
              <span class="step-number-text">{{ step.id }}</span>
            </div>

            <!-- Step Content -->
            <div class="flex-1 flex flex-col gap-6">
              <div>
                <div class="flex items-center gap-4 mb-4">
                  <span class="text-3xl">{{ step.icon }}</span>
                  <h2 class="text-2xl font-bold text-white">{{ step.title }}</h2>
                  <div v-if="step.difficulty" class="difficulty-badge" :class="getDifficultyClasses(step.difficulty)">
                    {{ step.difficulty }}
                  </div>
                </div>
                <p class="text-lg text-gray-300 mb-6">{{ step.description }}</p>
                <p class="text-gray-400 leading-relaxed">{{ step.content }}</p>

                <!-- Additional Tips -->
                <div v-if="step.tips" class="mt-4 p-4 bg-blue-900/20 border border-blue-500/30 rounded-lg">
                  <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                      <p class="text-blue-300 font-medium mb-1">💡 Pro Tip</p>
                      <p class="text-blue-200 text-sm">{{ step.tips }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Button -->
              <div v-if="step.actionText">
                <component
                  :is="step.external ? 'a' : 'RouterLink'"
                  :href="step.external ? step.actionLink : undefined"
                  :to="step.external ? undefined : step.actionLink"
                  :target="step.external ? '_blank' : undefined"
                  :rel="step.external ? 'noopener noreferrer' : undefined"
                  class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white cursor-pointer font-semibold rounded-xl hover:from-blue-500 hover:to-blue-600 transition-all duration-200 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transform hover:scale-105"
                >
                  {{ step.actionText }}
                  <svg
                    v-if="step.external"
                    class="w-4 h-4"
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
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
              <div v-if="step.codeExample" class="code-example">
                <div class="code-example-header" @click="toggleCodeExpansion(step.id)">
                  <h3 class="code-example-title">{{ step.codeExample.title }}</h3>
                  <button class="code-toggle-button">
                    <svg
                      class="code-toggle-icon"
                      :class="{ 'code-toggle-icon--expanded': expandedCode[step.id] }"
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
                  <div v-show="expandedCode[step.id]" class="code-content">
                    <div class="code-block">
                      <div class="code-header">
                        <span class="code-language">{{ step.codeExample.language }}</span>
                        <button
                          @click="copyToClipboard(step.codeExample.code, step.id)"
                          class="copy-button"
                          :class="{ 'copy-button--copied': copiedStep === step.id }"
                        >
                          <svg
                            v-if="copiedStep !== step.id"
                            class="copy-icon"
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
                            class="copy-icon"
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
                      <pre
                        class="code-pre"
                      ><code class="code-text">{{ step.codeExample.code }}</code></pre>
                    </div>
                  </div>
                </transition>
              </div>
            </div>

          </div>

          <!-- Step Connector -->
          <div v-if="index < steps.length - 1" class="step-connector"></div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
      <h2 class="text-3xl font-bold text-white text-center mb-8">Frequently Asked Questions</h2>
      
      <div class="flex flex-col gap-4">
        <div
          v-for="(faq, index) in faqs"
          :key="index"
          class="faq-item"
        >
          <button
            @click="toggleFaq(index)"
            class="faq-question"
            :class="{ 'faq-question--active': expandedFaq[index] }"
          >
            <span class="faq-question-text">{{ faq.question }}</span>
            <svg
              class="faq-icon"
              :class="{ 'faq-icon--expanded': expandedFaq[index] }"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          
          <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-96"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 max-h-96"
            leave-to-class="opacity-0 max-h-0"
          >
            <div v-show="expandedFaq[index]" class="faq-answer p-6">
              <p class="text-gray-300 leading-relaxed">{{ faq.answer }}</p>
            </div>
          </transition>
        </div>
      </div>
    </section>

    <!-- Final CTA Section -->
    <section
      class="bg-gradient-to-r from-gray-900 via-black to-gray-900 border-t border-gray-800/50 py-20"
    >
      <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-white mb-6">Ready to Build Your Custom Server?</h2>
        <p class="text-xl text-gray-300 mb-8">
          Start by selecting the plugins you need, or choose from our battle-tested presets
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <RouterLink
            to="/plugins"
            class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold text-lg rounded-full hover:from-blue-500 hover:to-blue-600 transition-all duration-300 transform hover:scale-105 shadow-lg shadow-blue-500/20"
          >
            🔧 Custom Build
          </RouterLink>
          <RouterLink
            to="/presets"
            class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold text-lg rounded-full hover:from-purple-500 hover:to-purple-600 transition-all duration-300 transform hover:scale-105 shadow-lg shadow-purple-500/20"
          >
            ⚡ Quick Start
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'

const currentStep = ref(1)
const copiedStep = ref<number | null>(null)
const expandedCode = ref<Record<number, boolean>>({})
const expandedFaq = ref<Record<number, boolean>>({})
const stepRefs = ref<Record<number, HTMLElement | null>>({})

const steps = [
  {
    id: 1,
    title: 'Select Required Plugins',
    description: 'Choose the plugins you need for your RoadRunner server',
    icon: '🔧',
    difficulty: 'Easy',
    content:
      'Browse our plugin catalog and select the ones that match your requirements. Our intelligent dependency resolver will automatically include any required dependencies.',
    tips: 'Start with presets if you\'re unsure which plugins to choose. They\'re pre-configured for common use cases.',
    actionText: 'Browse Plugins',
    actionLink: '/plugins',
    external: false,
    codeExample: null,
  },
  {
    id: 2,
    title: 'Generate Configuration',
    description: 'Create your custom RoadRunner configuration file',
    icon: '⚙️',
    difficulty: 'Easy',
    content:
      "Once you've selected your plugins, generate a configuration file in your preferred format. We support TOML, JSON, and Dockerfile formats.",
    tips: 'TOML format is recommended for most use cases as it\'s more readable and widely supported.',
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
    actionText: null,
    actionLink: null,
    external: false,
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

const faqs = [
  {
    question: 'Do I need a GitHub token to build with Velox?',
    answer: 'Yes, you need a GitHub personal access token to download plugins from GitHub repositories. This is required even for public repositories to avoid rate limiting. You can create one in your GitHub settings under Developer settings > Personal access tokens.',
  },
  {
    question: 'What\'s the difference between plugins and presets?',
    answer: 'Plugins are individual components that add specific functionality (like HTTP server, database connections, etc.). Presets are pre-configured collections of plugins optimized for common use cases (web server, API server, microservices, etc.). Presets are great for getting started quickly.',
  },
  {
    question: 'Can I use community plugins in production?',
    answer: 'Yes, but exercise caution. Community plugins are not officially maintained by the RoadRunner team. Always review the plugin code, check for active maintenance, and test thoroughly before using in production environments.',
  },
  {
    question: 'How do I update plugins in my configuration?',
    answer: 'You can update plugin versions by modifying the "ref" field in your .velox.toml configuration file, then rebuilding your binary. Always test updates in a staging environment first.',
  },
  {
    question: 'What if my build fails?',
    answer: 'Common issues include: missing GitHub token, outdated Go version, network connectivity issues, or plugin compatibility conflicts. Check the error message carefully and ensure all requirements are met. The video tutorial shows how to troubleshoot common problems.',
  },
]

// Initialize expanded states
onMounted(() => {
  steps.forEach((step) => {
    if (step.codeExample) {
      expandedCode.value[step.id] = false
    }
  })
  
  faqs.forEach((_, index) => {
    expandedFaq.value[index] = false
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

function scrollToStep(stepId: number) {
  const element = stepRefs.value[stepId]
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'center' })
  }
}

function toggleCodeExpansion(stepId: number) {
  expandedCode.value[stepId] = !expandedCode.value[stepId]
}

function toggleFaq(index: number) {
  expandedFaq.value[index] = !expandedFaq.value[index]
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
    return 'step-number--completed'
  } else if (isActive) {
    return 'step-number--active'
  } else {
    return 'step-number--pending'
  }
}

function getDifficultyClasses(difficulty: string) {
  switch (difficulty.toLowerCase()) {
    case 'easy':
      return 'difficulty-badge--easy'
    case 'medium':
      return 'difficulty-badge--medium'
    case 'hard':
      return 'difficulty-badge--hard'
    default:
      return 'difficulty-badge--medium'
  }
}
</script>

<style scoped>
.step-container {
  @apply relative;
}

.step-container--active {
  @apply transform scale-[1.02] transition-transform duration-300;
}

.step-number {
  @apply w-16 h-16 rounded-2xl flex items-center justify-center font-bold text-xl border-2 transition-all duration-300 flex-shrink-0;
}

.step-number--pending {
  @apply bg-gray-800/60 border-gray-700 text-gray-400;
}

.step-number--active {
  @apply bg-gradient-to-br from-blue-500 to-blue-600 border-blue-400 text-white shadow-lg shadow-blue-500/30;
}

.step-number--completed {
  @apply bg-gradient-to-br from-green-500 to-green-600 border-green-400 text-white shadow-lg shadow-green-500/30;
}

.step-number-text {
  @apply transition-transform duration-300;
}

.difficulty-badge {
  @apply px-2 py-1 text-xs font-medium rounded-lg;
}

.difficulty-badge--easy {
  @apply bg-green-900/30 text-green-300 border border-green-500/30;
}

.difficulty-badge--medium {
  @apply bg-yellow-900/30 text-yellow-300 border border-yellow-500/30;
}

.difficulty-badge--hard {
  @apply bg-red-900/30 text-red-300 border border-red-500/30;
}

.step-connector {
  @apply absolute left-8 top-16 w-0.5 h-16 bg-gradient-to-b from-gray-600 to-transparent;
}

.code-example {
  @apply bg-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 overflow-hidden;
}

.code-example-header {
  @apply flex items-center justify-between p-4 border-b border-gray-700/30 cursor-pointer hover:bg-gray-800/30 transition-colors;
}

.code-example-title {
  @apply font-medium text-white;
}

.code-toggle-button {
  @apply p-2 text-gray-400 hover:text-white transition-colors rounded-lg hover:bg-gray-700/50;
}

.code-toggle-icon {
  @apply w-5 h-5 transition-transform duration-200;
}

.code-toggle-icon--expanded {
  @apply rotate-180;
}

.code-content {
  @apply overflow-hidden;
}

.code-block {
  @apply relative;
}

.code-header {
  @apply flex items-center justify-between px-4 py-2 bg-gray-900/60 border-b border-gray-700/30;
}

.code-language {
  @apply text-xs font-medium text-gray-400 uppercase tracking-wider;
}

.copy-button {
  @apply flex items-center gap-2 px-3 py-1 text-xs font-medium rounded-lg transition-all duration-200;
  @apply text-gray-400 hover:text-white hover:bg-gray-700/50;
}

.copy-button--copied {
  @apply text-green-400 bg-green-900/20 border border-green-500/30;
}

.copy-icon {
  @apply w-4 h-4;
}

.code-pre {
  @apply p-4 overflow-x-auto;
}

.code-text {
  @apply text-sm text-gray-300 font-mono leading-relaxed whitespace-pre;
}

/* FAQ Styles */
.faq-item {
  @apply bg-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 overflow-hidden;
}

.faq-question {
  @apply w-full flex items-center justify-between p-6 text-left hover:bg-gray-800/30 transition-colors;
}

.faq-question--active {
  @apply bg-gray-800/30;
}

.faq-question-text {
  @apply font-medium text-white text-lg;
}

.faq-icon {
  @apply w-5 h-5 text-gray-400 transition-transform duration-200 flex-shrink-0 ml-4;
}

.faq-icon--expanded {
  @apply rotate-180;
}

.faq-answer {
  @apply px-6 pb-6 overflow-hidden;
}

/* Scrollbar styling */
.code-pre::-webkit-scrollbar {
  height: 8px;
}

.code-pre::-webkit-scrollbar-track {
  @apply bg-gray-800;
}

.code-pre::-webkit-scrollbar-thumb {
  @apply bg-gray-600 rounded-full;
}

.code-pre::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-500;
}

/* Responsive improvements */
@media (max-width: 768px) {
  .step-number {
    @apply w-12 h-12 text-lg;
  }
  
  .faq-question-text {
    @apply text-base;
  }
}

/* Animation improvements */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>

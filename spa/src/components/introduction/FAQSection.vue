<template>
  <section class="faq-section">
    <div class="faq-section__container">
      <h2 class="faq-section__title">Frequently Asked Questions</h2>

      <div class="faq-section__items">
        <div v-for="(faq, index) in faqs" :key="index" class="faq-section__item">
          <button
            @click="toggleFaq(index)"
            class="faq-section__question"
            :class="{ 'faq-section__question--active': expandedFaq[index] }"
          >
            <span class="faq-section__question-text">{{ faq.question }}</span>
            <svg
              class="faq-section__icon"
              :class="{ 'faq-section__icon--expanded': expandedFaq[index] }"
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

          <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-96"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 max-h-96"
            leave-to-class="opacity-0 max-h-0"
          >
            <div v-show="expandedFaq[index]" class="faq-section__answer">
              <p class="faq-section__answer-text">{{ faq.answer }}</p>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface FAQ {
  question: string
  answer: string
}

interface Props {
  faqs?: FAQ[]
}

const props = withDefaults(defineProps<Props>(), {
  faqs: () => [
    {
      question: 'Do I need a GitHub token to build with Velox?',
      answer:
        'Yes, you need a GitHub personal access token to download plugins from GitHub repositories. This is required even for public repositories to avoid rate limiting. You can create one in your GitHub settings under Developer settings > Personal access tokens.',
    },
    {
      question: "What's the difference between plugins and presets?",
      answer:
        'Plugins are individual components that add specific functionality (like HTTP server, database connections, etc.). Presets are pre-configured collections of plugins optimized for common use cases (web server, API server, microservices, etc.). Presets are great for getting started quickly.',
    },
    {
      question: 'Can I use community plugins in production?',
      answer:
        'Yes, but exercise caution. Community plugins are not officially maintained by the RoadRunner team. Always review the plugin code, check for active maintenance, and test thoroughly before using in production environments.',
    },
    {
      question: 'How do I update plugins in my configuration?',
      answer:
        'You can update plugin versions by modifying the "ref" field in your .velox.toml configuration file, then rebuilding your binary. Always test updates in a staging environment first.',
    },
    {
      question: 'What if my build fails?',
      answer:
        'Common issues include: missing GitHub token, outdated Go version, network connectivity issues, or plugin compatibility conflicts. Check the error message carefully and ensure all requirements are met. The video tutorial shows how to troubleshoot common problems.',
    },
  ]
})

const expandedFaq = ref<Record<number, boolean>>({})

onMounted(() => {
  props.faqs.forEach((_, index) => {
    expandedFaq.value[index] = false
  })
})

function toggleFaq(index: number) {
  expandedFaq.value[index] = !expandedFaq.value[index]
}
</script>

<style scoped>
.faq-section {
  @apply max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-20;
}

.faq-section__container {
  /* No additional styling needed */
}

.faq-section__title {
  @apply text-3xl font-bold text-white text-center mb-8;
}

.faq-section__items {
  @apply flex flex-col gap-4;
}

.faq-section__item {
  @apply bg-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 overflow-hidden;
}

.faq-section__question {
  @apply w-full flex items-center justify-between p-6 text-left;
  @apply hover:bg-gray-800/30 transition-colors;
}

.faq-section__question--active {
  @apply bg-gray-800/30;
}

.faq-section__question-text {
  @apply font-medium text-white text-lg;
}

.faq-section__icon {
  @apply w-5 h-5 text-gray-400 transition-transform duration-200 flex-shrink-0 ml-4;
}

.faq-section__icon--expanded {
  @apply rotate-180;
}

.faq-section__answer {
  @apply px-6 pb-6 overflow-hidden;
}

.faq-section__answer-text {
  @apply text-gray-300 leading-relaxed;
}

/* Responsive improvements */
@media (max-width: 768px) {
  .faq-section__question-text {
    @apply text-base;
  }
}
</style>

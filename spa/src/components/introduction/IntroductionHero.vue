<template>
  <section class="introduction-hero">
    <!-- Background Pattern -->
    <div class="introduction-hero__background">
      <div class="introduction-hero__background-blob introduction-hero__background-blob--blue"></div>
      <div class="introduction-hero__background-blob introduction-hero__background-blob--purple"></div>
    </div>

    <div class="introduction-hero__container">
      <h1 class="introduction-hero__title">
        Getting Started with
        <span class="introduction-hero__title-highlight">
          Velox
        </span>
      </h1>
      <p class="introduction-hero__subtitle">
        Follow these simple steps to build your custom RoadRunner binary with exactly the plugins
        you need
      </p>

      <!-- Video Link Section -->
      <div class="introduction-hero__video-section">
        <button
          @click="showVideoModal = true"
          class="introduction-hero__video-button"
        >
          <div class="introduction-hero__video-play-container">
            <!-- YouTube Play Button -->
            <div class="introduction-hero__video-play-button">
              <svg class="introduction-hero__video-play-icon" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z" />
              </svg>
            </div>
            <!-- Pulse animation -->
            <div class="introduction-hero__video-pulse"></div>
          </div>
          <div class="introduction-hero__video-text">
            <h3 class="introduction-hero__video-title">
              Watch Velox in Action
            </h3>
            <p class="introduction-hero__video-description">
              See how to build custom RoadRunner binaries in under 5 minutes
            </p>
          </div>
          <svg
            class="introduction-hero__video-external-icon"
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
        </button>
      </div>
    </div>

    <!-- Video Modal -->
    <VideoModal
      :show="showVideoModal"
      :video-url="videoUrl"
      :video-title="videoTitle"
      @close="showVideoModal = false"
    />
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import VideoModal from './VideoModal.vue'

interface Props {
  videoUrl?: string
  videoTitle?: string
}

const props = withDefaults(defineProps<Props>(), {
  videoUrl: 'https://www.youtube.com/watch?v=sddi_lh7ePo',
  videoTitle: 'Velox in Action - Build Custom RoadRunner Binaries'
})

const showVideoModal = ref(false)
</script>

<style scoped>
.introduction-hero {
  @apply relative overflow-hidden py-20;
}

.introduction-hero__background {
  @apply absolute inset-0 opacity-5;
}

.introduction-hero__background-blob {
  @apply absolute w-96 h-96 rounded-full blur-3xl;
}

.introduction-hero__background-blob--blue {
  @apply top-1/4 right-1/4 bg-blue-500/5;
}

.introduction-hero__background-blob--purple {
  @apply bottom-1/4 left-1/4 bg-purple-500/5;
}

.introduction-hero__container {
  @apply relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center;
}

.introduction-hero__title {
  @apply text-4xl sm:text-6xl font-bold text-white mb-6;
}

.introduction-hero__title-highlight {
  @apply bg-gradient-to-r from-blue-400 via-purple-400 to-cyan-400 bg-clip-text text-transparent;
}

.introduction-hero__subtitle {
  @apply text-xl text-gray-300 max-w-3xl mx-auto mb-8;
}

.introduction-hero__video-section {
  @apply max-w-2xl mx-auto mb-12;
}

.introduction-hero__video-button {
  @apply inline-flex items-center gap-4 p-6 bg-gradient-to-r from-red-600/20 to-red-700/20;
  @apply border border-red-500/30 rounded-2xl hover:from-red-600/30 hover:to-red-700/30;
  @apply hover:border-red-400/50 transition-all duration-300 transform hover:scale-105 cursor-pointer;
}

.introduction-hero__video-play-container {
  @apply relative;
}

.introduction-hero__video-play-button {
  @apply w-16 h-16 bg-red-600 rounded-full flex items-center justify-center;
  @apply group-hover:bg-red-500 transition-colors;
}

.introduction-hero__video-play-icon {
  @apply w-6 h-6 text-white ml-1;
}

.introduction-hero__video-pulse {
  @apply absolute inset-0 w-16 h-16 bg-red-600 rounded-full animate-ping opacity-20;
}

.introduction-hero__video-text {
  @apply text-left;
}

.introduction-hero__video-title {
  @apply text-lg font-semibold text-white mb-1 group-hover:text-red-200 transition-colors;
}

.introduction-hero__video-description {
  @apply text-gray-400 text-sm group-hover:text-gray-300 transition-colors;
}

.introduction-hero__video-external-icon {
  @apply w-5 h-5 text-gray-400 group-hover:text-white transition-colors;
}
</style>

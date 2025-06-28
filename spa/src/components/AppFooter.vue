<template>
  <footer class="app-footer">
    <div class="app-footer__container">
      <div class="app-footer__content">
        <div class="app-footer__brand">
          <div class="app-footer__logo">
            <svg class="app-footer__logo-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z"
              />
            </svg>
          </div>
          <span class="app-footer__text">© 2025 Velox UI - RoadRunner Configuration Builder</span>
        </div>

        <div class="app-footer__links">
          <a
            href="https://docs.roadrunner.dev/docs/customization/build"
            target="_blank"
            rel="noopener noreferrer"
            class="app-footer__link"
            >Documentation</a
          >
          <a
            href="https://github.com/roadrunner-php/velox-app"
            target="_blank"
            rel="noopener noreferrer"
            class="app-footer__link"
            >GitHub</a
          >
          <a
            href="https://t.me/spiralphp/4983"
            target="_blank"
            rel="noopener noreferrer"
            class="app-footer__link"
            >Support</a
          >
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.app-footer {
  @apply bg-gray-900 border-t border-gray-800 text-gray-400 w-full;
}

.app-footer__container {
  @apply max-w-6xl mx-auto px-4 py-6;
}

.app-footer__content {
  @apply flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0;
}

.app-footer__brand {
  @apply flex items-center space-x-2;
}

.app-footer__logo {
  @apply w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded-md flex items-center justify-center;
}

.app-footer__logo-icon {
  @apply w-3 h-3 text-white;
}

.app-footer__text {
  @apply text-sm;
}

.app-footer__links {
  @apply flex items-center space-x-6 text-sm;
}

.app-footer__link {
  @apply hover:text-white transition-colors duration-200;
}
</style>

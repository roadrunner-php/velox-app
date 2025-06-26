<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-900">
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 relative overflow-hidden">
    <!-- Animated Background -->
    <div class="absolute inset-0">
      <!-- Gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-br from-slate-900/90 via-purple-900/20 to-indigo-900/90"></div>

      <!-- Animated particles -->
      <div class="absolute inset-0">
        <div
          v-for="particle in particles"
          :key="particle.id"
          class="absolute w-1 h-1 bg-blue-400/30 rounded-full animate-pulse"
          :style="{
            left: particle.x + '%',
            top: particle.y + '%',
            animationDelay: particle.delay + 's',
            animationDuration: particle.duration + 's'
          }"
        ></div>
      </div>

      <!-- Geometric lines -->
      <div class="absolute inset-0 opacity-20">
        <svg class="w-full h-full" viewBox="0 0 1200 800" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:0.3" />
              <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:0.1" />
            </linearGradient>
          </defs>
          <g class="animate-pulse">
            <line x1="0" y1="400" x2="300" y2="200" stroke="url(#lineGradient)" stroke-width="1"/>
            <line x1="900" y1="600" x2="1200" y2="300" stroke="url(#lineGradient)" stroke-width="1"/>
            <line x1="100" y1="100" x2="500" y2="400" stroke="url(#lineGradient)" stroke-width="1"/>
            <line x1="700" y1="100" x2="1100" y2="500" stroke="url(#lineGradient)" stroke-width="1"/>
          </g>
        </svg>
      </div>
    </div>

    <!-- Hero Section -->
    <section class="relative overflow-hidden">
      <!-- Background Elements -->
      <div class="absolute inset-0 bg-black/30"></div>
      <div class="absolute top-0 left-0 w-full h-full">
        <div
          class="absolute top-20 left-20 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl animate-pulse"
        ></div>
        <div
          class="absolute bottom-20 right-20 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse delay-1000"
        ></div>
        <div
          class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-blue-500/5 to-purple-500/5 rounded-full blur-3xl animate-spin-slow"
        ></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
        <div class="text-center">
          <!-- Main Headline -->
          <h1 class="text-4xl sm:text-6xl lg:text-7xl font-bold text-white mb-6 animate-fade-in-up">
            Build Your Perfect
            <span
              class="bg-gradient-to-r from-blue-400 via-purple-400 to-cyan-400 bg-clip-text text-transparent animate-gradient-x"
            >
              RoadRunner Server
            </span>
          </h1>

          <!-- Subtitle -->
          <p
            class="text-xl sm:text-2xl text-gray-300 my-16 max-w-4xl mx-auto leading-relaxed animate-fade-in-up animation-delay-200"
          >
            Create optimized, lightweight server configurations with our intelligent plugin system
            and proven presets.
            <span class="text-cyan-400 font-semibold"
            >Deploy faster, run leaner, scale better.</span
            >
          </p>

          <!-- CTA Buttons -->
          <div
            class="flex flex-col sm:flex-row gap-4 justify-center items-center animate-fade-in-up animation-delay-400"
          >
            <RouterLink
              to="/plugins"
              class="group px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold text-lg rounded-full hover:from-blue-500 hover:to-blue-600 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25 min-w-[200px] border border-blue-500/30"
            >
              <span class="flex items-center justify-center gap-2">
                🚀 Start Building
                <svg
                  class="w-5 h-5 group-hover:translate-x-1 transition-transform"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                  />
                </svg>
              </span>
            </RouterLink>

            <RouterLink
              to="/presets"
              class="group px-8 py-4 bg-transparent border-2 border-purple-500/70 text-purple-400 font-bold text-lg rounded-full hover:bg-purple-500 hover:text-white hover:border-purple-400 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-purple-500/25 min-w-[200px]"
            >
              <span class="flex items-center justify-center gap-2">
                ⚡ Quick Presets
                <svg
                  class="w-5 h-5 group-hover:translate-x-1 transition-transform"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                  />
                </svg>
              </span>
            </RouterLink>
          </div>

          <!-- Key Benefits Pills -->
          <div
            class="flex flex-wrap justify-center gap-3 mt-12 animate-fade-in-up animation-delay-600"
          >
            <span
              class="px-4 py-2 bg-gray-800/60 backdrop-blur-sm text-gray-300 rounded-full text-sm font-medium border border-gray-700/50 hover:border-gray-600/50 transition-colors"
            >
              ✅ Zero Configuration Conflicts
            </span>
            <span
              class="px-4 py-2 bg-gray-800/60 backdrop-blur-sm text-gray-300 rounded-full text-sm font-medium border border-gray-700/50 hover:border-gray-600/50 transition-colors"
            >
              ⚡ Instant Deployment
            </span>
            <span
              class="px-4 py-2 bg-gray-800/60 backdrop-blur-sm text-gray-300 rounded-full text-sm font-medium border border-gray-700/50 hover:border-gray-600/50 transition-colors"
            >
              🔧 Auto Dependency Resolution
            </span>
            <span
              class="px-4 py-2 bg-gray-800/60 backdrop-blur-sm text-gray-300 rounded-full text-sm font-medium border border-gray-700/50 hover:border-gray-600/50 transition-colors"
            >
              📦 Optimized Binaries
            </span>
          </div>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 14l-7 7m0 0l-7-7m7 7V3"
          />
        </svg>
      </div>
    </section>
  </div>

    <!-- Architecture Diagram Section -->
    <section class="py-20 bg-gradient-to-b from-gray-900 to-black relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-5">
        <div
          class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-blue-500/10 to-purple-500/10"
        ></div>
        <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
        <div
          class="absolute bottom-1/4 left-1/4 w-96 h-96 bg-purple-500/5 rounded-full blur-3xl"
        ></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">How Velox Works</h2>
          <p class="text-xl text-gray-300 max-w-3xl mx-auto">
            See how our intelligent configuration system transforms plugin selection into optimized RoadRunner deployments
          </p>
        </div>

        <!-- Process Steps -->
        <div class="grid md:grid-cols-3 gap-8 mt-16">
          <div class="text-center group">
            <div
              class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-blue-500/20"
            >
              <span class="text-2xl font-bold text-white">1</span>
            </div>
            <h3 class="text-xl font-bold text-white mb-4">Select & Configure</h3>
            <p class="text-gray-300 leading-relaxed">
              Choose plugins or presets through our intuitive interface. Dependencies are automatically resolved and conflicts detected.
            </p>
          </div>

          <div class="text-center group">
            <div
              class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-purple-500/20"
            >
              <span class="text-2xl font-bold text-white">2</span>
            </div>
            <h3 class="text-xl font-bold text-white mb-4">Generate Config</h3>
            <p class="text-gray-300 leading-relaxed">
              Our engine creates optimized TOML, JSON, or Dockerfile configurations with zero manual intervention required.
            </p>
          </div>

          <div class="text-center group">
            <div
              class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-cyan-500/20"
            >
              <span class="text-2xl font-bold text-white">3</span>
            </div>
            <h3 class="text-xl font-bold text-white mb-4">Deploy & Scale</h3>
            <p class="text-gray-300 leading-relaxed">
              Launch your lean, optimized RoadRunner server with perfect plugin compatibility and enterprise performance.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-gray-900/80 backdrop-blur-sm border-t border-gray-800/50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="text-center group">
            <div
              class="text-4xl font-bold text-blue-400 mb-2 transition-transform group-hover:scale-110 duration-300"
            >
              {{ animatedStats.plugins }}+
            </div>
            <div class="text-gray-400 font-medium">Available Plugins</div>
          </div>
          <div class="text-center group">
            <div
              class="text-4xl font-bold text-green-400 mb-2 transition-transform group-hover:scale-110 duration-300"
            >
              {{ animatedStats.presets }}+
            </div>
            <div class="text-gray-400 font-medium">Ready Presets</div>
          </div>
          <div class="text-center group">
            <div
              class="text-4xl font-bold text-purple-400 mb-2 transition-transform group-hover:scale-110 duration-300"
            >
              {{ animatedStats.deployments }}K+
            </div>
            <div class="text-gray-400 font-medium">Deployments</div>
          </div>
          <div class="text-center group">
            <div
              class="text-4xl font-bold text-cyan-400 mb-2 transition-transform group-hover:scale-110 duration-300"
            >
              {{ animatedStats.time }}%
            </div>
            <div class="text-gray-400 font-medium">Faster Setup</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Feature Comparison Section -->
    <section class="py-20 bg-gradient-to-b from-gray-900 to-black">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Choose Your Approach</h2>
          <p class="text-xl text-gray-300 max-w-3xl mx-auto">
            Whether you prefer granular control or quick deployment, we've got you covered
          </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 max-w-6xl mx-auto">
          <!-- Plugins Card -->
          <div
            class="group relative bg-gray-800/40 backdrop-blur-sm rounded-2xl shadow-2xl hover:shadow-blue-500/10 transition-all duration-500 transform hover:-translate-y-2 overflow-hidden border border-gray-700/30 hover:border-blue-500/30"
          >
            <div
              class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-blue-600"
            ></div>

            <div class="p-8">
              <div
                class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg shadow-blue-500/20"
              >
                <svg
                  class="w-8 h-8 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 11.172V5l-1-1z"
                  />
                </svg>
              </div>

              <h3 class="text-2xl font-bold text-white mb-4">Custom Plugins</h3>
              <p class="text-gray-300 mb-6 leading-relaxed">
                Hand-pick exactly what you need. Build lean, optimized servers with precise plugin
                selection and automatic dependency resolution.
              </p>

              <ul class="space-y-3 mb-8">
                <li class="flex items-center text-gray-300">
                  <span class="w-2 h-2 bg-blue-400 rounded-full mr-3 flex-shrink-0"></span>
                  Granular control over features
                </li>
                <li class="flex items-center text-gray-300">
                  <span class="w-2 h-2 bg-blue-400 rounded-full mr-3 flex-shrink-0"></span>
                  Minimal resource footprint
                </li>
                <li class="flex items-center text-gray-300">
                  <span class="w-2 h-2 bg-blue-400 rounded-full mr-3 flex-shrink-0"></span>
                  Smart dependency management
                </li>
                <li class="flex items-center text-gray-300">
                  <span class="w-2 h-2 bg-blue-400 rounded-full mr-3 flex-shrink-0"></span>
                  Perfect for custom architectures
                </li>
              </ul>

              <RouterLink
                to="/plugins"
                class="inline-flex items-center justify-center w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-500 hover:to-blue-600 transition-all duration-300 group-hover:shadow-lg group-hover:shadow-blue-500/20 border border-blue-500/30"
              >
                Explore Plugins
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                  />
                </svg>
              </RouterLink>
            </div>
          </div>

          <!-- Presets Card -->
          <div
            class="group relative bg-gray-800/40 backdrop-blur-sm rounded-2xl shadow-2xl hover:shadow-purple-500/10 transition-all duration-500 transform hover:-translate-y-2 overflow-hidden border border-gray-700/30 hover:border-purple-500/30"
          >
            <div
              class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 to-purple-600"
            ></div>

            <div class="p-8">
              <div
                class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg shadow-purple-500/20"
              >
                <svg
                  class="w-8 h-8 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z"
                  />
                </svg>
              </div>

              <h3 class="text-2xl font-bold text-white mb-4">Quick Presets</h3>
              <p class="text-gray-300 mb-6 leading-relaxed">
                Get started instantly with battle-tested configurations. Perfect for common use
                cases with enterprise-grade reliability.
              </p>

              <ul class="space-y-3 mb-8">
                <li class="flex items-center text-gray-300">
                  <span class="w-2 h-2 bg-purple-400 rounded-full mr-3 flex-shrink-0"></span>
                  Production-ready templates
                </li>
                <li class="flex items-center text-gray-300">
                  <span class="w-2 h-2 bg-purple-400 rounded-full mr-3 flex-shrink-0"></span>
                  Zero configuration needed
                </li>
                <li class="flex items-center text-gray-300">
                  <span class="w-2 h-2 bg-purple-400 rounded-full mr-3 flex-shrink-0"></span>
                  Best practices included
                </li>
                <li class="flex items-center text-gray-300">
                  <span class="w-2 h-2 bg-purple-400 rounded-full mr-3 flex-shrink-0"></span>
                  Instant deployment ready
                </li>
              </ul>

              <RouterLink
                to="/presets"
                class="inline-flex items-center justify-center w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-xl hover:from-purple-500 hover:to-purple-600 transition-all duration-300 group-hover:shadow-lg group-hover:shadow-purple-500/20 border border-purple-500/30"
              >
                Browse Presets
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                  />
                </svg>
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Benefits Section -->
    <section
      class="py-20 bg-gradient-to-br from-black via-gray-900 to-black relative overflow-hidden"
    >
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
        <div
          class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-blue-400/10 to-purple-400/10"
        ></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Why Choose Velox?</h2>
          <p class="text-xl text-gray-300 max-w-3xl mx-auto">
            Transform your RoadRunner deployment experience with intelligent automation
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div class="text-center group">
            <div
              class="w-20 h-20 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-yellow-500/20"
            >
              <svg
                class="w-10 h-10 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 10V3L4 14h7v7l9-11h-7z"
                />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-4">Lightning Fast Setup</h3>
            <p class="text-gray-300 leading-relaxed">
              Deploy production-ready RoadRunner servers in minutes, not hours. Automated dependency
              resolution eliminates configuration headaches.
            </p>
          </div>

          <div class="text-center group">
            <div
              class="w-20 h-20 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-green-500/20"
            >
              <svg
                class="w-10 h-10 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-4">Zero Conflicts</h3>
            <p class="text-gray-300 leading-relaxed">
              Intelligent conflict detection prevents incompatible plugin combinations. Our
              validation engine ensures stable, reliable configurations every time.
            </p>
          </div>

          <div class="text-center group">
            <div
              class="w-20 h-20 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-purple-500/20"
            >
              <svg
                class="w-10 h-10 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-4">Optimized Binaries</h3>
            <p class="text-gray-300 leading-relaxed">
              Generate lean, purpose-built binaries containing only what you need. Reduce memory
              footprint and improve startup performance significantly.
            </p>
          </div>

          <div class="text-center group">
            <div
              class="w-20 h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-blue-500/20"
            >
              <svg
                class="w-10 h-10 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"
                />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-4">Enterprise Ready</h3>
            <p class="text-gray-300 leading-relaxed">
              Battle-tested presets used in production by teams worldwide. Includes monitoring,
              security, and scalability features out of the box.
            </p>
          </div>

          <div class="text-center group">
            <div
              class="w-20 h-20 bg-gradient-to-br from-pink-400 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-pink-500/20"
            >
              <svg
                class="w-10 h-10 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-4">Developer Experience</h3>
            <p class="text-gray-300 leading-relaxed">
              Intuitive interface designed for developers, by developers. Clear documentation,
              helpful tooltips, and smart defaults make configuration effortless.
            </p>
          </div>

          <div class="text-center group">
            <div
              class="w-20 h-20 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-indigo-500/20"
            >
              <svg
                class="w-10 h-10 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"
                />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-4">Multi-Format Output</h3>
            <p class="text-gray-300 leading-relaxed">
              Generate configurations in TOML, JSON, or complete Dockerfiles. Perfect integration
              with your existing CI/CD pipelines and deployment workflows.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Call to Action Section -->
    <section
      class="py-20 bg-gradient-to-r from-gray-900 via-black to-gray-900 border-t border-gray-800/50"
    >
      <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
          Ready to Build Something Amazing?
        </h2>
        <p class="text-xl text-gray-300 mb-8 leading-relaxed">
          Join thousands of developers who have streamlined their RoadRunner deployments with Velox
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <RouterLink
            to="/plugins"
            class="group px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold text-lg rounded-full hover:from-blue-500 hover:to-blue-600 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25 min-w-[200px] border border-blue-500/30"
          >
            <span class="flex items-center justify-center gap-2">
              🔧 Custom Build
              <svg
                class="w-5 h-5 group-hover:translate-x-1 transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 7l5 5m0 0l-5 5m5-5H6"
                />
              </svg>
            </span>
          </RouterLink>

          <RouterLink
            to="/presets"
            class="group px-8 py-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold text-lg rounded-full hover:from-purple-500 hover:to-purple-600 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-purple-500/25 min-w-[200px] border border-purple-500/30"
          >
            <span class="flex items-center justify-center gap-2">
              ⚡ Quick Start
              <svg
                class="w-5 h-5 group-hover:translate-x-1 transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 7l5 5m0 0l-5 5m5-5H6"
                />
              </svg>
            </span>
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import AnimatedDiagram from '@/components/AnimatedDiagram.vue'

// Generate random particles for background animation
const particles = ref(
  Array.from({ length: 30 }, (_, i) => ({
    id: i,
    x: Math.random() * 100,
    y: Math.random() * 100,
    delay: Math.random() * 3,
    duration: 2 + Math.random() * 2
  }))
)

// Animated statistics
const animatedStats = ref({
  plugins: 0,
  presets: 0,
  deployments: 0,
  time: 0,
})

// Target values for animation
const targetStats = {
  plugins: 50,
  presets: 12,
  deployments: 25,
  time: 85,
}

// Animate statistics on mount
onMounted(() => {
  const duration = 2000 // 2 seconds
  const steps = 60 // 60 FPS
  const stepDuration = duration / steps

  let currentStep = 0

  const animate = () => {
    currentStep++
    const progress = currentStep / steps

    // Easing function for smooth animation
    const easeOutQuart = 1 - Math.pow(1 - progress, 4)

    animatedStats.value.plugins = Math.floor(targetStats.plugins * easeOutQuart)
    animatedStats.value.presets = Math.floor(targetStats.presets * easeOutQuart)
    animatedStats.value.deployments = Math.floor(targetStats.deployments * easeOutQuart)
    animatedStats.value.time = Math.floor(targetStats.time * easeOutQuart)

    if (currentStep < steps) {
      setTimeout(animate, stepDuration)
    }
  }

  // Start animation after a brief delay
  setTimeout(animate, 500)
})
</script>

<style scoped>
/* Custom animations */
@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes gradient-x {
  0%,
  100% {
    background-size: 200% 200%;
    background-position: left center;
  }
  50% {
    background-size: 200% 200%;
    background-position: right center;
  }
}

@keyframes spin-slow {
  from {
    transform: translate(-50%, -50%) rotate(0deg);
  }
  to {
    transform: translate(-50%, -50%) rotate(360deg);
  }
}

.animate-fade-in-up {
  animation: fade-in-up 0.8s ease-out forwards;
}

.animation-delay-200 {
  animation-delay: 0.2s;
}

.animation-delay-400 {
  animation-delay: 0.4s;
}

.animation-delay-600 {
  animation-delay: 0.6s;
}

.animate-gradient-x {
  animation: gradient-x 3s ease infinite;
  background-size: 200% 200%;
}

.animate-spin-slow {
  animation: spin-slow 20s linear infinite;
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Custom gradient backgrounds */
.bg-gradient-to-br {
  background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}

/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}

/* Custom pulse animation delays */
.delay-1000 {
  animation-delay: 1s;
}

/* Enhanced hover effects */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

.group:hover .group-hover\:translate-x-1 {
  transform: translateX(0.25rem);
}

.group:hover .group-hover\:shadow-lg {
  box-shadow:
    0 10px 15px -3px rgba(0, 0, 0, 0.2),
    0 4px 6px -2px rgba(0, 0, 0, 0.1);
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

/* Text gradient */
.bg-clip-text {
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .text-4xl {
    font-size: 2.5rem;
  }

  .text-6xl {
    font-size: 3.5rem;
  }

  .text-7xl {
    font-size: 4rem;
  }
}

/* Enhanced shadow effects for dark theme */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Glow effects for buttons */
.hover\:shadow-blue-500\/25:hover {
  box-shadow: 0 25px 50px -12px rgba(59, 130, 246, 0.25);
}

.hover\:shadow-purple-500\/25:hover {
  box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.25);
}
</style>

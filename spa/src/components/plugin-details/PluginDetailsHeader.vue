<template>
  <div
    class="bg-gradient-to-br from-gray-800/60 to-gray-900/40 border border-gray-700/50 rounded-2xl p-8 mb-8 backdrop-blur-sm"
  >
    <!-- Plugin Header with Status -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
      <!-- Left Section: Plugin Info -->
      <div class="flex-1">
        <!-- Plugin Name & Badges -->
        <div class="flex items-start gap-4 mb-4">
          <div class="flex-1">
            <!-- Badges Row -->
            <div class="flex flex-wrap gap-2 mb-4">
              <!-- Official/Community Badge -->
              <span
                class="inline-flex items-center gap-1 px-3 py-1 text-sm font-medium rounded-full border"
                :class="
                  plugin.is_official
                    ? 'bg-emerald-900/20 text-emerald-300 border-emerald-500/30'
                    : 'bg-gray-700/60 text-gray-300 border-gray-600/50'
                "
              >
                <svg
                  v-if="plugin.is_official"
                  class="w-4 h-4"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd"
                  />
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"
                  />
                </svg>
                {{ plugin.is_official ? 'Official' : 'Community' }}
              </span>

              <!-- Category Badge -->
              <span
                v-if="plugin.category"
                class="px-3 py-1 text-sm font-medium bg-blue-900/20 text-blue-300 border border-blue-500/30 rounded-full"
              >
                {{ plugin.category }}
              </span>

              <!-- Version Badge -->
              <span
                class="px-3 py-1 text-sm font-medium bg-purple-900/20 text-purple-300 border border-purple-500/30 rounded-full"
              >
                {{ plugin.version }}
              </span>

              <!-- Selection Status Badge -->
              <span
                v-if="selectionState !== 'none'"
                class="px-3 py-1 text-sm font-bold rounded-full border"
                :class="selectionStatusClasses"
              >
                {{ selectionStatusText }}
              </span>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">
              {{ plugin.name }}
            </h1>

            <!-- Description -->
            <p class="text-gray-300 text-lg leading-relaxed">
              {{ plugin.description || 'No description available' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Right Section: Actions -->
      <div class="flex flex-col gap-3 lg:min-w-[200px]">
        <!-- Primary Action Button -->
        <button
          @click="$emit('toggleSelection')"
          :disabled="isLoadingDependencies"
          class="w-full px-6 py-3 font-semibold rounded-xl transition-all duration-200 border flex items-center justify-center gap-2"
          :class="primaryActionClasses"
        >
          <div
            v-if="isLoadingDependencies"
            class="animate-spin rounded-full h-4 w-4 border-b-2 border-current"
          ></div>
          <span>{{ primaryActionText }}</span>
        </button>
      </div>
    </div>

    <div class="space-y-3">
      <a
        v-if="plugin.repository_url"
        :href="plugin.repository_url"
        target="_blank"
        rel="noopener noreferrer"
        class="flex items-center gap-3 p-4 bg-gray-900/40 rounded-lg border border-gray-700/30 hover:border-gray-600/50 transition-all duration-200 group"
      >
        <div
          class="w-10 h-10 bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:bg-blue-800/40 transition-colors"
        >
          <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
            />
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-medium text-white group-hover:text-blue-300 transition-colors">
            View Repository
          </div>
          <div class="text-sm text-gray-400 truncate">
            {{ plugin.repository_url }}
          </div>
        </div>
        <svg
          class="w-4 h-4 text-gray-400 group-hover:text-white transition-colors"
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
      </a>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Plugin } from '@/api/pluginsApi'
import type { PluginSelectionState } from '@/types/plugin'

interface Props {
  plugin: Plugin
  selectionState: PluginSelectionState
  isLoadingDependencies: boolean
}

interface Emits {
  (e: 'toggleSelection'): void

  (e: 'share'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dependencyCount = computed(() => props.plugin.dependencies?.length || 0)

const selectionStatusClasses = computed(() => {
  switch (props.selectionState) {
    case 'manual':
      return 'bg-gradient-to-r from-blue-500 to-blue-600 text-white border-blue-400/50'
    case 'dependency':
      return 'bg-gradient-to-r from-green-500 to-green-600 text-white border-green-400/50'
    case 'conflict':
      return 'bg-gradient-to-r from-red-500 to-red-600 text-white border-red-400/50'
    default:
      return ''
  }
})

const selectionStatusText = computed(() => {
  switch (props.selectionState) {
    case 'manual':
      return 'Selected'
    case 'dependency':
      return 'Auto-selected'
    case 'conflict':
      return 'Conflict'
    default:
      return ''
  }
})

const primaryActionClasses = computed(() => {
  const isSelected = props.selectionState !== 'none'

  if (isSelected) {
    return 'bg-gray-700/50 text-gray-300 hover:bg-gray-600/50 border-gray-600/50 hover:border-gray-500/70'
  }

  return 'bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-500 hover:to-blue-600 border-blue-500/50 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30'
})

const primaryActionText = computed(() => {
  if (props.isLoadingDependencies) {
    return 'Loading...'
  }

  return props.selectionState !== 'none' ? 'Deselect Plugin' : 'Select Plugin'
})
</script>

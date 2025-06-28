<template>
  <section class="bg-gray-900 border-t border-gray-800 text-gray-400 w-full">
    <div class="max-w-6xl mx-auto px-4 py-6">
      <div>
        <h2 class="text-lg font-semibold mb-6 text-white text-center">
          We are RoadRunner contributors
        </h2>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-8">
          <div class="flex items-center gap-3 text-gray-400">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-400"></div>
            <span>Loading contributors...</span>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-8">
          <div class="text-red-400 mb-4">
            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <p class="text-gray-400">{{ error }}</p>
        </div>

        <!-- Contributors Grid -->
        <div v-else-if="contributors.length > 0" class="flex flex-wrap gap-5 justify-center">
          <a
            v-for="contributor in contributors"
            :key="contributor.login"
            :href="contributor.profile_url"
            target="_blank"
            rel="noopener noreferrer"
            class="group flex flex-col items-center justify-between hover:border-gray-700 rounded-lg px-5 py-4 transition transform"
          >
            <div
              class="border-2 border-gray-400 group-hover:border-transparent rounded-full p-1 transition bg-gradient-to-br group-hover:from-blue-500 group-hover:to-purple-600"
            >
              <img
                :src="contributor.avatar_url"
                :alt="`${contributor.login} avatar`"
                class="w-12 h-12 rounded-full"
                loading="lazy"
                @error="handleImageError"
              />
            </div>

            <div class="text-center mt-3">
              <div class="text-white font-medium group-hover:text-blue-400 transition">
                {{ contributor.login }}
              </div>
              <div class="text-sm text-gray-400">
                {{ formatContributions(contributor.contributions_count) }}
              </div>
            </div>
          </a>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-8">
          <div class="text-gray-500 mb-4">
            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
          </div>
          <p class="text-gray-400">No contributors found</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axiosClient from '@/api/axiosClient'

interface Contributor {
  login: string
  avatar_url: string
  profile_url: string
  contributions_count: number | null
}

interface ContributorMeta {
  total: number
  total_contributions?: number
  top_contributor?: {
    login: string
    contributions_count: number
  }
}

interface ContributorsResponse {
  data: Contributor[]
  meta: ContributorMeta
}

const contributors = ref<Contributor[]>([])
const meta = ref<ContributorMeta | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

async function loadContributors() {
  loading.value = true
  error.value = null

  try {
    const response = await axiosClient.get<ContributorsResponse>('/contributors', {
      params: {
        per_page: 30, // Load up to 30 contributors
        page: 1,
      },
    })

    contributors.value = response.data.data
    meta.value = response.data.meta
  } catch (e: any) {
    console.error('Failed to load contributors:', e)
    error.value = e.response?.data?.message || 'Failed to load contributors'
  } finally {
    loading.value = false
  }
}

function handleImageError(event: Event) {
  const img = event.target as HTMLImageElement
  // Fallback to a generic avatar if the image fails to load
  img.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(img.alt)}&background=374151&color=ffffff&size=64`
}

function formatContributions(count: number | null): string {
  if (!count) return 'Contributor'
  if (count === 1) return '1 contribution'
  if (count < 1000) return `${count} contributions`
  if (count < 1000000) return `${(count / 1000).toFixed(1)}k contributions`
  return `${(count / 1000000).toFixed(1)}m contributions`
}

function formatNumber(num: number): string {
  if (num < 1000) return num.toString()
  if (num < 1000000) return `${(num / 1000).toFixed(1)}k`
  return `${(num / 1000000).toFixed(1)}m`
}

onMounted(() => {
  loadContributors()
})
</script>

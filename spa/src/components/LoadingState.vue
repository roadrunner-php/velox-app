<template>
  <div class="text-center py-16">
    <!-- Loading Spinner -->
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-400 mx-auto mb-6"></div>
    
    <!-- Loading Message -->
    <p class="text-slate-300 text-lg mb-2">
      {{ message }}
    </p>
    
    <!-- Optional Subtitle -->
    <p v-if="subtitle" class="text-slate-500 text-sm">
      {{ subtitle }}
    </p>

    <!-- Progress Bar (Optional) -->
    <div v-if="showProgress && progress !== null" class="mt-6 max-w-xs mx-auto">
      <div class="bg-slate-700/50 rounded-full h-2 overflow-hidden">
        <div 
          class="bg-gradient-to-r from-blue-500 to-blue-600 h-full transition-all duration-500 ease-out rounded-full"
          :style="{ width: `${Math.min(100, Math.max(0, progress))}%` }"
        ></div>
      </div>
      <div class="text-xs text-slate-400 mt-2">
        {{ Math.round(progress) }}% complete
      </div>
    </div>

    <!-- Loading Steps (Optional) -->
    <div v-if="steps.length > 0" class="mt-8 max-w-md mx-auto">
      <div class="space-y-3">
        <div 
          v-for="(step, index) in steps"
          :key="index"
          class="flex items-center gap-3 text-sm"
          :class="{
            'text-green-400': step.status === 'complete',
            'text-blue-400': step.status === 'active',
            'text-slate-500': step.status === 'pending'
          }"
        >
          <!-- Step Icon -->
          <div class="flex-shrink-0">
            <!-- Complete -->
            <svg v-if="step.status === 'complete'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <!-- Active -->
            <div v-else-if="step.status === 'active'" class="w-5 h-5 border-2 border-current rounded-full animate-spin border-t-transparent"></div>
            <!-- Pending -->
            <div v-else class="w-5 h-5 border-2 border-current rounded-full opacity-50"></div>
          </div>
          
          <!-- Step Text -->
          <span :class="{ 'font-medium': step.status === 'active' }">
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
  steps: () => []
})
</script>
<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-3 text-white">
        {{ title }}
      </h1>
      <p v-if="description" class="text-slate-300 text-lg">
        {{ description }}
      </p>
    </div>

    <!-- Breadcrumb Navigation (if provided) -->
    <nav v-if="breadcrumbs?.length" class="flex mb-6" aria-label="Breadcrumb">
      <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li v-for="(crumb, index) in breadcrumbs" :key="index" class="inline-flex items-center">
          <div v-if="index > 0" class="flex items-center">
            <svg class="w-3 h-3 text-slate-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
          </div>
          
          <RouterLink
            v-if="crumb.to && index < breadcrumbs.length - 1"
            :to="crumb.to"
            class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white transition-colors duration-200"
          >
            <component v-if="crumb.icon" :is="crumb.icon" class="w-4 h-4 mr-2" />
            {{ crumb.label }}
          </RouterLink>
          
          <span
            v-else
            class="inline-flex items-center text-sm font-medium text-slate-300"
            :class="{ 'text-white': index === breadcrumbs.length - 1 }"
          >
            <component v-if="crumb.icon" :is="crumb.icon" class="w-4 h-4 mr-2" />
            {{ crumb.label }}
          </span>
        </li>
      </ol>
    </nav>

    <!-- Action Buttons -->
    <div v-if="actions?.length" class="flex flex-wrap gap-3 mb-6">
      <button
        v-for="action in actions"
        :key="action.id"
        @click="$emit('action', action.id)"
        :disabled="action.disabled"
        :class="[
          'px-4 py-2 rounded-lg font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900',
          action.variant === 'primary' ? 
            'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 disabled:bg-slate-600' :
          action.variant === 'secondary' ?
            'bg-slate-700 text-slate-300 border border-slate-600 hover:bg-slate-600 hover:text-white focus:ring-slate-500' :
          action.variant === 'danger' ?
            'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 disabled:bg-slate-600' :
            'bg-transparent text-slate-400 hover:text-white focus:ring-slate-500'
        ]"
      >
        <span class="flex items-center gap-2">
          <component v-if="action.icon" :is="action.icon" class="w-4 h-4" />
          {{ action.label }}
          <div v-if="action.loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-current ml-1"></div>
        </span>
      </button>
    </div>

    <!-- Stats/Metrics (if provided) -->
    <div v-if="stats?.length" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="bg-slate-800/60 border border-slate-600/50 rounded-lg p-4 text-center backdrop-blur-sm"
      >
        <div class="text-2xl font-bold mb-1" :class="stat.color || 'text-white'">
          {{ stat.value }}
        </div>
        <div class="text-sm text-slate-400">{{ stat.label }}</div>
      </div>
    </div>

    <!-- Alert/Notice (if provided) -->
    <div
      v-if="alert"
      class="p-4 rounded-lg border mb-6"
      :class="{
        'bg-blue-900/20 border-blue-500/30 text-blue-200': alert.type === 'info',
        'bg-green-900/20 border-green-500/30 text-green-200': alert.type === 'success',
        'bg-yellow-900/20 border-yellow-500/30 text-yellow-200': alert.type === 'warning',
        'bg-red-900/20 border-red-500/30 text-red-200': alert.type === 'error'
      }"
    >
      <div class="flex items-start gap-3">
        <div class="flex-shrink-0 mt-0.5">
          <!-- Info Icon -->
          <svg v-if="alert.type === 'info'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <!-- Success Icon -->
          <svg v-else-if="alert.type === 'success'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <!-- Warning Icon -->
          <svg v-else-if="alert.type === 'warning'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <!-- Error Icon -->
          <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        
        <div class="flex-1">
          <h4 v-if="alert.title" class="font-medium mb-1">{{ alert.title }}</h4>
          <p class="text-sm">{{ alert.message }}</p>
        </div>
        
        <button
          v-if="alert.dismissible"
          @click="$emit('dismissAlert')"
          class="flex-shrink-0 text-current opacity-70 hover:opacity-100 transition-opacity"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Main Content Slot -->
    <div class="flex-1">
      <slot></slot>
    </div>

    <!-- Footer Content Slot -->
    <div v-if="$slots.footer" class="mt-8 pt-6 border-t border-slate-700/50">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'

interface Breadcrumb {
  label: string
  to?: string
  icon?: any
}

interface Action {
  id: string
  label: string
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost'
  icon?: any
  disabled?: boolean
  loading?: boolean
}

interface Stat {
  label: string
  value: string | number
  color?: string
}

interface Alert {
  type: 'info' | 'success' | 'warning' | 'error'
  title?: string
  message: string
  dismissible?: boolean
}

interface Props {
  title: string
  description?: string
  breadcrumbs?: Breadcrumb[]
  actions?: Action[]
  stats?: Stat[]
  alert?: Alert
}

interface Emits {
  (e: 'action', actionId: string): void
  (e: 'dismissAlert'): void
}

defineProps<Props>()
defineEmits<Emits>()
</script>

<style scoped>
/* Breadcrumb hover effects */
nav a:hover {
  text-decoration: none;
}

/* Stats animation */
.grid > div {
  animation: fadeInUp 0.5s ease-out;
  animation-fill-mode: both;
}

.grid > div:nth-child(1) { animation-delay: 0.1s; }
.grid > div:nth-child(2) { animation-delay: 0.2s; }
.grid > div:nth-child(3) { animation-delay: 0.3s; }
.grid > div:nth-child(4) { animation-delay: 0.4s; }

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Button hover effects */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: scale(0.98);
}

/* Loading animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Alert animation */
.p-4.rounded-lg.border {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Focus styles */
button:focus-visible {
  outline: none;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .grid-cols-2 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }
  
  .flex-wrap {
    flex-direction: column;
  }
  
  .flex-wrap > button {
    width: 100%;
    justify-content: center;
  }
}
</style>

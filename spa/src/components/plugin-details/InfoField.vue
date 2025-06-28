<template>
  <div class="info-field">
    <!-- Icon -->
    <div 
      class="info-field-icon-wrapper"
      :class="iconBackgroundClasses"
    >
      <component :is="iconComponent" class="info-field-icon" :class="iconClasses" />
    </div>

    <!-- Content -->
    <div class="info-field-content">
      <div class="info-field-label">{{ label }}</div>
      <div 
        class="info-field-value"
        :class="{ 'info-field-value--monospace': monospace, 'info-field-value--break-all': breakAll }"
      >
        <slot>{{ value }}</slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, h } from 'vue'

interface Props {
  label: string
  value: string
  icon?: string
  highlight?: boolean
  monospace?: boolean
  breakAll?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  icon: 'info',
  highlight: false,
  monospace: false,
  breakAll: false
})

const iconBackgroundClasses = computed(() => {
  if (props.highlight) {
    return 'info-field-icon-wrapper--highlight'
  }
  return 'info-field-icon-wrapper--default'
})

const iconClasses = computed(() => {
  if (props.highlight) {
    return 'info-field-icon--highlight'
  }
  return 'info-field-icon--default'
})

const iconComponent = computed(() => {
  const iconPaths = {
    info: "M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
    puzzle: "M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z",
    tag: "M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z",
    folder: "M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z",
    source: "M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
    user: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
    repo: "M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10",
    name: "M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2h4a1 1 0 011 1v4a1 1 0 01-1 1h-1v9a2 2 0 01-2 2H6a2 2 0 01-2-2v-9H3a1 1 0 01-1-1V5a1 1 0 011-1h4z",
    git: "M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z",
    replace: "M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
  }

  const iconPath = iconPaths[props.icon as keyof typeof iconPaths] || iconPaths.info

  return () => h('svg', {
    fill: 'none',
    stroke: 'currentColor',
    viewBox: '0 0 24 24',
    'stroke-width': '2'
  }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      d: iconPath
    })
  ])
})
</script>

<style scoped>
.info-field {
  @apply flex items-start gap-3;
}

.info-field-icon-wrapper {
  @apply w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5;
}

.info-field-icon-wrapper--highlight {
  @apply bg-green-900/30 border border-green-500/30;
}

.info-field-icon-wrapper--default {
  @apply bg-gray-800/60 border border-gray-700/50;
}

.info-field-icon {
  @apply w-4 h-4;
}

.info-field-icon--highlight {
  @apply text-green-400;
}

.info-field-icon--default {
  @apply text-gray-400;
}

.info-field-content {
  @apply flex-1 min-w-0;
}

.info-field-label {
  @apply text-sm font-medium text-gray-400 mb-1;
}

.info-field-value {
  @apply text-white font-medium;
}

.info-field-value--monospace {
  @apply font-mono text-sm;
}

.info-field-value--break-all {
  @apply break-all;
}
</style>

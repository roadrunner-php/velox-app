<template>
  <button
    @click="$router.back()"
    class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline mb-4"
  >
    ← Back
  </button>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

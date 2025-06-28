<template>
  <div class="background-pattern">
    <div class="gradient-overlay"></div>
    <div class="floating-blob floating-blob--blue"></div>
    <div class="floating-blob floating-blob--purple"></div>
  </div>
</template>

<style scoped>
.background-pattern {
  @apply absolute inset-0 opacity-5;
}

.gradient-overlay {
  @apply absolute top-0 left-0 w-full h-full bg-gradient-to-br from-blue-500/10 to-purple-500/10;
}

.floating-blob {
  @apply absolute w-96 h-96 rounded-full blur-3xl;
}

.floating-blob--blue {
  @apply top-1/4 right-1/4 bg-blue-500/5;
}

.floating-blob--purple {
  @apply bottom-1/4 left-1/4 bg-purple-500/5;
}
</style>

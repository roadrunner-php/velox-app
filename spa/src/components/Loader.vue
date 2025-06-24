<template>
  <div :style="overlayStyle" v-if="visible" @click="onOverlayClick">
    <div v-for="(phrase, index) in phrases" :key="index" :style="getPhraseStyle(phrase)">
      {{ phrase.text }}
    </div>

    <button :style="closeButtonStyle" @click="hide = true">
      <div :style="lineOneStyle"></div>
      <div :style="lineTwoStyle"></div>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue'
import type { CSSProperties } from 'vue'

const visible = ref(true)
const hide = ref(false)

const phrases = [
  { text: 'SELECT', animation: 'select', style: { top: '350px', left: '400px' }, delay: 0 },
  { text: 'CONFIGURE', animation: 'configure', style: { top: '350px', left: '530px' }, delay: 0 },
  { text: 'DEPLOY', animation: 'deploy', style: { top: '520px', left: '630px' }, delay: 0 },
  { text: 'VELOX', animation: 'velox', style: { top: '380px', left: '550px' }, delay: 0 },
]

watch(hide, (val) => {
  if (val) setTimeout(() => (visible.value = false), 600)
})

const overlayStyle = computed<CSSProperties>(() => ({
  position: 'fixed',
  top: 0,
  left: 0,
  width: '100vw',
  height: '100vh',
  backgroundColor: 'rgba(18, 0, 0, 0.8)',
  backdropFilter: 'blur(6px)',
  zIndex: 1000,
  opacity: hide.value ? 0 : 1,
  transition: 'opacity 0.6s ease',
}))

const closeButtonStyle: CSSProperties = {
  position: 'absolute',
  top: '40px',
  right: '40px',
  width: '40px',
  height: '40px',
  backgroundColor: '#222',
  border: '1px solid #aaa',
  borderRadius: '6px',
  cursor: 'pointer',
  opacity: 1,
  transition: 'opacity 0.5s ease',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
}

const lineOneStyle: CSSProperties = {
  position: 'absolute',
  width: '16px',
  height: '2px',
  backgroundColor: '#fff',
  transform: 'rotate(45deg)',
}

const lineTwoStyle: CSSProperties = {
  position: 'absolute',
  width: '16px',
  height: '2px',
  backgroundColor: '#fff',
  transform: 'rotate(-45deg)',
}

function getPhraseStyle(phrase: any): CSSProperties {
  return {
    position: 'absolute',
    fontSize: '4rem',
    fontWeight: 'bold',
    color: '#ddddaa',
    opacity: 0,
    animation: `${phrase.animation} 12s ease ${phrase.delay}s forwards infinite`,
    ...phrase.style,
  }
}

function onOverlayClick(event: MouseEvent) {
  const target = event.target as HTMLElement
  if (target.closest('button')) return
  hide.value = true
}
</script>

<style>
@keyframes select {
  0% {
    transform: translateX(-300px);
    opacity: 0;
  }
  10% {
    transform: translateX(0);
    opacity: 1;
  }
  20% {
    transform: translateX(300px);
    opacity: 0;
  }
}

@keyframes configure {
  0% {
    transform: translateY(-300px);
    opacity: 0;
  }
  15% {
    transform: translateY(-300px);
    opacity: 0;
  }
  25% {
    transform: translateY(0);
    opacity: 1;
  }
  35% {
    transform: translateY(300px);
    opacity: 0;
  }
}

@keyframes deploy {
  0% {
    transform: translateX(300px);
    opacity: 0;
  }
  30% {
    transform: translateX(300px);
    opacity: 0;
  }
  40% {
    transform: translateX(0);
    opacity: 1;
  }
  50% {
    transform: translateX(-300px);
    opacity: 0;
  }
}

@keyframes velox {
  0% {
    transform: translateX(-300px) scale(0.5);
    opacity: 0;
  }
  50% {
    transform: translateX(-300px) scale(0.5);
    opacity: 0;
  }
  60% {
    transform: translateX(0) scale(1.4);
    opacity: 1;
  }
  80% {
    transform: translateY(0) scale(1.2);
    opacity: 1;
  }
  90% {
    transform: translateY(0px) scale(1.2);
    opacity: 1;
  }
  96% {
    transform: translateY(0px) scale(1.2);
    opacity: 1;
  }
  100% {
    transform: translateX(-300px) translateY(0px) scale(0.5);
    opacity: 0;
  }
}
</style>

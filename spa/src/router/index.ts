import { createRouter, createWebHistory } from 'vue-router'
import { navigationDirection } from '@/navigation'

const routes = [
  { path: '/', component: () => import('@/views/MainView.vue'), meta: { depth: 1 } },
  { path: '/introduction', component: () => import('@/views/IntroductionView.vue'), meta: { depth: 2 } },
  { path: '/plugins', component: () => import('@/views/PluginsList.vue'), meta: { depth: 2 } },
  { path: '/presets', component: () => import('@/views/PresetsList.vue'), meta: { depth: 2 } },
  {
    path: '/plugins/:name',
    component: () => import('@/views/PluginDetails.vue'),
    meta: { depth: 3 },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const toDepth = to.meta.depth || 0
  const fromDepth = from.meta.depth || 0
  navigationDirection.value = toDepth > fromDepth ? 'forward' : 'back'
  next()
})

export default router

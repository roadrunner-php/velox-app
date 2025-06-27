<template>
  <div class="space-y-6">
    <!-- Bulk Actions Bar -->
    <div 
      v-if="selectedItems.length > 0"
      class="flex items-center justify-between p-4 bg-blue-900/20 border border-blue-500/30 rounded-lg backdrop-blur-sm"
    >
      <div class="flex items-center gap-4">
        <span class="text-blue-200 font-medium">
          {{ selectedItems.length }} {{ itemType }}{{ selectedItems.length === 1 ? '' : 's' }} selected
        </span>
        
        <!-- Quick Actions -->
        <div class="flex gap-2">
          <button
            v-for="action in quickActions"
            :key="action.id"
            @click="$emit('bulkAction', action.id, selectedItems)"
            :disabled="action.disabled"
            class="px-3 py-1.5 text-sm font-medium bg-blue-600/80 text-white rounded-lg hover:bg-blue-600 disabled:bg-slate-600 disabled:text-slate-400 transition-all duration-200"
          >
            <span class="flex items-center gap-1.5">
              <component v-if="action.icon" :is="action.icon" class="w-3 h-3" />
              {{ action.label }}
            </span>
          </button>
        </div>
      </div>

      <button
        @click="$emit('clearSelection')"
        class="text-blue-200 hover:text-white text-sm font-medium transition-colors"
      >
        Clear Selection
      </button>
    </div>

    <!-- Grid/List Toggle -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <!-- View Toggle -->
        <div class="flex bg-slate-800/60 border border-slate-600 rounded-lg p-1">
          <button
            @click="$emit('update:viewMode', 'grid')"
            :class="[
              'px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200',
              viewMode === 'grid' 
                ? 'bg-blue-600 text-white shadow-sm' 
                : 'text-slate-300 hover:text-white hover:bg-slate-700'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
          </button>
          <button
            @click="$emit('update:viewMode', 'list')"
            :class="[
              'px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200',
              viewMode === 'list' 
                ? 'bg-blue-600 text-white shadow-sm' 
                : 'text-slate-300 hover:text-white hover:bg-slate-700'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
          </button>
        </div>

        <!-- Sort Dropdown -->
        <div class="relative" ref="sortDropdownRef">
          <button
            @click="showSortDropdown = !showSortDropdown"
            class="flex items-center gap-2 px-3 py-2 bg-slate-800/60 border border-slate-600 text-slate-300 rounded-lg hover:bg-slate-700 hover:text-white transition-all duration-200"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
            </svg>
            <span class="text-sm font-medium">
              Sort: {{ currentSortLabel }}
            </span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Sort Dropdown -->
          <transition name="dropdown">
            <div
              v-if="showSortDropdown"
              class="absolute top-full left-0 mt-2 w-48 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-10 py-1"
            >
              <button
                v-for="option in sortOptions"
                :key="option.value"
                @click="selectSort(option.value)"
                class="w-full text-left px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors flex items-center justify-between"
              >
                <span>{{ option.label }}</span>
                <svg v-if="sortBy === option.value" class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </transition>
        </div>

        <!-- Items per page -->
        <div v-if="showPagination" class="flex items-center gap-2 text-sm text-slate-300">
          <span>Show:</span>
          <select
            :value="itemsPerPage"
            @change="$emit('update:itemsPerPage', parseInt(($event.target as HTMLSelectElement).value))"
            class="bg-slate-800/60 border border-slate-600 text-white rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option v-for="option in perPageOptions" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
        </div>
      </div>

      <!-- Item Count -->
      <div class="text-sm text-slate-400">
        <span v-if="totalItems !== undefined">
          Showing {{ Math.min(itemsPerPage, totalItems) }} of {{ totalItems }} {{ itemType }}{{ totalItems === 1 ? '' : 's' }}
        </span>
        <span v-else>
          {{ items.length }} {{ itemType }}{{ items.length === 1 ? '' : 's' }}
        </span>
      </div>
    </div>

    <!-- Items Grid/List -->
    <div 
      :class="[
        'transition-all duration-300',
        viewMode === 'grid' 
          ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6' 
          : 'space-y-4'
      ]"
    >
      <slot 
        name="items" 
        :items="items" 
        :view-mode="viewMode"
        :selected-items="selectedItems"
      ></slot>
    </div>

    <!-- Empty State -->
    <slot v-if="items.length === 0" name="empty"></slot>

    <!-- Pagination -->
    <div v-if="showPagination && totalPages > 1" class="flex items-center justify-center gap-2 mt-8">
      <button
        @click="$emit('update:currentPage', Math.max(1, currentPage - 1))"
        :disabled="currentPage <= 1"
        class="px-3 py-2 text-sm font-medium bg-slate-800/60 border border-slate-600 text-slate-300 rounded-lg hover:bg-slate-700 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
      >
        Previous
      </button>

      <div class="flex gap-1">
        <button
          v-for="page in visiblePages"
          :key="page"
          @click="$emit('update:currentPage', page)"
          :class="[
            'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
            page === currentPage
              ? 'bg-blue-600 text-white'
              : 'bg-slate-800/60 border border-slate-600 text-slate-300 hover:bg-slate-700 hover:text-white'
          ]"
        >
          {{ page }}
        </button>
      </div>

      <button
        @click="$emit('update:currentPage', Math.min(totalPages, currentPage + 1))"
        :disabled="currentPage >= totalPages"
        class="px-3 py-2 text-sm font-medium bg-slate-800/60 border border-slate-600 text-slate-300 rounded-lg hover:bg-slate-700 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

interface SortOption {
  value: string
  label: string
}

interface BulkAction {
  id: string
  label: string
  icon?: any
  disabled?: boolean
}

interface Props {
  items: any[]
  selectedItems?: any[]
  viewMode?: 'grid' | 'list'
  sortBy?: string
  sortOptions?: SortOption[]
  itemType?: string
  quickActions?: BulkAction[]
  showPagination?: boolean
  currentPage?: number
  totalPages?: number
  itemsPerPage?: number
  totalItems?: number
  perPageOptions?: number[]
}

interface Emits {
  (e: 'update:viewMode', mode: 'grid' | 'list'): void
  (e: 'update:sortBy', sortBy: string): void
  (e: 'update:currentPage', page: number): void
  (e: 'update:itemsPerPage', count: number): void
  (e: 'bulkAction', actionId: string, items: any[]): void
  (e: 'clearSelection'): void
}

const props = withDefaults(defineProps<Props>(), {
  selectedItems: () => [],
  viewMode: 'grid',
  sortBy: 'name',
  sortOptions: () => [
    { value: 'name', label: 'Name (A-Z)' },
    { value: 'name-desc', label: 'Name (Z-A)' },
    { value: 'updated', label: 'Recently Updated' },
    { value: 'created', label: 'Recently Created' }
  ],
  itemType: 'item',
  quickActions: () => [],
  showPagination: false,
  currentPage: 1,
  totalPages: 1,
  itemsPerPage: 12,
  perPageOptions: () => [12, 24, 48, 96]
})

const emit = defineEmits<Emits>()

const showSortDropdown = ref(false)
const sortDropdownRef = ref<HTMLElement | null>(null)

const currentSortLabel = computed(() => {
  const option = props.sortOptions.find(opt => opt.value === props.sortBy)
  return option?.label || 'Name (A-Z)'
})

const visiblePages = computed(() => {
  const pages: number[] = []
  const total = props.totalPages
  const current = props.currentPage
  
  // Always show first page
  if (total > 0) pages.push(1)
  
  // Add pages around current
  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
    if (!pages.includes(i)) pages.push(i)
  }
  
  // Always show last page
  if (total > 1 && !pages.includes(total)) pages.push(total)
  
  return pages.sort((a, b) => a - b)
})

function selectSort(value: string) {
  emit('update:sortBy', value)
  showSortDropdown.value = false
}

function handleClickOutside(event: MouseEvent) {
  if (sortDropdownRef.value && !sortDropdownRef.value.contains(event.target as Node)) {
    showSortDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* Dropdown transition */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Enhanced shadow for dropdown */
.shadow-xl {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
}

/* Backdrop blur */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Focus styles */
button:focus-visible,
select:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Grid to list transition */
.grid {
  transition: all 0.3s ease;
}

.space-y-4 > * + * {
  transition: all 0.3s ease;
}

/* Button hover effects */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: scale(0.98);
}

/* Pagination styling */
.gap-1 button {
  min-width: 40px;
  justify-content: center;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .flex.items-center.justify-between {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .flex.items-center.gap-4 {
    flex-wrap: wrap;
    gap: 0.5rem;
  }
}
</style>

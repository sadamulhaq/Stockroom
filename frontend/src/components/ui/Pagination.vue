<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps<{
  currentPage: number
  lastPage: number
  total: number
  perPage: number
}>()

const emit = defineEmits<{ (e: 'page-change', page: number): void }>()

const pages = computed(() => {
  const arr: (number | string)[] = []
  const last = props.lastPage
  const cur = props.currentPage
  if (last <= 7) {
    for (let i = 1; i <= last; i++) arr.push(i)
  } else {
    arr.push(1)
    if (cur > 3) arr.push('...')
    for (let i = Math.max(2, cur - 1); i <= Math.min(last - 1, cur + 1); i++) arr.push(i)
    if (cur < last - 2) arr.push('...')
    arr.push(last)
  }
  return arr
})

const from = computed(() => (props.currentPage - 1) * props.perPage + 1)
const to = computed(() => Math.min(props.currentPage * props.perPage, props.total))
</script>

<template>
  <div v-if="lastPage > 1" class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <p class="text-sm text-gray-700">
        Showing <span class="font-medium">{{ from }}</span> to <span class="font-medium">{{ to }}</span> of
        <span class="font-medium">{{ total }}</span> results
      </p>
      <nav class="isolate inline-flex -space-x-px rounded-md shadow-xs">
        <button
          :disabled="currentPage === 1"
          class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 disabled:opacity-50"
          @click="emit('page-change', currentPage - 1)"
        >
          <ChevronLeftIcon class="h-5 w-5" />
        </button>
        <template v-for="(p, i) in pages" :key="i">
          <span v-if="p === '...'" class="relative inline-flex items-center px-4 py-2 text-sm text-gray-700 ring-1 ring-gray-300 ring-inset">...</span>
          <button
            v-else
            :class="p === currentPage ? 'bg-primary-600 text-white' : 'text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50'"
            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold"
            @click="emit('page-change', p as number)"
          >
            {{ p }}
          </button>
        </template>
        <button
          :disabled="currentPage === lastPage"
          class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 disabled:opacity-50"
          @click="emit('page-change', currentPage + 1)"
        >
          <ChevronRightIcon class="h-5 w-5" />
        </button>
      </nav>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  status: string
  type?: 'order' | 'stock' | 'movement'
}>()

const colorMap: Record<string, string> = {
  draft: 'bg-gray-100 text-gray-700',
  pending: 'bg-warning-50 text-warning-600',
  approved: 'bg-blue-100 text-blue-700',
  received: 'bg-success-50 text-success-600',
  cancelled: 'bg-danger-50 text-danger-600',
  low: 'bg-danger-50 text-danger-600',
  ok: 'bg-success-50 text-success-600',
  out: 'bg-danger-50 text-danger-700',
  in: 'bg-success-50 text-success-600',
  adjustment: 'bg-blue-100 text-blue-700',
  return: 'bg-warning-50 text-warning-600',
  active: 'bg-success-50 text-success-600',
  inactive: 'bg-gray-100 text-gray-600',
}

const classes = computed(() => colorMap[props.status] ?? 'bg-gray-100 text-gray-700')
</script>

<template>
  <span :class="classes" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">
    {{ status }}
  </span>
</template>

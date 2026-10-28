<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { CheckCircleIcon, ExclamationTriangleIcon, XCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps<{
  message: string
  type?: 'success' | 'error' | 'warning'
  duration?: number
}>()

const emit = defineEmits<{ (e: 'close'): void }>()
const visible = ref(true)

onMounted(() => {
  setTimeout(() => {
    visible.value = false
    emit('close')
  }, props.duration ?? 3000)
})
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="translate-y-2 opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-2 opacity-0"
  >
    <div
      v-if="visible"
      :class="{
        'bg-success-50 text-success-600': type === 'success' || !type,
        'bg-danger-50 text-danger-600': type === 'error',
        'bg-warning-50 text-warning-600': type === 'warning',
      }"
      class="pointer-events-auto flex items-center gap-3 rounded-lg p-4 shadow-lg"
    >
      <CheckCircleIcon v-if="type === 'success' || !type" class="h-5 w-5 shrink-0" />
      <XCircleIcon v-else-if="type === 'error'" class="h-5 w-5 shrink-0" />
      <ExclamationTriangleIcon v-else class="h-5 w-5 shrink-0" />
      <span class="text-sm font-medium">{{ message }}</span>
      <button class="ml-auto" @click="visible = false; emit('close')">
        <XMarkIcon class="h-4 w-4" />
      </button>
    </div>
  </Transition>
</template>

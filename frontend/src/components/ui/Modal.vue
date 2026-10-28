<script setup lang="ts">
import { XMarkIcon } from '@heroicons/vue/24/outline'

defineProps<{ show: boolean; title?: string; maxWidth?: string }>()
const emit = defineEmits<{ (e: 'close'): void }>()
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center px-4">
          <div class="fixed inset-0 bg-black/50" @click="emit('close')" />
          <div
            :class="maxWidth ?? 'max-w-lg'"
            class="relative z-10 w-full rounded-lg bg-white p-6 shadow-xl"
          >
            <div v-if="title" class="mb-4 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
              <button class="text-gray-400 hover:text-gray-600" @click="emit('close')">
                <XMarkIcon class="h-5 w-5" />
              </button>
            </div>
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

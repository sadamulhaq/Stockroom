<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterView, useRouter } from 'vue-router'
import Sidebar from './Sidebar.vue'
import Toast from '@/components/ui/Toast.vue'
import { useAuthStore } from '@/stores/auth'
import { ArrowRightOnRectangleIcon, UserCircleIcon, Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const router = useRouter()
const sidebarOpen = ref(false)

onMounted(() => {
  if (authStore.isAuthenticated) {
    authStore.fetchMe()
  }
})

const toasts = ref<{ id: number; message: string; type: 'success' | 'error' | 'warning' }[]>([])
let toastId = 0

function addToast(message: string, type: 'success' | 'error' | 'warning' = 'success') {
  toasts.value.push({ id: ++toastId, message, type })
}

function removeToast(id: number) {
  toasts.value = toasts.value.filter((t) => t.id !== id)
}

async function handleLogout() {
  await authStore.logout()
  router.replace('/login')
}

// Provide toast globally
import { provide } from 'vue'
provide('addToast', addToast)
</script>

<template>
  <div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Mobile sidebar overlay -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 lg:hidden" @click="sidebarOpen = false">
      <div class="absolute inset-0 bg-black/50" />
    </div>
    <div
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      class="fixed inset-y-0 left-0 z-50 transition-transform lg:relative lg:z-0 lg:translate-x-0"
    >
      <Sidebar />
    </div>

    <div class="flex flex-1 flex-col overflow-hidden">
      <!-- Top bar -->
      <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-6">
        <button class="lg:hidden" @click="sidebarOpen = !sidebarOpen">
          <Bars3Icon v-if="!sidebarOpen" class="h-6 w-6 text-gray-600" />
          <XMarkIcon v-else class="h-6 w-6 text-gray-600" />
        </button>
        <h1 class="text-lg font-semibold text-gray-800">StockFlow — Inventory Management</h1>
        <div class="flex items-center gap-4">
          <div v-if="authStore.user" class="flex items-center gap-2 text-sm text-gray-600">
            <UserCircleIcon class="h-6 w-6" />
            <span>{{ authStore.user.name }}</span>
          </div>
          <button
            class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100"
            @click="handleLogout"
          >
            <ArrowRightOnRectangleIcon class="h-5 w-5" />
            Logout
          </button>
        </div>
      </header>

      <!-- Main content -->
      <main class="flex-1 overflow-y-auto p-6">
        <RouterView />
      </main>
    </div>

    <!-- Toast container -->
    <div class="pointer-events-none fixed right-4 top-4 z-[100] flex flex-col gap-2">
      <Toast
        v-for="toast in toasts"
        :key="toast.id"
        :message="toast.message"
        :type="toast.type"
        @close="removeToast(toast.id)"
      />
    </div>
  </div>
</template>

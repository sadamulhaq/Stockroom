<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { CubeIcon } from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({ email: '', password: '' })
const errors = ref<Record<string, string[]>>({})
const loading = ref(false)

async function handleLogin() {
  loading.value = true
  errors.value = {}
  try {
    await authStore.login(form.value.email, form.value.password)
    router.replace('/')
  } catch (err: any) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      errors.value = { email: [err.response?.data?.message ?? 'Login failed'] }
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-md">
      <div class="mb-8 text-center">
        <CubeIcon class="mx-auto h-12 w-12 text-primary-600" />
        <h1 class="mt-4 text-3xl font-bold text-gray-900">StockFlow</h1>
        <p class="mt-1 text-sm text-gray-500">Sign in to your account</p>
      </div>
      <form class="rounded-xl bg-white p-8 shadow-sm ring-1 ring-gray-200" @submit.prevent="handleLogin">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
            />
            <p v-if="errors.email" class="mt-1 text-xs text-danger-500">{{ errors.email[0] }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input
              v-model="form.password"
              type="password"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
            />
            <p v-if="errors.password" class="mt-1 text-xs text-danger-500">{{ errors.password[0] }}</p>
          </div>
        </div>
        <button
          type="submit"
          :disabled="loading"
          class="mt-6 w-full rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50"
        >
          {{ loading ? 'Signing in...' : 'Sign In' }}
        </button>
        <p class="mt-4 text-center text-sm text-gray-500">
          Don't have an account?
          <RouterLink to="/register" class="font-medium text-primary-600 hover:text-primary-500">Register</RouterLink>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/client'
import type { Supplier } from '@/types'

const route = useRoute()
const router = useRouter()
const addToast = inject<(msg: string, type: 'success' | 'error' | 'warning') => void>('addToast')

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const saving = ref(false)
const errors = ref<Record<string, string[]>>({})

const form = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  country: '',
  contact_person: '',
  is_active: true,
})

onMounted(async () => {
  if (isEdit.value) {
    loading.value = true
    try {
      const { data } = await api.get(`/suppliers/${route.params.id}`)
      const s: Supplier = data.data ?? data
      form.value = {
        name: s.name,
        email: s.email ?? '',
        phone: s.phone ?? '',
        address: s.address ?? '',
        city: s.city ?? '',
        country: s.country ?? '',
        contact_person: s.contact_person ?? '',
        is_active: s.is_active,
      }
    } finally {
      loading.value = false
    }
  }
})

async function handleSubmit() {
  saving.value = true
  errors.value = {}
  try {
    if (isEdit.value) {
      await api.put(`/suppliers/${route.params.id}`, form.value)
      addToast?.('Supplier updated', 'success')
    } else {
      await api.post('/suppliers', form.value)
      addToast?.('Supplier created', 'success')
    }
    router.push('/suppliers')
  } catch (err: any) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors
    else addToast?.(err.response?.data?.message ?? 'Failed to save', 'error')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="mx-auto max-w-2xl">
    <h2 class="mb-6 text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Supplier' : 'Create Supplier' }}</h2>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600" />
    </div>

    <form v-else class="space-y-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200" @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700">Name *</label>
          <input v-model="form.name" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
          <p v-if="errors.name" class="mt-1 text-xs text-danger-500">{{ errors.name[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Contact Person</label>
          <input v-model="form.contact_person" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        </div>
      </div>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input v-model="form.email" type="email" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
          <p v-if="errors.email" class="mt-1 text-xs text-danger-500">{{ errors.email[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Phone</label>
          <input v-model="form.phone" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Address</label>
        <textarea v-model="form.address" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
      </div>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700">City</label>
          <input v-model="form.city" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Country</label>
          <input v-model="form.country" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        </div>
      </div>
      <div class="flex items-center gap-2">
        <input v-model="form.is_active" type="checkbox" id="supplier_active" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
        <label for="supplier_active" class="text-sm text-gray-700">Active</label>
      </div>
      <div class="flex justify-end gap-3">
        <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="router.back()">Cancel</button>
        <button type="submit" :disabled="saving" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50">
          {{ saving ? 'Saving...' : isEdit ? 'Update' : 'Create' }}
        </button>
      </div>
    </form>
  </div>
</template>

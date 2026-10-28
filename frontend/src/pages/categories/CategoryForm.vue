<script setup lang="ts">
import { ref, onMounted, computed, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/client'
import type { Category } from '@/types'

const route = useRoute()
const router = useRouter()
const addToast = inject<(msg: string, type: 'success' | 'error' | 'warning') => void>('addToast')

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const saving = ref(false)
const errors = ref<Record<string, string[]>>({})
const parentCategories = ref<Category[]>([])

const form = ref({ name: '', description: '', parent_id: '' as string, is_active: true })

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await api.get('/categories', { params: { per_page: 200 } })
    parentCategories.value = data.data

    if (isEdit.value) {
      const { data: catData } = await api.get(`/categories/${route.params.id}`)
      const c: Category = catData.data ?? catData
      form.value = {
        name: c.name,
        description: c.description ?? '',
        parent_id: c.parent_id ? String(c.parent_id) : '',
        is_active: c.is_active,
      }
    }
  } finally {
    loading.value = false
  }
})

async function handleSubmit() {
  saving.value = true
  errors.value = {}
  try {
    const payload = {
      ...form.value,
      parent_id: form.value.parent_id ? Number(form.value.parent_id) : null,
    }
    if (isEdit.value) {
      await api.put(`/categories/${route.params.id}`, payload)
      addToast?.('Category updated', 'success')
    } else {
      await api.post('/categories', payload)
      addToast?.('Category created', 'success')
    }
    router.push('/categories')
  } catch (err: any) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors
    else addToast?.(err.response?.data?.message ?? 'Failed to save', 'error')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="mx-auto max-w-xl">
    <h2 class="mb-6 text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Category' : 'Create Category' }}</h2>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600" />
    </div>

    <form v-else class="space-y-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200" @submit.prevent="handleSubmit">
      <div>
        <label class="block text-sm font-medium text-gray-700">Name *</label>
        <input v-model="form.name" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        <p v-if="errors.name" class="mt-1 text-xs text-danger-500">{{ errors.name[0] }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Parent Category</label>
        <select v-model="form.parent_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none">
          <option value="">None (Top Level)</option>
          <option v-for="c in parentCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <div class="flex items-center gap-2">
        <input v-model="form.is_active" type="checkbox" id="is_active" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
        <label for="is_active" class="text-sm text-gray-700">Active</label>
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

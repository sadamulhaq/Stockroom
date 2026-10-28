<script setup lang="ts">
import { ref, onMounted, computed, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/client'
import type { Product, Category, Supplier } from '@/types'

const route = useRoute()
const router = useRouter()
const addToast = inject<(msg: string, type: 'success' | 'error' | 'warning') => void>('addToast')

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const saving = ref(false)
const errors = ref<Record<string, string[]>>({})
const categories = ref<Category[]>([])
const suppliers = ref<Supplier[]>([])

const form = ref({
  name: '',
  sku: '',
  description: '',
  category_id: '',
  supplier_id: '',
  unit_price: 0,
  cost_price: 0,
  quantity: 0,
  min_stock_level: 0,
  max_stock_level: 0,
  unit: 'pcs',
  location: '',
  barcode: '',
})

const units = ['pcs', 'kg', 'liters', 'boxes', 'meters']

onMounted(async () => {
  loading.value = true
  try {
    const [catRes, supRes] = await Promise.all([
      api.get('/categories', { params: { per_page: 100 } }),
      api.get('/suppliers', { params: { per_page: 100 } }),
    ])
    categories.value = catRes.data.data
    suppliers.value = supRes.data.data

    if (isEdit.value) {
      const { data } = await api.get(`/products/${route.params.id}`)
      const p: Product = data.data ?? data
      form.value = {
        name: p.name,
        sku: p.sku,
        description: p.description ?? '',
        category_id: String(p.category_id),
        supplier_id: p.supplier_id ? String(p.supplier_id) : '',
        unit_price: p.unit_price,
        cost_price: p.cost_price,
        quantity: p.quantity,
        min_stock_level: p.min_stock_level,
        max_stock_level: p.max_stock_level,
        unit: p.unit,
        location: p.location ?? '',
        barcode: p.barcode ?? '',
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
      category_id: Number(form.value.category_id),
      supplier_id: form.value.supplier_id ? Number(form.value.supplier_id) : null,
    }
    if (isEdit.value) {
      await api.put(`/products/${route.params.id}`, payload)
      addToast?.('Product updated successfully', 'success')
    } else {
      await api.post('/products', payload)
      addToast?.('Product created successfully', 'success')
    }
    router.push('/products')
  } catch (err: any) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      addToast?.(err.response?.data?.message ?? 'Failed to save product', 'error')
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="mx-auto max-w-3xl">
    <h2 class="mb-6 text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Product' : 'Create Product' }}</h2>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600" />
    </div>

    <form v-else class="space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200" @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700">Name *</label>
          <input v-model="form.name" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
          <p v-if="errors.name" class="mt-1 text-xs text-danger-500">{{ errors.name[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">SKU *</label>
          <input v-model="form.sku" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
          <p v-if="errors.sku" class="mt-1 text-xs text-danger-500">{{ errors.sku[0] }}</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700">Category *</label>
          <select v-model="form.category_id" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none">
            <option value="">Select category</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <p v-if="errors.category_id" class="mt-1 text-xs text-danger-500">{{ errors.category_id[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Supplier</label>
          <select v-model="form.supplier_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none">
            <option value="">No supplier</option>
            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
          <label class="block text-sm font-medium text-gray-700">Unit Price *</label>
          <input v-model.number="form.unit_price" type="number" step="0.01" min="0" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
          <p v-if="errors.unit_price" class="mt-1 text-xs text-danger-500">{{ errors.unit_price[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Cost Price *</label>
          <input v-model.number="form.cost_price" type="number" step="0.01" min="0" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
          <p v-if="errors.cost_price" class="mt-1 text-xs text-danger-500">{{ errors.cost_price[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Unit</label>
          <select v-model="form.unit" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none">
            <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div v-if="!isEdit">
          <label class="block text-sm font-medium text-gray-700">Initial Quantity</label>
          <input v-model.number="form.quantity" type="number" min="0" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Min Stock Level</label>
          <input v-model.number="form.min_stock_level" type="number" min="0" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Max Stock Level</label>
          <input v-model.number="form.max_stock_level" type="number" min="0" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700">Location</label>
          <input v-model="form.location" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Barcode</label>
          <input v-model="form.barcode" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        </div>
      </div>

      <div class="flex justify-end gap-3">
        <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="router.back()">Cancel</button>
        <button type="submit" :disabled="saving" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50">
          {{ saving ? 'Saving...' : isEdit ? 'Update Product' : 'Create Product' }}
        </button>
      </div>
    </form>
  </div>
</template>

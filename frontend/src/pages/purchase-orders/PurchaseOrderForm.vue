<script setup lang="ts">
import { ref, onMounted, computed, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/client'
import type { Supplier, Product } from '@/types'
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const addToast = inject<(msg: string, type: 'success' | 'error' | 'warning') => void>('addToast')

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const saving = ref(false)
const errors = ref<Record<string, string[]>>({})
const suppliers = ref<Supplier[]>([])
const products = ref<Product[]>([])

const fmt = (v: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(v)

interface LineItem {
  product_id: string
  quantity: number
  unit_price: number
}

const form = ref({
  supplier_id: '',
  expected_date: '',
  notes: '',
  items: [{ product_id: '', quantity: 1, unit_price: 0 }] as LineItem[],
})

const subtotal = computed(() =>
  form.value.items.reduce((sum, i) => sum + i.quantity * i.unit_price, 0),
)
const tax = computed(() => subtotal.value * 0.1)
const total = computed(() => subtotal.value + tax.value)

function addItem() {
  form.value.items.push({ product_id: '', quantity: 1, unit_price: 0 })
}

function removeItem(idx: number) {
  form.value.items.splice(idx, 1)
}

function onProductChange(idx: number) {
  const pid = form.value.items[idx].product_id
  const prod = products.value.find((p) => String(p.id) === pid)
  if (prod) {
    form.value.items[idx].unit_price = prod.cost_price
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const [sRes, pRes] = await Promise.all([
      api.get('/suppliers', { params: { per_page: 100 } }),
      api.get('/products', { params: { per_page: 200 } }),
    ])
    suppliers.value = sRes.data.data
    products.value = pRes.data.data

    if (isEdit.value) {
      const { data } = await api.get(`/purchase-orders/${route.params.id}`)
      const o = data.data ?? data
      form.value = {
        supplier_id: String(o.supplier_id),
        expected_date: o.expected_date ?? '',
        notes: o.notes ?? '',
        items: o.items?.map((i: any) => ({
          product_id: String(i.product_id),
          quantity: i.quantity,
          unit_price: i.unit_price,
        })) ?? [{ product_id: '', quantity: 1, unit_price: 0 }],
      }
    }
  } finally {
    loading.value = false
  }
})

async function handleSubmit(status: 'draft' | 'pending' = 'draft') {
  saving.value = true
  errors.value = {}
  try {
    const payload = {
      supplier_id: Number(form.value.supplier_id),
      expected_date: form.value.expected_date || null,
      notes: form.value.notes || null,
      status,
      items: form.value.items.map((i) => ({
        product_id: Number(i.product_id),
        quantity: i.quantity,
        unit_price: i.unit_price,
      })),
    }
    if (isEdit.value) {
      await api.put(`/purchase-orders/${route.params.id}`, payload)
      addToast?.('Order updated', 'success')
    } else {
      await api.post('/purchase-orders', payload)
      addToast?.('Order created', 'success')
    }
    router.push('/purchase-orders')
  } catch (err: any) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors
    else addToast?.(err.response?.data?.message ?? 'Failed to save', 'error')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="mx-auto max-w-4xl">
    <h2 class="mb-6 text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Purchase Order' : 'New Purchase Order' }}</h2>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600" />
    </div>

    <form v-else class="space-y-6" @submit.prevent="handleSubmit('draft')">
      <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <label class="block text-sm font-medium text-gray-700">Supplier *</label>
            <select v-model="form.supplier_id" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none">
              <option value="">Select supplier</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
            <p v-if="errors.supplier_id" class="mt-1 text-xs text-danger-500">{{ errors.supplier_id[0] }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Expected Date</label>
            <input v-model="form.expected_date" type="date" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <input v-model="form.notes" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
          </div>
        </div>
      </div>

      <!-- Line Items -->
      <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Line Items</h3>
          <button type="button" class="inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:text-primary-500" @click="addItem">
            <PlusIcon class="h-4 w-4" /> Add Item
          </button>
        </div>
        <div class="space-y-3">
          <div v-for="(item, idx) in form.items" :key="idx" class="grid grid-cols-12 gap-3 items-end">
            <div class="col-span-5">
              <label v-if="idx === 0" class="block text-xs font-medium text-gray-500">Product</label>
              <select
                v-model="item.product_id"
                required
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
                @change="onProductChange(idx)"
              >
                <option value="">Select product</option>
                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
              </select>
            </div>
            <div class="col-span-2">
              <label v-if="idx === 0" class="block text-xs font-medium text-gray-500">Quantity</label>
              <input v-model.number="item.quantity" type="number" min="1" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div class="col-span-2">
              <label v-if="idx === 0" class="block text-xs font-medium text-gray-500">Unit Price</label>
              <input v-model.number="item.unit_price" type="number" step="0.01" min="0" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div class="col-span-2 text-right text-sm font-medium text-gray-900">
              <label v-if="idx === 0" class="block text-xs font-medium text-gray-500">Total</label>
              {{ fmt(item.quantity * item.unit_price) }}
            </div>
            <div class="col-span-1">
              <button v-if="form.items.length > 1" type="button" class="text-gray-400 hover:text-danger-600" @click="removeItem(idx)">
                <TrashIcon class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Totals -->
        <div class="mt-6 border-t border-gray-200 pt-4 text-right">
          <div class="space-y-1 text-sm">
            <div class="flex justify-end gap-8"><span class="text-gray-500">Subtotal:</span> <span class="font-medium">{{ fmt(subtotal) }}</span></div>
            <div class="flex justify-end gap-8"><span class="text-gray-500">Tax (10%):</span> <span class="font-medium">{{ fmt(tax) }}</span></div>
            <div class="flex justify-end gap-8 text-base"><span class="font-semibold text-gray-900">Total:</span> <span class="font-bold text-gray-900">{{ fmt(total) }}</span></div>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3">
        <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="router.back()">Cancel</button>
        <button type="submit" :disabled="saving" class="rounded-lg border border-primary-600 px-4 py-2 text-sm font-semibold text-primary-600 hover:bg-primary-50 disabled:opacity-50">
          Save as Draft
        </button>
        <button type="button" :disabled="saving" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50" @click="handleSubmit('pending')">
          Submit Order
        </button>
      </div>
    </form>
  </div>
</template>

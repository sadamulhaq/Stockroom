<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@/api/client'
import type { Supplier, Product, PurchaseOrder } from '@/types'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import dayjs from 'dayjs'
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const addToast = inject<(msg: string, type: 'success' | 'error' | 'warning') => void>('addToast')

const supplier = ref<Supplier | null>(null)
const products = ref<Product[]>([])
const orders = ref<PurchaseOrder[]>([])
const loading = ref(true)

const fmt = (v: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(v)

onMounted(async () => {
  try {
    const [sRes, pRes, oRes] = await Promise.all([
      api.get(`/suppliers/${route.params.id}`),
      api.get('/products', { params: { supplier_id: route.params.id, per_page: 50 } }),
      api.get('/purchase-orders', { params: { supplier_id: route.params.id, per_page: 20 } }),
    ])
    supplier.value = sRes.data.data ?? sRes.data
    products.value = pRes.data.data
    orders.value = oRes.data.data
  } finally {
    loading.value = false
  }
})

async function deleteSupplier() {
  if (!confirm('Delete this supplier?')) return
  try {
    await api.delete(`/suppliers/${route.params.id}`)
    addToast?.('Supplier deleted', 'success')
    router.push('/suppliers')
  } catch (err: any) {
    addToast?.(err.response?.data?.message ?? 'Failed to delete', 'error')
  }
}
</script>

<template>
  <div>
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600" />
    </div>

    <template v-else-if="supplier">
      <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-gray-900">{{ supplier.name }}</h2>
        <div class="flex gap-2">
          <RouterLink
            :to="`/suppliers/${supplier.id}/edit`"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            <PencilIcon class="h-4 w-4" /> Edit
          </RouterLink>
          <button
            class="inline-flex items-center gap-2 rounded-lg border border-danger-500 px-4 py-2 text-sm font-medium text-danger-600 hover:bg-danger-50"
            @click="deleteSupplier"
          >
            <TrashIcon class="h-4 w-4" /> Delete
          </button>
        </div>
      </div>

      <!-- Info -->
      <div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Supplier Information</h3>
        <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
          <div>
            <dt class="text-gray-500">Contact Person</dt>
            <dd class="font-medium text-gray-900">{{ supplier.contact_person ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">Email</dt>
            <dd class="font-medium text-gray-900">{{ supplier.email ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">Phone</dt>
            <dd class="font-medium text-gray-900">{{ supplier.phone ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">Address</dt>
            <dd class="font-medium text-gray-900">{{ supplier.address ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">City</dt>
            <dd class="font-medium text-gray-900">{{ supplier.city ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">Country</dt>
            <dd class="font-medium text-gray-900">{{ supplier.country ?? '—' }}</dd>
          </div>
        </dl>
      </div>

      <!-- Products -->
      <div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Products ({{ products.length }})</h3>
        <div v-if="!products.length" class="text-sm text-gray-400">No products from this supplier</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
                <th class="px-4 py-2">SKU</th>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Quantity</th>
                <th class="px-4 py-2">Unit Price</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="p in products" :key="p.id" class="hover:bg-gray-50">
                <td class="px-4 py-2 font-mono text-xs text-gray-600">{{ p.sku }}</td>
                <td class="px-4 py-2">
                  <RouterLink :to="`/products/${p.id}`" class="font-medium text-primary-600 hover:text-primary-500">{{ p.name }}</RouterLink>
                </td>
                <td class="px-4 py-2 text-gray-600">{{ p.quantity }} {{ p.unit }}</td>
                <td class="px-4 py-2 text-gray-600">{{ fmt(p.unit_price) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Purchase Orders -->
      <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Purchase Orders ({{ orders.length }})</h3>
        <div v-if="!orders.length" class="text-sm text-gray-400">No purchase orders for this supplier</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
                <th class="px-4 py-2">Order #</th>
                <th class="px-4 py-2">Total</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="o in orders" :key="o.id" class="hover:bg-gray-50">
                <td class="px-4 py-2">
                  <RouterLink :to="`/purchase-orders/${o.id}`" class="font-medium text-primary-600 hover:text-primary-500">{{ o.order_number }}</RouterLink>
                </td>
                <td class="px-4 py-2 text-gray-600">{{ fmt(o.total) }}</td>
                <td class="px-4 py-2"><StatusBadge :status="o.status" /></td>
                <td class="px-4 py-2 text-gray-600">{{ dayjs(o.created_at).format('MMM D, YYYY') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

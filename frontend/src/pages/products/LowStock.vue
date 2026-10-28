<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/client'
import type { Product } from '@/types'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const products = ref<Product[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await api.get('/products', { params: { stock_status: 'low', per_page: 50 } })
    products.value = data.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <div class="mb-6 flex items-center gap-3">
      <ExclamationTriangleIcon class="h-7 w-7 text-warning-500" />
      <h2 class="text-2xl font-bold text-gray-900">Low Stock Alerts</h2>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600" />
    </div>

    <div v-else-if="!products.length" class="rounded-xl bg-white p-10 text-center shadow-sm ring-1 ring-gray-200">
      <p class="text-gray-500">No low stock items. Everything is well stocked!</p>
    </div>

    <div v-else class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
            <th class="px-4 py-3">SKU</th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Category</th>
            <th class="px-4 py-3">Current Qty</th>
            <th class="px-4 py-3">Min Level</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="p in products" :key="p.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ p.sku }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ p.name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ p.category?.name ?? '—' }}</td>
            <td class="px-4 py-3 font-semibold text-danger-600">{{ p.quantity }} {{ p.unit }}</td>
            <td class="px-4 py-3 text-gray-600">{{ p.min_stock_level }}</td>
            <td class="px-4 py-3">
              <StatusBadge :status="p.quantity <= 0 ? 'out' : 'low'" />
            </td>
            <td class="px-4 py-3">
              <RouterLink :to="`/products/${p.id}`" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                View →
              </RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

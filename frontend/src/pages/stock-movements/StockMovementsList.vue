<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import api, { stripEmpty } from '@/api/client'
import type { StockMovement, Product, PaginatedResponse } from '@/types'
import Pagination from '@/components/ui/Pagination.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import dayjs from 'dayjs'

const movements = ref<StockMovement[]>([])
const meta = ref({ current_page: 1, last_page: 1, per_page: 20, total: 0 })
const products = ref<Product[]>([])
const loading = ref(true)

const productFilter = ref('')
const typeFilter = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const page = ref(1)

async function fetchMovements() {
  loading.value = true
  try {
    const { data } = await api.get<PaginatedResponse<StockMovement>>('/stock-movements', {
      params: stripEmpty({
        product_id: productFilter.value,
        type: typeFilter.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        page: page.value,
        per_page: 20,
      }),
    })
    movements.value = data.data
    meta.value = { current_page: data.current_page, last_page: data.last_page, per_page: data.per_page, total: data.total }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  const { data } = await api.get('/products', { params: { per_page: 200 } })
  products.value = data.data
  fetchMovements()
})

watch([productFilter, typeFilter, dateFrom, dateTo], () => {
  page.value = 1
  fetchMovements()
})
</script>

<template>
  <div>
    <h2 class="mb-6 text-2xl font-bold text-gray-900">Stock Movements</h2>

    <!-- Filters -->
    <div class="mb-4 flex flex-wrap gap-3">
      <select
        v-model="productFilter"
        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
      >
        <option value="">All Products</option>
        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
      </select>
      <select
        v-model="typeFilter"
        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
      >
        <option value="">All Types</option>
        <option value="in">In</option>
        <option value="out">Out</option>
        <option value="adjustment">Adjustment</option>
        <option value="return">Return</option>
      </select>
      <input
        v-model="dateFrom"
        type="date"
        placeholder="From"
        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
      />
      <input
        v-model="dateTo"
        type="date"
        placeholder="To"
        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
      />
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
            <th class="px-4 py-3">Date</th>
            <th class="px-4 py-3">Product</th>
            <th class="px-4 py-3">Type</th>
            <th class="px-4 py-3">Quantity</th>
            <th class="px-4 py-3">Reason</th>
            <th class="px-4 py-3">User</th>
            <th class="px-4 py-3">Reference</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading">
            <td colspan="7" class="px-4 py-10 text-center text-gray-400">Loading...</td>
          </tr>
          <tr v-else-if="!movements.length">
            <td colspan="7" class="px-4 py-10 text-center text-gray-400">No movements found</td>
          </tr>
          <tr v-for="m in movements" :key="m.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-gray-600">{{ dayjs(m.created_at).format('MMM D, YYYY HH:mm') }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ m.product?.name ?? `#${m.product_id}` }}</td>
            <td class="px-4 py-3"><StatusBadge :status="m.type" /></td>
            <td class="px-4 py-3" :class="m.type === 'out' ? 'text-danger-600 font-semibold' : 'text-success-600 font-semibold'">
              {{ m.type === 'out' ? '-' : '+' }}{{ m.quantity }}
            </td>
            <td class="px-4 py-3 text-gray-600">{{ m.reason ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ m.user?.name ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-500 text-xs">
              {{ m.reference_type ? `${m.reference_type} #${m.reference_id}` : '—' }}
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination
        :current-page="meta.current_page"
        :last-page="meta.last_page"
        :total="meta.total"
        :per-page="meta.per_page"
        @page-change="(p: number) => { page = p; fetchMovements() }"
      />
    </div>
  </div>
</template>

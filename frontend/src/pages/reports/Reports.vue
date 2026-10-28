<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api/client'
import MovementChart from '@/components/charts/MovementChart.vue'
import StockValueChart from '@/components/charts/StockValueChart.vue'

const activeTab = ref('summary')
const loading = ref(true)

const fmt = (v: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(v)

const tabs = [
  { key: 'summary', label: 'Inventory Summary' },
  { key: 'movements', label: 'Stock Movements' },
  { key: 'top', label: 'Top Products' },
  { key: 'orders', label: 'Purchase Orders' },
]

// Inventory Summary
const summaryData = ref({
  total_products: 0,
  total_value: 0,
  by_category: [] as { category: string; products: number; value: number }[],
})

// Stock Movements
const movementPeriod = ref('30')
const movementStats = ref({ in: 0, out: 0, adjustment: 0, return: 0 })
const movementChartLabels = ref<string[]>([])
const movementChartIn = ref<number[]>([])
const movementChartOut = ref<number[]>([])

// Top Products
const topByValue = ref<{ name: string; sku: string; value: number }[]>([])
const topByMovement = ref<{ name: string; sku: string; movements: number }[]>([])

// Purchase Orders
const orderStats = ref({ draft: 0, pending: 0, approved: 0, received: 0, cancelled: 0, total_spend: 0 })

onMounted(async () => {
  await loadReports()
})

async function loadReports() {
  loading.value = true
  try {
    const { data } = await api.get('/reports', { params: { period: movementPeriod.value } })
    const r = data.data ?? data
    summaryData.value = r.summary ?? summaryData.value
    movementStats.value = r.movement_stats ?? movementStats.value
    movementChartLabels.value = r.movement_chart?.labels ?? []
    movementChartIn.value = r.movement_chart?.in ?? []
    movementChartOut.value = r.movement_chart?.out ?? []
    topByValue.value = r.top_by_value ?? []
    topByMovement.value = r.top_by_movement ?? []
    orderStats.value = r.order_stats ?? orderStats.value
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <h2 class="mb-6 text-2xl font-bold text-gray-900">Reports</h2>

    <!-- Tabs -->
    <div class="mb-6 flex gap-1 overflow-x-auto rounded-lg bg-gray-100 p-1">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :class="activeTab === tab.key ? 'bg-white shadow-sm text-primary-700' : 'text-gray-600 hover:text-gray-900'"
        class="rounded-md px-4 py-2 text-sm font-medium transition"
        @click="activeTab = tab.key"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600" />
    </div>

    <template v-else>
      <!-- Inventory Summary -->
      <div v-if="activeTab === 'summary'" class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm text-gray-500">Total Products</p>
            <p class="text-3xl font-bold text-gray-900">{{ summaryData.total_products }}</p>
          </div>
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm text-gray-500">Total Inventory Value</p>
            <p class="text-3xl font-bold text-gray-900">{{ fmt(summaryData.total_value) }}</p>
          </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">By Category</h3>
          <StockValueChart :data="summaryData.by_category.map(c => ({ category: c.category, value: c.value }))" />
          <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
                  <th class="px-4 py-2">Category</th>
                  <th class="px-4 py-2">Products</th>
                  <th class="px-4 py-2">Value</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="c in summaryData.by_category" :key="c.category">
                  <td class="px-4 py-2 font-medium text-gray-900">{{ c.category }}</td>
                  <td class="px-4 py-2 text-gray-600">{{ c.products }}</td>
                  <td class="px-4 py-2 text-gray-600">{{ fmt(c.value) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Stock Movements -->
      <div v-else-if="activeTab === 'movements'" class="space-y-6">
        <div class="flex items-center gap-3">
          <label class="text-sm text-gray-600">Period:</label>
          <select
            v-model="movementPeriod"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
            @change="loadReports()"
          >
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
          <div class="rounded-xl bg-success-50 p-4 text-center">
            <p class="text-sm text-gray-500">Stock In</p>
            <p class="text-2xl font-bold text-success-600">{{ movementStats.in }}</p>
          </div>
          <div class="rounded-xl bg-danger-50 p-4 text-center">
            <p class="text-sm text-gray-500">Stock Out</p>
            <p class="text-2xl font-bold text-danger-600">{{ movementStats.out }}</p>
          </div>
          <div class="rounded-xl bg-blue-50 p-4 text-center">
            <p class="text-sm text-gray-500">Adjustments</p>
            <p class="text-2xl font-bold text-blue-600">{{ movementStats.adjustment }}</p>
          </div>
          <div class="rounded-xl bg-warning-50 p-4 text-center">
            <p class="text-sm text-gray-500">Returns</p>
            <p class="text-2xl font-bold text-warning-600">{{ movementStats.return }}</p>
          </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">Movement Trend</h3>
          <MovementChart :labels="movementChartLabels" :in-data="movementChartIn" :out-data="movementChartOut" />
        </div>
      </div>

      <!-- Top Products -->
      <div v-else-if="activeTab === 'top'" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">Top by Stock Value</h3>
          <div v-if="!topByValue.length" class="text-sm text-gray-400">No data</div>
          <table v-else class="min-w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
                <th class="px-4 py-2">#</th>
                <th class="px-4 py-2">Product</th>
                <th class="px-4 py-2">Value</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(p, i) in topByValue" :key="p.sku">
                <td class="px-4 py-2 text-gray-500">{{ i + 1 }}</td>
                <td class="px-4 py-2 font-medium text-gray-900">{{ p.name }}</td>
                <td class="px-4 py-2 text-gray-600">{{ fmt(p.value) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">Top by Movement Frequency</h3>
          <div v-if="!topByMovement.length" class="text-sm text-gray-400">No data</div>
          <table v-else class="min-w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
                <th class="px-4 py-2">#</th>
                <th class="px-4 py-2">Product</th>
                <th class="px-4 py-2">Movements</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(p, i) in topByMovement" :key="p.sku">
                <td class="px-4 py-2 text-gray-500">{{ i + 1 }}</td>
                <td class="px-4 py-2 font-medium text-gray-900">{{ p.name }}</td>
                <td class="px-4 py-2 text-gray-600">{{ p.movements }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Purchase Orders -->
      <div v-else-if="activeTab === 'orders'" class="space-y-6">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
          <div class="rounded-xl bg-gray-50 p-4 text-center">
            <p class="text-xs text-gray-500">Draft</p>
            <p class="text-xl font-bold text-gray-700">{{ orderStats.draft }}</p>
          </div>
          <div class="rounded-xl bg-warning-50 p-4 text-center">
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-xl font-bold text-warning-600">{{ orderStats.pending }}</p>
          </div>
          <div class="rounded-xl bg-blue-50 p-4 text-center">
            <p class="text-xs text-gray-500">Approved</p>
            <p class="text-xl font-bold text-blue-600">{{ orderStats.approved }}</p>
          </div>
          <div class="rounded-xl bg-success-50 p-4 text-center">
            <p class="text-xs text-gray-500">Received</p>
            <p class="text-xl font-bold text-success-600">{{ orderStats.received }}</p>
          </div>
          <div class="rounded-xl bg-danger-50 p-4 text-center">
            <p class="text-xs text-gray-500">Cancelled</p>
            <p class="text-xl font-bold text-danger-600">{{ orderStats.cancelled }}</p>
          </div>
          <div class="rounded-xl bg-primary-50 p-4 text-center">
            <p class="text-xs text-gray-500">Total Spend</p>
            <p class="text-xl font-bold text-primary-700">{{ fmt(orderStats.total_spend) }}</p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

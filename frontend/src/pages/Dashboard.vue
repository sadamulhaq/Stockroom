<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/client'
import type { DashboardStats } from '@/types'
import StockValueChart from '@/components/charts/StockValueChart.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import dayjs from 'dayjs'
import {
  CubeIcon,
  TagIcon,
  TruckIcon,
  CurrencyDollarIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline'

const stats = ref<DashboardStats | null>(null)
const loading = ref(true)

const fmt = (v: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(v)

onMounted(async () => {
  try {
    const { data } = await api.get('/dashboard')
    stats.value = data.data ?? data
  } finally {
    loading.value = false
  }
})

const statCards = [
  { key: 'total_products', label: 'Total Products', icon: CubeIcon, color: 'bg-primary-50 text-primary-600' },
  { key: 'total_categories', label: 'Categories', icon: TagIcon, color: 'bg-blue-50 text-blue-600' },
  { key: 'total_suppliers', label: 'Suppliers', icon: TruckIcon, color: 'bg-purple-50 text-purple-600' },
  { key: 'total_stock_value', label: 'Stock Value', icon: CurrencyDollarIcon, color: 'bg-green-50 text-green-600', isCurrency: true },
  { key: 'low_stock_count', label: 'Low Stock Alerts', icon: ExclamationTriangleIcon, color: 'bg-warning-50 text-warning-600' },
  { key: 'out_of_stock_count', label: 'Out of Stock', icon: XCircleIcon, color: 'bg-danger-50 text-danger-600' },
]
</script>

<template>
  <div>
    <h2 class="mb-6 text-2xl font-bold text-gray-900">Dashboard</h2>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600" />
    </div>

    <template v-else-if="stats">
      <!-- Stat Cards -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div
          v-for="card in statCards"
          :key="card.key"
          class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200"
        >
          <div class="flex items-center gap-3">
            <div :class="card.color" class="rounded-lg p-2.5">
              <component :is="card.icon" class="h-6 w-6" />
            </div>
            <div>
              <p class="text-sm text-gray-500">{{ card.label }}</p>
              <p class="text-xl font-bold text-gray-900">
                {{ card.isCurrency ? fmt((stats as any)[card.key]) : (stats as any)[card.key] }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts -->
      <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">Stock Value by Category</h3>
          <StockValueChart :data="stats.stock_by_category" />
        </div>

        <!-- Low Stock Alerts -->
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Low Stock Alerts</h3>
            <RouterLink to="/products/low-stock" class="text-sm text-primary-600 hover:text-primary-500">View all</RouterLink>
          </div>
          <div v-if="stats.recent_movements.length" class="space-y-3">
            <div
              v-for="m in stats.recent_movements.slice(0, 5)"
              :key="m.id"
              class="flex items-center justify-between rounded-lg border border-gray-100 p-3"
            >
              <div>
                <p class="text-sm font-medium text-gray-900">{{ m.product?.name ?? `Product #${m.product_id}` }}</p>
                <p class="text-xs text-gray-500">{{ m.reason ?? m.type }}</p>
              </div>
              <StatusBadge :status="m.type" />
            </div>
          </div>
          <p v-else class="text-sm text-gray-400">No recent movements</p>
        </div>
      </div>

      <!-- Recent Stock Movements -->
      <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Recent Stock Movements</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Product</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Quantity</th>
                <th class="px-4 py-3">Reason</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="m in stats.recent_movements" :key="m.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-600">{{ dayjs(m.created_at).format('MMM D, YYYY HH:mm') }}</td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ m.product?.name ?? `#${m.product_id}` }}</td>
                <td class="px-4 py-3"><StatusBadge :status="m.type" /></td>
                <td class="px-4 py-3" :class="m.type === 'out' ? 'text-danger-600' : 'text-success-600'">
                  {{ m.type === 'out' ? '-' : '+' }}{{ m.quantity }}
                </td>
                <td class="px-4 py-3 text-gray-600">{{ m.reason ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Purchase Orders -->
      <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Recent Purchase Orders</h3>
          <RouterLink to="/purchase-orders" class="text-sm text-primary-600 hover:text-primary-500">View all</RouterLink>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
                <th class="px-4 py-3">Order #</th>
                <th class="px-4 py-3">Supplier</th>
                <th class="px-4 py-3">Total</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="po in stats.recent_purchase_orders" :key="po.id" class="hover:bg-gray-50">
                <td class="px-4 py-3">
                  <RouterLink :to="`/purchase-orders/${po.id}`" class="font-medium text-primary-600 hover:text-primary-500">
                    {{ po.order_number }}
                  </RouterLink>
                </td>
                <td class="px-4 py-3 text-gray-900">{{ po.supplier?.name ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-900">{{ fmt(po.total) }}</td>
                <td class="px-4 py-3"><StatusBadge :status="po.status" /></td>
                <td class="px-4 py-3 text-gray-600">{{ dayjs(po.created_at).format('MMM D, YYYY') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

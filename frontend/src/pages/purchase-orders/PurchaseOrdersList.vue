<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api, { stripEmpty } from '@/api/client'
import type { PurchaseOrder, PaginatedResponse } from '@/types'
import Pagination from '@/components/ui/Pagination.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import dayjs from 'dayjs'
import { PlusIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const orders = ref<PurchaseOrder[]>([])
const meta = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
const loading = ref(true)
const statusFilter = ref('')
const page = ref(1)

const fmt = (v: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(v)

const tabs = [
  { label: 'All', value: '' },
  { label: 'Draft', value: 'draft' },
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'approved' },
  { label: 'Received', value: 'received' },
  { label: 'Cancelled', value: 'cancelled' },
]

async function fetchOrders() {
  loading.value = true
  try {
    const { data } = await api.get<PaginatedResponse<PurchaseOrder>>('/purchase-orders', {
      params: stripEmpty({ status: statusFilter.value, page: page.value, per_page: 15 }),
    })
    orders.value = data.data
    meta.value = { current_page: data.current_page, last_page: data.last_page, per_page: data.per_page, total: data.total }
  } finally {
    loading.value = false
  }
}

onMounted(fetchOrders)
watch(statusFilter, () => { page.value = 1; fetchOrders() })
</script>

<template>
  <div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <h2 class="text-2xl font-bold text-gray-900">Purchase Orders</h2>
      <router-link
        to="/purchase-orders/create"
        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
      >
        <PlusIcon class="h-5 w-5" /> New Order
      </router-link>
    </div>

    <!-- Status Tabs -->
    <div class="mb-4 flex gap-1 overflow-x-auto rounded-lg bg-gray-100 p-1">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        :class="statusFilter === tab.value ? 'bg-white shadow-sm text-primary-700' : 'text-gray-600 hover:text-gray-900'"
        class="rounded-md px-4 py-2 text-sm font-medium transition"
        @click="statusFilter = tab.value"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
            <th class="px-4 py-3">Order #</th>
            <th class="px-4 py-3">Supplier</th>
            <th class="px-4 py-3">Items</th>
            <th class="px-4 py-3">Total</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading">
            <td colspan="6" class="px-4 py-10 text-center text-gray-400">Loading...</td>
          </tr>
          <tr v-else-if="!orders.length">
            <td colspan="6" class="px-4 py-10 text-center text-gray-400">No orders found</td>
          </tr>
          <tr
            v-for="o in orders"
            :key="o.id"
            class="cursor-pointer hover:bg-gray-50"
            @click="router.push(`/purchase-orders/${o.id}`)"
          >
            <td class="px-4 py-3 font-medium text-primary-600">{{ o.order_number }}</td>
            <td class="px-4 py-3 text-gray-900">{{ o.supplier?.name ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ (o as any).items_count ?? o.items?.length ?? 0 }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ fmt(o.total) }}</td>
            <td class="px-4 py-3"><StatusBadge :status="o.status" /></td>
            <td class="px-4 py-3 text-gray-600">{{ dayjs(o.created_at).format('MMM D, YYYY') }}</td>
          </tr>
        </tbody>
      </table>
      <Pagination
        :current-page="meta.current_page"
        :last-page="meta.last_page"
        :total="meta.total"
        :per-page="meta.per_page"
        @page-change="(p: number) => { page = p; fetchOrders() }"
      />
    </div>
  </div>
</template>

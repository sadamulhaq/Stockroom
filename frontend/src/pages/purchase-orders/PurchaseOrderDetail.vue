<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/client'
import type { PurchaseOrder } from '@/types'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import dayjs from 'dayjs'
import { PencilIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const addToast = inject<(msg: string, type: 'success' | 'error' | 'warning') => void>('addToast')

const order = ref<PurchaseOrder | null>(null)
const loading = ref(true)
const updating = ref(false)

const fmt = (v: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(v)

onMounted(async () => {
  try {
    const { data } = await api.get(`/purchase-orders/${route.params.id}`)
    order.value = data.data ?? data
  } finally {
    loading.value = false
  }
})

const nextStatus: Record<string, string> = {
  draft: 'pending',
  pending: 'approved',
  approved: 'received',
}

const nextLabel: Record<string, string> = {
  draft: 'Submit for Approval',
  pending: 'Approve Order',
  approved: 'Mark as Received',
}

async function updateStatus(status: string) {
  if (status === 'received' && !confirm('Mark this order as received? Stock will be updated automatically.')) return
  updating.value = true
  try {
    const { data } = await api.patch(`/purchase-orders/${route.params.id}/status`, { status })
    order.value = data.data ?? data
    addToast?.(`Order ${status}`, 'success')
  } catch (err: any) {
    addToast?.(err.response?.data?.message ?? 'Failed to update', 'error')
  } finally {
    updating.value = false
  }
}

async function cancelOrder() {
  if (!confirm('Cancel this order?')) return
  updating.value = true
  try {
    const { data } = await api.patch(`/purchase-orders/${route.params.id}/status`, { status: 'cancelled' })
    order.value = data.data ?? data
    addToast?.('Order cancelled', 'success')
  } catch (err: any) {
    addToast?.(err.response?.data?.message ?? 'Failed to cancel', 'error')
  } finally {
    updating.value = false
  }
}
</script>

<template>
  <div>
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600" />
    </div>

    <template v-else-if="order">
      <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">{{ order.order_number }}</h2>
          <StatusBadge :status="order.status" />
        </div>
        <div class="flex gap-2">
          <router-link
            v-if="order.status === 'draft'"
            :to="`/purchase-orders/${order.id}/edit`"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            <PencilIcon class="h-4 w-4" /> Edit
          </router-link>
          <button
            v-if="nextStatus[order.status]"
            :disabled="updating"
            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50"
            @click="updateStatus(nextStatus[order.status])"
          >
            {{ nextLabel[order.status] }}
          </button>
          <button
            v-if="order.status !== 'cancelled' && order.status !== 'received'"
            :disabled="updating"
            class="rounded-lg border border-danger-500 px-4 py-2 text-sm font-medium text-danger-600 hover:bg-danger-50 disabled:opacity-50"
            @click="cancelOrder"
          >
            Cancel Order
          </button>
        </div>
      </div>

      <!-- Order Info -->
      <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">Order Details</h3>
          <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <dt class="text-gray-500">Order Number</dt>
              <dd class="font-medium text-gray-900">{{ order.order_number }}</dd>
            </div>
            <div>
              <dt class="text-gray-500">Status</dt>
              <dd><StatusBadge :status="order.status" /></dd>
            </div>
            <div>
              <dt class="text-gray-500">Created</dt>
              <dd class="font-medium text-gray-900">{{ dayjs(order.created_at).format('MMM D, YYYY') }}</dd>
            </div>
            <div>
              <dt class="text-gray-500">Expected Date</dt>
              <dd class="font-medium text-gray-900">{{ order.expected_date ? dayjs(order.expected_date).format('MMM D, YYYY') : '—' }}</dd>
            </div>
            <div v-if="order.received_date">
              <dt class="text-gray-500">Received Date</dt>
              <dd class="font-medium text-gray-900">{{ dayjs(order.received_date).format('MMM D, YYYY') }}</dd>
            </div>
            <div v-if="order.notes" class="col-span-2">
              <dt class="text-gray-500">Notes</dt>
              <dd class="text-gray-900">{{ order.notes }}</dd>
            </div>
          </dl>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">Supplier</h3>
          <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <dt class="text-gray-500">Name</dt>
              <dd class="font-medium text-gray-900">{{ order.supplier?.name ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-gray-500">Contact</dt>
              <dd class="font-medium text-gray-900">{{ order.supplier?.contact_person ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-gray-500">Email</dt>
              <dd class="font-medium text-gray-900">{{ order.supplier?.email ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-gray-500">Phone</dt>
              <dd class="font-medium text-gray-900">{{ order.supplier?.phone ?? '—' }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Items Table -->
      <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Order Items</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
                <th class="px-4 py-3">Product</th>
                <th class="px-4 py-3">Quantity</th>
                <th class="px-4 py-3">Received</th>
                <th class="px-4 py-3">Unit Price</th>
                <th class="px-4 py-3">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="item in order.items" :key="item.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">{{ item.product?.name ?? `Product #${item.product_id}` }}</td>
                <td class="px-4 py-3 text-gray-600">{{ item.quantity }}</td>
                <td class="px-4 py-3 text-gray-600">{{ item.received_quantity }}</td>
                <td class="px-4 py-3 text-gray-600">{{ fmt(item.unit_price) }}</td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ fmt(item.total) }}</td>
              </tr>
            </tbody>
            <tfoot class="border-t border-gray-200">
              <tr>
                <td colspan="4" class="px-4 py-2 text-right text-sm text-gray-500">Subtotal</td>
                <td class="px-4 py-2 font-medium text-gray-900">{{ fmt(order.subtotal) }}</td>
              </tr>
              <tr>
                <td colspan="4" class="px-4 py-2 text-right text-sm text-gray-500">Tax</td>
                <td class="px-4 py-2 font-medium text-gray-900">{{ fmt(order.tax) }}</td>
              </tr>
              <tr>
                <td colspan="4" class="px-4 py-2 text-right text-sm font-semibold text-gray-900">Total</td>
                <td class="px-4 py-2 font-bold text-gray-900">{{ fmt(order.total) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

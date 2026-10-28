<script setup lang="ts">
import { ref, onMounted, computed, inject } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@/api/client'
import type { Product, StockMovement } from '@/types'
import Modal from '@/components/ui/Modal.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import dayjs from 'dayjs'
import { PencilIcon, TrashIcon, ArrowsRightLeftIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const addToast = inject<(msg: string, type: 'success' | 'error' | 'warning') => void>('addToast')

const product = ref<Product | null>(null)
const movements = ref<StockMovement[]>([])
const loading = ref(true)
const showAdjust = ref(false)
const adjusting = ref(false)

const adjustForm = ref({ type: 'in' as 'in' | 'out' | 'adjustment', quantity: 1, reason: '' })

const fmt = (v: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(v)

const stockPercent = computed(() => {
  if (!product.value || !product.value.max_stock_level) return 0
  return Math.min(100, Math.round((product.value.quantity / product.value.max_stock_level) * 100))
})

const stockStatus = computed(() => {
  if (!product.value) return 'ok'
  if (product.value.quantity <= 0) return 'out'
  if (product.value.quantity <= product.value.min_stock_level) return 'low'
  return 'ok'
})

onMounted(async () => {
  try {
    const [pRes, mRes] = await Promise.all([
      api.get(`/products/${route.params.id}`),
      api.get(`/products/${route.params.id}/movements`, { params: { per_page: 20 } }),
    ])
    product.value = pRes.data.data ?? pRes.data
    movements.value = (mRes.data.data ?? mRes.data) as StockMovement[]
  } finally {
    loading.value = false
  }
})

async function adjustStock() {
  adjusting.value = true
  try {
    await api.post(`/products/${route.params.id}/adjust-stock`, adjustForm.value)
    addToast?.('Stock adjusted successfully', 'success')
    showAdjust.value = false
    // Reload
    const [pRes, mRes] = await Promise.all([
      api.get(`/products/${route.params.id}`),
      api.get(`/products/${route.params.id}/movements`, { params: { per_page: 20 } }),
    ])
    product.value = pRes.data.data ?? pRes.data
    movements.value = (mRes.data.data ?? mRes.data) as StockMovement[]
    adjustForm.value = { type: 'in', quantity: 1, reason: '' }
  } catch (err: any) {
    addToast?.(err.response?.data?.message ?? 'Failed to adjust stock', 'error')
  } finally {
    adjusting.value = false
  }
}

async function deleteProduct() {
  if (!confirm('Are you sure you want to delete this product?')) return
  try {
    await api.delete(`/products/${route.params.id}`)
    addToast?.('Product deleted', 'success')
    router.push('/products')
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

    <template v-else-if="product">
      <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">{{ product.name }}</h2>
          <p class="text-sm text-gray-500">SKU: {{ product.sku }}</p>
        </div>
        <div class="flex gap-2">
          <button
            class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
            @click="showAdjust = true"
          >
            <ArrowsRightLeftIcon class="h-4 w-4" /> Adjust Stock
          </button>
          <RouterLink
            :to="`/products/${product.id}/edit`"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            <PencilIcon class="h-4 w-4" /> Edit
          </RouterLink>
          <button
            class="inline-flex items-center gap-2 rounded-lg border border-danger-500 px-4 py-2 text-sm font-medium text-danger-600 hover:bg-danger-50"
            @click="deleteProduct"
          >
            <TrashIcon class="h-4 w-4" /> Delete
          </button>
        </div>
      </div>

      <!-- Info Card -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Product Information</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <dt class="text-gray-500">Category</dt>
                <dd class="font-medium text-gray-900">{{ product.category?.name ?? '—' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Supplier</dt>
                <dd class="font-medium text-gray-900">{{ product.supplier?.name ?? '—' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Unit Price</dt>
                <dd class="font-medium text-gray-900">{{ fmt(product.unit_price) }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Cost Price</dt>
                <dd class="font-medium text-gray-900">{{ fmt(product.cost_price) }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Unit</dt>
                <dd class="font-medium text-gray-900">{{ product.unit }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Location</dt>
                <dd class="font-medium text-gray-900">{{ product.location ?? '—' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Barcode</dt>
                <dd class="font-medium text-gray-900">{{ product.barcode ?? '—' }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Status</dt>
                <dd><StatusBadge :status="product.is_active ? 'active' : 'inactive'" /></dd>
              </div>
            </dl>
            <p v-if="product.description" class="mt-4 text-sm text-gray-600">{{ product.description }}</p>
          </div>

          <!-- Movements Table -->
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Stock Movements</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Quantity</th>
                    <th class="px-4 py-3">Reason</th>
                    <th class="px-4 py-3">User</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-if="!movements.length">
                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">No movements yet</td>
                  </tr>
                  <tr v-for="m in movements" :key="m.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-600">{{ dayjs(m.created_at).format('MMM D, YYYY HH:mm') }}</td>
                    <td class="px-4 py-3"><StatusBadge :status="m.type" /></td>
                    <td class="px-4 py-3" :class="m.type === 'out' ? 'text-danger-600' : 'text-success-600'">
                      {{ m.type === 'out' ? '-' : '+' }}{{ m.quantity }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ m.reason ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ m.user?.name ?? '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Stock Level -->
        <div>
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Stock Level</h3>
            <div class="mb-2 flex items-end justify-between">
              <span class="text-3xl font-bold text-gray-900">{{ product.quantity }}</span>
              <span class="text-sm text-gray-500">/ {{ product.max_stock_level }} {{ product.unit }}</span>
            </div>
            <div class="mb-2 h-3 overflow-hidden rounded-full bg-gray-200">
              <div
                :class="{
                  'bg-danger-500': stockStatus === 'out' || stockStatus === 'low',
                  'bg-success-500': stockStatus === 'ok',
                }"
                :style="{ width: `${stockPercent}%` }"
                class="h-full rounded-full transition-all"
              />
            </div>
            <div class="flex justify-between text-xs text-gray-500">
              <span>Min: {{ product.min_stock_level }}</span>
              <span>Max: {{ product.max_stock_level }}</span>
            </div>
            <div class="mt-3">
              <StatusBadge :status="stockStatus" />
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Adjust Stock Modal -->
    <Modal :show="showAdjust" title="Adjust Stock" @close="showAdjust = false">
      <form class="space-y-4" @submit.prevent="adjustStock">
        <div>
          <label class="block text-sm font-medium text-gray-700">Type</label>
          <select v-model="adjustForm.type" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none">
            <option value="in">Stock In</option>
            <option value="out">Stock Out</option>
            <option value="adjustment">Adjustment</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Quantity</label>
          <input v-model.number="adjustForm.quantity" type="number" min="1" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Reason</label>
          <textarea v-model="adjustForm.reason" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="showAdjust = false">Cancel</button>
          <button type="submit" :disabled="adjusting" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50">
            {{ adjusting ? 'Saving...' : 'Adjust Stock' }}
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import api, { stripEmpty } from '@/api/client'
import type { Product, Category, Supplier, PaginatedResponse } from '@/types'
import Pagination from '@/components/ui/Pagination.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import { MagnifyingGlassIcon, PlusIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const products = ref<Product[]>([])
const meta = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
const categories = ref<Category[]>([])
const suppliers = ref<Supplier[]>([])
const loading = ref(true)

const search = ref('')
const categoryFilter = ref('')
const supplierFilter = ref('')
const stockFilter = ref('')
const page = ref(1)

const fmt = (v: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(v)

async function fetchProducts() {
  loading.value = true
  try {
    const { data } = await api.get<PaginatedResponse<Product>>('/products', {
      params: stripEmpty({
        search: search.value,
        category_id: categoryFilter.value,
        supplier_id: supplierFilter.value,
        stock_status: stockFilter.value,
        page: page.value,
        per_page: 15,
      }),
    })
    products.value = data.data
    meta.value = { current_page: data.current_page, last_page: data.last_page, per_page: data.per_page, total: data.total }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  const [catRes, supRes] = await Promise.all([
    api.get('/categories', { params: { per_page: 100 } }),
    api.get('/suppliers', { params: { per_page: 100 } }),
  ])
  categories.value = catRes.data.data
  suppliers.value = supRes.data.data
  fetchProducts()
})

watch([search, categoryFilter, supplierFilter, stockFilter], () => {
  page.value = 1
  fetchProducts()
})

function stockIndicator(p: Product) {
  if (p.quantity <= 0) return 'out'
  if (p.quantity <= p.min_stock_level) return 'low'
  return 'ok'
}
</script>

<template>
  <div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <h2 class="text-2xl font-bold text-gray-900">Products</h2>
      <RouterLink
        to="/products/create"
        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
      >
        <PlusIcon class="h-5 w-5" /> Add Product
      </RouterLink>
    </div>

    <!-- Filters -->
    <div class="mb-4 flex flex-wrap gap-3">
      <div class="relative flex-1" style="min-width: 200px">
        <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
        <input
          v-model="search"
          placeholder="Search by name or SKU..."
          class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
        />
      </div>
      <select
        v-model="categoryFilter"
        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
      >
        <option value="">All Categories</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
      <select
        v-model="supplierFilter"
        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
      >
        <option value="">All Suppliers</option>
        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
      <select
        v-model="stockFilter"
        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
      >
        <option value="">All Stock</option>
        <option value="low">Low Stock</option>
        <option value="out">Out of Stock</option>
        <option value="in">In Stock</option>
      </select>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
            <th class="px-4 py-3">SKU</th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Category</th>
            <th class="px-4 py-3">Quantity</th>
            <th class="px-4 py-3">Unit Price</th>
            <th class="px-4 py-3">Cost Price</th>
            <th class="px-4 py-3">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading">
            <td colspan="7" class="px-4 py-10 text-center text-gray-400">Loading...</td>
          </tr>
          <tr v-else-if="!products.length">
            <td colspan="7" class="px-4 py-10 text-center text-gray-400">No products found</td>
          </tr>
          <tr
            v-for="p in products"
            :key="p.id"
            class="cursor-pointer hover:bg-gray-50"
            @click="router.push(`/products/${p.id}`)"
          >
            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ p.sku }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ p.name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ p.category?.name ?? '—' }}</td>
            <td class="px-4 py-3">
              <span
                :class="{
                  'text-danger-600 font-semibold': stockIndicator(p) === 'out' || stockIndicator(p) === 'low',
                  'text-success-600': stockIndicator(p) === 'ok',
                }"
              >
                {{ p.quantity }}
              </span>
              <span class="ml-1 text-xs text-gray-400">{{ p.unit }}</span>
            </td>
            <td class="px-4 py-3 text-gray-900">{{ fmt(p.unit_price) }}</td>
            <td class="px-4 py-3 text-gray-600">{{ fmt(p.cost_price) }}</td>
            <td class="px-4 py-3">
              <StatusBadge :status="stockIndicator(p)" />
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination
        :current-page="meta.current_page"
        :last-page="meta.last_page"
        :total="meta.total"
        :per-page="meta.per_page"
        @page-change="(p: number) => { page = p; fetchProducts() }"
      />
    </div>
  </div>
</template>

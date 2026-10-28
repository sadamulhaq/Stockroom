<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import api, { stripEmpty } from '@/api/client'
import type { Supplier, PaginatedResponse } from '@/types'
import Pagination from '@/components/ui/Pagination.vue'
import { MagnifyingGlassIcon, PlusIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const suppliers = ref<Supplier[]>([])
const meta = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
const loading = ref(true)
const search = ref('')
const page = ref(1)

async function fetchSuppliers() {
  loading.value = true
  try {
    const { data } = await api.get<PaginatedResponse<Supplier>>('/suppliers', {
      params: stripEmpty({ search: search.value, page: page.value, per_page: 15 }),
    })
    suppliers.value = data.data
    meta.value = { current_page: data.current_page, last_page: data.last_page, per_page: data.per_page, total: data.total }
  } finally {
    loading.value = false
  }
}

onMounted(fetchSuppliers)
watch(search, () => { page.value = 1; fetchSuppliers() })
</script>

<template>
  <div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <h2 class="text-2xl font-bold text-gray-900">Suppliers</h2>
      <RouterLink
        to="/suppliers/create"
        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
      >
        <PlusIcon class="h-5 w-5" /> Add Supplier
      </RouterLink>
    </div>

    <div class="mb-4 relative" style="max-width: 400px">
      <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
      <input
        v-model="search"
        placeholder="Search suppliers..."
        class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none"
      />
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Contact Person</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Phone</th>
            <th class="px-4 py-3">City</th>
            <th class="px-4 py-3">Products</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading">
            <td colspan="6" class="px-4 py-10 text-center text-gray-400">Loading...</td>
          </tr>
          <tr v-else-if="!suppliers.length">
            <td colspan="6" class="px-4 py-10 text-center text-gray-400">No suppliers found</td>
          </tr>
          <tr
            v-for="s in suppliers"
            :key="s.id"
            class="cursor-pointer hover:bg-gray-50"
            @click="router.push(`/suppliers/${s.id}`)"
          >
            <td class="px-4 py-3 font-medium text-gray-900">{{ s.name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ s.contact_person ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ s.email ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ s.phone ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ s.city ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ s.products_count ?? 0 }}</td>
          </tr>
        </tbody>
      </table>
      <Pagination
        :current-page="meta.current_page"
        :last-page="meta.last_page"
        :total="meta.total"
        :per-page="meta.per_page"
        @page-change="(p: number) => { page = p; fetchSuppliers() }"
      />
    </div>
  </div>
</template>

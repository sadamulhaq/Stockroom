<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/client'
import type { Category } from '@/types'
import { PlusIcon, PencilIcon, TrashIcon, FolderIcon, FolderOpenIcon } from '@heroicons/vue/24/outline'

const addToast = inject<(msg: string, type: 'success' | 'error' | 'warning') => void>('addToast')
const categories = ref<Category[]>([])
const loading = ref(true)
const expanded = ref<Set<number>>(new Set())

onMounted(async () => {
  await fetchCategories()
})

async function fetchCategories() {
  loading.value = true
  try {
    const { data } = await api.get('/categories', { params: { per_page: 200 } })
    categories.value = buildTree(data.data)
  } finally {
    loading.value = false
  }
}

function buildTree(flat: Category[]): Category[] {
  const map = new Map<number, Category>()
  const roots: Category[] = []
  flat.forEach((c) => map.set(c.id, { ...c, children: [] }))
  flat.forEach((c) => {
    const node = map.get(c.id)!
    if (c.parent_id && map.has(c.parent_id)) {
      map.get(c.parent_id)!.children!.push(node)
    } else {
      roots.push(node)
    }
  })
  return roots
}

function toggle(id: number) {
  if (expanded.value.has(id)) expanded.value.delete(id)
  else expanded.value.add(id)
}

async function deleteCategory(id: number) {
  if (!confirm('Delete this category?')) return
  try {
    await api.delete(`/categories/${id}`)
    addToast?.('Category deleted', 'success')
    await fetchCategories()
  } catch (err: any) {
    addToast?.(err.response?.data?.message ?? 'Failed to delete', 'error')
  }
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h2 class="text-2xl font-bold text-gray-900">Categories</h2>
      <RouterLink
        to="/categories/create"
        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
      >
        <PlusIcon class="h-5 w-5" /> Add Category
      </RouterLink>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600" />
    </div>

    <div v-else class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
      <div v-if="!categories.length" class="py-8 text-center text-gray-400">No categories found</div>
      <ul v-else class="space-y-1">
        <template v-for="cat in categories" :key="cat.id">
          <li>
            <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-gray-50">
              <div class="flex items-center gap-2">
                <button
                  v-if="cat.children?.length"
                  class="text-gray-400 hover:text-gray-600"
                  @click="toggle(cat.id)"
                >
                  <FolderOpenIcon v-if="expanded.has(cat.id)" class="h-5 w-5" />
                  <FolderIcon v-else class="h-5 w-5" />
                </button>
                <FolderIcon v-else class="h-5 w-5 text-gray-300" />
                <span class="text-sm font-medium text-gray-900">{{ cat.name }}</span>
                <span v-if="cat.products_count != null" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">
                  {{ cat.products_count }} products
                </span>
              </div>
              <div class="flex items-center gap-2">
                <RouterLink :to="`/categories/${cat.id}/edit`" class="text-gray-400 hover:text-primary-600">
                  <PencilIcon class="h-4 w-4" />
                </RouterLink>
                <button class="text-gray-400 hover:text-danger-600" @click="deleteCategory(cat.id)">
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </div>
            <!-- Children -->
            <ul v-if="cat.children?.length && expanded.has(cat.id)" class="ml-8 space-y-1">
              <li v-for="child in cat.children" :key="child.id">
                <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-gray-50">
                  <div class="flex items-center gap-2">
                    <FolderIcon class="h-4 w-4 text-gray-300" />
                    <span class="text-sm text-gray-800">{{ child.name }}</span>
                    <span v-if="child.products_count != null" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">
                      {{ child.products_count }} products
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <RouterLink :to="`/categories/${child.id}/edit`" class="text-gray-400 hover:text-primary-600">
                      <PencilIcon class="h-4 w-4" />
                    </RouterLink>
                    <button class="text-gray-400 hover:text-danger-600" @click="deleteCategory(child.id)">
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </li>
            </ul>
          </li>
        </template>
      </ul>
    </div>
  </div>
</template>

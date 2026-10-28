<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router'
import {
  HomeIcon,
  CubeIcon,
  TagIcon,
  TruckIcon,
  ClipboardDocumentListIcon,
  ArrowsRightLeftIcon,
  ChartBarIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()

const nav = [
  { name: 'Dashboard', to: '/', icon: HomeIcon },
  { name: 'Products', to: '/products', icon: CubeIcon },
  { name: 'Low Stock', to: '/products/low-stock', icon: ExclamationTriangleIcon },
  { name: 'Categories', to: '/categories', icon: TagIcon },
  { name: 'Suppliers', to: '/suppliers', icon: TruckIcon },
  { name: 'Purchase Orders', to: '/purchase-orders', icon: ClipboardDocumentListIcon },
  { name: 'Stock Movements', to: '/stock-movements', icon: ArrowsRightLeftIcon },
  { name: 'Reports', to: '/reports', icon: ChartBarIcon },
]

function isActive(to: string) {
  if (to === '/') return route.path === '/'
  return route.path.startsWith(to)
}
</script>

<template>
  <aside class="flex h-full w-64 flex-col bg-primary-900 text-white">
    <div class="flex h-16 items-center gap-2 px-6">
      <CubeIcon class="h-8 w-8 text-primary-300" />
      <span class="text-xl font-bold tracking-tight">StockFlow</span>
    </div>
    <nav class="mt-4 flex-1 space-y-1 px-3">
      <RouterLink
        v-for="item in nav"
        :key="item.name"
        :to="item.to"
        :class="isActive(item.to) ? 'bg-primary-700 text-white' : 'text-primary-200 hover:bg-primary-800 hover:text-white'"
        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
      >
        <component :is="item.icon" class="h-5 w-5 shrink-0" />
        {{ item.name }}
      </RouterLink>
    </nav>
    <div class="border-t border-primary-700 px-6 py-4 text-xs text-primary-400">
      &copy; {{ new Date().getFullYear() }} StockFlow
    </div>
  </aside>
</template>

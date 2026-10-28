import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/pages/auth/Login.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/pages/auth/Register.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('@/components/layout/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', name: 'Dashboard', component: () => import('@/pages/Dashboard.vue') },
      { path: 'products', name: 'Products', component: () => import('@/pages/products/ProductsList.vue') },
      { path: 'products/create', name: 'ProductCreate', component: () => import('@/pages/products/ProductForm.vue') },
      { path: 'products/:id', name: 'ProductDetail', component: () => import('@/pages/products/ProductDetail.vue') },
      { path: 'products/:id/edit', name: 'ProductEdit', component: () => import('@/pages/products/ProductForm.vue') },
      { path: 'products/low-stock', name: 'LowStock', component: () => import('@/pages/products/LowStock.vue') },
      { path: 'categories', name: 'Categories', component: () => import('@/pages/categories/CategoriesList.vue') },
      { path: 'categories/create', name: 'CategoryCreate', component: () => import('@/pages/categories/CategoryForm.vue') },
      { path: 'categories/:id/edit', name: 'CategoryEdit', component: () => import('@/pages/categories/CategoryForm.vue') },
      { path: 'suppliers', name: 'Suppliers', component: () => import('@/pages/suppliers/SuppliersList.vue') },
      { path: 'suppliers/create', name: 'SupplierCreate', component: () => import('@/pages/suppliers/SupplierForm.vue') },
      { path: 'suppliers/:id', name: 'SupplierDetail', component: () => import('@/pages/suppliers/SupplierDetail.vue') },
      { path: 'suppliers/:id/edit', name: 'SupplierEdit', component: () => import('@/pages/suppliers/SupplierForm.vue') },
      { path: 'purchase-orders', name: 'PurchaseOrders', component: () => import('@/pages/purchase-orders/PurchaseOrdersList.vue') },
      { path: 'purchase-orders/create', name: 'PurchaseOrderCreate', component: () => import('@/pages/purchase-orders/PurchaseOrderForm.vue') },
      { path: 'purchase-orders/:id', name: 'PurchaseOrderDetail', component: () => import('@/pages/purchase-orders/PurchaseOrderDetail.vue') },
      { path: 'purchase-orders/:id/edit', name: 'PurchaseOrderEdit', component: () => import('@/pages/purchase-orders/PurchaseOrderForm.vue') },
      { path: 'stock-movements', name: 'StockMovements', component: () => import('@/pages/stock-movements/StockMovementsList.vue') },
      { path: 'reports', name: 'Reports', component: () => import('@/pages/reports/Reports.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, _from, next) => {
  const token = localStorage.getItem('inv_token')
  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else if (to.meta.guest && token) {
    next('/')
  } else {
    next()
  }
})

export default router

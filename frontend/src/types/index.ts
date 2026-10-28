export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'manager' | 'staff'
  is_active: boolean
  created_at: string
}

export interface Category {
  id: number
  name: string
  slug: string
  description?: string
  parent_id?: number
  is_active: boolean
  products_count?: number
  children?: Category[]
  created_at: string
}

export interface Supplier {
  id: number
  name: string
  email?: string
  phone?: string
  address?: string
  city?: string
  country?: string
  contact_person?: string
  is_active: boolean
  products_count?: number
  created_at: string
}

export interface Product {
  id: number
  name: string
  sku: string
  description?: string
  category_id: number
  supplier_id?: number
  unit_price: number
  cost_price: number
  quantity: number
  min_stock_level: number
  max_stock_level: number
  unit: string
  location?: string
  barcode?: string
  is_active: boolean
  category?: Category
  supplier?: Supplier
  created_at: string
  updated_at: string
}

export interface PurchaseOrder {
  id: number
  order_number: string
  supplier_id: number
  user_id: number
  status: 'draft' | 'pending' | 'approved' | 'received' | 'cancelled'
  subtotal: number
  tax: number
  total: number
  notes?: string
  expected_date?: string
  received_date?: string
  supplier?: Supplier
  user?: User
  items?: PurchaseOrderItem[]
  created_at: string
}

export interface PurchaseOrderItem {
  id: number
  purchase_order_id: number
  product_id: number
  quantity: number
  unit_price: number
  total: number
  received_quantity: number
  product?: Product
}

export interface StockMovement {
  id: number
  product_id: number
  user_id: number
  type: 'in' | 'out' | 'adjustment' | 'return'
  quantity: number
  reference_type?: string
  reference_id?: number
  reason?: string
  notes?: string
  product?: Product
  user?: User
  created_at: string
}

export interface DashboardStats {
  total_products: number
  total_categories: number
  total_suppliers: number
  total_stock_value: number
  low_stock_count: number
  out_of_stock_count: number
  recent_movements: StockMovement[]
  recent_purchase_orders: PurchaseOrder[]
  stock_by_category: { category: string; value: number }[]
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
  next_page_url: string | null
  prev_page_url: string | null
}

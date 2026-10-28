<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@inventory.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@inventory.local',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'is_active' => true,
        ]);

        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@inventory.local',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $users = [$admin, $manager, $staff];

        // Categories
        $categories = [];
        $categoryNames = [
            'Electronics' => 'Computers, phones, and electronic components',
            'Furniture' => 'Office and warehouse furniture',
            'Office Supplies' => 'Paper, pens, and general office items',
            'Clothing' => 'Workwear and uniforms',
            'Food & Beverages' => 'Snacks, drinks, and pantry supplies',
            'Tools' => 'Hand tools, power tools, and equipment',
            'Packaging' => 'Boxes, tape, and packaging materials',
            'Cleaning Supplies' => 'Cleaning products and janitorial items',
        ];

        foreach ($categoryNames as $name => $description) {
            $categories[$name] = Category::create([
                'name' => $name,
                'description' => $description,
                'is_active' => true,
            ]);
        }

        // Suppliers
        $suppliersData = [
            ['name' => 'TechWorld Distributors', 'email' => 'orders@techworld.com', 'phone' => '+1-555-0101', 'address' => '123 Tech Park Drive', 'city' => 'San Jose', 'country' => 'USA', 'contact_person' => 'John Mitchell'],
            ['name' => 'Global Office Solutions', 'email' => 'sales@globaloffice.com', 'phone' => '+1-555-0202', 'address' => '456 Commerce Blvd', 'city' => 'Chicago', 'country' => 'USA', 'contact_person' => 'Sarah Williams'],
            ['name' => 'ProPack Industries', 'email' => 'info@propack.com', 'phone' => '+1-555-0303', 'address' => '789 Industrial Ave', 'city' => 'Dallas', 'country' => 'USA', 'contact_person' => 'Mike Johnson'],
            ['name' => 'CleanPro Supplies', 'email' => 'support@cleanpro.com', 'phone' => '+1-555-0404', 'address' => '321 Sanitary Lane', 'city' => 'Miami', 'country' => 'USA', 'contact_person' => 'Lisa Brown'],
            ['name' => 'FurnishRight Co.', 'email' => 'orders@furnishright.com', 'phone' => '+1-555-0505', 'address' => '654 Furniture Row', 'city' => 'Atlanta', 'country' => 'USA', 'contact_person' => 'David Chen'],
            ['name' => 'MegaTool Enterprises', 'email' => 'wholesale@megatool.com', 'phone' => '+1-555-0606', 'address' => '987 Hardware Blvd', 'city' => 'Detroit', 'country' => 'USA', 'contact_person' => 'Rachel Torres'],
        ];

        $suppliers = [];
        foreach ($suppliersData as $data) {
            $suppliers[] = Supplier::create(array_merge($data, ['is_active' => true]));
        }

        // Products
        $productsData = [
            ['name' => 'Laptop Pro 15"', 'sku' => 'ELEC-LP15-001', 'category' => 'Electronics', 'supplier' => 0, 'unit_price' => 1299.99, 'cost_price' => 899.99, 'quantity' => 25, 'min_stock' => 5, 'unit' => 'pcs', 'location' => 'A1-01'],
            ['name' => 'Wireless Mouse', 'sku' => 'ELEC-WM-002', 'category' => 'Electronics', 'supplier' => 0, 'unit_price' => 29.99, 'cost_price' => 12.50, 'quantity' => 150, 'min_stock' => 20, 'unit' => 'pcs', 'location' => 'A1-02'],
            ['name' => 'USB-C Hub', 'sku' => 'ELEC-UCH-003', 'category' => 'Electronics', 'supplier' => 0, 'unit_price' => 49.99, 'cost_price' => 22.00, 'quantity' => 80, 'min_stock' => 15, 'unit' => 'pcs', 'location' => 'A1-03'],
            ['name' => '27" Monitor', 'sku' => 'ELEC-MON27-004', 'category' => 'Electronics', 'supplier' => 0, 'unit_price' => 399.99, 'cost_price' => 250.00, 'quantity' => 3, 'min_stock' => 5, 'unit' => 'pcs', 'location' => 'A1-04'],
            ['name' => 'Standing Desk', 'sku' => 'FURN-SD-001', 'category' => 'Furniture', 'supplier' => 4, 'unit_price' => 599.99, 'cost_price' => 350.00, 'quantity' => 12, 'min_stock' => 3, 'unit' => 'pcs', 'location' => 'B1-01'],
            ['name' => 'Ergonomic Chair', 'sku' => 'FURN-EC-002', 'category' => 'Furniture', 'supplier' => 4, 'unit_price' => 449.99, 'cost_price' => 280.00, 'quantity' => 8, 'min_stock' => 3, 'unit' => 'pcs', 'location' => 'B1-02'],
            ['name' => 'Filing Cabinet', 'sku' => 'FURN-FC-003', 'category' => 'Furniture', 'supplier' => 4, 'unit_price' => 179.99, 'cost_price' => 95.00, 'quantity' => 15, 'min_stock' => 5, 'unit' => 'pcs', 'location' => 'B1-03'],
            ['name' => 'A4 Copy Paper (Ream)', 'sku' => 'OFFC-CP-001', 'category' => 'Office Supplies', 'supplier' => 1, 'unit_price' => 8.99, 'cost_price' => 4.50, 'quantity' => 500, 'min_stock' => 100, 'unit' => 'pcs', 'location' => 'C1-01'],
            ['name' => 'Ballpoint Pens (Box)', 'sku' => 'OFFC-BP-002', 'category' => 'Office Supplies', 'supplier' => 1, 'unit_price' => 12.99, 'cost_price' => 5.99, 'quantity' => 200, 'min_stock' => 30, 'unit' => 'boxes', 'location' => 'C1-02'],
            ['name' => 'Sticky Notes Pack', 'sku' => 'OFFC-SN-003', 'category' => 'Office Supplies', 'supplier' => 1, 'unit_price' => 6.99, 'cost_price' => 2.80, 'quantity' => 300, 'min_stock' => 50, 'unit' => 'pcs', 'location' => 'C1-03'],
            ['name' => 'Stapler Heavy Duty', 'sku' => 'OFFC-SH-004', 'category' => 'Office Supplies', 'supplier' => 1, 'unit_price' => 24.99, 'cost_price' => 11.00, 'quantity' => 40, 'min_stock' => 10, 'unit' => 'pcs', 'location' => 'C1-04'],
            ['name' => 'Safety Vest', 'sku' => 'CLTH-SV-001', 'category' => 'Clothing', 'supplier' => 2, 'unit_price' => 15.99, 'cost_price' => 7.50, 'quantity' => 100, 'min_stock' => 20, 'unit' => 'pcs', 'location' => 'D1-01'],
            ['name' => 'Work Gloves (Pair)', 'sku' => 'CLTH-WG-002', 'category' => 'Clothing', 'supplier' => 2, 'unit_price' => 9.99, 'cost_price' => 4.20, 'quantity' => 2, 'min_stock' => 30, 'unit' => 'pcs', 'location' => 'D1-02'],
            ['name' => 'Steel Toe Boots', 'sku' => 'CLTH-STB-003', 'category' => 'Clothing', 'supplier' => 2, 'unit_price' => 89.99, 'cost_price' => 52.00, 'quantity' => 25, 'min_stock' => 10, 'unit' => 'pcs', 'location' => 'D1-03'],
            ['name' => 'Bottled Water (Case)', 'sku' => 'FOOD-BW-001', 'category' => 'Food & Beverages', 'supplier' => 1, 'unit_price' => 12.99, 'cost_price' => 6.00, 'quantity' => 50, 'min_stock' => 15, 'unit' => 'boxes', 'location' => 'E1-01'],
            ['name' => 'Coffee Beans 1kg', 'sku' => 'FOOD-CB-002', 'category' => 'Food & Beverages', 'supplier' => 1, 'unit_price' => 18.99, 'cost_price' => 10.50, 'quantity' => 30, 'min_stock' => 8, 'unit' => 'kg', 'location' => 'E1-02'],
            ['name' => 'Snack Box Assorted', 'sku' => 'FOOD-SB-003', 'category' => 'Food & Beverages', 'supplier' => 1, 'unit_price' => 24.99, 'cost_price' => 14.00, 'quantity' => 20, 'min_stock' => 5, 'unit' => 'boxes', 'location' => 'E1-03'],
            ['name' => 'Power Drill', 'sku' => 'TOOL-PD-001', 'category' => 'Tools', 'supplier' => 5, 'unit_price' => 129.99, 'cost_price' => 72.00, 'quantity' => 10, 'min_stock' => 3, 'unit' => 'pcs', 'location' => 'F1-01'],
            ['name' => 'Screwdriver Set', 'sku' => 'TOOL-SS-002', 'category' => 'Tools', 'supplier' => 5, 'unit_price' => 34.99, 'cost_price' => 16.50, 'quantity' => 35, 'min_stock' => 10, 'unit' => 'pcs', 'location' => 'F1-02'],
            ['name' => 'Measuring Tape', 'sku' => 'TOOL-MT-003', 'category' => 'Tools', 'supplier' => 5, 'unit_price' => 14.99, 'cost_price' => 5.80, 'quantity' => 50, 'min_stock' => 15, 'unit' => 'pcs', 'location' => 'F1-03'],
            ['name' => 'Socket Wrench Set', 'sku' => 'TOOL-SW-004', 'category' => 'Tools', 'supplier' => 5, 'unit_price' => 79.99, 'cost_price' => 42.00, 'quantity' => 0, 'min_stock' => 5, 'unit' => 'pcs', 'location' => 'F1-04'],
            ['name' => 'Cardboard Boxes (Large)', 'sku' => 'PACK-CBL-001', 'category' => 'Packaging', 'supplier' => 2, 'unit_price' => 2.99, 'cost_price' => 1.20, 'quantity' => 500, 'min_stock' => 100, 'unit' => 'pcs', 'location' => 'G1-01'],
            ['name' => 'Packing Tape Roll', 'sku' => 'PACK-PT-002', 'category' => 'Packaging', 'supplier' => 2, 'unit_price' => 4.99, 'cost_price' => 1.80, 'quantity' => 200, 'min_stock' => 40, 'unit' => 'pcs', 'location' => 'G1-02'],
            ['name' => 'Bubble Wrap (30m)', 'sku' => 'PACK-BW-003', 'category' => 'Packaging', 'supplier' => 2, 'unit_price' => 19.99, 'cost_price' => 8.50, 'quantity' => 40, 'min_stock' => 10, 'unit' => 'pcs', 'location' => 'G1-03'],
            ['name' => 'Shipping Labels (Roll)', 'sku' => 'PACK-SL-004', 'category' => 'Packaging', 'supplier' => 2, 'unit_price' => 14.99, 'cost_price' => 6.00, 'quantity' => 60, 'min_stock' => 15, 'unit' => 'pcs', 'location' => 'G1-04'],
            ['name' => 'All-Purpose Cleaner', 'sku' => 'CLEN-APC-001', 'category' => 'Cleaning Supplies', 'supplier' => 3, 'unit_price' => 7.99, 'cost_price' => 3.20, 'quantity' => 80, 'min_stock' => 20, 'unit' => 'liters', 'location' => 'H1-01'],
            ['name' => 'Disinfectant Spray', 'sku' => 'CLEN-DS-002', 'category' => 'Cleaning Supplies', 'supplier' => 3, 'unit_price' => 9.99, 'cost_price' => 4.50, 'quantity' => 60, 'min_stock' => 15, 'unit' => 'pcs', 'location' => 'H1-02'],
            ['name' => 'Paper Towels (Pack)', 'sku' => 'CLEN-PTW-003', 'category' => 'Cleaning Supplies', 'supplier' => 3, 'unit_price' => 16.99, 'cost_price' => 8.00, 'quantity' => 0, 'min_stock' => 20, 'unit' => 'pcs', 'location' => 'H1-03'],
            ['name' => 'Trash Bags (50pk)', 'sku' => 'CLEN-TB-004', 'category' => 'Cleaning Supplies', 'supplier' => 3, 'unit_price' => 12.99, 'cost_price' => 5.50, 'quantity' => 45, 'min_stock' => 10, 'unit' => 'boxes', 'location' => 'H1-04'],
            ['name' => 'Keyboard Wireless', 'sku' => 'ELEC-KW-005', 'category' => 'Electronics', 'supplier' => 0, 'unit_price' => 59.99, 'cost_price' => 28.00, 'quantity' => 45, 'min_stock' => 10, 'unit' => 'pcs', 'location' => 'A1-05'],
        ];

        $products = [];
        foreach ($productsData as $pd) {
            $products[] = Product::create([
                'name' => $pd['name'],
                'sku' => $pd['sku'],
                'category_id' => $categories[$pd['category']]->id,
                'supplier_id' => $suppliers[$pd['supplier']]->id,
                'unit_price' => $pd['unit_price'],
                'cost_price' => $pd['cost_price'],
                'quantity' => $pd['quantity'],
                'min_stock_level' => $pd['min_stock'],
                'max_stock_level' => 1000,
                'unit' => $pd['unit'],
                'location' => $pd['location'],
                'barcode' => strtoupper(str_replace('-', '', $pd['sku'])),
                'is_active' => true,
            ]);
        }

        // Purchase Orders
        $statuses = ['draft', 'pending', 'approved', 'received', 'cancelled'];
        $poData = [
            ['supplier' => 0, 'user' => $admin, 'status' => 'received', 'products' => [0, 1, 2]],
            ['supplier' => 1, 'user' => $manager, 'status' => 'approved', 'products' => [7, 8, 9]],
            ['supplier' => 4, 'user' => $admin, 'status' => 'pending', 'products' => [4, 5]],
            ['supplier' => 3, 'user' => $manager, 'status' => 'draft', 'products' => [25, 26, 27]],
            ['supplier' => 5, 'user' => $staff, 'status' => 'cancelled', 'products' => [17, 18]],
        ];

        foreach ($poData as $index => $po) {
            $subtotal = 0;
            $items = [];

            foreach ($po['products'] as $pIdx) {
                $product = $products[$pIdx];
                $qty = rand(5, 50);
                $price = $product->cost_price;
                $total = $qty * $price;
                $subtotal += $total;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total' => $total,
                    'received_quantity' => $po['status'] === 'received' ? $qty : 0,
                ];
            }

            $tax = round($subtotal * 0.1, 2);
            $order = PurchaseOrder::create([
                'order_number' => 'PO-' . now()->subDays($index * 3)->format('Ymd') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'supplier_id' => $suppliers[$po['supplier']]->id,
                'user_id' => $po['user']->id,
                'status' => $po['status'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
                'notes' => 'Seed purchase order #' . ($index + 1),
                'expected_date' => now()->addDays(rand(7, 30)),
                'received_date' => $po['status'] === 'received' ? now()->subDay() : null,
            ]);

            foreach ($items as $item) {
                PurchaseOrderItem::create(array_merge($item, ['purchase_order_id' => $order->id]));
            }
        }

        // Stock Movements
        $movementTypes = ['in', 'out', 'adjustment', 'return'];
        $reasons = [
            'in' => ['Purchase order received', 'Return from customer', 'Inventory correction', 'New stock delivery'],
            'out' => ['Sold to customer', 'Damaged goods', 'Shipped to branch', 'Sample given'],
            'adjustment' => ['Inventory audit', 'Count correction', 'System reconciliation', 'Cycle count'],
            'return' => ['Defective item returned', 'Customer return', 'Supplier return', 'Wrong item received'],
        ];

        for ($i = 0; $i < 55; $i++) {
            $type = $movementTypes[array_rand($movementTypes)];
            $product = $products[array_rand($products)];
            $user = $users[array_rand($users)];
            $qty = rand(1, 30);

            if ($type === 'out') {
                $qty = -$qty;
            }

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'type' => $type,
                'quantity' => $qty,
                'reference_type' => $type === 'in' ? 'purchase_order' : 'manual',
                'reference_id' => $type === 'in' ? rand(1, 5) : null,
                'reason' => $reasons[$type][array_rand($reasons[$type])],
                'notes' => 'Seeded movement #' . ($i + 1),
                'created_at' => now()->subDays(rand(0, 60))->subHours(rand(0, 23)),
            ]);
        }
    }
}

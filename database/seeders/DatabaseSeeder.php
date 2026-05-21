<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMutation;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $plans = $this->seedPlans();

        User::query()->updateOrCreate(
            ['email' => 'admin@stokpintar.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPER_ADMIN,
                'tenant_id' => null,
            ]
        );

        foreach ($this->storeDatasets() as $index => $dataset) {
            $this->seedStoreTenant($dataset, $plans, $index + 1);
        }
    }

    /**
     * @return array<string, SubscriptionPlan>
     */
    private function seedPlans(): array
    {
        SubscriptionPlan::query()
            ->where('code', 'full')
            ->delete();

        $coreFeatures = [
            'basic_pos',
            'stock_alerts',
            'pdf_export',
            'excel_export',
            'team_access',
            'fnb_recipes',
            'barcode_scanner',
            'activity_logs',
        ];

        return [
            'free' => SubscriptionPlan::query()->updateOrCreate(['code' => 'free'], [
                'name' => 'Free',
                'price' => 0,
                'max_stores' => 1,
                'max_products' => 50,
                'max_users' => 2,
                'report_retention_days' => 7,
                'features' => $coreFeatures,
            ]),
            'starter' => SubscriptionPlan::query()->updateOrCreate(['code' => 'starter'], [
                'name' => 'Starter',
                'price' => 49000,
                'max_stores' => 1,
                'max_products' => 500,
                'max_users' => 5,
                'report_retention_days' => 30,
                'features' => array_merge($coreFeatures, ['receipt_branding']),
            ]),
            'pro' => SubscriptionPlan::query()->updateOrCreate(['code' => 'pro'], [
                'name' => 'Pro',
                'price' => 99000,
                'max_stores' => 5,
                'max_products' => null,
                'max_users' => null,
                'report_retention_days' => null,
                'features' => array_merge($coreFeatures, ['multi_store', 'receipt_branding', 'api_access']),
            ]),
            'business' => SubscriptionPlan::query()->updateOrCreate(['code' => 'business'], [
                'name' => 'Business',
                'price' => 199000,
                'max_stores' => null,
                'max_products' => null,
                'max_users' => null,
                'report_retention_days' => null,
                'features' => array_merge($coreFeatures, ['multi_store', 'receipt_branding', 'api_access', 'priority_support', 'white_label']),
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $dataset
     * @param  array<string, SubscriptionPlan>  $plans
     */
    private function seedStoreTenant(array $dataset, array $plans, int $number): void
    {
        DB::transaction(function () use ($dataset, $plans, $number) {
            $owner = User::query()->updateOrCreate(
                ['email' => $dataset['owner_email']],
                [
                    'name' => $dataset['owner_name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_OWNER,
                ]
            );

            $plan = $plans[$dataset['plan']];

            $tenant = Tenant::query()->updateOrCreate(
                ['slug' => $dataset['slug']],
                [
                    'owner_id' => $owner->id,
                    'subscription_plan_id' => $plan->id,
                    'name' => $dataset['tenant_name'],
                    'status' => $dataset['status'],
                    'trial_ends_at' => $dataset['status'] === 'trial' ? now()->addDays(14) : null,
                    'subscription_ends_at' => $dataset['status'] === 'active' ? now()->addMonth() : null,
                ]
            );

            $owner->forceFill([
                'tenant_id' => $tenant->id,
                'role' => User::ROLE_OWNER,
            ])->save();

            Subscription::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'provider_reference' => 'seed-'.$dataset['slug'],
                ],
                [
                    'subscription_plan_id' => $plan->id,
                    'status' => $dataset['status'] === 'suspended' ? 'paused' : 'active',
                    'provider' => 'manual',
                    'starts_at' => now()->subDays(20),
                    'ends_at' => $dataset['status'] === 'active' ? now()->addMonth() : null,
                    'metadata' => ['source' => 'database-seeder'],
                ]
            );

            $store = Store::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'MAIN'],
                [
                    'name' => $dataset['store_name'],
                    'address' => $dataset['address'],
                    'phone' => $dataset['phone'],
                    'is_default' => true,
                ]
            );

            $team = [
                $owner,
                $this->seedUser($tenant, $dataset, User::ROLE_MANAGER, 'manager', 'Manager '.$dataset['short_name']),
                $this->seedUser($tenant, $dataset, User::ROLE_CASHIER, 'kasir', 'Kasir '.$dataset['short_name']),
                $this->seedUser($tenant, $dataset, User::ROLE_STAFF_GUDANG, 'gudang', 'Gudang '.$dataset['short_name']),
                $this->seedUser($tenant, $dataset, User::ROLE_VIEWER, 'viewer', 'Viewer '.$dataset['short_name']),
            ];

            foreach ($team as $user) {
                DB::table('user_store_access')->updateOrInsert(
                    ['user_id' => $user->id, 'store_id' => $store->id],
                    [
                        'tenant_id' => $tenant->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $this->resetSeededTenantData($tenant);

            $categories = [];
            foreach ($dataset['categories'] as $categoryName) {
                $categories[$categoryName] = Category::withoutGlobalScopes()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $categoryName],
                    ['icon' => null]
                );
            }

            $products = $this->seedProducts($tenant, $store, $owner, $categories, $dataset['products']);
            $this->seedSales($tenant, $store, $team, $products, $number);
        });
    }

    private function resetSeededTenantData(Tenant $tenant): void
    {
        SaleItem::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->delete();

        Sale::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->delete();

        StockMutation::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->delete();

        Product::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->delete();

        Category::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->delete();
    }

    /**
     * @param  array<string, mixed>  $dataset
     */
    private function seedUser(Tenant $tenant, array $dataset, string $role, string $prefix, string $name): User
    {
        return User::query()->updateOrCreate(
            ['email' => $prefix.'@'.$dataset['slug'].'.test'],
            [
                'tenant_id' => $tenant->id,
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => $role,
            ]
        );
    }

    /**
     * @param  array<string, Category>  $categories
     * @param  array<int, array<string, mixed>>  $products
     * @return array<int, Product>
     */
    private function seedProducts(Tenant $tenant, Store $store, User $owner, array $categories, array $products): array
    {
        $seeded = [];

        foreach ($products as $productData) {
            $product = Product::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'sku' => $productData['sku']],
                [
                    'store_id' => $store->id,
                    'category_id' => $categories[$productData['category']]->id,
                    'name' => $productData['name'],
                    'product_type' => $productData['type'] ?? Product::TYPE_STOCK,
                    'unit' => $productData['unit'],
                    'cost_price' => $productData['cost'],
                    'selling_price' => $productData['price'],
                    'stock' => ($productData['type'] ?? Product::TYPE_STOCK) === Product::TYPE_MENU ? 0 : $productData['stock'],
                    'minimum_stock' => $productData['minimum_stock'],
                    'is_active' => $productData['is_active'] ?? true,
                ]
            );

            if (! $product->isMenu()) {
                StockMutation::withoutGlobalScopes()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'product_id' => $product->id,
                        'type' => StockMutation::TYPE_IN,
                        'notes' => 'Stok awal seeder',
                    ],
                    [
                        'store_id' => $store->id,
                        'user_id' => $owner->id,
                        'quantity' => $productData['stock'],
                        'stock_before' => 0,
                        'stock_after' => $productData['stock'],
                        'created_at' => now()->subDays(30),
                        'updated_at' => now()->subDays(30),
                    ]
                );
            }

            $seeded[] = $product;
        }

        $bySku = collect($seeded)->keyBy('sku');

        foreach ($products as $productData) {
            if (($productData['type'] ?? Product::TYPE_STOCK) !== Product::TYPE_MENU || empty($productData['recipe'])) {
                continue;
            }

            $menu = $bySku[$productData['sku']] ?? null;

            if (! $menu) {
                continue;
            }

            $menu->recipes()->delete();

            foreach ($productData['recipe'] as $ingredientSku => $quantity) {
                $ingredient = $bySku[$ingredientSku] ?? null;

                if (! $ingredient) {
                    continue;
                }

                $menu->recipes()->create([
                    'tenant_id' => $tenant->id,
                    'ingredient_product_id' => $ingredient->id,
                    'quantity' => $quantity,
                ]);
            }
        }

        return collect($seeded)->filter(fn (Product $product) => $product->is_active)->values()->all();
    }

    /**
     * @param  array<int, User>  $team
     * @param  array<int, Product>  $products
     */
    private function seedSales(Tenant $tenant, Store $store, array $team, array $products, int $tenantNumber): void
    {
        $cashiers = collect($team)->filter(fn (User $user) => in_array($user->role, [
            User::ROLE_OWNER,
            User::ROLE_MANAGER,
            User::ROLE_CASHIER,
        ], true))->values();
        $methods = ['cash', 'qris', 'transfer'];

        for ($day = 13; $day >= 0; $day--) {
            for ($transaction = 1; $transaction <= 3; $transaction++) {
                $soldAt = now()->subDays($day)->setTime(8 + $transaction + ($tenantNumber % 5), 10 + ($transaction * 9));
                $invoice = 'INV-'.$tenantNumber.'-'.$soldAt->format('Ymd').'-'.str_pad((string) $transaction, 4, '0', STR_PAD_LEFT);

                if (Sale::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('invoice_number', $invoice)->exists()) {
                    continue;
                }

                $selected = collect($products)
                    ->skip(($transaction + $tenantNumber) % max(1, count($products)))
                    ->take(3)
                    ->values();

                if ($selected->count() < 3) {
                    $selected = collect($products)->take(3)->values();
                }

                $items = [];
                $subtotal = 0;

                foreach ($selected as $index => $product) {
                    $quantity = ($index % 2) + 1;
                    $lineTotal = $product->selling_price * $quantity;
                    $subtotal += $lineTotal;
                    $items[] = compact('product', 'quantity', 'lineTotal');
                }

                $paid = (int) ceil($subtotal / 10000) * 10000;
                $cashier = $cashiers[($transaction + $tenantNumber) % $cashiers->count()];

                $sale = Sale::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'store_id' => $store->id,
                    'cashier_id' => $cashier->id,
                    'invoice_number' => $invoice,
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $subtotal,
                    'paid_amount' => $paid,
                    'change_amount' => $paid - $subtotal,
                    'payment_method' => $methods[($transaction + $day + $tenantNumber) % count($methods)],
                    'status' => 'paid',
                    'sold_at' => $soldAt,
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ]);

                foreach ($items as $item) {
                    SaleItem::withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'sale_id' => $sale->id,
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'sku' => $item['product']->sku,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['product']->selling_price,
                        'line_total' => $item['lineTotal'],
                        'created_at' => $soldAt,
                        'updated_at' => $soldAt,
                    ]);

                    $stockBefore = $item['product']->stock;
                    $stockAfter = max(0, $stockBefore - $item['quantity']);

                    StockMutation::withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'store_id' => $store->id,
                        'product_id' => $item['product']->id,
                        'user_id' => $cashier->id,
                        'type' => StockMutation::TYPE_SALE,
                        'quantity' => -$item['quantity'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'notes' => 'Transaksi POS '.$invoice,
                        'created_at' => $soldAt,
                        'updated_at' => $soldAt,
                    ]);

                    $item['product']->forceFill(['stock' => $stockAfter])->save();
                    $item['product']->stock = $stockAfter;
                }
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function storeDatasets(): array
    {
        return [
            [
                'short_name' => 'Maju',
                'tenant_name' => 'Toko Maju Jaya',
                'store_name' => 'Toko Maju Jaya Bandung',
                'slug' => 'toko-maju-jaya',
                'plan' => 'starter',
                'status' => 'active',
                'owner_name' => 'Budi Santoso',
                'owner_email' => 'owner@stokpintar.test',
                'address' => 'Jl. Pahlawan No. 45, Bandung',
                'phone' => '022-7301001',
                'categories' => ['Minuman', 'Makanan', 'Snack', 'Sembako', 'Kebutuhan Rumah'],
                'products' => [
                    ['name' => 'Kopi Susu Botol 350ml', 'sku' => 'MJ-KSB-001', 'category' => 'Minuman', 'unit' => 'pcs', 'cost' => 9000, 'price' => 18000, 'stock' => 70, 'minimum_stock' => 12],
                    ['name' => 'Teh Lemon Cup', 'sku' => 'MJ-TLC-002', 'category' => 'Minuman', 'unit' => 'pcs', 'cost' => 3500, 'price' => 7000, 'stock' => 44, 'minimum_stock' => 10],
                    ['name' => 'Roti Cokelat', 'sku' => 'MJ-RTC-003', 'category' => 'Makanan', 'unit' => 'pcs', 'cost' => 4500, 'price' => 9000, 'stock' => 36, 'minimum_stock' => 8],
                    ['name' => 'Keripik Pedas', 'sku' => 'MJ-KRP-004', 'category' => 'Snack', 'unit' => 'pcs', 'cost' => 7000, 'price' => 12000, 'stock' => 82, 'minimum_stock' => 15],
                    ['name' => 'Beras Premium 5kg', 'sku' => 'MJ-BRS-005', 'category' => 'Sembako', 'unit' => 'karung', 'cost' => 70000, 'price' => 85000, 'stock' => 24, 'minimum_stock' => 5],
                    ['name' => 'Sabun Lifebuoy', 'sku' => 'MJ-SBN-006', 'category' => 'Kebutuhan Rumah', 'unit' => 'pcs', 'cost' => 5000, 'price' => 8500, 'stock' => 58, 'minimum_stock' => 12],
                ],
            ],
            [
                'short_name' => 'Sehat',
                'tenant_name' => 'Apotek Sehat Sentosa',
                'store_name' => 'Apotek Sehat Sentosa',
                'slug' => 'apotek-sehat-sentosa',
                'plan' => 'business',
                'status' => 'active',
                'owner_name' => 'dr. Rina Prameswari',
                'owner_email' => 'owner@apotek-sehat-sentosa.test',
                'address' => 'Jl. Diponegoro No. 12, Yogyakarta',
                'phone' => '0274-510202',
                'categories' => ['Obat Bebas', 'Vitamin', 'Alat Kesehatan', 'Perawatan Tubuh'],
                'products' => [
                    ['name' => 'Paracetamol 500mg', 'sku' => 'AS-PCT-001', 'category' => 'Obat Bebas', 'unit' => 'strip', 'cost' => 5500, 'price' => 9000, 'stock' => 120, 'minimum_stock' => 30],
                    ['name' => 'Vitamin C 500mg', 'sku' => 'AS-VTC-002', 'category' => 'Vitamin', 'unit' => 'botol', 'cost' => 28000, 'price' => 42000, 'stock' => 35, 'minimum_stock' => 10],
                    ['name' => 'Masker Medis 50pcs', 'sku' => 'AS-MSK-003', 'category' => 'Alat Kesehatan', 'unit' => 'box', 'cost' => 18000, 'price' => 28000, 'stock' => 42, 'minimum_stock' => 12],
                    ['name' => 'Hand Sanitizer 100ml', 'sku' => 'AS-HS-004', 'category' => 'Perawatan Tubuh', 'unit' => 'pcs', 'cost' => 9000, 'price' => 16000, 'stock' => 64, 'minimum_stock' => 15],
                    ['name' => 'Minyak Kayu Putih 60ml', 'sku' => 'AS-MKP-005', 'category' => 'Obat Bebas', 'unit' => 'pcs', 'cost' => 17000, 'price' => 26000, 'stock' => 28, 'minimum_stock' => 8],
                    ['name' => 'Termometer Digital', 'sku' => 'AS-TRM-006', 'category' => 'Alat Kesehatan', 'unit' => 'pcs', 'cost' => 42000, 'price' => 65000, 'stock' => 16, 'minimum_stock' => 5],
                ],
            ],
            [
                'short_name' => 'Kopi',
                'tenant_name' => 'Kedai Kopi Senja',
                'store_name' => 'Kedai Kopi Senja Surabaya',
                'slug' => 'kedai-kopi-senja',
                'plan' => 'pro',
                'status' => 'trial',
                'owner_name' => 'Arman Hakim',
                'owner_email' => 'owner@kedai-kopi-senja.test',
                'address' => 'Jl. Tunjungan No. 88, Surabaya',
                'phone' => '031-9908801',
                'categories' => ['Menu Kopi', 'Menu Non Kopi', 'Pastry', 'Bahan Baku', 'Kemasan'],
                'products' => [
                    ['name' => 'Biji Kopi Blend Senja', 'sku' => 'KS-BHN-KOPI', 'category' => 'Bahan Baku', 'unit' => 'gram', 'cost' => 120, 'price' => 0, 'stock' => 4200, 'minimum_stock' => 900, 'is_active' => false],
                    ['name' => 'Susu UHT Barista', 'sku' => 'KS-BHN-SUSU', 'category' => 'Bahan Baku', 'unit' => 'ml', 'cost' => 18, 'price' => 0, 'stock' => 18000, 'minimum_stock' => 4000, 'is_active' => false],
                    ['name' => 'Gula Aren Cair', 'sku' => 'KS-BHN-GULA', 'category' => 'Bahan Baku', 'unit' => 'ml', 'cost' => 35, 'price' => 0, 'stock' => 7000, 'minimum_stock' => 1500, 'is_active' => false],
                    ['name' => 'Bubuk Matcha', 'sku' => 'KS-BHN-MATCHA', 'category' => 'Bahan Baku', 'unit' => 'gram', 'cost' => 280, 'price' => 0, 'stock' => 1700, 'minimum_stock' => 350, 'is_active' => false],
                    ['name' => 'Cup Plastik 16oz', 'sku' => 'KS-BHN-CUP', 'category' => 'Kemasan', 'unit' => 'pcs', 'cost' => 700, 'price' => 0, 'stock' => 160, 'minimum_stock' => 40, 'is_active' => false],
                    ['name' => 'Es Kopi Susu Senja', 'sku' => 'KS-EKS-001', 'category' => 'Menu Kopi', 'unit' => 'porsi', 'cost' => 9000, 'price' => 22000, 'stock' => 0, 'minimum_stock' => 10, 'type' => Product::TYPE_MENU, 'recipe' => ['KS-BHN-KOPI' => 18, 'KS-BHN-SUSU' => 150, 'KS-BHN-GULA' => 30, 'KS-BHN-CUP' => 1]],
                    ['name' => 'Americano Ice', 'sku' => 'KS-AMI-002', 'category' => 'Menu Kopi', 'unit' => 'porsi', 'cost' => 7000, 'price' => 18000, 'stock' => 0, 'minimum_stock' => 10, 'type' => Product::TYPE_MENU, 'recipe' => ['KS-BHN-KOPI' => 18, 'KS-BHN-CUP' => 1]],
                    ['name' => 'Matcha Latte', 'sku' => 'KS-MTL-003', 'category' => 'Menu Non Kopi', 'unit' => 'porsi', 'cost' => 10000, 'price' => 24000, 'stock' => 0, 'minimum_stock' => 8, 'type' => Product::TYPE_MENU, 'recipe' => ['KS-BHN-MATCHA' => 15, 'KS-BHN-SUSU' => 180, 'KS-BHN-GULA' => 20, 'KS-BHN-CUP' => 1]],
                    ['name' => 'Croissant Butter', 'sku' => 'KS-CRB-004', 'category' => 'Pastry', 'unit' => 'pcs', 'cost' => 12000, 'price' => 26000, 'stock' => 30, 'minimum_stock' => 8],
                    ['name' => 'Brownies Slice', 'sku' => 'KS-BRW-005', 'category' => 'Pastry', 'unit' => 'pcs', 'cost' => 8000, 'price' => 18000, 'stock' => 34, 'minimum_stock' => 10],
                ],
            ],
            [
                'short_name' => 'Fresh',
                'tenant_name' => 'Minimarket FreshMart',
                'store_name' => 'FreshMart Bekasi',
                'slug' => 'minimarket-freshmart',
                'plan' => 'business',
                'status' => 'active',
                'owner_name' => 'Dewi Lestari',
                'owner_email' => 'owner@minimarket-freshmart.test',
                'address' => 'Jl. Ahmad Yani No. 19, Bekasi',
                'phone' => '021-8833004',
                'categories' => ['Dairy', 'Frozen Food', 'Minuman', 'Perawatan', 'Sembako'],
                'products' => [
                    ['name' => 'Susu UHT Full Cream 1L', 'sku' => 'FM-SUS-001', 'category' => 'Dairy', 'unit' => 'pcs', 'cost' => 16500, 'price' => 22000, 'stock' => 54, 'minimum_stock' => 15],
                    ['name' => 'Nugget Ayam 500gr', 'sku' => 'FM-NGT-002', 'category' => 'Frozen Food', 'unit' => 'pack', 'cost' => 32000, 'price' => 45000, 'stock' => 28, 'minimum_stock' => 8],
                    ['name' => 'Air Mineral 1.5L', 'sku' => 'FM-AIR-003', 'category' => 'Minuman', 'unit' => 'botol', 'cost' => 4500, 'price' => 7500, 'stock' => 96, 'minimum_stock' => 24],
                    ['name' => 'Shampoo Sachet Renceng', 'sku' => 'FM-SHP-004', 'category' => 'Perawatan', 'unit' => 'renceng', 'cost' => 10000, 'price' => 15000, 'stock' => 40, 'minimum_stock' => 10],
                    ['name' => 'Telur Ayam 1kg', 'sku' => 'FM-TLR-005', 'category' => 'Sembako', 'unit' => 'kg', 'cost' => 26000, 'price' => 32000, 'stock' => 32, 'minimum_stock' => 8],
                    ['name' => 'Gula Pasir 1kg', 'sku' => 'FM-GLA-006', 'category' => 'Sembako', 'unit' => 'kg', 'cost' => 14500, 'price' => 18000, 'stock' => 48, 'minimum_stock' => 12],
                ],
            ],
            [
                'short_name' => 'Hijau',
                'tenant_name' => 'Toko Bangunan Hijau',
                'store_name' => 'Toko Bangunan Hijau Malang',
                'slug' => 'toko-bangunan-hijau',
                'plan' => 'starter',
                'status' => 'active',
                'owner_name' => 'Hendra Wijaya',
                'owner_email' => 'owner@toko-bangunan-hijau.test',
                'address' => 'Jl. Ijen No. 77, Malang',
                'phone' => '0341-770088',
                'categories' => ['Cat', 'Perkakas', 'Listrik', 'Pipa', 'Semen'],
                'products' => [
                    ['name' => 'Cat Tembok Putih 5kg', 'sku' => 'BH-CAT-001', 'category' => 'Cat', 'unit' => 'kaleng', 'cost' => 95000, 'price' => 125000, 'stock' => 22, 'minimum_stock' => 5],
                    ['name' => 'Kuas Cat 2 inch', 'sku' => 'BH-KUS-002', 'category' => 'Perkakas', 'unit' => 'pcs', 'cost' => 7000, 'price' => 12000, 'stock' => 60, 'minimum_stock' => 12],
                    ['name' => 'Kabel NYM 2x1.5', 'sku' => 'BH-KBL-003', 'category' => 'Listrik', 'unit' => 'roll', 'cost' => 225000, 'price' => 285000, 'stock' => 12, 'minimum_stock' => 3],
                    ['name' => 'Lampu LED 12W', 'sku' => 'BH-LED-004', 'category' => 'Listrik', 'unit' => 'pcs', 'cost' => 18000, 'price' => 28000, 'stock' => 44, 'minimum_stock' => 10],
                    ['name' => 'Pipa PVC 1/2 inch', 'sku' => 'BH-PVC-005', 'category' => 'Pipa', 'unit' => 'batang', 'cost' => 21000, 'price' => 32000, 'stock' => 38, 'minimum_stock' => 8],
                    ['name' => 'Semen 40kg', 'sku' => 'BH-SMN-006', 'category' => 'Semen', 'unit' => 'sak', 'cost' => 51000, 'price' => 65000, 'stock' => 30, 'minimum_stock' => 6],
                ],
            ],
        ];
    }
}

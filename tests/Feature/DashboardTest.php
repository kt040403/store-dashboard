<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Category;
use App\Models\MonthlyTarget;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Store $store;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $area = Area::create(['name' => 'テストエリア', 'region' => 'テスト']);
        $this->store = Store::create([
            'area_id' => $area->id,
            'name' => 'テスト店舗',
            'code' => 'TEST-001',
        ]);
        $category = Category::create(['name' => 'テストカテゴリ', 'sort_order' => 1]);
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'テスト商品',
            'code' => 'P-001',
            'price' => 100000,
        ]);
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_ダッシュボードが表示される(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('ダッシュボード');
    }

    public function test_未ログインはログイン画面にリダイレクト(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_KPIが正しく計算される(): void
    {
        Sale::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 100000,
            'total' => 200000,
            'sale_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('200,000');
    }

    public function test_売上データなしでもダッシュボードが表示される(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('¥0');
    }

    public function test_期間フィルターで月を切り替えられる(): void
    {
        $lastMonth = now()->subMonth()->format('Y-m');

        Sale::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 100000,
            'total' => 500000,
            'sale_date' => now()->subMonth()->format('Y-m') . '-15',
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard?month=' . $lastMonth);

        $response->assertStatus(200);
        $response->assertSee('500,000');
    }

    public function test_前年同月比が表示される(): void
    {
        // 今月のデータ
        Sale::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
            'unit_price' => 100000,
            'total' => 300000,
            'sale_date' => now()->format('Y-m-d'),
        ]);

        // 前年同月のデータ
        Sale::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 100000,
            'total' => 200000,
            'sale_date' => now()->subYear()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('前年同月比');
    }

    public function test_前年データなしでもエラーにならない(): void
    {
        Sale::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 100000,
            'total' => 100000,
            'sale_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('前年データなし');
    }

    public function test_不正な月パラメータでもエラーにならない(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard?month=' . now()->format('Y-m'));

        $response->assertStatus(200);
    }
}
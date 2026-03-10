<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SaleImportTest extends TestCase
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

    public function test_インポート画面が表示される(): void
    {
        $response = $this->actingAs($this->user)->get('/sales-import');

        $response->assertStatus(200);
    }

    public function test_CSVインポートが成功する(): void
    {
        $csv = "store_code,product_code,quantity,sale_date\n";
        $csv .= "TEST-001,P-001,2,2026-03-01\n";
        $csv .= "TEST-001,P-001,3,2026-03-02\n";

        $file = UploadedFile::fake()->createWithContent('sales.csv', $csv);

        $response = $this->actingAs($this->user)->post('/sales-import', [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('sales', [
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'total' => 200000,
        ]);
        $this->assertDatabaseHas('sales', [
            'quantity' => 3,
            'total' => 300000,
        ]);
    }

    public function test_不正な店舗コードでエラーになる(): void
    {
        $csv = "store_code,product_code,quantity,sale_date\n";
        $csv .= "INVALID-999,P-001,1,2026-03-01\n";

        $file = UploadedFile::fake()->createWithContent('sales.csv', $csv);

        $response = $this->actingAs($this->user)->post('/sales-import', [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('import_errors');
    }

    public function test_不正な商品コードでエラーになる(): void
    {
        $csv = "store_code,product_code,quantity,sale_date\n";
        $csv .= "TEST-001,INVALID-999,1,2026-03-01\n";

        $file = UploadedFile::fake()->createWithContent('sales.csv', $csv);

        $response = $this->actingAs($this->user)->post('/sales-import', [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('import_errors');
    }

    public function test_数量0でエラーになる(): void
    {
        $csv = "store_code,product_code,quantity,sale_date\n";
        $csv .= "TEST-001,P-001,0,2026-03-01\n";

        $file = UploadedFile::fake()->createWithContent('sales.csv', $csv);

        $response = $this->actingAs($this->user)->post('/sales-import', [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('import_errors');
    }

    public function test_不正な日付でエラーになる(): void
    {
        $csv = "store_code,product_code,quantity,sale_date\n";
        $csv .= "TEST-001,P-001,1,invalid-date\n";

        $file = UploadedFile::fake()->createWithContent('sales.csv', $csv);

        $response = $this->actingAs($this->user)->post('/sales-import', [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('import_errors');
    }

    public function test_必須カラム不足でエラーになる(): void
    {
        $csv = "store_code,quantity\n";
        $csv .= "TEST-001,1\n";

        $file = UploadedFile::fake()->createWithContent('sales.csv', $csv);

        $response = $this->actingAs($this->user)->post('/sales-import', [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_テンプレートCSVがダウンロードできる(): void
    {
        $response = $this->actingAs($this->user)->get('/sales-import/template');

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="sales_import_template.csv"');
    }
}
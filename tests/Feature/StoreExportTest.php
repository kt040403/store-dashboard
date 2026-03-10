<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        $this->area = Area::create(['name' => 'テストエリア', 'region' => 'テスト']);
        Store::create([
            'area_id' => $this->area->id,
            'name' => 'テスト店舗',
            'code' => 'TEST-001',
        ]);
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_店舗Excelエクスポートができる(): void
    {
        $response = $this->actingAs($this->user)->get('/stores-export?format=xlsx');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_店舗CSVエクスポートができる(): void
    {
        $response = $this->actingAs($this->user)->get('/stores-export?format=csv');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
    }

    public function test_エリアで絞り込んでエクスポートできる(): void
    {
        $response = $this->actingAs($this->user)->get('/stores-export?format=xlsx&area_id=' . $this->area->id);

        $response->assertStatus(200);
    }
}
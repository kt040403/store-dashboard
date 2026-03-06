<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['store', 'product.category']);

        // 検索: 店舗
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        // 検索: 期間
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        // 検索: キーワード（商品名）
        if ($request->filled('keyword')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%');
            });
        }

        // ソート
        $sortBy = $request->get('sort', 'sale_date');
        $sortDir = $request->get('direction', 'desc');
        $allowedSorts = ['sale_date', 'total', 'quantity'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('sale_date', 'desc');
        }

        $sales = $query->paginate(20)->withQueryString();
        $stores = Store::orderBy('name')->get();

        return view('sales.index', compact('sales', 'stores'));
    }

    public function create()
    {
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('sales.create', compact('stores', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'sale_date' => 'required|date',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $validated['unit_price'] = $product->price;
        $validated['total'] = $product->price * $validated['quantity'];

        Sale::create($validated);

        return redirect()->route('sales.index')->with('success', '売上を登録しました。');
    }

    public function edit(Sale $sale)
    {
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('sales.edit', compact('sale', 'stores', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'sale_date' => 'required|date',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $validated['unit_price'] = $product->price;
        $validated['total'] = $product->price * $validated['quantity'];

        $sale->update($validated);

        return redirect()->route('sales.index')->with('success', '売上を更新しました。');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        return redirect()->route('sales.index')->with('success', '売上を削除しました。');
    }
}
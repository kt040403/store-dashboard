<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📋 売上一覧
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('sales.import.show') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    📤 インポート
                </a>
                <a href="{{ route('sales.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    📥 Excel
                </a>
                <a href="{{ route('sales.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    📥 CSV
                </a>
                <a href="{{ route('sales.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    + 新規登録
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 成功メッセージ --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            {{-- 検索フォーム --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">店舗</label>
                        <select name="store_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">すべて</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">開始日</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">終了日</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">商品名</label>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="キーワード" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white py-2 px-4 rounded text-sm">
                            検索
                        </button>
                        <a href="{{ route('sales.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded text-sm">
                            リセット
                        </a>
                    </div>
                </form>
            </div>

            {{-- 売上テーブル --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'sale_date', 'direction' => request('sort') === 'sale_date' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                    売上日 {!! request('sort') === 'sale_date' ? (request('direction') === 'asc' ? '▲' : '▼') : '' !!}
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">店舗</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">商品</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">カテゴリ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                <a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'quantity', 'direction' => request('sort') === 'quantity' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                    数量 {!! request('sort') === 'quantity' ? (request('direction') === 'asc' ? '▲' : '▼') : '' !!}
                                </a>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">単価</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                <a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'total', 'direction' => request('sort') === 'total' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                    合計 {!! request('sort') === 'total' ? (request('direction') === 'asc' ? '▲' : '▼') : '' !!}
                                </a>
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($sales as $sale)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $sale->sale_date->format('Y/m/d') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $sale->store->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $sale->product->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $sale->product->category->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($sale->quantity) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">¥{{ number_format($sale->unit_price) }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">¥{{ number_format($sale->total) }}</td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <a href="{{ route('sales.edit', $sale) }}" class="text-blue-600 hover:text-blue-800 mr-3">編集</a>
                                    <form method="POST" action="{{ route('sales.destroy', $sale) }}" class="inline" onsubmit="return confirm('削除しますか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">削除</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">データがありません</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- ページネーション --}}
                <div class="px-6 py-4 border-t">
                    {{ $sales->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
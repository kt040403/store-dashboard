<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📤 売上データインポート
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('import_errors'))
                <div class="bg-yellow-50 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-6">
                    <div class="font-bold mb-2">インポートエラー詳細:</div>
                    <ul class="text-sm space-y-1">
                        @foreach (session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">CSVファイルからインポート</h3>

                <form method="POST" action="{{ route('sales.import') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">CSVファイルを選択</label>
                        <input type="file" name="csv_file" accept=".csv,.txt" class="w-full border border-gray-300 rounded-md shadow-sm text-sm p-2" required>
                        @error('csv_file') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-between items-center">
                        <a href="{{ route('sales.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            戻る
                        </a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            インポート実行
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">CSVフォーマット</h3>

                <div class="mb-4">
                    <a href="{{ route('sales.import.template') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        📥 テンプレートCSVをダウンロード
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">カラム名</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">説明</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">例</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-2 font-mono text-gray-900">store_code</td>
                                <td class="px-4 py-2 text-gray-600">店舗コード</td>
                                <td class="px-4 py-2 text-gray-500">TK-001</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono text-gray-900">product_code</td>
                                <td class="px-4 py-2 text-gray-600">商品コード</td>
                                <td class="px-4 py-2 text-gray-500">NC-001</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono text-gray-900">quantity</td>
                                <td class="px-4 py-2 text-gray-600">数量（1以上）</td>
                                <td class="px-4 py-2 text-gray-500">1</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono text-gray-900">sale_date</td>
                                <td class="px-4 py-2 text-gray-600">売上日（YYYY-MM-DD）</td>
                                <td class="px-4 py-2 text-gray-500">2026-03-01</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-sm text-gray-500">
                    <p>※ 単価・合計は商品マスタから自動計算されます</p>
                    <p>※ 文字コードはUTF-8（BOM付き可）に対応</p>
                    <p>※ 最大ファイルサイズ: 5MB</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
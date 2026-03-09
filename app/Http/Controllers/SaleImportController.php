<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleImportController extends Controller
{
    public function show()
    {
        return view('sales.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->with('error', 'ファイルを開けませんでした。');
        }

        // BOM除去
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // ヘッダー行を読み込み
        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return back()->with('error', 'CSVのヘッダーを読み取れませんでした。');
        }

        $header = array_map('trim', $header);

        // 必須カラムの確認
        $requiredColumns = ['store_code', 'product_code', 'quantity', 'sale_date'];
        $missingColumns = array_diff($requiredColumns, $header);
        if (!empty($missingColumns)) {
            fclose($handle);
            return back()->with('error', '必須カラムがありません: ' . implode(', ', $missingColumns));
        }

        // 店舗・商品マスタをキャッシュ
        $stores = Store::pluck('id', 'code')->toArray();
        $products = Product::all()->keyBy('code');

        $successCount = 0;
        $errors = [];
        $lineNumber = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                if (count($row) !== count($header)) {
                    $errors[] = "{$lineNumber}行目: カラム数が一致しません";
                    continue;
                }

                $data = array_combine($header, array_map('trim', $row));

                // 店舗コード確認
                if (!isset($stores[$data['store_code']])) {
                    $errors[] = "{$lineNumber}行目: 店舗コード '{$data['store_code']}' が見つかりません";
                    continue;
                }

                // 商品コード確認
                if (!isset($products[$data['product_code']])) {
                    $errors[] = "{$lineNumber}行目: 商品コード '{$data['product_code']}' が見つかりません";
                    continue;
                }

                $product = $products[$data['product_code']];
                $quantity = (int) $data['quantity'];

                if ($quantity < 1) {
                    $errors[] = "{$lineNumber}行目: 数量は1以上を指定してください";
                    continue;
                }

                // 日付バリデーション
                $validator = Validator::make(['sale_date' => $data['sale_date']], [
                    'sale_date' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $errors[] = "{$lineNumber}行目: 日付の形式が不正です '{$data['sale_date']}'";
                    continue;
                }

                Sale::create([
                    'store_id' => $stores[$data['store_code']],
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'total' => $product->price * $quantity,
                    'sale_date' => $data['sale_date'],
                ]);

                $successCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->with('error', 'インポート中にエラーが発生しました: ' . $e->getMessage());
        }

        fclose($handle);

        $message = "{$successCount}件のデータをインポートしました。";
        if (!empty($errors)) {
            $message .= '（' . count($errors) . '件のエラーあり）';
        }

        return back()
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    public function template()
    {
        $headers = ['store_code', 'product_code', 'quantity', 'sale_date'];
        $example = ['TK-001', 'NC-001', '1', '2026-03-01'];

        $callback = function () use ($headers, $example) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM
            fputcsv($handle, $headers);
            fputcsv($handle, $example);
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="sales_import_template.csv"',
        ]);
    }
}
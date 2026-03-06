<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SaleExportController extends Controller
{
    public function export(Request $request)
    {
        $format = $request->get('format', 'xlsx');

        $query = Sale::with(['store', 'product.category']);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }
        if ($request->filled('keyword')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%');
            });
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('売上データ');

        // ヘッダー
        $headers = ['売上日', '店舗', '商品', 'カテゴリ', '数量', '単価', '合計'];
        foreach ($headers as $col => $header) {
            $cell = chr(65 + $col) . '1';
            $sheet->setCellValue($cell, $header);
        }

        // ヘッダースタイル
        $headerRange = 'A1:G1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2937'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // データ行
        $row = 2;
        foreach ($sales as $sale) {
            $sheet->setCellValue("A{$row}", $sale->sale_date->format('Y/m/d'));
            $sheet->setCellValue("B{$row}", $sale->store->name);
            $sheet->setCellValue("C{$row}", $sale->product->name);
            $sheet->setCellValue("D{$row}", $sale->product->category->name);
            $sheet->setCellValue("E{$row}", $sale->quantity);
            $sheet->setCellValue("F{$row}", $sale->unit_price);
            $sheet->setCellValue("G{$row}", $sale->total);
            $row++;
        }

        // 合計行
        $lastDataRow = $row - 1;
        $sheet->setCellValue("A{$row}", '合計');
        $sheet->setCellValue("G{$row}", "=SUM(G2:G{$lastDataRow})");
        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);

        // 数値フォーマット
        $sheet->getStyle("E2:E{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("F2:G{$row}")->getNumberFormat()->setFormatCode('¥#,##0');

        // 列幅自動調整
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 罫線
        $sheet->getStyle("A1:G{$row}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // ダウンロード
        $filename = '売上データ_' . date('Ymd_His');

        if ($format === 'csv') {
            $filename .= '.csv';
            $writer = new Csv($spreadsheet);
            $writer->setUseBOM(true);
            $contentType = 'text/csv';
        } else {
            $filename .= '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'export');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => $contentType,
        ])->deleteFileAfterSend(true);
    }
}
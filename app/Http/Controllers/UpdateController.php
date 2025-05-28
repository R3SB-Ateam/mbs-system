<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class UpdateController extends Controller
{
    public function update(Request $request)
    {
        if ($request->isMethod('post') && $request->hasFile('excel_file')) {
            $file = $request->file('excel_file');
            $fileName = $file->getClientOriginalName();

            // 店舗ID判定（ファイル名に含まれる）
            $storeId = null;
            if (str_contains($fileName, '緑橋')) {
                $storeId = 1;
            } elseif (str_contains($fileName, '今里')) {
                $storeId = 2;
            } elseif (str_contains($fileName, '深江橋')) {
                $storeId = 3;
            } else {
                return back()->withErrors(['ファイル名に店舗情報が含まれていません。']);
            }

            // Excel 読み込み
            $spreadsheet = IOFactory::load($file->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $excelCustomers = [];
            foreach ($rows as $index => $row) {
                if ($index === 1) continue; // ヘッダー

                $customerId = $row['A'] ?? null;
                if (!$customerId) continue;

                $excelCustomers[$customerId] = [
                    'store_id' => $storeId,
                    'customer_id' => $customerId,
                    'name' => $row['B'] ?? null,
                    'staff' => $row['C'] ?? null,
                    'address' => $row['D'] ?? null,
                    'phone_number' => $row['E'] ?? null,
                    'delivery_location' => $row['F'] ?? null,
                    'remarks' => $row['G'] ?? null,
                    'registration_date' => $row['H'] ?? date('Y-m-d'),
                    'deletion_flag' => null, // 明示的に削除解除
                ];
            }

            // DB顧客取得（未削除のみ）
            $dbCustomers = DB::table('customers')
                ->where('store_id', $storeId)
                ->get()
                ->keyBy('customer_id');

            // 追加・更新処理
            foreach ($excelCustomers as $id => $excelData) {
                if (!$dbCustomers->has($id)) {
                    // 新規追加
                    DB::table('customers')->insert($excelData);
                } else {
                    $dbData = (array) $dbCustomers[$id];

                    // 差分チェック
                    $updateNeeded = false;
                    foreach ($excelData as $key => $value) {
                        if ($dbData[$key] != $value) {
                            $updateNeeded = true;
                            break;
                        }
                    }

                    if ($updateNeeded) {
                        DB::table('customers')
                            ->where('store_id', $storeId)
                            ->where('customer_id', $id)
                            ->update($excelData);
                    }

                    // 処理済として除外
                    unset($dbCustomers[$id]);
                }
            }

            // Excelにないデータ → 削除フラグ
            foreach ($dbCustomers as $id => $unusedCustomer) {
                DB::table('customers')
                    ->where('store_id', $storeId)
                    ->where('customer_id', $id)
                    ->update(['deletion_flag' => now()]);
            }

            return redirect()->route('customers.edit')->with('success', 'ファイルが正常に処理されました。');
        }

        return view('update.customers_update');
    }
}

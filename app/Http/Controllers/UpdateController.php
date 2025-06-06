<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class UpdateController extends Controller
{
    // 設定定数
    private const STORE_MAPPING = [
        '緑橋' => 1,
        '今里' => 2,
        '深江橋' => 3,
    ];

    private const REQUIRED_COLUMNS = ['A', 'B']; // customer_id, name
    private const BATCH_SIZE = 500; // バッチ処理サイズ
    private const MAX_FILE_SIZE = 10240; // 10MB in KB


    public function edit()
    {
        // フォーム画面を返す
        return view('update.customers_update'); // Blade ファイル名に応じて修正
    }

    public function update(Request $request)
    {
        if ($request->isMethod('post') && $request->hasFile('excel_file')) {
            
            // 基本バリデーション
            $validation = $this->validateUploadedFile($request);
            if ($validation !== true) {
                return back()->withErrors([$validation]);
            }

            try {
                $result = $this->processExcelFile($request->file('excel_file'));
                
                $message = $this->buildSuccessMessage($result);
                return redirect()->route('edit')->with('success', $message);
                
            } catch (Exception $e) {
                Log::error('Excel処理エラー', [
                    'error' => $e->getMessage(),
                    'file_name' => $request->file('excel_file')->getClientOriginalName(),
                    'memory_usage' => $this->formatBytes(memory_get_usage())
                ]);
                
                return back()->withErrors(['処理中にエラーが発生しました: ' . $e->getMessage()]);
            }
        }

        return view('update.customers_update');
    }

    private function validateUploadedFile(Request $request)
    {
        $file = $request->file('excel_file');
        
        // ファイルサイズチェック
        if ($file->getSize() > self::MAX_FILE_SIZE * 1024) {
            return 'ファイルサイズは10MB以下にしてください。';
        }

        // 拡張子チェック
        $allowedExtensions = ['xlsx', 'xls'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            return 'Excel形式（.xlsx, .xls）のファイルを選択してください。';
        }

        // ファイル名に店舗情報が含まれているかチェック
        $fileName = $file->getClientOriginalName();
        $hasStoreName = false;
        foreach (array_keys(self::STORE_MAPPING) as $storeName) {
            if (str_contains($fileName, $storeName)) {
                $hasStoreName = true;
                break;
            }
        }
        
        if (!$hasStoreName) {
            return 'ファイル名に店舗情報（緑橋/今里/深江橋）が含まれている必要があります。';
        }

        return true;
    }

    private function processExcelFile($file)
    {
        $fileName = $file->getClientOriginalName();
        $storeId = $this->getStoreIdFromFileName($fileName);
        
        if (!$storeId) {
            throw new Exception('ファイル名に店舗情報が含まれていません。');
        }

        // メモリ監視開始
        $initialMemory = memory_get_usage();
        Log::info('Excel処理開始', [
            'store_id' => $storeId,
            'file_name' => $fileName,
            'memory_usage' => $this->formatBytes($initialMemory)
        ]);

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            // データ検証
            $this->validateExcelData($rows);

            // 顧客データ処理
            $excelCustomers = $this->parseExcelData($rows, $storeId);
            $result = $this->updateCustomerData($excelCustomers, $storeId);

            // 処理完了ログ
            $finalMemory = memory_get_usage();
            Log::info('Excel処理完了', [
                'store_id' => $storeId,
                'result' => $result,
                'memory_usage' => $this->formatBytes($finalMemory),
                'memory_increase' => $this->formatBytes($finalMemory - $initialMemory)
            ]);

            return $result;

        } finally {
            // メモリクリーンアップ
            if (isset($spreadsheet)) {
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            }
            gc_collect_cycles();
        }
    }

    private function getStoreIdFromFileName($fileName)
    {
        foreach (self::STORE_MAPPING as $storeName => $storeId) {
            if (str_contains($fileName, $storeName)) {
                return $storeId;
            }
        }
        return null;
    }

    private function validateExcelData($rows)
    {
        if (count($rows) < 2) {
            throw new Exception('Excelファイルにデータが含まれていません。');
        }

        // ヘッダー行の検証
        $headerRow = $rows[1];
        if (empty(trim($headerRow['A'] ?? '')) || empty(trim($headerRow['B'] ?? ''))) {
            throw new Exception('Excelファイルの形式が正しくありません。A列：顧客ID、B列：名前は必須です。');
        }
    }

    private function parseExcelData($rows, $storeId)
    {
        $excelCustomers = [];
        $duplicateIds = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index === 1) continue; // ヘッダー行をスキップ

            try {
                $customerId = trim($row['A'] ?? '');
                if (empty($customerId)) continue; // 空行をスキップ

                // 重複チェック
                if (isset($excelCustomers[$customerId])) {
                    $duplicateIds[] = $customerId;
                    continue;
                }

                $customerData = $this->buildCustomerData($row, $storeId, $index);
                $excelCustomers[$customerId] = $customerData;

            } catch (Exception $e) {
                $errors[] = "行{$index}: " . $e->getMessage();
            }
        }

        // エラーチェック
        if (!empty($errors)) {
            throw new Exception("データ形式エラー:\n" . implode("\n", array_slice($errors, 0, 5)) . 
                              (count($errors) > 5 ? "\n...他" . (count($errors) - 5) . "件のエラーあり" : ""));
        }

        if (!empty($duplicateIds)) {
            throw new Exception('重複した顧客IDがあります: ' . implode(', ', array_slice(array_unique($duplicateIds), 0, 10)) .
                              (count($duplicateIds) > 10 ? '...他多数' : ''));
        }

        if (empty($excelCustomers)) {
            throw new Exception('処理可能なデータがありません。');
        }

        return $excelCustomers;
    }

    private function buildCustomerData($row, $storeId, $index)
{
    $customerId = trim($row['A'] ?? '');
    $name = trim($row['C'] ?? ''); // C列：顧客名

    // 顧客ID必須 & 5桁チェック
    if (!preg_match('/^\d{5}$/', $customerId)) {
        throw new Exception("顧客IDが無効です（{$customerId}）: {$index} 行目");
    }

    if (empty($name)) {
        throw new Exception("顧客名が入力されていません（顧客ID: {$customerId}）");
    }

    $phoneNumber = $this->validateAndFormatPhoneNumber($row['F'] ?? '', $customerId); // F列：電話番号

    return [
        'customer_id' => $customerId,                  // A列：顧客ID
        'store_id' => $storeId,
        'name' => $name,                               // C列：顧客名
        'staff' => trim($row['D'] ?? ''),              // D列：担当者名
        'address' => trim($row['E'] ?? ''),            // E列：住所
        'phone_number' => $phoneNumber,                // F列：電話番号
        'delivery_location' => trim($row['G'] ?? ''),  // G列：配達条件等
        'remarks' => trim($row['H'] ?? ''),            // H列：備考
        'registration_date' => now()->toDateString(),  // 任意に固定値（または他列から取得するなら指定）
        'deletion_flag' => 0,
    ];
}


    private function validateAndFormatPhoneNumber($phoneNumber, $customerId)
    {
        if (empty($phoneNumber)) return '';

        $cleaned = preg_replace('/[^0-9\-]/', '', $phoneNumber);
        $numbersOnly = preg_replace('/[^0-9]/', '', $cleaned);
        
        // 基本的なバリデーション
        if (!empty($numbersOnly) && (strlen($numbersOnly) < 10 || strlen($numbersOnly) > 11)) {
            throw new Exception("電話番号の形式が正しくありません（顧客ID: {$customerId}）: {$phoneNumber}");
        }

        return $this->formatPhoneNumber($cleaned);
    }

    private function updateCustomerData($excelCustomers, $storeId)
    {
        $addedCount = 0;
        $updatedCount = 0;
        $deletedCount = 0;

        DB::beginTransaction();
        try {
            // 既存データ取得
            $dbCustomers = DB::table('customers')
                ->where('store_id', $storeId)
                ->where('deletion_flag', 0)
                ->get()
                ->keyBy('customer_id');

            // バッチ処理で効率化
            $chunks = array_chunk($excelCustomers, self::BATCH_SIZE, true);
            
            foreach ($chunks as $chunk) {
                $chunkResult = $this->processCustomerChunk($chunk, $dbCustomers, $storeId);
                $addedCount += $chunkResult['added'];
                $updatedCount += $chunkResult['updated'];
            }

            // 削除フラグ設定（Excelにないデータ）
            foreach ($dbCustomers as $customerId => $unusedCustomer) {
                DB::table('customers')
                    ->where('store_id', $storeId)
                    ->where('customer_id', $customerId)
                    ->update([
                        'deletion_flag' => 1,
                        'updated_at' => now()
                    ]);
                $deletedCount++;
            }

            DB::commit();

            // 処理結果ログ
            Log::info("顧客データ更新完了", [
                'store_id' => $storeId,
                'added' => $addedCount,
                'updated' => $updatedCount,
                'deleted' => $deletedCount,
                'total_processed' => $addedCount + $updatedCount + $deletedCount
            ]);

            return [
                'added' => $addedCount,
                'updated' => $updatedCount,
                'deleted' => $deletedCount
            ];

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function processCustomerChunk($chunk, &$dbCustomers, $storeId)
    {
        $addedCount = 0;
        $updatedCount = 0;
        $insertData = [];

        foreach ($chunk as $customerId => $excelData) {
            $excelData = (array) $excelData;

            if (!$dbCustomers->has($customerId)) {
                // created_at を追加せず保存
                $insertData[] = $excelData;
                $addedCount++;
            } else {
                $dbData = (array) $dbCustomers[$customerId];
                if ($this->needsUpdate($dbData, $excelData)) {
                    DB::table('customers')
                        ->where('store_id', $storeId)
                        ->where('customer_id', $customerId)
                        ->update($excelData);
                    $updatedCount++;
                }
                $dbCustomers->forget($customerId);
            }
        }



        // バッチ挿入
        if (!empty($insertData)) {
            DB::table('customers')->insert($insertData);
        }

        return ['added' => $addedCount, 'updated' => $updatedCount];
    }

    private function needsUpdate($dbData, $excelData)
    {
        $checkFields = ['name', 'staff', 'address', 'phone_number', 'delivery_location', 'remarks'];
        
        foreach ($checkFields as $field) {
            if (($dbData[$field] ?? '') !== ($excelData[$field] ?? '')) {
                return true;
            }
        }
        return false;
    }

    private function formatPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) return '';
        
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // 携帯電話（11桁）
        if (strlen($cleaned) === 11 && str_starts_with($cleaned, '0')) {
            return substr($cleaned, 0, 3) . '-' . substr($cleaned, 3, 4) . '-' . substr($cleaned, 7);
        }
        
        // 固定電話（10桁）
        if (strlen($cleaned) === 10 && str_starts_with($cleaned, '0')) {
            return substr($cleaned, 0, 2) . '-' . substr($cleaned, 2, 4) . '-' . substr($cleaned, 6);
        }
        
        return $phoneNumber; // 元の値を返す
    }

    private function parseDate($dateValue)
    {
        if (empty($dateValue)) return null;
        
        try {
            if (is_numeric($dateValue)) {
                // Excelの日付シリアル値の場合
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                return $date->format('Y-m-d');
            }
            
            // 文字列の場合
            $date = new \DateTime($dateValue);
            return $date->format('Y-m-d');
            
        } catch (Exception $e) {
            return null; // エラーの場合はnullを返す
        }
    }

    private function buildSuccessMessage($result)
    {
        $messages = [];
        
        if ($result['added'] > 0) {
            $messages[] = "新規追加: {$result['added']}件";
        }
        
        if ($result['updated'] > 0) {
            $messages[] = "更新: {$result['updated']}件";
        }
        
        if ($result['deleted'] > 0) {
            $messages[] = "削除: {$result['deleted']}件";
        }

        $baseMessage = 'ファイルが正常に処理されました。';
        return $baseMessage . (empty($messages) ? '' : ' (' . implode(', ', $messages) . ')');
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
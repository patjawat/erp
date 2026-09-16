<?php

namespace app\modules\accounting\services;

use app\modules\accounting\models\AccountingChartAccount;
use app\modules\accounting\models\AccountingChartVersion;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;

class AccountingChartImportService
{
    public const MAX_ROWS = 5000;

    public function preview(string $path, string $originalName, int $fiscalYear, string $versionCode, string $title, ?string $sheetName = null): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $names = $reader->listWorksheetNames($path);
        $resolvedSheet = $this->resolveSheetName($names, $sheetName);
        if (method_exists($reader, 'setLoadSheetsOnly')) {
            $reader->setLoadSheetsOnly([$resolvedSheet]);
        }
        $book = $reader->load($path);
        $sheet = $book->getSheetByName($resolvedSheet);
        if (!$sheet) {
            throw new \RuntimeException('ไม่พบแท็บ “' . $resolvedSheet . '” ในไฟล์');
        }
        $codeHeader = trim((string) $sheet->getCell('A1')->getFormattedValue());
        $nameHeader = trim((string) $sheet->getCell('B1')->getFormattedValue());
        if (!in_array($codeHeader, ['รหัส', 'รหัสบัญชี'], true) || !in_array($nameHeader, ['ชื่อบัญชี', 'ชื่อ'], true)) {
            throw new \RuntimeException('หัวตารางไม่ถูกต้อง: คอลัมน์ A ต้องเป็น “รหัส” และคอลัมน์ B ต้องเป็น “ชื่อบัญชี”');
        }
        $highestRow = $sheet->getHighestDataRow();
        if ($highestRow > self::MAX_ROWS) {
            throw new \RuntimeException('ไฟล์มีข้อมูลเกิน ' . number_format(self::MAX_ROWS) . ' แถว กรุณาตรวจสอบไฟล์');
        }

        $baseline = $this->baselineAccounts($fiscalYear);
        $seen = [];
        $rows = [];
        $counts = ['new' => 0, 'unchanged' => 0, 'changed' => 0, 'invalid' => 0];
        for ($row = 2; $row <= $highestRow; $row++) {
            $rawCode = $sheet->getCell('A' . $row)->getValue();
            $name = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());
            if (($rawCode === null || $rawCode === '') && $name === '') {
                continue;
            }
            $code = self::normalizeCode($rawCode);
            $errors = [];
            if ($code === null) {
                $errors[] = 'รหัสต้องอยู่ในรูป 10 หลัก.3 หลัก';
            }
            if ($name === '') {
                $errors[] = 'ไม่มีชื่อบัญชี';
            }
            if ($code !== null && isset($seen[$code])) {
                $errors[] = 'รหัสซ้ำในไฟล์ (พบครั้งแรกที่แถว ' . $seen[$code] . ')';
            }
            if ($code !== null) {
                $seen[$code] ??= $row;
            }

            $result = 'invalid';
            $oldName = null;
            if (!$errors) {
                $oldName = $baseline[$code] ?? null;
                if ($oldName === null) {
                    $result = 'new';
                } elseif ($this->normalizeName($oldName) === $this->normalizeName($name)) {
                    $result = 'unchanged';
                } else {
                    $result = 'changed';
                }
            }
            $counts[$result]++;
            $rows[] = [
                'row' => $row,
                'code' => $code ?: trim((string) $rawCode),
                'name' => $name,
                'category' => $code ? substr($code, 0, 1) : null,
                'result' => $result,
                'old_name' => $oldName,
                'errors' => $errors,
            ];
        }
        if (!$rows) {
            throw new \RuntimeException('ไม่พบรายการบัญชี กรุณาตรวจว่าคอลัมน์ A เป็นรหัสและคอลัมน์ B เป็นชื่อบัญชี');
        }

        return [
            'fiscal_year' => $fiscalYear,
            'version_code' => strtoupper($versionCode),
            'title' => $title,
            'sheet' => $resolvedSheet,
            'file_name' => $originalName,
            'file_hash' => hash_file('sha256', $path),
            'rows' => $rows,
            'counts' => $counts,
            'valid' => count($rows) - $counts['invalid'],
            'invalid' => $counts['invalid'],
        ];
    }

    public function save(array $preview): AccountingChartVersion
    {
        if (!empty($preview['invalid'])) {
            throw new \DomainException('ยังมีรายการที่ไม่ผ่านการตรวจสอบ จึงไม่สามารถนำเข้าได้');
        }
        if (AccountingChartVersion::find()->where([
            'fiscal_year' => $preview['fiscal_year'],
            'version_code' => $preview['version_code'],
        ])->exists()) {
            throw new \DomainException('มีรหัสเวอร์ชันนี้ในปีงบประมาณเดียวกันแล้ว กรุณาเปลี่ยนรหัสเวอร์ชัน');
        }
        if (AccountingChartVersion::find()->where([
            'fiscal_year' => $preview['fiscal_year'],
            'source_file_hash' => $preview['file_hash'],
        ])->exists()) {
            throw new \DomainException('ไฟล์นี้เคยถูกนำเข้าในปีงบประมาณนี้แล้ว');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $version = new AccountingChartVersion([
                'fiscal_year' => $preview['fiscal_year'],
                'version_code' => $preview['version_code'],
                'title' => $preview['title'],
                'scope' => AccountingChartVersion::SCOPE_STANDARD,
                'status' => AccountingChartVersion::STATUS_DRAFT,
                'source_file_name' => $preview['file_name'],
                'source_file_hash' => $preview['file_hash'],
                'account_count' => $preview['valid'],
            ]);
            if (!$version->save()) {
                throw new \RuntimeException(implode(' ', $version->getFirstErrors()));
            }
            foreach ($preview['rows'] as $row) {
                $account = new AccountingChartAccount([
                    'version_id' => $version->id,
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'is_active' => true,
                ]);
                if (!$account->save()) {
                    throw new \RuntimeException('แถว ' . $row['row'] . ': ' . implode(' ', $account->getFirstErrors()));
                }
            }
            $transaction->commit();
            return $version;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function activate(AccountingChartVersion $version): void
    {
        if ($version->status === AccountingChartVersion::STATUS_ACTIVE) {
            return;
        }
        if ($version->status !== AccountingChartVersion::STATUS_DRAFT) {
            throw new \DomainException('เปิดใช้ได้เฉพาะผังบัญชีฉบับรอตรวจสอบ');
        }
        if ($version->account_count < 1 || AccountingChartAccount::find()->where(['version_id' => $version->id])->count() != $version->account_count) {
            throw new \DomainException('จำนวนรหัสในผังบัญชีไม่ตรงกับที่นำเข้า กรุณาตรวจสอบก่อนเปิดใช้');
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            AccountingChartVersion::updateAll(
                ['status' => AccountingChartVersion::STATUS_ARCHIVED, 'updated_at' => date('Y-m-d H:i:s')],
                ['fiscal_year' => $version->fiscal_year, 'status' => AccountingChartVersion::STATUS_ACTIVE]
            );
            $version->status = AccountingChartVersion::STATUS_ACTIVE;
            $version->activated_at = date('Y-m-d H:i:s');
            $version->activated_by = Yii::$app->user->id;
            if (!$version->save()) {
                throw new \RuntimeException(implode(' ', $version->getFirstErrors()));
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public static function normalizeCode($value): ?string
    {
        if (is_int($value) || is_float($value)) {
            $value = number_format((float) $value, 3, '.', '');
        }
        $value = trim((string) $value);
        if (preg_match('/^\d{10}\.\d{3}$/', $value)) {
            return $value;
        }
        return null;
    }

    private function baselineAccounts(int $fiscalYear): array
    {
        $version = AccountingChartVersion::find()
            ->where(['fiscal_year' => $fiscalYear])
            ->orderBy(new \yii\db\Expression("CASE status WHEN 'active' THEN 0 WHEN 'draft' THEN 1 ELSE 2 END, id DESC"))
            ->one();
        if (!$version) {
            return [];
        }
        return AccountingChartAccount::find()->select('name')->indexBy('code')->where(['version_id' => $version->id])->column();
    }

    private function resolveSheetName(array $names, ?string $requested): string
    {
        if (!$names) {
            throw new \RuntimeException('ไฟล์นี้ไม่มีแท็บข้อมูล');
        }
        $requested = trim((string) $requested);
        if ($requested === '') {
            return $names[0];
        }
        foreach ($names as $name) {
            if (mb_strtolower(trim($name)) === mb_strtolower($requested)) {
                return $name;
            }
        }
        throw new \RuntimeException('ไม่พบแท็บ “' . $requested . '” · แท็บที่มี: ' . implode(', ', $names));
    }

    private function normalizeName(string $value): string
    {
        return mb_strtolower(preg_replace('/[\s\-–—]+/u', '', trim($value)));
    }
}

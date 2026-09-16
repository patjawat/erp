<?php

namespace app\modules\accounting\services;

use app\modules\accounting\models\AccountingChartAccount;
use app\modules\accounting\models\AccountingChartMapping;
use app\modules\accounting\models\AccountingChartVersion;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;

class AccountingChartImportService
{
    public const MAX_ROWS = 5000;

    public function preview(string $path, string $originalName, int $fiscalYear, string $versionCode, string $title, ?string $sheetName = null, string $scope = AccountingChartVersion::SCOPE_STANDARD): array
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
        $hasHeader = in_array($codeHeader, ['รหัส', 'รหัสบัญชี'], true) && in_array($nameHeader, ['ชื่อบัญชี', 'ชื่อ'], true);
        $firstDataRow = $hasHeader ? 2 : 1;
        $highestRow = $sheet->getHighestDataRow();
        if ($highestRow > self::MAX_ROWS) {
            throw new \RuntimeException('ไฟล์มีข้อมูลเกิน ' . number_format(self::MAX_ROWS) . ' แถว กรุณาตรวจสอบไฟล์');
        }

        $baseline = $this->baselineAccounts($fiscalYear, $scope);
        $seen = [];
        $rows = [];
        $counts = ['new' => 0, 'unchanged' => 0, 'changed' => 0, 'invalid' => 0];
        for ($row = $firstDataRow; $row <= $highestRow; $row++) {
            $rawCode = $sheet->getCell('A' . $row)->getValue();
            $name = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());
            // แบบฟอร์มผังโรงพยาบาลมีแถว subtotal เช่น “รวมเงินสดในมือ” ซึ่งตั้งใจไม่มีรหัส
            // จึงไม่ใช่บัญชีที่ต้องนำเข้า แม้คอลัมน์ชื่อจะมีข้อความอยู่ก็ตาม
            if ($rawCode === null || trim((string) $rawCode) === '') {
                continue;
            }
            $code = self::normalizeCode($rawCode, $scope === AccountingChartVersion::SCOPE_HOSPITAL);
            $errors = [];
            if ($code === null) {
                $errors[] = $scope === AccountingChartVersion::SCOPE_HOSPITAL
                    ? 'รหัสต้องอยู่ในรูป 10 หลัก.3 หลัก หรือรหัสย่อย .2 หลัก'
                    : 'รหัสต้องอยู่ในรูป 10 หลัก.3 หลัก';
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
            'scope' => $scope,
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
            'scope' => $preview['scope'],
            'version_code' => $preview['version_code'],
        ])->exists()) {
            throw new \DomainException('มีรหัสเวอร์ชันนี้ในปีงบประมาณเดียวกันแล้ว กรุณาเปลี่ยนรหัสเวอร์ชัน');
        }
        if (AccountingChartVersion::find()->where([
            'fiscal_year' => $preview['fiscal_year'],
            'scope' => $preview['scope'],
            'source_file_hash' => $preview['file_hash'],
        ])->exists()) {
            throw new \DomainException('ไฟล์นี้เคยถูกนำเข้าในปีงบประมาณนี้แล้ว');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $version = new AccountingChartVersion([
                'fiscal_year' => $preview['fiscal_year'],
                'scope' => $preview['scope'],
                'version_code' => $preview['version_code'],
                'title' => $preview['title'],
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
            if ($version->scope === AccountingChartVersion::SCOPE_HOSPITAL) {
                $this->buildSuggestedMappings($version);
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
        if ($version->scope === AccountingChartVersion::SCOPE_HOSPITAL && !$this->mappingReadiness($version)['ready']) {
            throw new \DomainException('ยังตรวจการจับคู่บัญชีรายได้และค่าใช้จ่ายไม่ครบ จึงเปิดใช้ผังโรงพยาบาลไม่ได้');
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            AccountingChartVersion::updateAll(
                ['status' => AccountingChartVersion::STATUS_ARCHIVED, 'updated_at' => date('Y-m-d H:i:s')],
                ['fiscal_year' => $version->fiscal_year, 'scope' => $version->scope, 'status' => AccountingChartVersion::STATUS_ACTIVE]
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

    public static function normalizeCode($value, bool $allowHospitalSubcode = false): ?string
    {
        if (is_int($value) || is_float($value)) {
            $value = number_format((float) $value, 3, '.', '');
        }
        $value = trim((string) $value);
        $pattern = $allowHospitalSubcode ? '/^\d{10}\.\d{3}(?:\.\d{2})?$/' : '/^\d{10}\.\d{3}$/';
        if (preg_match($pattern, $value)) {
            return $value;
        }
        return null;
    }

    private function baselineAccounts(int $fiscalYear, string $scope): array
    {
        $version = AccountingChartVersion::find()
            ->where(['fiscal_year' => $fiscalYear, 'scope' => $scope])
            ->orderBy(new \yii\db\Expression("CASE status WHEN 'active' THEN 0 WHEN 'draft' THEN 1 ELSE 2 END, id DESC"))
            ->one();
        if (!$version) {
            return [];
        }
        return AccountingChartAccount::find()->select('name')->indexBy('code')->where(['version_id' => $version->id])->column();
    }

    private function buildSuggestedMappings(AccountingChartVersion $hospitalVersion): void
    {
        $standardVersion = AccountingChartVersion::find()
            ->where([
                'fiscal_year' => $hospitalVersion->fiscal_year,
                'scope' => AccountingChartVersion::SCOPE_STANDARD,
            ])
            ->orderBy(new \yii\db\Expression("CASE status WHEN 'active' THEN 0 WHEN 'draft' THEN 1 ELSE 2 END, id DESC"))
            ->one();
        if (!$standardVersion) return;

        $standardByCode = AccountingChartAccount::find()
            ->where(['version_id' => $standardVersion->id])
            ->indexBy('code')
            ->all();
        $hospitalAccounts = AccountingChartAccount::find()
            ->where(['version_id' => $hospitalVersion->id, 'category' => ['4', '5']])
            ->all();
        foreach ($hospitalAccounts as $hospitalAccount) {
            $standardAccount = $standardByCode[$hospitalAccount->code] ?? null;
            $matchType = AccountingChartMapping::TYPE_EXACT;
            $parentCode = self::standardCandidateCode($hospitalAccount->code);
            if (!$standardAccount && $parentCode !== null) {
                $standardAccount = $standardByCode[$parentCode] ?? null;
                $matchType = AccountingChartMapping::TYPE_PARENT;
            }
            if (!$standardAccount) continue;
            $mapping = new AccountingChartMapping([
                'fiscal_year' => $hospitalVersion->fiscal_year,
                'standard_version_id' => $standardVersion->id,
                'standard_account_id' => $standardAccount->id,
                'hospital_version_id' => $hospitalVersion->id,
                'hospital_account_id' => $hospitalAccount->id,
                'match_type' => $matchType,
                'status' => $matchType === AccountingChartMapping::TYPE_EXACT
                    ? AccountingChartMapping::STATUS_CONFIRMED
                    : AccountingChartMapping::STATUS_SUGGESTED,
            ]);
            if (!$mapping->save()) {
                throw new \RuntimeException('สร้างคำแนะนำการจับคู่รหัส ' . $hospitalAccount->code . ' ไม่สำเร็จ');
            }
        }
    }

    public static function standardCandidateCode(string $hospitalCode): ?string
    {
        return preg_match('/^(\d{10}\.\d{3})\.\d{2}$/', $hospitalCode, $match) ? $match[1] : null;
    }

    public function mappingReadiness(AccountingChartVersion $hospitalVersion): array
    {
        $total = (int) AccountingChartAccount::find()
            ->where(['version_id' => $hospitalVersion->id, 'category' => ['4', '5']])
            ->count();
        $counts = AccountingChartMapping::find()
            ->select(['status', 'total' => new \yii\db\Expression('COUNT(*)')])
            ->where(['hospital_version_id' => $hospitalVersion->id])
            ->groupBy('status')
            ->indexBy('status')
            ->column();
        $confirmed = (int) ($counts[AccountingChartMapping::STATUS_CONFIRMED] ?? 0);
        $suggested = (int) ($counts[AccountingChartMapping::STATUS_SUGGESTED] ?? 0);
        $rejected = (int) ($counts[AccountingChartMapping::STATUS_REJECTED] ?? 0);
        $unmapped = max(0, $total - $confirmed - $suggested - $rejected);
        $standardVersions = (int) AccountingChartMapping::find()
            ->select('standard_version_id')->distinct()
            ->where(['hospital_version_id' => $hospitalVersion->id])->count();

        return compact('total', 'confirmed', 'suggested', 'rejected', 'unmapped') + [
            'ready' => $total > 0 && $confirmed === $total && $standardVersions === 1,
        ];
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

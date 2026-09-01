<?php

namespace app\modules\finance\controllers;

use app\modules\finance\services\PayrollReadinessService;
use app\modules\finance\services\PayrollPeriodService;
use DateTimeImmutable;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;
use yii\db\Query;
use app\modules\hr\models\Employees;

class PayrollController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index'], 'roles' => ['payrollView']],
                ['allow' => true, 'actions' => ['open-period', 'add-employee', 'exclude', 'restore'], 'roles' => ['payrollPrepare']],
                ['allow' => true, 'actions' => ['settings', 'save-bank-account', 'download-bank-template', 'import-bank-csv', 'add-item-type', 'save-item-type', 'create-item-type', 'update-item-type', 'create-contribution-rule'], 'roles' => ['payrollBankManage']],
                ['allow' => true, 'actions' => ['employee-items', 'add-item-employees', 'save-item-employees', 'create-employee-item', 'create-employee-items-bulk', 'update-employee-item', 'save-item-amounts', 'toggle-item-type', 'remove-employee-item', 'reorder-employee-items'], 'roles' => ['payrollBankManage']],
            ]],
        ]);
    }

    public function actionIndex()
    {
        $q = mb_substr(trim((string) Yii::$app->request->get('q', '')), 0, 100);
        $employeeTypeId = (int) Yii::$app->request->get('employee_type_id');
        $departmentId = (int) Yii::$app->request->get('department');
        $bankStatus = (string) Yii::$app->request->get('bank_status', '');
        $bankCode = strtoupper(trim((string) Yii::$app->request->get('bank_code', '')));
        if (!in_array($bankStatus, ['', 'registered', 'missing'], true)) $bankStatus = '';
        if ($bankCode !== '' && !isset($this->bankOptions()[$bankCode])) $bankCode = '';
        $month = date('Y-m');
        $start = new DateTimeImmutable($month . '-01');
        $rows = (new PayrollReadinessService())->build($start->format('Y-m-d'), $start->modify('last day of this month')->format('Y-m-d'), $q);
        $rows = array_values(array_filter($rows, static function (array $row) use ($employeeTypeId): bool {
            return (string) $row['employee']->status === '1'
                && ($employeeTypeId <= 0 || (int) $row['employee']->employee_type_id === $employeeTypeId);
        }));
        $employeeIds = array_map(static fn(array $row): int => (int) $row['employee_id'], $rows);
        $bankByEmployee = [];
        if ($employeeIds) {
            foreach ((new Query())->select(['id', 'employee_id', 'bank_code', 'account_last4', 'status'])
                ->from('{{%payroll_bank_account}}')->where(['employee_id' => $employeeIds, 'is_active' => 1])
                ->orderBy(['verified_at' => SORT_DESC, 'id' => SORT_DESC])->all() as $bankRow) {
                $employeeId = (int) $bankRow['employee_id'];
                if (!isset($bankByEmployee[$employeeId])) $bankByEmployee[$employeeId] = $bankRow;
            }
        }
        foreach ($rows as &$row) $row['bank_account'] = $bankByEmployee[(int) $row['employee_id']] ?? null;
        unset($row);
        $departmentIds = array_values(array_unique(array_filter(array_map(
            static fn(array $row): int => (int) $row['employee']->department,
            $rows
        ))));
        $departmentOptions = [];
        if ($departmentIds) {
            $departmentRows = (new Query())->select(['u.id', 'u.name', 'u.lvl', 'parent_name' => 'p.name'])
                ->from(['u' => '{{%tree}}'])
                ->leftJoin(['p' => '{{%tree}}'], 'p.root = u.root AND p.lft < u.lft AND p.rgt > u.rgt AND p.lvl = u.lvl - 1')
                ->where(['u.id' => $departmentIds])->orderBy(['u.root' => SORT_ASC, 'u.lft' => SORT_ASC])->all();
            foreach ($departmentRows as $row) {
                $label = (int) $row['lvl'] >= 2 && trim((string) $row['parent_name']) !== ''
                    ? $row['parent_name'] . ' › ' . $row['name']
                    : $row['name'];
                $departmentOptions[(int) $row['id']] = $label;
            }
        }
        $rows = array_values(array_filter($rows, static function (array $row) use ($departmentId, $bankStatus, $bankCode): bool {
            $bank = $row['bank_account'];
            return ($departmentId <= 0 || (int) $row['employee']->department === $departmentId)
                && ($bankStatus === '' || ($bankStatus === 'registered' ? $bank !== null : $bank === null))
                && ($bankCode === '' || ($bank && (string) $bank['bank_code'] === $bankCode));
        }));
        $pagination = new Pagination(['totalCount' => count($rows), 'pageSize' => 50, 'pageSizeLimit' => [50, 50]]);
        $pageRows = array_slice($rows, $pagination->offset, $pagination->limit);
        $typeOptions = (new Query())->select('title')->from('{{%employee_type}}')->where(['active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->indexBy('id')->column();
        $bankOptions = $this->bankOptions();
        $registeredCount = count($bankByEmployee);
        return $this->render('index', compact('q', 'employeeTypeId', 'departmentId', 'departmentOptions', 'bankStatus', 'bankCode', 'typeOptions', 'bankOptions', 'pageRows', 'pagination', 'registeredCount'));
    }

    public function actionSaveBankAccount()
    {
        $request = Yii::$app->request;
        $employeeId = (int) $request->post('employee_id');
        $bankCode = strtoupper(trim((string) $request->post('bank_code')));
        $accountNumber = preg_replace('/\D+/', '', (string) $request->post('account_number'));
        $returnParams = [
            'index',
            'q' => mb_substr(trim((string) $request->post('q')), 0, 100),
            'employee_type_id' => (int) $request->post('employee_type_id'),
            'department' => (int) $request->post('department'),
            'bank_status' => (string) $request->post('bank_status'),
            'bank_code' => strtoupper(trim((string) $request->post('bank_code_filter'))),
        ];
        $allowedBanks = array_keys($this->bankOptions());
        $employeeExists = Employees::find()->where(['id' => $employeeId, 'status' => 1])->exists();
        if (!$employeeExists || !in_array($bankCode, $allowedBanks, true) || strlen($accountNumber) < 6 || strlen($accountNumber) > 20) {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ กรุณาตรวจสอบบุคลากร ธนาคาร และเลขบัญชี');
            return $this->redirect($returnParams);
        }
        $now = date('Y-m-d H:i:s');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->replaceBankAccount($employeeId, $bankCode, $accountNumber, $now);
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'บันทึกบัญชีธนาคารเรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            if ($transaction->isActive) $transaction->rollBack();
            Yii::error($e);
            Yii::$app->session->setFlash('error', 'บันทึกบัญชีธนาคารไม่สำเร็จ ระบบไม่ได้เปลี่ยนข้อมูลเดิม');
        }
        return $this->redirect($returnParams);
    }

    public function actionDownloadBankTemplate()
    {
        $start = new DateTimeImmutable(date('Y-m-01'));
        $rows = (new PayrollReadinessService())->build($start->format('Y-m-d'), $start->modify('last day of this month')->format('Y-m-d'));
        $rows = array_values(array_filter($rows, static fn(array $row): bool => (string) $row['employee']->status === '1'));
        usort($rows, static fn(array $a, array $b): int => strcmp($a['full_name'], $b['full_name']));
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['citizen_id', 'employee_name', 'employee_type', 'department', 'bank_code', 'account_number']);
        foreach ($rows as $row) {
            $employee = $row['employee'];
            fputcsv($stream, [
                "'" . preg_replace('/\D+/', '', (string) $employee->cid),
                $this->csvSafeText($row['full_name']),
                $this->csvSafeText($row['employee_type']),
                $this->csvSafeText($row['department']),
                '',
                '',
            ]);
        }
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        return Yii::$app->response->sendContentAsFile($content, 'payroll-bank-template-' . date('Ymd') . '.csv', [
            'mimeType' => 'text/csv; charset=UTF-8',
            'inline' => false,
        ]);
    }

    public function actionImportBankCsv()
    {
        $file = UploadedFile::getInstanceByName('csv_file');
        if (!$file || strtolower($file->extension) !== 'csv' || $file->size <= 0 || $file->size > 5 * 1024 * 1024) {
            Yii::$app->session->setFlash('error', 'นำเข้าไม่สำเร็จ กรุณาเลือกไฟล์ CSV ขนาดไม่เกิน 5 MB');
            return $this->redirect(['index']);
        }
        $handle = fopen($file->tempName, 'rb');
        if (!$handle) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถอ่านไฟล์ CSV ได้ ระบบยังไม่ได้เปลี่ยนข้อมูล');
            return $this->redirect(['index']);
        }
        $header = fgetcsv($handle);
        if ($header) $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        $expected = ['citizen_id', 'employee_name', 'employee_type', 'department', 'bank_code', 'account_number'];
        if (!$header || array_map(static fn($value) => strtolower(trim((string) $value)), $header) !== $expected) {
            fclose($handle);
            Yii::$app->session->setFlash('error', 'รูปแบบคอลัมน์ไม่ถูกต้อง กรุณาใช้ไฟล์ Template ที่ดาวน์โหลดจากระบบ');
            return $this->redirect(['index']);
        }
        $employees = Employees::find()->select(['id', 'cid'])->where(['status' => 1])->indexBy('cid')->all();
        $validRows = [];
        $errors = [];
        $seen = [];
        $line = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if ($line > 2001) { $errors[] = 'ไฟล์มีข้อมูลเกิน 2,000 รายการ'; break; }
            $values = array_pad($values, count($expected), '');
            $citizenId = $this->normalizeCsvIdentifier($values[0]);
            $bankCode = strtoupper(trim((string) $values[4]));
            $accountNumber = preg_replace('/[\s-]+/', '', $this->normalizeCsvIdentifier($values[5]));
            if ($bankCode === '' && $accountNumber === '') continue;
            if ($citizenId === '' || !isset($employees[$citizenId])) $errors[] = "แถว {$line}: ไม่พบบุคลากรที่ยังปฏิบัติงาน";
            elseif (isset($seen[$citizenId])) $errors[] = "แถว {$line}: เลขประชาชนซ้ำในไฟล์";
            elseif (!isset($this->bankOptions()[$bankCode])) $errors[] = "แถว {$line}: bank_code ไม่ถูกต้อง";
            elseif (!ctype_digit($accountNumber) || strlen($accountNumber) < 6 || strlen($accountNumber) > 20) $errors[] = "แถว {$line}: เลขบัญชีต้องเป็นตัวเลข 6–20 หลัก";
            else {
                $seen[$citizenId] = true;
                $validRows[] = ['employee_id' => (int) $employees[$citizenId]->id, 'bank_code' => $bankCode, 'account_number' => $accountNumber];
            }
        }
        fclose($handle);
        if ($errors) {
            $shown = array_slice($errors, 0, 5);
            $suffix = count($errors) > 5 ? ' และอีก ' . number_format(count($errors) - 5) . ' จุด' : '';
            Yii::$app->session->setFlash('error', 'ยังไม่ได้นำเข้า: ' . implode(' · ', $shown) . $suffix);
            return $this->redirect(['index']);
        }
        if (!$validRows) {
            Yii::$app->session->setFlash('error', 'ไม่พบแถวที่กรอก bank_code และ account_number ระบบยังไม่ได้เปลี่ยนข้อมูล');
            return $this->redirect(['index']);
        }
        $now = date('Y-m-d H:i:s');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($validRows as $row) $this->replaceBankAccount($row['employee_id'], $row['bank_code'], $row['account_number'], $now);
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'นำเข้าบัญชีธนาคารเรียบร้อยแล้ว ' . number_format(count($validRows)) . ' คน');
        } catch (\Throwable $e) {
            if ($transaction->isActive) $transaction->rollBack();
            Yii::error($e);
            Yii::$app->session->setFlash('error', 'นำเข้าไม่สำเร็จ ระบบยกเลิกทั้งไฟล์และไม่ได้เปลี่ยนข้อมูลเดิม');
        }
        return $this->redirect(['index']);
    }

    private function bankOptions(): array
    {
        return ['BAAC' => 'ธ.ก.ส.', 'BBL' => 'กรุงเทพ', 'KBANK' => 'กสิกรไทย', 'KTB' => 'กรุงไทย', 'SCB' => 'ไทยพาณิชย์', 'TTB' => 'ทหารไทยธนชาต', 'GSB' => 'ออมสิน', 'OTHER' => 'ธนาคารอื่น'];
    }

    private function replaceBankAccount(int $employeeId, string $bankCode, string $accountNumber, string $now): void
    {
        $key = hash('sha256', (string) Yii::$app->request->cookieValidationKey, true);
        $ciphertext = Yii::$app->security->encryptByKey($accountNumber, $key);
        Yii::$app->db->createCommand()->update('{{%payroll_bank_account}}', ['is_active' => 0, 'updated_at' => $now, 'updated_by' => Yii::$app->user->id], ['employee_id' => $employeeId, 'is_active' => 1])->execute();
        Yii::$app->db->createCommand()->insert('{{%payroll_bank_account}}', [
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'employee_id' => $employeeId, 'bank_code' => $bankCode,
            'account_last4' => substr($accountNumber, -4), 'account_ciphertext' => $ciphertext, 'account_nonce' => '', 'key_version' => 1,
            'status' => 'verified', 'is_active' => 1, 'verified_at' => $now, 'verified_by' => Yii::$app->user->id,
            'created_at' => $now, 'updated_at' => $now, 'created_by' => Yii::$app->user->id, 'updated_by' => Yii::$app->user->id,
        ])->execute();
    }

    private function normalizeCsvIdentifier($value): string
    {
        $value = trim((string) $value);
        if (preg_match('/^="(.*)"$/', $value, $matches)) $value = $matches[1];
        return ltrim($value, "'\t ");
    }

    private function csvSafeText(string $value): string
    {
        $value = trim($value);
        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }

    public function actionOpenPeriod()
    {
        $month = PayrollReadinessService::normalizeMonth((string) Yii::$app->request->post('month'), '');
        if ($month === '') throw new BadRequestHttpException('รอบเดือนไม่ถูกต้อง');
        $start = new DateTimeImmutable($month . '-01');
        try {
            (new PayrollPeriodService())->open($month, (new PayrollReadinessService())->build($start->format('Y-m-d'), $start->modify('last day of this month')->format('Y-m-d')));
            Yii::$app->session->setFlash('success', 'เปิดรอบเงินเดือนและบันทึกรายชื่อเรียบร้อยแล้ว');
        } catch (\Throwable $e) { Yii::error($e); Yii::$app->session->setFlash('error', 'เปิดรอบไม่สำเร็จ: ' . $e->getMessage()); }
        return $this->redirect(['index', 'month' => $month]);
    }

    public function actionAddEmployee()
    {
        $periodId = (int) Yii::$app->request->post('period_id');
        $month = (string) Yii::$app->request->post('month');
        $reason = mb_substr(trim((string) Yii::$app->request->post('reason')), 0, 1000);
        if ($reason === '') { Yii::$app->session->setFlash('error', 'กรุณาระบุเหตุผลที่เพิ่มรายชื่อ'); return $this->redirect(['index', 'month' => $month]); }
        try {
            (new PayrollPeriodService())->addEmployee($periodId, (int) Yii::$app->request->post('employee_id'), $reason, (string) Yii::$app->request->post('payroll_case', 'adjustment'));
            Yii::$app->session->setFlash('success', 'เพิ่มรายชื่อเข้ารอบเรียบร้อยแล้ว');
        } catch (\Throwable $e) { Yii::error($e); Yii::$app->session->setFlash('error', $e->getMessage()); }
        return $this->redirect(['index', 'month' => $month]);
    }

    public function actionExclude()
    {
        return $this->changeRosterStatus('excluded', 'exclude');
    }

    public function actionRestore()
    {
        return $this->changeRosterStatus('needs_review', 'restore');
    }

    public function actionSettings()
    {
        $itemTypes = (new Query())->from('{{%payroll_item_type}}')->orderBy(['direction' => SORT_ASC, 'name' => SORT_ASC])->all();
        $contributionRules = (new Query())->from('{{%payroll_contribution_rule}}')->orderBy(['effective_from' => SORT_DESC])->all();
        return $this->render('settings', compact('itemTypes', 'contributionRules'));
    }

    public function actionEmployeeItems()
    {
        $q = mb_substr(trim((string) Yii::$app->request->get('q')), 0, 100);
        $group = (string) Yii::$app->request->get('group', 'monthly_pay');
        if (!in_array($group, ['monthly_pay', 'compensation', 'deduction'], true)) $group = 'monthly_pay';
        $itemTypeId = (int) Yii::$app->request->get('item_type_id');
        $query = (new Query())->select(['pei.*', 'pit.code item_code', 'pit.name item_name', 'pit.direction', 'e.cid',
            "CONCAT(COALESCE(e.prefix,''),COALESCE(e.fname,''),' ',COALESCE(e.lname,'')) employee_name"])
            ->from(['pei' => '{{%payroll_employee_item}}'])->innerJoin(['pit' => '{{%payroll_item_type}}'], 'pit.id=pei.item_type_id')
            ->innerJoin(['e' => '{{%employees}}'], 'e.id=pei.employee_id')->where(['pei.status' => 'active']);
        if ($itemTypeId > 0) $query->andWhere(['pei.item_type_id' => $itemTypeId]);
        if ($q !== '') $query->andWhere(['or', ['like', 'e.id', $q], ['like', 'e.fname', $q], ['like', 'e.lname', $q], ['like', 'pit.name', $q]]);
        $pagination = null;
        if ($itemTypeId > 0) {
            $pagination = new Pagination(['totalCount' => (clone $query)->count(), 'pageSize' => 50, 'pageSizeLimit' => [50, 50]]);
            $employeeItems = $query->orderBy(['pei.document_order' => SORT_ASC, 'pei.id' => SORT_ASC])->offset($pagination->offset)->limit($pagination->limit)->all();
        } else {
            $employeeItems = [];
        }
        $employees = Employees::find()->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])->all();
        $employeeOptions = []; foreach ($employees as $employee) $employeeOptions[(int) $employee->id] = trim((string) $employee->prefix . (string) $employee->fname . ' ' . (string) $employee->lname) . ' (รหัส ' . $employee->id . ')';
        $itemTypes = (new Query())->select(['id', 'code', 'name', 'direction', 'item_group', 'status'])->from('{{%payroll_item_type}}')
            ->where(['direction' => ['earning', 'deduction']])->orderBy(['direction' => SORT_ASC, 'name' => SORT_ASC])->all();
        $itemOptions = []; foreach ($itemTypes as $itemType) $itemOptions[(int) $itemType['id']] = $itemType['name'];
        $typeOptions = (new Query())->select('title')->from('{{%employee_type}}')->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->indexBy('id')->column();
        $month = date('Y-m'); $start = new \DateTimeImmutable($month . '-01');
        $candidateRows = (new PayrollReadinessService())->build($start->format('Y-m-d'), $start->modify('last day of this month')->format('Y-m-d'));
        if ($employeeItems) {
            $profileByEmployee = [];
            foreach ($candidateRows as $candidateRow) $profileByEmployee[(int) $candidateRow['employee_id']] = $candidateRow;
            $pageEmployeeIds = array_map('intval', array_column($employeeItems, 'employee_id'));
            $bankByEmployee = [];
            foreach ((new Query())->select(['employee_id', 'bank_code', 'account_last4', 'status'])->from('{{%payroll_bank_account}}')
                ->where(['employee_id' => $pageEmployeeIds, 'is_active' => 1])->orderBy(['verified_at' => SORT_DESC, 'id' => SORT_DESC])->all() as $bankRow) {
                $bankEmployeeId = (int) $bankRow['employee_id'];
                if (!isset($bankByEmployee[$bankEmployeeId])) $bankByEmployee[$bankEmployeeId] = $bankRow;
            }
            foreach ($employeeItems as &$employeeItem) {
                $employeeId = (int) $employeeItem['employee_id']; $profile = $profileByEmployee[$employeeId] ?? null; $employee = $profile['employee'] ?? null;
                $employeeItem['position'] = $profile['position'] ?? 'ไม่ระบุ';
                $employeeItem['department'] = $profile['department'] ?? 'ไม่ระบุ';
                $employeeItem['employee_type'] = $profile['employee_type'] ?? 'ไม่ระบุ';
                $employeeItem['employment_status'] = $employee && (string) $employee->status === '1' ? 'ปฏิบัติงาน' : 'สิ้นสุดการปฏิบัติงาน';
                $profileBank = trim((string) ($employee->banking ?? ''));
                if ($profileBank !== '') {
                    $decodedBank = json_decode($profileBank, true);
                    if (is_array($decodedBank)) {
                        if (isset($decodedBank[0]) && is_array($decodedBank[0])) $decodedBank = $decodedBank[0];
                        $bankName = $decodedBank['bank_name'] ?? $decodedBank['bank'] ?? $decodedBank['name'] ?? '';
                        $accountNo = $decodedBank['account_number'] ?? $decodedBank['account_no'] ?? $decodedBank['bank_account'] ?? $decodedBank['number'] ?? '';
                        $profileBank = trim((string) $bankName . ' ' . (string) $accountNo);
                    }
                }
                $employeeItem['bank'] = $profileBank !== '' ? $profileBank : (isset($bankByEmployee[$employeeId])
                    ? trim((string) $bankByEmployee[$employeeId]['bank_code'] . ' ••••' . (string) $bankByEmployee[$employeeId]['account_last4'])
                    : 'ยังไม่มีข้อมูลบัญชี');
            }
            unset($employeeItem);
        }
        $assignedByEmployee = [];
        foreach ((new Query())->select(['employee_id', 'item_type_id'])->from('{{%payroll_employee_item}}')->where(['status' => 'active'])->all() as $assignedRow) {
            $assignedByEmployee[(int) $assignedRow['employee_id']][] = (int) $assignedRow['item_type_id'];
        }
        $candidates = array_map(static function (array $row) use ($assignedByEmployee): array {
            $employee = $row['employee'];
            return ['id' => (int) $row['employee_id'], 'name' => $row['full_name'], 'cid' => (string) $employee->cid,
                'type_id' => (int) $employee->employee_type_id, 'type_name' => $row['employee_type'], 'department' => $row['department'], 'salary' => (float) $row['salary'],
                'assigned_item_ids' => $assignedByEmployee[(int) $row['employee_id']] ?? []];
        }, $candidateRows);
        $summaries = [];
        foreach ($itemTypes as $itemType) $summaries[(int) $itemType['id']] = ['item' => $itemType, 'count' => 0, 'total' => 0.0];
        $summaryRows = (new Query())->select(['item_type_id', 'employee_count' => 'COUNT(*)', 'total_amount' => 'SUM(amount)'])
            ->from('{{%payroll_employee_item}}')->where(['status' => 'active'])->groupBy('item_type_id')->all();
        foreach ($summaryRows as $summaryRow) if (isset($summaries[(int) $summaryRow['item_type_id']])) {
            $summaries[(int) $summaryRow['item_type_id']]['count'] = (int) $summaryRow['employee_count'];
            $summaries[(int) $summaryRow['item_type_id']]['total'] = (float) $summaryRow['total_amount'];
        }
        $groupSummaries = array_filter($summaries, static fn(array $summary): bool => $summary['item']['item_group'] === $group);
        $selectedItem = $itemTypeId > 0 && isset($summaries[$itemTypeId]) ? $summaries[$itemTypeId]['item'] : null;
        $viewData = compact('employeeItems', 'employeeOptions', 'itemOptions', 'itemTypes', 'typeOptions', 'candidates', 'summaries', 'groupSummaries', 'selectedItem', 'group', 'itemTypeId', 'q', 'pagination');
        return $this->render($selectedItem ? 'item-employees' : 'employee-items', $viewData);
    }

    public function actionAddItemType()
    {
        $group = (string) Yii::$app->request->get('group', 'monthly_pay');
        if (!in_array($group, ['monthly_pay', 'compensation', 'deduction'], true)) $group = 'monthly_pay';
        return $this->render('add-item-type', compact('group'));
    }

    public function actionSaveItemType()
    {
        $group = (string) Yii::$app->request->post('group', 'monthly_pay');
        if (!in_array($group, ['monthly_pay', 'compensation', 'deduction'], true)) $group = 'monthly_pay';
        $name = mb_substr(trim((string) Yii::$app->request->post('name')), 0, 255);
        if ($name === '') {
            Yii::$app->session->setFlash('error', 'กรุณาระบุชื่อรายการ');
            return $this->redirect(['add-item-type', 'group' => $group]);
        }
        $duplicate = (new Query())->from('{{%payroll_item_type}}')->where(['name' => $name, 'item_group' => $group, 'status' => 'active'])->exists();
        if ($duplicate) {
            Yii::$app->session->setFlash('error', 'มีรายการชื่อนี้อยู่แล้ว จึงไม่ได้เพิ่มรายการซ้ำ');
            return $this->redirect(['employee-items', 'group' => $group]);
        }
        try {
            $now = date('Y-m-d H:i:s');
            Yii::$app->db->createCommand()->insert('{{%payroll_item_type}}', [
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'code' => 'PAY_' . strtoupper(substr(Yii::$app->getSecurity()->generateRandomString(), 0, 10)),
                'name' => $name, 'direction' => $group === 'deduction' ? 'deduction' : 'earning', 'item_group' => $group,
                'is_recurring' => 1, 'is_sso_wage' => 0, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now,
                'created_by' => Yii::$app->user->id, 'updated_by' => Yii::$app->user->id,
            ])->execute();
            Yii::$app->session->setFlash('success', 'เพิ่มรายการ ' . $name . ' เรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            Yii::error($e); Yii::$app->session->setFlash('error', 'เพิ่มรายการไม่สำเร็จ กรุณาลองใหม่');
        }
        return $this->redirect(['employee-items', 'group' => $group]);
    }

    public function actionCreateEmployeeItem()
    {
        return $this->saveEmployeeItem(null);
    }

    public function actionAddItemEmployees()
    {
        $itemTypeId = (int) Yii::$app->request->get('item_type_id');
        $group = (string) Yii::$app->request->get('group', 'monthly_pay');
        $employeeTypeId = (int) Yii::$app->request->get('employee_type_id');
        $q = mb_substr(trim((string) Yii::$app->request->get('q')), 0, 100);
        // The item status controls monthly processing only. A disabled item must
        // remain editable so payroll staff can prepare its people and amounts.
        $itemType = (new Query())->from('{{%payroll_item_type}}')
            ->where(['id' => $itemTypeId, 'direction' => ['earning', 'deduction']])->one();
        if (!$itemType) throw new BadRequestHttpException('ไม่พบรายการรับ/จ่ายที่ต้องการเพิ่มรายชื่อ');

        $month = date('Y-m');
        $start = new DateTimeImmutable($month . '-01');
        $rows = (new PayrollReadinessService())->build($start->format('Y-m-d'), $start->modify('last day of this month')->format('Y-m-d'), $q);
        $assignedIds = array_map('intval', (new Query())->select('employee_id')->from('{{%payroll_employee_item}}')
            ->where(['item_type_id' => $itemTypeId, 'status' => 'active'])->column());
        $assignedLookup = array_fill_keys($assignedIds, true);
        $rows = array_values(array_filter($rows, static function (array $row) use ($employeeTypeId, $assignedLookup): bool {
            return !isset($assignedLookup[(int) $row['employee_id']])
                && ($employeeTypeId <= 0 || (int) $row['employee']->employee_type_id === $employeeTypeId);
        }));
        $pagination = new Pagination(['totalCount' => count($rows), 'pageSize' => 50, 'pageSizeLimit' => [50, 50]]);
        $pageRows = array_slice($rows, $pagination->offset, $pagination->limit);
        $typeOptions = (new Query())->select('title')->from('{{%employee_type}}')->where(['active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->indexBy('id')->column();

        return $this->render('add-item-employees', compact('itemType', 'group', 'employeeTypeId', 'q', 'pageRows', 'pagination', 'typeOptions'));
    }

    public function actionSaveItemEmployees()
    {
        $request = Yii::$app->request;
        $itemTypeId = (int) $request->post('item_type_id');
        $group = (string) $request->post('group', 'monthly_pay');
        $employeeIds = array_values(array_unique(array_filter(array_map('intval', (array) $request->post('employee_ids', [])))));
        $amounts = (array) $request->post('amounts', []);
        // Allow roster maintenance while processing is disabled. The status is
        // checked later when a monthly payroll run selects enabled item types.
        $itemType = (new Query())->from('{{%payroll_item_type}}')
            ->where(['id' => $itemTypeId, 'direction' => ['earning', 'deduction']])->one();
        if (!$itemType || !$employeeIds || count($employeeIds) > 1000) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกรายชื่ออย่างน้อย 1 คน');
            return $this->redirect(['add-item-employees', 'item_type_id' => $itemTypeId, 'group' => $group]);
        }

        $month = date('Y-m');
        $start = new DateTimeImmutable($month . '-01');
        $profileRows = (new PayrollReadinessService())->build($start->format('Y-m-d'), $start->modify('last day of this month')->format('Y-m-d'));
        $profileSalary = [];
        foreach ($profileRows as $row) $profileSalary[(int) $row['employee_id']] = round((float) $row['salary'], 2);
        $validEmployees = Employees::find()->select('id')->where(['id' => $employeeIds])->indexBy('id')->column();
        $nextOrder = ((int) (new Query())->from('{{%payroll_employee_item}}')->where(['item_type_id' => $itemTypeId])->max('document_order')) + 1;
        $now = date('Y-m-d H:i:s');
        $created = 0; $skipped = 0;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($employeeIds as $employeeId) {
                $amount = $itemType['code'] === 'SALARY' ? ($profileSalary[$employeeId] ?? 0) : round((float) str_replace(',', '', (string) ($amounts[$employeeId] ?? 0)), 2);
                $exists = (new Query())->from('{{%payroll_employee_item}}')->where(['employee_id' => $employeeId, 'item_type_id' => $itemTypeId, 'status' => 'active'])->exists();
                if (!isset($validEmployees[$employeeId]) || $amount <= 0 || $exists) { $skipped++; continue; }
                $data = ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'employee_id' => $employeeId, 'item_type_id' => $itemTypeId,
                    'document_order' => $nextOrder++, 'amount' => $amount, 'effective_from' => $start->format('Y-m-d'), 'effective_to' => null,
                    'reference_no' => null, 'reason' => 'กำหนดรายชื่อพื้นฐาน', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now,
                    'created_by' => Yii::$app->user->id, 'updated_by' => Yii::$app->user->id];
                Yii::$app->db->createCommand()->insert('{{%payroll_employee_item}}', $data)->execute();
                $id = (int) Yii::$app->db->getLastInsertID();
                Yii::$app->db->createCommand()->insert('{{%payroll_audit_log}}', ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                    'entity_type' => 'payroll_employee_item', 'entity_id' => $id, 'action' => 'create_base_employee', 'reason' => 'กำหนดรายชื่อพื้นฐาน',
                    'before_json' => null, 'after_json' => $data, 'ip_address' => mb_substr((string) $request->userIP, 0, 45), 'created_at' => $now, 'created_by' => Yii::$app->user->id])->execute();
                $created++;
            }
            $transaction->commit();
            $message = 'เพิ่มรายชื่อเรียบร้อย ' . number_format($created) . ' คน';
            if ($skipped) $message .= ' ข้าม ' . number_format($skipped) . ' คนที่ไม่มียอดหรือมีอยู่แล้ว';
            Yii::$app->session->setFlash($created ? 'success' : 'error', $message);
        } catch (\Throwable $e) {
            if ($transaction->isActive) $transaction->rollBack();
            Yii::error($e);
            Yii::$app->session->setFlash('error', 'เพิ่มรายชื่อไม่สำเร็จ ระบบยกเลิกทั้งชุดแล้ว');
        }
        return $this->redirect(['employee-items', 'group' => $group, 'item_type_id' => $itemTypeId]);
    }

    public function actionEmployeeItemMatrix()
    {
        $direction = Yii::$app->request->get('direction') === 'deduction' ? 'deduction' : 'earning';
        $employeeTypeId = (int) Yii::$app->request->get('employee_type_id');
        $month = date('Y-m'); $start = new \DateTimeImmutable($month . '-01'); $asOf = $start->modify('last day of this month')->format('Y-m-d');
        $rows = (new PayrollReadinessService())->build($start->format('Y-m-d'), $asOf);
        if ($employeeTypeId > 0) $rows = array_values(array_filter($rows, static fn(array $row): bool => (int) $row['employee']->employee_type_id === $employeeTypeId));
        $itemTypes = (new Query())->select(['id', 'code', 'name'])->from('{{%payroll_item_type}}')
            ->where(['status' => 'active', 'direction' => $direction])->orderBy(['name' => SORT_ASC])->all();
        $employeeIds = array_map(static fn(array $row): int => (int) $row['employee_id'], $rows);
        $existing = [];
        if ($employeeIds && $itemTypes) {
            $itemIds = array_column($itemTypes, 'id');
            $existingRows = (new Query())->from('{{%payroll_employee_item}}')->where(['employee_id' => $employeeIds, 'item_type_id' => $itemIds, 'status' => 'active'])
                ->andWhere(['<=', 'effective_from', $asOf])->andWhere(['or', ['effective_to' => null], ['>=', 'effective_to', $start->format('Y-m-d')]])->all();
            foreach ($existingRows as $existingRow) $existing[(int) $existingRow['employee_id']][(int) $existingRow['item_type_id']] = $existingRow;
        }
        $typeOptions = (new Query())->select('title')->from('{{%employee_type}}')->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->indexBy('id')->column();
        return $this->render('employee-item-matrix', compact('direction', 'employeeTypeId', 'typeOptions', 'itemTypes', 'rows', 'existing', 'asOf'));
    }

    public function actionSaveEmployeeItemMatrix()
    {
        $request = Yii::$app->request; $direction = $request->post('direction') === 'deduction' ? 'deduction' : 'earning';
        $employeeTypeId = (int) $request->post('employee_type_id'); $from = (string) $request->post('effective_from');
        $reason = mb_substr(trim((string) $request->post('reason')), 0, 2000); $reference = mb_substr(trim((string) $request->post('reference_no')), 0, 255);
        $amounts = (array) $request->post('amounts', []);
        if (!$this->validDate($from) || $reason === '' || count($amounts, COUNT_RECURSIVE) > 6000) {
            Yii::$app->session->setFlash('error', 'บันทึกตารางไม่สำเร็จ กรุณาตรวจวันที่ เหตุผล และจำนวนรายการ');
            return $this->redirect(['employee-item-matrix', 'direction' => $direction, 'employee_type_id' => $employeeTypeId]);
        }
        $allowedItems = (new Query())->select('id')->from('{{%payroll_item_type}}')->where(['status' => 'active', 'direction' => $direction])->indexBy('id')->column();
        $employeeQuery = Employees::find()->select('id')->where(['id' => array_map('intval', array_keys($amounts))]);
        if ($employeeTypeId > 0) $employeeQuery->andWhere(['employee_type_id' => $employeeTypeId]);
        $allowedEmployees = $employeeQuery->indexBy('id')->column();
        $now = date('Y-m-d H:i:s'); $created = 0; $updated = 0; $skipped = 0; $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($amounts as $employeeId => $itemAmounts) foreach ((array) $itemAmounts as $itemTypeId => $rawAmount) {
                $employeeId = (int) $employeeId; $itemTypeId = (int) $itemTypeId; $amount = round((float) $rawAmount, 2);
                if ($amount <= 0) continue;
                if (!isset($allowedEmployees[$employeeId]) || !isset($allowedItems[$itemTypeId])) { $skipped++; continue; }
                $before = (new Query())->from('{{%payroll_employee_item}}')->where(['employee_id' => $employeeId, 'item_type_id' => $itemTypeId, 'status' => 'active'])
                    ->andWhere(['<=', 'effective_from', $from])->andWhere(['or', ['effective_to' => null], ['>=', 'effective_to', $from]])->orderBy(['effective_from' => SORT_DESC])->one();
                if ($before) {
                    if ((float) $before['amount'] === $amount) continue;
                    $data = ['amount' => $amount, 'reference_no' => $reference ?: $before['reference_no'], 'reason' => $reason, 'updated_at' => $now, 'updated_by' => Yii::$app->user->id];
                    Yii::$app->db->createCommand()->update('{{%payroll_employee_item}}', $data, ['id' => $before['id']])->execute(); $id = (int) $before['id']; $updated++;
                } else {
                    $data = ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'employee_id' => $employeeId, 'item_type_id' => $itemTypeId, 'amount' => $amount,
                        'effective_from' => $from, 'effective_to' => null, 'reference_no' => $reference ?: null, 'reason' => $reason, 'status' => 'active',
                        'created_at' => $now, 'updated_at' => $now, 'created_by' => Yii::$app->user->id, 'updated_by' => Yii::$app->user->id];
                    Yii::$app->db->createCommand()->insert('{{%payroll_employee_item}}', $data)->execute(); $id = (int) Yii::$app->db->getLastInsertID(); $created++;
                }
                Yii::$app->db->createCommand()->insert('{{%payroll_audit_log}}', ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'entity_type' => 'payroll_employee_item',
                    'entity_id' => $id, 'action' => $before ? 'matrix_update' : 'matrix_create', 'reason' => $reason, 'before_json' => $before ?: null,
                    'after_json' => $data, 'ip_address' => mb_substr((string) $request->userIP, 0, 45), 'created_at' => $now, 'created_by' => Yii::$app->user->id])->execute();
            }
            $transaction->commit(); Yii::$app->session->setFlash('success', 'บันทึกตารางสำเร็จ เพิ่ม ' . number_format($created) . ' และแก้ไข ' . number_format($updated) . ' รายการ' . ($skipped ? ' ข้าม ' . number_format($skipped) . ' รายการ' : ''));
        } catch (\Throwable $e) { if ($transaction->isActive) $transaction->rollBack(); Yii::error($e); Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ ระบบยกเลิกทั้งชุดแล้ว'); }
        return $this->redirect(['employee-item-matrix', 'direction' => $direction, 'employee_type_id' => $employeeTypeId]);
    }

    public function actionCreateEmployeeItemsBulk()
    {
        $request = Yii::$app->request;
        $itemTypeId = (int) $request->post('item_type_id');
        $from = (string) $request->post('effective_from');
        $to = trim((string) $request->post('effective_to')) ?: null;
        $reference = mb_substr(trim((string) $request->post('reference_no')), 0, 255);
        $reason = mb_substr(trim((string) $request->post('reason')), 0, 2000);
        $employeeIds = array_values(array_unique(array_filter(array_map('intval', (array) $request->post('employee_ids', [])))));
        $amounts = (array) $request->post('amounts', []);
        $itemType = (new Query())->from('{{%payroll_item_type}}')->where(['id' => $itemTypeId, 'status' => 'active', 'direction' => ['earning', 'deduction']])->one();
        if (!$itemType || !$this->validDate($from) || ($to && (!$this->validDate($to) || $to < $from)) || $reason === '' || $employeeIds === [] || count($employeeIds) > 1000) {
            Yii::$app->session->setFlash('error', 'บันทึกแบบกลุ่มไม่สำเร็จ กรุณาเลือกรายการ บุคลากร ช่วงวันที่ และระบุเหตุผลให้ครบ');
            return $this->redirect(['employee-items']);
        }
        $existingEmployees = Employees::find()->select('id')->where(['id' => $employeeIds])->indexBy('id')->column();
        $now = date('Y-m-d H:i:s'); $created = 0; $skipped = 0;
        $nextDocumentOrder = (int) (new Query())->from('{{%payroll_employee_item}}')->where(['item_type_id' => $itemTypeId])->max('document_order') + 1;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($employeeIds as $employeeId) {
                $amount = round((float) ($amounts[$employeeId] ?? 0), 2);
                if (!isset($existingEmployees[$employeeId]) || $amount <= 0) { $skipped++; continue; }
                $overlap = (new Query())->from('{{%payroll_employee_item}}')->where(['employee_id' => $employeeId, 'item_type_id' => $itemTypeId, 'status' => 'active'])
                    ->andWhere(['<=', 'effective_from', $to ?: '9999-12-31'])->andWhere(['or', ['effective_to' => null], ['>=', 'effective_to', $from]])->exists();
                if ($overlap) { $skipped++; continue; }
                $data = ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'employee_id' => $employeeId, 'item_type_id' => $itemTypeId, 'document_order' => $nextDocumentOrder++,
                    'amount' => $amount, 'effective_from' => $from, 'effective_to' => $to, 'reference_no' => $reference ?: null, 'reason' => $reason,
                    'status' => 'active', 'created_at' => $now, 'updated_at' => $now, 'created_by' => Yii::$app->user->id, 'updated_by' => Yii::$app->user->id];
                Yii::$app->db->createCommand()->insert('{{%payroll_employee_item}}', $data)->execute();
                $id = (int) Yii::$app->db->getLastInsertID();
                Yii::$app->db->createCommand()->insert('{{%payroll_audit_log}}', ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                    'entity_type' => 'payroll_employee_item', 'entity_id' => $id, 'action' => 'bulk_create', 'reason' => $reason,
                    'before_json' => null, 'after_json' => $data, 'ip_address' => mb_substr((string) $request->userIP, 0, 45), 'created_at' => $now, 'created_by' => Yii::$app->user->id])->execute();
                $created++;
            }
            $transaction->commit();
            $message = "เพิ่ม {$itemType['name']} สำเร็จ " . number_format($created) . ' คน';
            if ($skipped > 0) $message .= ' ข้าม ' . number_format($skipped) . ' คน เนื่องจากยอดไม่ถูกต้องหรือมีช่วงวันที่ซ้ำ';
            Yii::$app->session->setFlash($created > 0 ? 'success' : 'error', $message);
        } catch (\Throwable $e) {
            if ($transaction->isActive) $transaction->rollBack(); Yii::error($e);
            Yii::$app->session->setFlash('error', 'บันทึกแบบกลุ่มไม่สำเร็จ ระบบยกเลิกทั้งชุดและไม่มีข้อมูลบางส่วนตกค้าง');
        }
        return $this->redirect(['employee-items']);
    }

    public function actionUpdateEmployeeItem()
    {
        return $this->saveEmployeeItem((int) Yii::$app->request->post('id'));
    }

    public function actionRemoveEmployeeItem()
    {
        $request = Yii::$app->request;
        $id = (int) $request->post('id');
        $group = (string) $request->post('group', 'monthly_pay');
        $itemTypeId = (int) $request->post('item_type_id');
        $before = (new Query())->from('{{%payroll_employee_item}}')->where(['id' => $id, 'item_type_id' => $itemTypeId, 'status' => 'active'])->one();
        if (!$before) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายชื่อที่ต้องการลบ หรือถูกลบไปแล้ว');
            return $this->redirect(['employee-items', 'group' => $group, 'item_type_id' => $itemTypeId]);
        }
        $now = date('Y-m-d H:i:s');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand()->update('{{%payroll_employee_item}}', ['status' => 'inactive', 'updated_at' => $now, 'updated_by' => Yii::$app->user->id], ['id' => $id])->execute();
            Yii::$app->db->createCommand()->insert('{{%payroll_audit_log}}', [
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'entity_type' => 'payroll_employee_item',
                'entity_id' => $id, 'action' => 'remove_base_employee', 'reason' => 'นำรายชื่อออกจากรายการพื้นฐาน',
                'before_json' => $before, 'after_json' => ['status' => 'inactive'], 'ip_address' => mb_substr((string) $request->userIP, 0, 45),
                'created_at' => $now, 'created_by' => Yii::$app->user->id,
            ])->execute();
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'นำรายชื่อออกจากรายการเรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            if ($transaction->isActive) $transaction->rollBack();
            Yii::error($e);
            Yii::$app->session->setFlash('error', 'นำรายชื่อออกไม่สำเร็จ ระบบไม่ได้เปลี่ยนข้อมูล');
        }
        return $this->redirect(['employee-items', 'group' => $group, 'item_type_id' => $itemTypeId]);
    }

    public function actionSaveItemAmounts()
    {
        $request = Yii::$app->request;
        $itemTypeId = (int) $request->post('item_type_id'); $group = (string) $request->post('group', 'monthly_pay');
        $amounts = (array) $request->post('amounts', []); $orders = (array) $request->post('orders', []);
        if (!$amounts || count($amounts) > 50 || array_diff_key($amounts, $orders) || array_diff_key($orders, $amounts)) { Yii::$app->session->setFlash('error', 'ไม่พบจำนวนเงินหรือลำดับที่ต้องการบันทึก'); return $this->redirect(['employee-items', 'group' => $group, 'item_type_id' => $itemTypeId]); }
        $now = date('Y-m-d H:i:s');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $updated = 0; $moves = [];
            foreach ($amounts as $id => $rawAmount) {
                $id = (int) $id; $amount = round((float) str_replace(',', '', (string) $rawAmount), 2); $order = (int) ($orders[$id] ?? 0);
                $before = (new Query())->from('{{%payroll_employee_item}}')->where(['id' => $id, 'item_type_id' => $itemTypeId, 'status' => 'active'])->one();
                if (!$before || $amount <= 0 || $order <= 0) throw new BadRequestHttpException('จำนวนเงินหรือลำดับไม่ถูกต้อง');
                if ((int) $before['document_order'] !== $order) $moves[$id] = $order;
                if ((float) $before['amount'] !== $amount) {
                    Yii::$app->db->createCommand()->update('{{%payroll_employee_item}}', ['amount' => $amount, 'updated_at' => $now, 'updated_by' => Yii::$app->user->id], ['id' => $id])->execute();
                    Yii::$app->db->createCommand()->insert('{{%payroll_audit_log}}', ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'entity_type' => 'payroll_employee_item',
                        'entity_id' => $id, 'action' => 'update_base_amount', 'reason' => 'แก้จำนวนเงินพื้นฐาน', 'before_json' => $before, 'after_json' => ['amount' => $amount],
                        'ip_address' => mb_substr((string) $request->userIP, 0, 45), 'created_at' => $now, 'created_by' => Yii::$app->user->id])->execute();
                    $updated++;
                }
            }
            if ($moves) {
                $orderedIds = array_map('intval', (new Query())->select('id')->from('{{%payroll_employee_item}}')->where(['item_type_id' => $itemTypeId, 'status' => 'active'])->orderBy(['document_order' => SORT_ASC, 'id' => SORT_ASC])->column());
                asort($moves, SORT_NUMERIC);
                foreach ($moves as $moveId => $target) { $orderedIds = array_values(array_diff($orderedIds, [(int) $moveId])); array_splice($orderedIds, max(0, min(count($orderedIds), $target - 1)), 0, [(int) $moveId]); }
                foreach ($orderedIds as $index => $orderedId) Yii::$app->db->createCommand()->update('{{%payroll_employee_item}}', ['document_order' => $index + 1, 'updated_at' => $now, 'updated_by' => Yii::$app->user->id], ['id' => $orderedId])->execute();
                Yii::$app->db->createCommand()->insert('{{%payroll_audit_log}}', ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'entity_type' => 'payroll_item_type', 'entity_id' => $itemTypeId,
                    'action' => 'reorder_document', 'reason' => 'จัดลำดับรายชื่อตามเอกสาร', 'before_json' => null, 'after_json' => ['moves' => $moves],
                    'ip_address' => mb_substr((string) $request->userIP, 0, 45), 'created_at' => $now, 'created_by' => Yii::$app->user->id])->execute();
            }
            $transaction->commit(); Yii::$app->session->setFlash('success', ($updated || $moves) ? 'บันทึกจำนวนเงินและลำดับเรียบร้อยแล้ว' : 'ข้อมูลไม่มีการเปลี่ยนแปลง');
        } catch (\Throwable $e) {
            if ($transaction->isActive) $transaction->rollBack(); Yii::error($e); Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ กรุณาตรวจจำนวนเงินทุกคน');
        }
        return $this->redirect(['employee-items', 'group' => $group, 'item_type_id' => $itemTypeId]);
    }

    public function actionToggleItemType()
    {
        $request = Yii::$app->request; $id = (int) $request->post('id'); $group = (string) $request->post('group', 'monthly_pay');
        $before = (new Query())->from('{{%payroll_item_type}}')->where(['id' => $id, 'item_group' => $group])->one();
        if (!$before) { Yii::$app->session->setFlash('error', 'ไม่พบรายการที่ต้องการเปลี่ยนสถานะ'); return $this->redirect(['employee-items', 'group' => $group]); }
        $status = $before['status'] === 'active' ? 'inactive' : 'active'; $now = date('Y-m-d H:i:s');
        try {
            Yii::$app->db->createCommand()->update('{{%payroll_item_type}}', ['status' => $status, 'updated_at' => $now, 'updated_by' => Yii::$app->user->id], ['id' => $id])->execute();
            Yii::$app->db->createCommand()->insert('{{%payroll_audit_log}}', ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'entity_type' => 'payroll_item_type', 'entity_id' => $id,
                'action' => 'toggle_status', 'reason' => $status === 'active' ? 'เปิดใช้งานรายการ' : 'ปิดใช้งานรายการ', 'before_json' => $before, 'after_json' => ['status' => $status],
                'ip_address' => mb_substr((string) $request->userIP, 0, 45), 'created_at' => $now, 'created_by' => Yii::$app->user->id])->execute();
            Yii::$app->session->setFlash('success', ($status === 'active' ? 'เปิด' : 'ปิด') . 'ใช้งานรายการเรียบร้อยแล้ว');
        } catch (\Throwable $e) { Yii::error($e); Yii::$app->session->setFlash('error', 'เปลี่ยนสถานะรายการไม่สำเร็จ'); }
        return $this->redirect(['employee-items', 'group' => $group]);
    }

    public function actionReorderEmployeeItems()
    {
        $request = Yii::$app->request; $itemTypeId = (int) $request->post('item_type_id');
        $orderedIds = array_values(array_unique(array_filter(array_map('intval', (array) $request->post('ordered_ids', [])))));
        $group = (string) $request->post('group', 'monthly_pay');
        $validIds = (new Query())->select('id')->from('{{%payroll_employee_item}}')->where(['item_type_id' => $itemTypeId, 'status' => 'active'])->orderBy(['document_order' => SORT_ASC, 'id' => SORT_ASC])->column();
        $validIds = array_map('intval', $validIds);
        if ($itemTypeId <= 0 || $orderedIds === [] || array_diff($orderedIds, $validIds) || array_diff($validIds, $orderedIds)) {
            Yii::$app->session->setFlash('error', 'จัดลำดับไม่สำเร็จ รายชื่อมีการเปลี่ยนแปลง กรุณาโหลดหน้าใหม่');
            return $this->redirect(['employee-items', 'group' => $group, 'item_type_id' => $itemTypeId]);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($orderedIds as $index => $id) Yii::$app->db->createCommand()->update('{{%payroll_employee_item}}', ['document_order' => $index + 1, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Yii::$app->user->id], ['id' => $id])->execute();
            Yii::$app->db->createCommand()->insert('{{%payroll_audit_log}}', ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'entity_type' => 'payroll_item_type', 'entity_id' => $itemTypeId,
                'action' => 'reorder_document', 'reason' => 'จัดลำดับรายชื่อตามเอกสาร', 'before_json' => null, 'after_json' => ['ordered_ids' => $orderedIds],
                'ip_address' => mb_substr((string) $request->userIP, 0, 45), 'created_at' => date('Y-m-d H:i:s'), 'created_by' => Yii::$app->user->id])->execute();
            $transaction->commit(); Yii::$app->session->setFlash('success', 'บันทึกลำดับตามเอกสารเรียบร้อยแล้ว');
        } catch (\Throwable $e) { if ($transaction->isActive) $transaction->rollBack(); Yii::error($e); Yii::$app->session->setFlash('error', 'จัดลำดับไม่สำเร็จ ระบบไม่ได้เปลี่ยนลำดับบางส่วน'); }
        return $this->redirect(['employee-items', 'group' => $group, 'item_type_id' => $itemTypeId]);
    }

    private function saveEmployeeItem(?int $id)
    {
        $request = Yii::$app->request; $employeeId = (int) $request->post('employee_id'); $itemTypeId = (int) $request->post('item_type_id');
        $amount = round((float) $request->post('amount'), 2); $from = (string) $request->post('effective_from'); $to = trim((string) $request->post('effective_to')) ?: null;
        $reason = mb_substr(trim((string) $request->post('reason')), 0, 2000); $reference = mb_substr(trim((string) $request->post('reference_no')), 0, 255);
        $before = $id ? (new Query())->from('{{%payroll_employee_item}}')->where(['id' => $id])->one() : null;
        $employeeExists = Employees::find()->where(['id' => $employeeId])->exists();
        $itemExists = (new Query())->from('{{%payroll_item_type}}')->where(['id' => $itemTypeId, 'status' => 'active', 'direction' => ['earning', 'deduction']])->exists();
        if (($id && !$before) || !$employeeExists || !$itemExists || $amount <= 0 || !$this->validDate($from) || ($to && (!$this->validDate($to) || $to < $from)) || $reason === '') {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ กรุณาตรวจบุคลากร รายการ จำนวนเงิน ช่วงวันที่ และเหตุผล'); return $this->redirect(['employee-items']);
        }
        $overlap = (new Query())->from('{{%payroll_employee_item}}')->where(['employee_id' => $employeeId, 'item_type_id' => $itemTypeId, 'status' => 'active'])
            ->andFilterWhere($id ? ['<>', 'id', $id] : [])->andWhere(['<=', 'effective_from', $to ?: '9999-12-31'])
            ->andWhere(['or', ['effective_to' => null], ['>=', 'effective_to', $from]])->exists();
        if ($overlap) { Yii::$app->session->setFlash('error', 'บันทึกไม่ได้ เพราะบุคลากรมีรายการประเภทนี้ในช่วงวันที่ซ้อนกัน'); return $this->redirect(['employee-items']); }
        $now = date('Y-m-d H:i:s'); $data = ['employee_id' => $employeeId, 'item_type_id' => $itemTypeId, 'amount' => $amount, 'effective_from' => $from, 'effective_to' => $to, 'reference_no' => $reference ?: null, 'reason' => $reason, 'updated_at' => $now, 'updated_by' => Yii::$app->user->id];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($id) Yii::$app->db->createCommand()->update('{{%payroll_employee_item}}', $data, ['id' => $id])->execute();
            else { $data += ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'document_order' => ((int) (new Query())->from('{{%payroll_employee_item}}')->where(['item_type_id' => $itemTypeId])->max('document_order')) + 1, 'status' => 'active', 'created_at' => $now, 'created_by' => Yii::$app->user->id]; Yii::$app->db->createCommand()->insert('{{%payroll_employee_item}}', $data)->execute(); $id = (int) Yii::$app->db->getLastInsertID(); }
            Yii::$app->db->createCommand()->insert('{{%payroll_audit_log}}', ['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'entity_type' => 'payroll_employee_item', 'entity_id' => $id, 'action' => $before ? 'update' : 'create', 'reason' => $reason, 'before_json' => $before, 'after_json' => $data, 'ip_address' => mb_substr((string) $request->userIP, 0, 45), 'created_at' => $now, 'created_by' => Yii::$app->user->id])->execute();
            $transaction->commit(); Yii::$app->session->setFlash('success', $before ? 'แก้ไขรายการประจำบุคคลเรียบร้อยแล้ว' : 'เพิ่มรายการประจำบุคคลเรียบร้อยแล้ว');
        } catch (\Throwable $e) { if ($transaction->isActive) $transaction->rollBack(); Yii::error($e); Yii::$app->session->setFlash('error', 'บันทึกรายการประจำบุคคลไม่สำเร็จ กรุณาลองใหม่'); }
        return $this->redirect(['employee-items']);
    }

    public function actionCreateItemType()
    {
        $request = Yii::$app->request;
        $code = preg_replace('/[^A-Z0-9_]/', '', strtoupper(trim((string) $request->post('code'))));
        $name = mb_substr(trim((string) $request->post('name')), 0, 255);
        $direction = (string) $request->post('direction');
        $itemGroup = (string) $request->post('item_group');
        if ($direction === 'deduction') $itemGroup = 'deduction'; elseif ($direction === 'employer_contribution') $itemGroup = 'employer_contribution';
        if ($code === '' || !preg_match('/[A-Z0-9]/', $code) || $name === '' || !in_array($direction, ['earning', 'deduction', 'employer_contribution'], true) || !in_array($itemGroup, ['monthly_pay', 'compensation', 'deduction', 'employer_contribution'], true)) {
            Yii::$app->session->setFlash('error', 'กรุณากรอกรหัส ชื่อ และประเภทของรายการให้ครบถ้วน');
            return $this->redirect(['settings']);
        }
        try {
            $now = date('Y-m-d H:i:s');
            Yii::$app->db->createCommand()->insert('{{%payroll_item_type}}', [
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'code' => $code, 'name' => $name,
                'direction' => $direction, 'item_group' => $itemGroup, 'is_recurring' => $request->post('is_recurring') ? 1 : 0,
                'is_sso_wage' => $request->post('is_sso_wage') ? 1 : 0, 'status' => 'active',
                'created_at' => $now, 'updated_at' => $now, 'created_by' => Yii::$app->user->id, 'updated_by' => Yii::$app->user->id,
            ])->execute();
            Yii::$app->session->setFlash('success', "เพิ่มประเภทรายการ {$name} เรียบร้อยแล้ว");
        } catch (\Throwable $e) { Yii::error($e); Yii::$app->session->setFlash('error', 'เพิ่มประเภทรายการไม่สำเร็จ รหัสอาจซ้ำกับรายการเดิม'); }
        return $this->redirect(['settings']);
    }

    public function actionUpdateItemType()
    {
        $request = Yii::$app->request;
        $id = (int) $request->post('id');
        $before = (new Query())->from('{{%payroll_item_type}}')->where(['id' => $id])->one();
        if (!$before) { Yii::$app->session->setFlash('error', 'ไม่พบประเภทรายการที่ต้องการแก้ไข'); return $this->redirect(['settings']); }

        $code = preg_replace('/[^A-Z0-9_]/', '', strtoupper(trim((string) $request->post('code'))));
        $name = mb_substr(trim((string) $request->post('name')), 0, 255);
        $direction = (string) $request->post('direction');
        $itemGroup = (string) $request->post('item_group');
        if ($direction === 'deduction') $itemGroup = 'deduction'; elseif ($direction === 'employer_contribution') $itemGroup = 'employer_contribution';
        if ($code === '' || !preg_match('/[A-Z0-9]/', $code) || $name === '' || !in_array($direction, ['earning', 'deduction', 'employer_contribution'], true) || !in_array($itemGroup, ['monthly_pay', 'compensation', 'deduction', 'employer_contribution'], true)) {
            Yii::$app->session->setFlash('error', 'แก้ไขไม่สำเร็จ กรุณากรอกรหัส ชื่อ และประเภทให้ครบถ้วน');
            return $this->redirect(['settings']);
        }
        $duplicate = (new Query())->from('{{%payroll_item_type}}')->where(['code' => $code])->andWhere(['<>', 'id', $id])->exists();
        if ($duplicate) { Yii::$app->session->setFlash('error', "แก้ไขไม่ได้ เพราะรหัส {$code} ถูกใช้แล้ว"); return $this->redirect(['settings']); }

        $after = [
            'code' => $code, 'name' => $name, 'direction' => $direction, 'item_group' => $itemGroup,
            'is_recurring' => $request->post('is_recurring') ? 1 : 0,
            'is_sso_wage' => $request->post('is_sso_wage') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Yii::$app->user->id,
        ];
        try {
            $transaction = Yii::$app->db->beginTransaction();
            Yii::$app->db->createCommand()->update('{{%payroll_item_type}}', $after, ['id' => $id])->execute();
            Yii::$app->db->createCommand()->insert('{{%payroll_audit_log}}', [
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                'entity_type' => 'payroll_item_type', 'entity_id' => $id, 'action' => 'update',
                'reason' => 'แก้ไขประเภทรายการเงินเดือน', 'before_json' => $before, 'after_json' => array_merge($before, $after),
                'ip_address' => mb_substr((string) $request->userIP, 0, 45), 'created_at' => date('Y-m-d H:i:s'), 'created_by' => Yii::$app->user->id,
            ])->execute();
            $transaction->commit();
            Yii::$app->session->setFlash('success', "บันทึกการแก้ไข {$name} เรียบร้อยแล้ว");
        } catch (\Throwable $e) {
            if (isset($transaction) && $transaction->isActive) $transaction->rollBack();
            Yii::error($e); Yii::$app->session->setFlash('error', 'แก้ไขประเภทรายการไม่สำเร็จ กรุณาลองใหม่');
        }
        return $this->redirect(['settings']);
    }

    public function actionCreateContributionRule()
    {
        $request = Yii::$app->request;
        $from = (string) $request->post('effective_from'); $to = trim((string) $request->post('effective_to')) ?: null;
        $minimum = (float) $request->post('minimum_wage_base'); $maximum = (float) $request->post('maximum_wage_base');
        $employeeRate = (float) $request->post('employee_rate') / 100; $employerRate = (float) $request->post('employer_rate') / 100;
        if (!$this->validDate($from) || ($to && !$this->validDate($to)) || ($to && $to < $from) || $minimum < 0 || $maximum <= $minimum || $employeeRate < 0 || $employerRate < 0) {
            Yii::$app->session->setFlash('error', 'ข้อมูลกฎไม่ถูกต้อง กรุณาตรวจวันที่ ฐานค่าจ้าง และอัตราอีกครั้ง');
            return $this->redirect(['settings']);
        }
        $overlap = (new Query())->from('{{%payroll_contribution_rule}}')->where(['scheme' => 'sso_m33', 'status' => 'active'])
            ->andWhere(['<=', 'effective_from', $to ?: '9999-12-31'])
            ->andWhere(['or', ['effective_to' => null], ['>=', 'effective_to', $from]])->exists();
        if ($overlap) { Yii::$app->session->setFlash('error', 'เพิ่มกฎไม่ได้ เพราะช่วงวันที่ซ้อนกับกฎประกันสังคมที่ใช้งานอยู่'); return $this->redirect(['settings']); }
        $name = mb_substr(trim((string) $request->post('name')), 0, 255);
        try {
            $now = date('Y-m-d H:i:s');
            Yii::$app->db->createCommand()->insert('{{%payroll_contribution_rule}}', [
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10), 'scheme' => 'sso_m33', 'name' => $name ?: 'ประกันสังคม ม.33',
                'effective_from' => $from, 'effective_to' => $to, 'minimum_wage_base' => $minimum, 'maximum_wage_base' => $maximum,
                'employee_rate' => $employeeRate, 'employer_rate' => $employerRate, 'rounding_mode' => 'half_up_whole',
                'legal_reference' => mb_substr(trim((string) $request->post('legal_reference')), 0, 500), 'status' => 'active',
                'created_at' => $now, 'updated_at' => $now, 'created_by' => Yii::$app->user->id, 'updated_by' => Yii::$app->user->id,
            ])->execute();
            Yii::$app->session->setFlash('success', 'เพิ่มกฎประกันสังคมเรียบร้อยแล้ว');
        } catch (\Throwable $e) { Yii::error($e); Yii::$app->session->setFlash('error', 'เพิ่มกฎประกันสังคมไม่สำเร็จ กรุณาตรวจข้อมูลแล้วลองใหม่'); }
        return $this->redirect(['settings']);
    }

    private function validDate(string $date): bool
    {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    private function changeRosterStatus(string $status, string $action)
    {
        $month = (string) Yii::$app->request->post('month');
        $reason = mb_substr(trim((string) Yii::$app->request->post('reason')), 0, 1000);
        if ($reason === '') $reason = $action === 'restore' ? 'คืนรายชื่อกลับเข้ารอบ' : 'นำออกจากรอบโดยผู้จัดทำ';
        try {
            $row = (new PayrollPeriodService())->setRosterStatus((int) Yii::$app->request->post('id'), $status, $reason, $action);
            $snapshot = PayrollPeriodService::decodeSnapshot($row['employee_snapshot']);
            $name = $snapshot['full_name'] ?? ('รหัส ' . $row['employee_id']);
            $message = $action === 'restore'
                ? "คืน {$name} กลับเข้ารอบเรียบร้อยแล้ว"
                : "นำ {$name} ออกจากรอบเรียบร้อยแล้ว ข้อมูลยังไม่ถูกลบและสามารถคืนกลับได้";
            Yii::$app->session->setFlash('success', $message);
        }
        catch (\Throwable $e) { Yii::error($e); Yii::$app->session->setFlash('error', $e->getMessage()); }
        return $this->redirect(['index', 'month' => $month]);
    }
}

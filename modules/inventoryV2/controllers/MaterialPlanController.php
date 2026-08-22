<?php

namespace app\modules\inventoryV2\controllers;

use app\components\SiteHelper;
use app\modules\inventoryV2\models\MaterialPlan;
use app\modules\inventoryV2\models\MaterialPlanItem;
use app\modules\inventoryV2\services\MaterialPlanForecastService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * จัดทำแผนวัสดุประจำปี — คาดการณ์ปริมาณการใช้จากยอดจ่ายจริงย้อนหลัง
 * แล้วเสนอปริมาณจัดซื้อแบ่ง 4 ไตรมาส ส่งออกตามแบบฟอร์มของทางราชการ
 */
class MaterialPlanController extends Controller
{
    /** สิทธิ์เปิด-ปิดค่าแผน แยกจากสิทธิ์เข้าดู/คำนวณ/บันทึกร่าง */
    public const PERMISSION_LOCK = 'materialPlanLock';

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            // ปิดค่า = ตรึงตัวเลขที่ส่ง สสจ. และกำหนดอัตราเผื่อกลาง จึงจำกัดสิทธิ์เฉพาะผู้ที่ได้รับมอบ
            // กฎข้อที่สองต้องมี ไม่งั้นคนที่ไม่มีสิทธิ์จะตกไปเข้ากฎท้ายสุดแล้วผ่านได้
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['lock', 'unlock'],
                        'allow' => true,
                        'roles' => [self::PERMISSION_LOCK],
                    ],
                    [
                        'actions' => ['lock', 'unlock'],
                        'allow' => false,
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            // การกระทำที่เปลี่ยนสถานะเอกสารต้องมาทาง POST เท่านั้น
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save' => ['post'],
                    'lock' => ['post'],
                    'unlock' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * ผู้ใช้ปัจจุบันเปิด-ปิดค่าแผนได้หรือไม่ (ใช้ตัดสินใจแสดงปุ่มบนหน้าจอ)
     */
    public function canLock(): bool
    {
        return Yii::$app->user->can(self::PERMISSION_LOCK);
    }

    /**
     * หน้าคำนวณแผน พร้อมตัวกรองและตารางที่ปรับตัวเลขได้
     */
    public function actionIndex()
    {
        $filter = $this->resolveFilter();
        $service = new MaterialPlanForecastService();
        $plan = MaterialPlan::findForScope($filter['fiscal_year'], $filter['warehouse_id']);

        // แผนที่บันทึกแล้วต้องแสดงตัวเลขที่บันทึกไว้ ไม่ใช่คำนวณใหม่ ไม่งั้นการ "ปิดค่า" ไม่มีความหมาย
        if ($plan !== null) {
            $rows = $this->savedRows($plan, $filter);
            $coverage = [
                'months' => (int) $plan->months_covered,
                'factor' => (float) $plan->annual_factor,
                'last_date' => $plan->data_cutoff_date,
            ];
            $balanceSource = (string) $plan->balance_source;
            $filter['growth_pct'] = (float) $plan->growth_pct;
        } else {
            $rows = $this->collectRows($service, $filter);
            $coverage = $service->getCoverage();
            $balanceSource = $service->getBalanceSource();
        }

        return $this->render('index', [
            'plan' => $plan,
            'filter' => $filter,
            'rows' => $rows,
            'summary' => $service->summarize($rows),
            'warehouses' => MaterialPlanForecastService::mainWarehouseOptions(),
            'departments' => MaterialPlanForecastService::departmentOptions(),
            'categories' => MaterialPlanForecastService::categoryOptions(),
            'quarterLabels' => MaterialPlanForecastService::quarterLabels(),
            'baseYear' => MaterialPlanForecastService::baseFiscalYear($filter['fiscal_year']),
            'balanceSource' => $balanceSource,
            'coverage' => $coverage,
            'canLock' => $this->canLock(),
        ]);
    }

    /**
     * แถวจากแผนที่บันทึกไว้ ใช้ตัวกรองหมวด/คำค้นกับชุดที่บันทึกแล้วเท่านั้น
     *
     * @return array<int, array>
     */
    protected function savedRows(MaterialPlan $plan, array $filter): array
    {
        $query = $plan->getItems()->orderBy(['category_title' => SORT_ASC, 'item_name' => SORT_ASC]);

        if (($filter['category_id'] ?? '') !== '') {
            $query->andWhere(['category_id' => $filter['category_id']]);
        }
        if (($filter['q'] ?? '') !== '') {
            $query->andWhere([
                'or',
                ['like', 'item_code', $filter['q']],
                ['like', 'item_name', $filter['q']],
                ['like', 'category_title', $filter['q']],
            ]);
        }

        $rows = [];
        $seq = 1;
        foreach ($query->all() as $item) {
            $row = $item->toRow();
            $row['seq'] = $seq++;
            $rows[] = $row;
        }

        return $this->applyOverrides($rows, $this->readOverrides());
    }

    /**
     * ส่งออก Excel ตามแบบฟอร์ม "แผนการจัดวัสดุ" ของโรงพยาบาล
     * รับตัวเลขที่ผู้ใช้ปรับบนหน้าจอผ่าน POST เพื่อให้ไฟล์ตรงกับที่เห็น
     */
    public function actionExport()
    {
        $filter = $this->resolveFilter();
        $service = new MaterialPlanForecastService();
        $plan = MaterialPlan::findForScope($filter['fiscal_year'], $filter['warehouse_id']);

        // ไฟล์ที่ส่งออกต้องตรงกับฉบับที่บันทึกไว้ ไม่ใช่คำนวณใหม่ตอนกดส่งออก
        if ($plan !== null) {
            $rows = $this->savedRows($plan, $filter);
            $coverage = [
                'months' => (int) $plan->months_covered,
                'factor' => (float) $plan->annual_factor,
                'last_date' => $plan->data_cutoff_date,
            ];
            $filter['growth_pct'] = (float) $plan->growth_pct;
        } else {
            $rows = $this->collectRows($service, $filter);
            $coverage = $service->getCoverage();
        }

        return $this->streamXlsx($rows, $service->summarize($rows), $filter, $coverage);
    }

    /**
     * บันทึกแผนที่คำนวณและปรับแล้วเป็นฉบับอ้างอิง
     *
     * บันทึกทั้งชุดเสมอ ไม่สนใจตัวกรองหมวด/คำค้นบนหน้าจอ เพราะเอกสารที่ส่งต้องครบ
     */
    public function actionSave()
    {
        $filter = $this->resolveFilter();
        $plan = MaterialPlan::findForScope($filter['fiscal_year'], $filter['warehouse_id']);

        if ($plan !== null && $plan->isLocked()) {
            Yii::$app->session->setFlash('error', 'แผนปีงบ ' . $filter['fiscal_year'] . ' ปิดค่าแล้ว ต้องปลดล็อกก่อนจึงจะบันทึกทับได้');

            return $this->redirect($this->filterUrl($filter));
        }

        // ตัวกรองมุมมองไม่ควรตัดรายการออกจากเอกสาร บันทึกจากชุดเต็มเสมอ
        $fullFilter = array_merge($filter, ['category_id' => '', 'q' => '']);
        $service = new MaterialPlanForecastService();
        $rows = $this->collectRows($service, $fullFilter);

        if ($rows === []) {
            Yii::$app->session->setFlash('error', 'ไม่มีรายการให้บันทึก');

            return $this->redirect($this->filterUrl($filter));
        }

        $summary = $service->summarize($rows);
        $coverage = $service->getCoverage();

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($plan === null) {
                $plan = new MaterialPlan([
                    'fiscal_year' => $filter['fiscal_year'],
                    'warehouse_id' => $filter['warehouse_id'],
                ]);
            }
            $plan->base_year = MaterialPlanForecastService::baseFiscalYear($filter['fiscal_year']);
            $plan->growth_pct = $filter['growth_pct'];
            $plan->months_covered = (int) $coverage['months'];
            $plan->annual_factor = (float) $coverage['factor'];
            $plan->data_cutoff_date = $coverage['last_date'];
            $plan->balance_source = $service->getBalanceSource();
            $plan->item_count = (int) $summary['item_count'];
            $plan->plan_value = (float) $summary['plan_value'];
            $plan->status = MaterialPlan::STATUS_DRAFT;
            $plan->note = trim((string) Yii::$app->request->post('note', $plan->note));
            if (!$plan->save()) {
                throw new \RuntimeException('บันทึกหัวแผนไม่สำเร็จ');
            }

            MaterialPlanItem::deleteAll(['material_plan_id' => $plan->id]);
            foreach ($rows as $row) {
                $item = new MaterialPlanItem();
                $item->setAttributes(MaterialPlanItem::attributesFromRow($plan->id, $row), false);
                if (!$item->save(false)) {
                    throw new \RuntimeException('บันทึกรายการ ' . $row['item_code'] . ' ไม่สำเร็จ');
                }
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'บันทึกแผน ' . number_format($plan->item_count) . ' รายการ มูลค่า ' . number_format($plan->plan_value, 2) . ' บาท');
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
        }

        return $this->redirect($this->filterUrl($filter));
    }

    /**
     * ปิดค่าแผน — ตัวเลขหยุดนิ่ง ใช้เป็นฉบับที่ส่ง สสจ. และเป็นอัตราเผื่อกลางของทั้งระบบ
     */
    public function actionLock()
    {
        $filter = $this->resolveFilter();
        $plan = MaterialPlan::findForScope($filter['fiscal_year'], $filter['warehouse_id']);

        if ($plan === null) {
            Yii::$app->session->setFlash('error', 'ยังไม่มีแผนที่บันทึกไว้ ต้องบันทึกก่อนจึงปิดค่าได้');
        } elseif ($plan->isLocked()) {
            Yii::$app->session->setFlash('error', 'แผนนี้ปิดค่าไว้แล้ว');
        } else {
            $plan->status = MaterialPlan::STATUS_LOCKED;
            $plan->locked_at = date('Y-m-d H:i:s');
            $plan->locked_by = Yii::$app->user->id;
            $plan->save(false);
            Yii::$app->session->setFlash('success', 'ปิดค่าแผนปีงบ ' . $plan->fiscal_year . ' แล้ว ตัวเลขจะไม่เปลี่ยนอีก');
        }

        return $this->redirect($this->filterUrl($filter));
    }

    /**
     * ปลดล็อกเพื่อแก้ไข — บันทึกไว้ว่าเคยปิดค่าเมื่อไรและใครปลด
     */
    public function actionUnlock()
    {
        $filter = $this->resolveFilter();
        $plan = MaterialPlan::findForScope($filter['fiscal_year'], $filter['warehouse_id']);

        if ($plan === null || !$plan->isLocked()) {
            Yii::$app->session->setFlash('error', 'ไม่มีแผนที่ปิดค่าไว้');
        } else {
            $history = (array) ($plan->data_json['unlock_history'] ?? []);
            $history[] = [
                'locked_at' => $plan->locked_at,
                'locked_by' => $plan->locked_by,
                'unlocked_at' => date('Y-m-d H:i:s'),
                'unlocked_by' => Yii::$app->user->id,
            ];
            $plan->data_json = array_merge((array) $plan->data_json, ['unlock_history' => $history]);
            $plan->status = MaterialPlan::STATUS_DRAFT;
            $plan->locked_at = null;
            $plan->locked_by = null;
            $plan->save(false);
            Yii::$app->session->setFlash('success', 'ปลดล็อกแล้ว แก้ไขและบันทึกทับได้');
        }

        return $this->redirect($this->filterUrl($filter));
    }

    /**
     * URL กลับไปหน้าเดิมพร้อมตัวกรองเดิม
     */
    protected function filterUrl(array $filter): array
    {
        return [
            '/inventory-v2/material-plan/index',
            'fiscal_year' => $filter['fiscal_year'],
            'growth_pct' => $filter['growth_pct'],
            'warehouse_id' => $filter['warehouse_id'],
            'dept_warehouse_id' => $filter['dept_warehouse_id'],
            'category_id' => $filter['category_id'],
            'q' => $filter['q'],
        ];
    }

    /**
     * ค้นวัสดุจากทะเบียนเพื่อเพิ่มเข้าแผน (เรียกจากช่องค้นหาบนหน้าจอ)
     */
    public function actionSearchItem($q = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return (new MaterialPlanForecastService())->searchItems((string) $q);
    }

    /**
     * แถวที่คำนวณได้ + แถวที่ผู้ใช้เพิ่มเอง แล้วทับด้วยตัวเลขที่ปรับบนหน้าจอ
     */
    protected function collectRows(MaterialPlanForecastService $service, array $filter): array
    {
        $rows = $service->buildRows($filter);

        $addedCodes = array_diff(
            $this->readAddedItems(),
            \yii\helpers\ArrayHelper::getColumn($rows, 'item_code')
        );
        if ($addedCodes !== []) {
            $rows = array_merge($rows, $service->buildManualRows($addedCodes, $filter));
            $seq = 1;
            foreach ($rows as &$row) {
                $row['seq'] = $seq++;
            }
            unset($row);
        }

        return $this->applyOverrides($rows, $this->readOverrides());
    }

    /**
     * รหัสวัสดุที่ผู้ใช้เพิ่มเข้าแผนเอง ส่งมาเป็น JSON array
     *
     * @return array<int, string>
     */
    protected function readAddedItems(): array
    {
        $raw = (string) Yii::$app->request->post('added_items', '');
        if (trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? array_map('strval', $decoded) : [];
    }

    /**
     * อ่านตัวกรองจาก request ให้อยู่ในรูปที่ service ใช้ได้
     */
    protected function resolveFilter(): array
    {
        $request = Yii::$app->request;
        $get = static fn ($key, $default = '') => trim((string) $request->post($key, $request->get($key, $default)));

        return [
            'fiscal_year' => MaterialPlanForecastService::normalizeFiscalYear($get('fiscal_year')),
            'growth_pct' => MaterialPlanForecastService::normalizeGrowthPct($get('growth_pct', (string) MaterialPlanForecastService::DEFAULT_GROWTH_PCT)),
            'warehouse_id' => $get('warehouse_id') !== '' ? (int) $get('warehouse_id') : null,
            'dept_warehouse_id' => $get('dept_warehouse_id') !== '' ? (int) $get('dept_warehouse_id') : null,
            'category_id' => $get('category_id'),
            'q' => $get('q'),
        ];
    }

    /**
     * ตัวเลขที่ผู้ใช้แก้บนหน้าจอ ส่งมาเป็น JSON ก้อนเดียวเฉพาะแถวที่เปลี่ยน
     *
     * @return array<string, array>
     */
    protected function readOverrides(): array
    {
        $raw = (string) Yii::$app->request->post('overrides', '');
        if (trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * ทับค่าที่คำนวณได้ด้วยตัวเลขที่ผู้ใช้ปรับ แล้วคิดมูลค่ากับไตรมาสใหม่
     */
    protected function applyOverrides(array $rows, array $overrides): array
    {
        if ($overrides === []) {
            return $rows;
        }

        foreach ($rows as &$row) {
            $override = $overrides[$row['item_code']] ?? null;
            if (!is_array($override)) {
                continue;
            }

            $row['is_adjusted'] = true;

            if (isset($override['forecast_qty']) && is_numeric($override['forecast_qty'])) {
                $row['forecast_qty'] = (int) round((float) $override['forecast_qty']);
                $row['plan_qty'] = MaterialPlanForecastService::planQty($row['forecast_qty'], $row['opening_qty']);
            }
            if (isset($override['plan_qty']) && is_numeric($override['plan_qty'])) {
                $row['plan_qty'] = (int) ceil(max((float) $override['plan_qty'], 0));
            }
            if (isset($override['unit_price']) && is_numeric($override['unit_price'])) {
                $row['unit_price'] = round(max((float) $override['unit_price'], 0), 2);
                $row['price_source'] = 'manual';
            }

            $quarters = MaterialPlanForecastService::splitQuarters($row['plan_qty']);
            foreach ($quarters as $index => $default) {
                $key = 'q' . ($index + 1);
                if (isset($override[$key]) && is_numeric($override[$key])) {
                    $quarters[$index] = (int) ceil(max((float) $override[$key], 0));
                }
            }

            $row['quarters'] = $quarters;
            $row['quarter_values'] = array_map(static fn ($qty) => round($qty * $row['unit_price'], 2), $quarters);
            $row['plan_value'] = round($row['plan_qty'] * $row['unit_price'], 2);
        }
        unset($row);

        return $rows;
    }

    /**
     * เขียนไฟล์ Excel ให้มีคอลัมน์และลำดับตรงกับแบบฟอร์มต้นฉบับ
     */
    protected function streamXlsx(array $rows, array $summary, array $filter, array $coverage = [])
    {
        $planYear = $filter['fiscal_year'];
        $baseYear = MaterialPlanForecastService::baseFiscalYear($planYear);
        $quarterLabels = MaterialPlanForecastService::quarterLabels();
        $historyYears = array_keys(MaterialPlanForecastService::historyUsage($baseYear, 0.0, $filter['growth_pct']));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('แผนจัดซื้อ ปี ' . $planYear);

        $lastColumn = 'AF';
        $sheet->setCellValue('A1', 'แผนการจัดวัสดุ ' . $this->organizationName() . ' ประจำปีงบประมาณ ' . $planYear);
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->setCellValue('A2', $this->filterCaption($filter, $coverage));
        $sheet->mergeCells("A2:{$lastColumn}2");

        $this->writeHeader($sheet, $historyYears, $planYear, $quarterLabels);

        $rowIndex = 6;
        foreach ($rows as $row) {
            $history = $row['history'];
            $sheet->setCellValue('A' . $rowIndex, $row['item_code']);
            $sheet->setCellValue('B' . $rowIndex, $row['item_name']);
            $sheet->setCellValue('C' . $rowIndex, $row['category_title']);
            $sheet->setCellValue('D' . $rowIndex, $row['unit_name']);

            $column = 'E';
            foreach ($historyYears as $year) {
                $sheet->setCellValue($column . $rowIndex, $history[$year] ?? 0);
                $column++;
            }

            $sheet->setCellValue('H' . $rowIndex, $row['forecast_qty']);
            $sheet->setCellValue('I' . $rowIndex, $row['opening_qty']);
            $sheet->setCellValue('J' . $rowIndex, $row['plan_qty']);
            $sheet->setCellValue('K' . $rowIndex, $row['unit_price']);
            $sheet->setCellValue('L' . $rowIndex, $row['plan_value']);

            // ไตรมาส 1-4 บล็อกละ 4 คอลัมน์: แผนจัดซื้อ, มูลค่า, ซื้อจริง, มูลค่าซื้อจริง
            // ช่อง "ซื้อจริง" เว้นว่างไว้ให้กรอกระหว่างปี ตามแบบฟอร์มเดิม
            $blockStart = 13; // คอลัมน์ M
            foreach ($row['quarters'] as $index => $qty) {
                $qtyColumn = Coordinate::stringFromColumnIndex($blockStart + ($index * 4));
                $valueColumn = Coordinate::stringFromColumnIndex($blockStart + ($index * 4) + 1);
                $sheet->setCellValue($qtyColumn . $rowIndex, $qty);
                $sheet->setCellValue($valueColumn . $rowIndex, $row['quarter_values'][$index]);
            }

            $sheet->setCellValue('AC' . $rowIndex, array_sum($row['quarters']));
            $sheet->setCellValue('AD' . $rowIndex, array_sum($row['quarter_values']));
            $rowIndex++;
        }

        $lastRow = $rowIndex - 1;
        $this->styleSheet($sheet, $lastColumn, $lastRow);
        $this->writeFooter($sheet, $rowIndex, $summary);

        $tempPath = tempnam(sys_get_temp_dir(), 'material-plan-');
        (new Xlsx($spreadsheet))->save($tempPath);
        $filename = 'material-plan-' . $planYear . '.xlsx';

        return Yii::$app->response
            ->sendFile($tempPath, $filename)
            ->on(Response::EVENT_AFTER_SEND, static function () use ($tempPath) {
                @unlink($tempPath);
            });
    }

    /**
     * หัวตาราง 2 ชั้นตามแบบฟอร์ม (แถว 4 = กลุ่ม, แถว 5 = คอลัมน์ย่อย)
     */
    protected function writeHeader($sheet, array $historyYears, int $planYear, array $quarterLabels): void
    {
        $single = [
            'A' => 'รหัส',
            'B' => 'รายการสินค้า',
            'C' => 'ประเภทรายการ',
            'D' => 'หน่วยบรรจุ',
        ];
        foreach ($single as $column => $label) {
            $sheet->setCellValue($column . '4', $label);
            $sheet->mergeCells("{$column}4:{$column}5");
        }

        $sheet->setCellValue('E4', 'ข้อมูลการใช้ย้อนหลัง ' . count($historyYears) . ' ปี');
        $sheet->mergeCells('E4:G4');
        $column = 'E';
        foreach ($historyYears as $year) {
            $sheet->setCellValue($column . '5', $year);
            $column++;
        }

        $stacked = [
            'H' => ['ประมาณการใช้', 'ปี ' . $planYear],
            'I' => ['ยอด', 'คงคลัง'],
            'J' => ['ประมาณ', 'การจัดซื้อ'],
            'K' => ['ราคา', 'ต่อขนาดบรรจุ'],
            'L' => ['ประมาณ', 'มูลค่า'],
        ];
        foreach ($stacked as $col => $labels) {
            $sheet->setCellValue($col . '4', $labels[0]);
            $sheet->setCellValue($col . '5', $labels[1]);
        }

        $blockStart = 13; // คอลัมน์ M
        foreach ($quarterLabels as $index => $label) {
            $from = Coordinate::stringFromColumnIndex($blockStart + ($index * 4));
            $to = Coordinate::stringFromColumnIndex($blockStart + ($index * 4) + 3);
            $sheet->setCellValue($from . '4', $label);
            $sheet->mergeCells("{$from}4:{$to}4");

            $subs = ['แผนจัดซื้อ', 'มูลค่า', 'ซื้อจริง', 'มูลค่าซื้อจริง'];
            foreach ($subs as $offset => $sub) {
                $column = Coordinate::stringFromColumnIndex($blockStart + ($index * 4) + $offset);
                $sheet->setCellValue($column . '5', $sub);
            }
        }

        $sheet->setCellValue('AC4', 'ยอดรวม');
        $sheet->mergeCells('AC4:AF4');
        foreach (['AC' => 'แผนจัดซื้อ', 'AD' => 'มูลค่า', 'AE' => 'ซื้อจริง', 'AF' => 'มูลค่าซื้อจริง'] as $column => $label) {
            $sheet->setCellValue($column . '5', $label);
        }
    }

    /**
     * แถวสรุปและช่องลงนามท้ายแบบฟอร์ม
     */
    protected function writeFooter($sheet, int $rowIndex, array $summary): void
    {
        $summaryRow = $rowIndex + 1;
        $sheet->setCellValue('C' . $summaryRow, 'จำนวน');
        $sheet->setCellValue('D' . $summaryRow, $summary['item_count']);
        $sheet->setCellValue('E' . $summaryRow, 'รายการ');
        $sheet->setCellValue('K' . $summaryRow, 'มูลค่าประมาณการ');
        $sheet->setCellValue('L' . $summaryRow, $summary['plan_value']);
        $sheet->setCellValue('M' . $summaryRow, 'บาท');
        $sheet->getStyle("C{$summaryRow}:M{$summaryRow}")->getFont()->setBold(true);
        $sheet->getStyle("L{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        $signRow = $summaryRow + 3;
        $signatures = [
            'B' => 'ผู้จัดทำแผน.............................................................',
            'F' => 'ผู้เสนอแผน.............................................................',
            'M' => 'ผู้เห็นชอบแผน.............................................................',
        ];
        foreach ($signatures as $column => $label) {
            $sheet->setCellValue($column . $signRow, $label);
            $sheet->setCellValue($column . ($signRow + 1), '(.............................................................)');
        }
    }

    /**
     * ตกแต่งตารางให้อ่านง่ายและพิมพ์ได้ (ไม่ใช้สีนอกเหนือจากเส้นตารางมาตรฐาน)
     */
    protected function styleSheet($sheet, string $lastColumn, int $lastRow): void
    {
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A4:{$lastColumn}5")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        if ($lastRow >= 6) {
            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("E6:{$lastColumn}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D6:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->freezePane('E6');
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(42);
        $sheet->getColumnDimension('C')->setWidth(26);
        $sheet->getColumnDimension('D')->setWidth(12);
        foreach (range('E', 'L') as $column) {
            $sheet->getColumnDimension($column)->setWidth(14);
        }
        for ($index = 13; $index <= 32; $index++) {
            $sheet->getColumnDimensionByColumn($index)->setWidth(13);
        }
        $sheet->getRowDimension(4)->setRowHeight(24);
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
    }

    /**
     * บรรทัดบอกเงื่อนไขที่ใช้คำนวณ เพื่อให้ผู้อ่านไฟล์รู้ที่มาของตัวเลข
     */
    protected function filterCaption(array $filter, array $coverage = []): string
    {
        $baseYear = MaterialPlanForecastService::baseFiscalYear($filter['fiscal_year']);
        $parts = ['คำนวณจากยอดใช้จริงปีงบ ' . $baseYear];

        $months = (int) ($coverage['months'] ?? 12);
        if ($months < 12) {
            $parts[] = 'ข้อมูล ' . $months . ' เดือน ปรับเป็นเต็มปี ×' . $coverage['factor'];
        }

        $parts[] = 'อัตราปรับ ' . $filter['growth_pct'] . '%';

        if ($filter['dept_warehouse_id']) {
            $departments = MaterialPlanForecastService::departmentOptions();
            $parts[] = 'หน่วยงาน ' . ($departments[$filter['dept_warehouse_id']] ?? '-');
        }
        if ($filter['warehouse_id']) {
            $warehouses = MaterialPlanForecastService::mainWarehouseOptions();
            $parts[] = 'คลัง ' . ($warehouses[$filter['warehouse_id']] ?? '-');
        }
        if ($filter['category_id'] !== '') {
            $categories = MaterialPlanForecastService::categoryOptions();
            $parts[] = 'หมวด ' . ($categories[$filter['category_id']] ?? '-');
        }

        return implode(' · ', $parts);
    }

    protected function organizationName(): string
    {
        $name = SiteHelper::getInfo()['company_name'] ?? null;

        return trim((string) $name) !== '' ? (string) $name : 'โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย';
    }
}

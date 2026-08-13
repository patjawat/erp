<?php

namespace app\modules\roster\controllers;

use app\components\ApproveLevelResolver;
use app\components\ModalHelper;
use app\modules\hr\models\Employees;
use app\modules\roster\helpers\RosterAccess;
use app\modules\roster\helpers\RosterAutoScheduler;
use app\modules\roster\helpers\RosterContext;
use app\modules\roster\helpers\RosterCopier;
use app\modules\roster\helpers\RosterExporter;
use app\modules\roster\helpers\RosterSwapService;
use app\modules\roster\helpers\RuleChecker;
use app\modules\roster\models\Item;
use app\modules\roster\models\Period;
use app\modules\roster\models\PeriodSearch;
use app\modules\roster\models\Request as RosterRequest;
use app\modules\roster\models\ShiftType;
use app\modules\roster\models\Swap;
use app\modules\roster\models\UnitShift;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * รอบเวรและกริดจัดเวร
 */
class PeriodController extends Controller
{
    /**
     * โมดูลนี้อยู่ใน allowActions ของ AccessControl ระดับแอป (สิทธิ์มาจากผังองค์กร ไม่ใช่ role)
     * จึงต้องบังคับล็อกอินเองที่นี่ ไม่งั้นผู้ใช้ที่ยังไม่ล็อกอินจะเจอ 403 แทนหน้า login
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'authOnly' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ]);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!RosterAccess::canEnter()) {
            // ส่งไปหน้าที่อธิบายสาเหตุ แทน 403 เปล่าๆ ที่ผู้ใช้เดาไม่ออกว่าต้องแก้ที่ไหน
            $this->redirect(['/roster/default/no-access'])->send();
            return false;
        }
        return true;
    }

    public function actionIndex()
    {
        $searchModel = new PeriodSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // ตัวกรองในทะเบียนใช้ขอบเขต "ที่ดูได้" ไม่ใช่ "ที่จัดเวรได้"
            'units' => RosterAccess::creatableUnitOptions(),
            'canCreate' => !empty(RosterAccess::creatableUnitOptions()),
            'pendingCount' => $this->pendingCount(),
        ]);
    }

    /**
     * สร้างรอบเวร "ของหน่วยงานตนเอง" — ล็อกหน่วยจากผังองค์กรเหมือนคลังหน่วยงาน
     * ผูกคำขอที่ยื่นล่วงหน้าเข้ารอบให้อัตโนมัติ
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $groups = RosterAccess::creatableUnitGroups();
        $flat = RosterAccess::creatableUnitOptions();
        if (empty($flat)) {
            return [
                'status' => 'error',
                'message' => 'คุณยังไม่ได้ถูกกำหนดเป็นหัวหน้าหน่วยงานในผังองค์กร จึงยังสร้างตารางเวรไม่ได้',
            ];
        }

        // หน่วยของตัวเองเป็นค่าตั้งต้น ที่เหลือเลือกจากผังได้
        $own = RosterAccess::manageableUnitIds();
        $default = (count($flat) === 1) ? (int) array_key_first($flat)
            : (!empty($own) ? (int) reset($own) : null);

        $model = new Period([
            'month' => (int) date('n', strtotime('+1 month')),
            'year_ce' => (int) date('Y', strtotime('+1 month')),
            'unit_id' => $default,
        ]);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if (!RosterAccess::canCreateForUnit((int) $model->unit_id)) {
                return ['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์สร้างตารางเวรของหน่วยงานนี้'];
            }
            // ขอบเขตเวรของแผ่นนี้ — ไม่เลือก = ครอบทุกเวรของหน่วย
            $picked = array_map('intval', (array) $this->request->post('unit_shift_ids', []));
            $model->setShiftIds($picked);
            if ($model->save()) {
                // ไม่ผูกคำขอหยุดเข้าแผ่นใดแผ่นหนึ่ง เพราะเดือนหนึ่งมีหลายแผ่น
                // ทุกแผ่นอ่านคำขอจาก หน่วย+วันที่ ผ่าน Request::gridForUnit() อยู่แล้ว
                return [
                    'status' => 'success',
                    'message' => 'สร้างแผ่น "' . $model->title . '" แล้ว',
                    // handleFormSubmit อ่านคีย์นี้เพื่อพาไปหน้ากริดต่อ
                    'redirect_url' => \yii\helpers\Url::to(['grid', 'id' => $model->id]),
                ];
            }
            $first = array_values($model->getFirstErrors());
            return ['status' => 'error', 'message' => $first[0] ?? 'บันทึกไม่สำเร็จ'];
        }

        return [
            'title' => 'สร้างรอบเวร',
            'content' => $this->renderAjax('_create_form', [
                'model' => $model,
                'groups' => $groups,
                'units' => $flat,
                'shiftsByUnit' => $this->shiftsByUnit(array_keys($flat)),
            ]),
            'footer' => ModalHelper::modalFooterSaveClose(),
        ];
    }

    /**
     * เวรของแต่ละหน่วย สำหรับให้ฟอร์มสร้างแผ่นเลือกขอบเขต
     * @param int[] $unitIds
     */
    private function shiftsByUnit(array $unitIds): array
    {
        $map = [];
        foreach ($unitIds as $unitId) {
            foreach (UnitShift::listForUnit((int) $unitId) as $shift) {
                $map[(int) $unitId][] = [
                    'id' => (int) $shift->id,
                    'name' => $shift->displayName(),
                    'short' => $shift->displayShort(),
                    'time' => $shift->timeRangeLabel(),
                    'standby' => (int) $shift->is_standby,
                ];
            }
        }
        return $map;
    }

    /**
     * กริดจัดเวร คน × วัน
     *
     * ดึงทุกอย่างเป็นชุดเดียว (เวร, ลา, ไปราชการ, วันหยุด, คำขอ) แล้วส่งให้ view วาด
     * เพื่อไม่ให้ 25 คน × 31 วัน กลายเป็น 775 query
     */
    public function actionGrid($id)
    {
        $period = $this->findPeriod((int) $id);
        $unitId = (int) $period->unit_id;

        $employees = $this->employeesOfUnit($unitId);
        $empIds = array_map(static fn($e) => (int) $e['id'], $employees);

        return $this->render('grid', [
            'period' => $period,
            'employees' => $employees,
            'types' => ShiftType::activeList(),
            'unitShifts' => $period->sheetShifts(),
            'grid' => Item::gridForPeriod($period->id),
            'counts' => Item::countByDayShift($period->id),
            'holidays' => RosterContext::holidays($period->firstDate(), $period->lastDate()),
            'weekends' => RosterContext::weekends((int) $period->year_ce, (int) $period->month),
            'leaves' => RosterContext::leaves($empIds, $period->firstDate(), $period->lastDate()),
            'trips' => RosterContext::trips($empIds, $period->firstDate(), $period->lastDate()),
            'requests' => RosterRequest::gridForUnit($unitId, $period->firstDate(), $period->lastDate()),
            'swaps' => Swap::approvedByItem((int) $period->id),
            'canEdit' => $period->isEditable() && RosterAccess::canManageUnit($unitId),
            'canManage' => RosterAccess::canManageUnit($unitId),
            'canReview' => RosterAccess::canReviewUnit($unitId),
            'canApprove' => RosterAccess::canApprove(),
            'reviewerIsApprover' => RosterAccess::reviewerIsApprover($unitId),
            'chain' => $this->approvalChain($period),
            'pendingCount' => $this->pendingCount(),
        ]);
    }

    /**
     * สายผู้ตรวจสอบ/ผู้อนุมัติของรอบนี้ ตามที่ตั้งค่าไว้ที่ /approve-v2/setting/levels?system=roster
     * ใช้ "แสดงให้เห็น" ว่าใบนี้ต้องผ่านใคร — สถานะจริงยังคุมด้วย Period ไม่ใช่ตาราง approve
     * (ตั้งใจไม่สร้าง record ในตาราง approve เพื่อไม่ให้มีแหล่งความจริง 2 ที่แล้วเพี้ยนกัน)
     */
    private function approvalChain(Period $period): array
    {
        $anchorEmpId = (int) Employees::find()
            ->select('id')
            ->where(['department' => $period->unit_id, 'status' => 1])
            ->scalar();
        if (!$anchorEmpId) {
            return [];
        }
        try {
            $resolved = ApproveLevelResolver::resolve('roster', $anchorEmpId);
        } catch (\Throwable $e) {
            return [];
        }
        $chain = [];
        foreach ($resolved as $step) {
            $name = null;
            if (!empty($step['emp_id'])) {
                $emp = Employees::findOne((int) $step['emp_id']);
                $name = $emp ? $emp->fullname : null;
            }
            $chain[] = [
                'level' => $step['level'],
                'label' => $step['label'],
                'name' => $name,
                'org' => $step['org_node_name'] ?? null,
            ];
        }
        return $chain;
    }

    // ── เปลี่ยนตัวเวรหลังประกาศ ──────────────────────────────────────────────

    /** ฟอร์มเปลี่ยนตัวฉุกเฉิน (หัวหน้าหน่วย) */
    public function actionReplaceForm($item_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $item = Item::findOne((int) $item_id);
        if (!$item) {
            return ['status' => 'error', 'message' => 'ไม่พบเวร'];
        }
        $period = $item->period;
        if (!$period || !RosterAccess::canManageUnit((int) $period->unit_id)) {
            return ['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์เปลี่ยนตัวเวรของหน่วยงานนี้'];
        }
        if (!$period->allowsSwap()) {
            return ['status' => 'error', 'message' => 'รอบเวรนี้ยังไม่ประกาศ หรือปิดรอบแล้ว'];
        }

        $candidates = [];
        foreach ($this->employeesOfUnit((int) $period->unit_id) as $emp) {
            if ((int) $emp['id'] === (int) $item->emp_id) {
                continue;
            }
            $candidates[(int) $emp['id']] = trim(($emp['prefix'] ?? '') . $emp['fname'] . ' ' . $emp['lname']);
        }

        return [
            'title' => 'เปลี่ยนตัวเวรฉุกเฉิน',
            'content' => $this->renderAjax('_replace_form', [
                'item' => $item,
                'period' => $period,
                'candidates' => $candidates,
            ]),
            'footer' => ModalHelper::modalFooterSaveClose(),
        ];
    }

    /** บันทึกการเปลี่ยนตัวฉุกเฉิน */
    public function actionReplace()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $item = Item::findOne((int) $this->request->post('item_id'));
        if (!$item) {
            return ['status' => 'error', 'message' => 'ไม่พบเวร'];
        }
        $period = $item->period;
        if (!$period || !RosterAccess::canManageUnit((int) $period->unit_id)) {
            return ['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์เปลี่ยนตัวเวรของหน่วยงานนี้'];
        }
        $actor = RosterAccess::currentEmployee();
        if (!$actor) {
            return ['status' => 'error', 'message' => 'ไม่พบข้อมูลพนักงานของคุณ'];
        }

        try {
            $swap = RosterSwapService::replace(
                $item,
                (int) $this->request->post('to_emp_id'),
                (string) $this->request->post('reason'),
                (int) $actor->id
            );
        } catch (\RuntimeException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        $warnings = $swap->warningList();
        return [
            'status' => 'success',
            'message' => 'เปลี่ยนตัวเวรแล้ว' . ($warnings ? ' — แต่ผิดกฎ: ' . implode(' · ', $warnings) : ''),
        ];
    }

    /** รายการใบเปลี่ยนตัวที่รอหัวหน้าหน่วยอนุมัติ + ประวัติของรอบ */
    public function actionSwaps($id)
    {
        $period = $this->findPeriod((int) $id);
        $swaps = Swap::find()
            ->where(['period_id' => $period->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
        return $this->render('swaps', [
            'period' => $period,
            'swaps' => $swaps,
            'canManage' => RosterAccess::canManageUnit((int) $period->unit_id),
            'pendingCount' => $this->pendingCount(),
        ]);
    }

    /** หัวหน้าหน่วยอนุมัติ/ปฏิเสธใบขอเปลี่ยนตัว */
    public function actionSwapDecide()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $swap = Swap::findOne((int) $this->request->post('swap_id'));
        if (!$swap) {
            return ['status' => 'error', 'message' => 'ไม่พบใบขอ'];
        }
        $period = $swap->period;
        if (!$period || !RosterAccess::canManageUnit((int) $period->unit_id)) {
            return ['status' => 'error', 'message' => 'เฉพาะหัวหน้าหน่วยงานนี้เท่านั้นที่อนุมัติได้'];
        }
        $actor = RosterAccess::currentEmployee();
        if (!$actor) {
            return ['status' => 'error', 'message' => 'ไม่พบข้อมูลพนักงานของคุณ'];
        }

        try {
            if ($this->request->post('decision') === 'approve') {
                $swap = RosterSwapService::approve($swap, (int) $actor->id);
                $warnings = $swap->warningList();
                return [
                    'status' => 'success',
                    'message' => 'อนุมัติและเปลี่ยนตัวแล้ว' . ($warnings ? ' — แต่ผิดกฎ: ' . implode(' · ', $warnings) : ''),
                ];
            }
            RosterSwapService::reject($swap, (int) $actor->id);
            return ['status' => 'success', 'message' => 'ปฏิเสธใบขอแล้ว'];
        } catch (\RuntimeException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /** ดูล่วงหน้าว่าถ้าเปลี่ยนเป็นคนนี้จะผิดกฎอะไร — ใช้เตือนก่อนกดยืนยัน */
    public function actionSwapPreview()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $item = Item::findOne((int) $this->request->get('item_id'));
        $toEmpId = (int) $this->request->get('to_emp_id');
        if (!$item || !$toEmpId) {
            return ['warnings' => []];
        }
        $period = $item->period;
        if (!$period || !RosterAccess::canViewUnit((int) $period->unit_id)) {
            return ['warnings' => []];
        }
        return ['warnings' => RosterSwapService::previewWarnings($item, $toEmpId)];
    }

    /**
     * คลิกช่องในกริด — สลับเปิด/ปิดเวรของคนนั้นในวันนั้น
     * คืนคำเตือนตามกฎกลับไปด้วย แต่ยังบันทึกให้เสมอ (กฎเป็นการเตือน ไม่ใช่การห้าม)
     */
    public function actionAssign()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $period = Period::findOne((int) $this->request->post('period_id'));
        if (!$period) {
            return ['status' => 'error', 'message' => 'ไม่พบรอบเวร'];
        }
        if (!RosterAccess::canManageUnit((int) $period->unit_id)) {
            return ['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์จัดเวรของหน่วยงานนี้'];
        }
        if (!$period->isEditable()) {
            return ['status' => 'error', 'message' => 'รอบเวรนี้ถูกล็อกแล้ว (' . $period->getStatusLabel() . ')'];
        }

        $empId = (int) $this->request->post('emp_id');
        $day = (int) $this->request->post('day');
        $unitShiftId = (int) $this->request->post('unit_shift_id');
        if (!$empId || !$day || !$unitShiftId) {
            return ['status' => 'error', 'message' => 'ข้อมูลไม่ครบ'];
        }
        if ($day < 1 || $day > $period->daysInMonth()) {
            return ['status' => 'error', 'message' => 'วันที่อยู่นอกรอบเวรนี้'];
        }
        $unitShift = UnitShift::findOne(['id' => $unitShiftId, 'unit_id' => $period->unit_id]);
        if (!$unitShift) {
            return ['status' => 'error', 'message' => 'ไม่พบเวรนี้ในหน่วยงาน'];
        }
        if (!$period->coversShift($unitShiftId)) {
            return ['status' => 'error', 'message' => 'เวรนี้ไม่ได้อยู่ในแผ่นนี้ — ไปจัดที่แผ่นที่ครอบเวรนี้'];
        }
        $workDate = $period->dateOfDay($day);

        $existing = Item::findOne([
            'period_id' => $period->id,
            'emp_id' => $empId,
            'work_date' => $workDate,
            'unit_shift_id' => $unitShiftId,
        ]);

        if ($existing) {
            $existing->delete();
            return [
                'status' => 'success',
                'action' => 'removed',
                'counts' => $this->dayCounts($period, $day),
                'summary' => $this->summary($period),
                'empTotals' => $this->employeeTotals($period, $empId),
            ];
        }

        $item = new Item([
            'period_id' => $period->id,
            'emp_id' => $empId,
            'work_date' => $workDate,
            'unit_shift_id' => $unitShiftId,
            'is_extra' => $unitShift->shiftType && $unitShift->shiftType->is_extra ? 1 : 0,
        ]);
        if (!$item->save()) {
            $first = array_values($item->getFirstErrors());
            return ['status' => 'error', 'message' => $first[0] ?? 'บันทึกไม่สำเร็จ'];
        }

        // ตรวจกฎ "หลัง" บันทึก เพราะกฎเป็นคำเตือน ไม่ใช่เงื่อนไขการบันทึก
        $checker = new RuleChecker((int) $period->unit_id);
        $shifts = RuleChecker::shiftsOfEmployee($empId, $period->firstDate(), $period->lastDate(), $item->id);
        $warnings = $checker->checkAssignment($workDate, $unitShiftId, $shifts);
        $positionWarning = $checker->checkPosition($unitShiftId, $empId);
        if ($positionWarning !== null) {
            array_unshift($warnings, $positionWarning);
        }

        return [
            'status' => 'success',
            'action' => 'added',
            'itemId' => $item->id,
            'warnings' => $warnings,
            'counts' => $this->dayCounts($period, $day),
            'summary' => $this->summary($period),
            'empTotals' => $this->employeeTotals($period, $empId),
        ];
    }

    /**
     * สรุปท้ายแถวของคนหนึ่ง — เวรทำงาน / วันหยุด / เวรนอกเวลา / ค่าตอบแทน
     * วันหยุดไม่นับเป็นเวรทำงานและไม่คิดเงิน
     */
    private function employeeTotals(Period $period, int $empId): array
    {
        $items = Item::find()
            ->with('unitShift')
            ->where(['period_id' => $period->id, 'emp_id' => $empId])
            ->andWhere(['<>', 'status', Item::STATUS_CANCELLED])
            ->all();
        $work = 0;
        $off = 0;
        $ot = 0;
        $pay = 0.0;
        foreach ($items as $item) {
            if ($item->isOff()) {
                $off++;
                continue;
            }
            $work++;
            if ($item->isOt()) {
                $ot++;
            }
            $pay += $item->payAmount();
        }
        return ['work' => $work, 'off' => $off, 'ot' => $ot, 'pay' => $pay];
    }

    /** คัดลอกเวรจากเดือนก่อน — จับคู่ตามวันในสัปดาห์ ดู RosterCopier */
    public function actionCopyPrevious($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $period = $this->findPeriod((int) $id);
        if (!$period->isEditable() || !RosterAccess::canManageUnit((int) $period->unit_id)) {
            return ['status' => 'error', 'message' => 'รอบเวรนี้แก้ไขไม่ได้'];
        }

        try {
            $result = RosterCopier::copy($period);
        } catch (\RuntimeException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'คัดลอกไม่สำเร็จ: ' . $e->getMessage()];
        }

        $message = 'คัดลอกจาก' . $result['source']->monthLabel() . ' ' . $result['copied'] . ' ช่อง';
        if ($result['skipped']) {
            $message .= ' (ข้าม ' . $result['skipped'] . ' ช่องที่ซ้ำ)';
        }
        if ($result['uncoveredDays']) {
            $message .= ' — วันที่ ' . implode(', ', $result['uncoveredDays']) . ' ยังว่าง ต้องจัดเอง';
        }
        $message .= ' และยังไม่ได้ตรวจวันลา ควรไล่ดูอีกครั้ง';
        return ['status' => 'success', 'message' => $message];
    }

    /**
     * จัดเวรอัตโนมัติ — เติมช่องที่ยังขาดให้ครบตามอัตรากำลัง
     * ไม่ลบเวรที่จัดมือไว้ ถ้าต้องการเริ่มใหม่ให้กด "ล้างทั้งเดือน" ก่อน
     */
    public function actionAutoFill($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $period = $this->findPeriod((int) $id);
        if (!$period->isEditable() || !RosterAccess::canManageUnit((int) $period->unit_id)) {
            return ['status' => 'error', 'message' => 'รอบเวรนี้แก้ไขไม่ได้'];
        }

        // หัวหน้าเลือกเองว่าจะให้เติมจนครบแม้ผิดกฎ หรือหยุดที่กฎแล้วเว้นช่องไว้
        $allowRelax = (string) $this->request->post('relax', '1') === '1';

        try {
            $result = (new RosterAutoScheduler($period))->run($allowRelax);
        } catch (\RuntimeException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        if ($result['placed'] === 0 && empty($result['shortages'])) {
            return ['status' => 'error', 'message' => 'ไม่มีช่องที่ต้องเติม — ตารางครบตามอัตรากำลังแล้ว'];
        }

        return [
            'status' => 'success',
            'placed' => $result['placed'],
            'relaxed' => $result['relaxed'],
            'warnings' => array_slice($result['warnings'], 0, 40),
            'warningTotal' => count($result['warnings']),
            'shortages' => array_slice($result['shortages'], 0, 40),
            'shortageTotal' => count($result['shortages']),
        ];
    }

    /**
     * เปลี่ยนชื่อแผ่นตารางเวร
     *
     * ชื่อคือสิ่งที่แยกแผ่นในเดือนเดียวกันออกจากกัน (บ่ายดึก / Refer / On call)
     * จึงเปลี่ยนได้เฉพาะตอนยังเป็นร่าง — ประกาศแล้วเปลี่ยนชื่อคือเปลี่ยนเอกสารที่แจกไปแล้ว
     */
    public function actionRename($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $period = $this->findPeriod((int) $id);
        if (!$period->isEditable() || !RosterAccess::canManageUnit((int) $period->unit_id)) {
            return ['status' => 'error', 'message' => 'เปลี่ยนชื่อได้เฉพาะตอนยังเป็นร่าง'];
        }

        $title = trim((string) $this->request->post('title', ''));
        if ($title === '') {
            return ['status' => 'error', 'message' => 'กรุณาระบุชื่อตารางเวร'];
        }
        if ($title === $period->title) {
            return ['status' => 'success', 'title' => $title, 'message' => 'ชื่อเดิม ไม่มีอะไรเปลี่ยน'];
        }

        $period->title = $title;
        if (!$period->save()) {
            $errors = array_merge(...array_values($period->getErrors()));
            return ['status' => 'error', 'message' => implode(' ', $errors)];
        }
        return ['status' => 'success', 'title' => $period->title, 'message' => 'เปลี่ยนชื่อแล้ว'];
    }

    /** ล้างเวรทั้งรอบ — ใช้ตอนคัดลอกผิดเดือนแล้วอยากเริ่มใหม่ */
    public function actionClear($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $period = $this->findPeriod((int) $id);
        if (!$period->isEditable() || !RosterAccess::canManageUnit((int) $period->unit_id)) {
            return ['status' => 'error', 'message' => 'รอบเวรนี้แก้ไขไม่ได้'];
        }
        $deleted = Item::deleteAll(['period_id' => $period->id]);
        return ['status' => 'success', 'message' => "ล้างแล้ว $deleted ช่อง"];
    }

    /**
     * เดินสถานะ: ส่งตรวจ / ตรวจแล้ว / อนุมัติ+ประกาศ / ปิดรอบ / ดึงกลับมาแก้
     *
     * สิทธิ์แต่ละขั้นต่างกันคนละชั้นในผังองค์กร จึงตรวจแยกทีละปลายทาง
     */
    public function actionTransition($id, $to)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $period = $this->findPeriod((int) $id);
        $unitId = (int) $period->unit_id;

        $denied = $this->transitionDenialReason($period, (string) $to, $unitId);
        if ($denied !== null) {
            return ['status' => 'error', 'message' => $denied];
        }

        if (!$period->transitionTo($to)) {
            return ['status' => 'error', 'message' => 'เปลี่ยนสถานะจาก "' . $period->getStatusLabel() . '" ไปขั้นนี้ไม่ได้'];
        }
        return ['status' => 'success', 'message' => 'อัปเดตสถานะเป็น "' . $period->getStatusLabel() . '" แล้ว'];
    }

    /**
     * ตรวจแล้วอนุมัติในคลิกเดียว — ใช้กับหน่วยที่แขวนใต้ root โดยตรง
     * ซึ่งผู้ตรวจ (หัวหน้าหน่วยแม่) กับผู้อนุมัติ (ผอ.) เป็นคนเดียวกัน จะได้ไม่ต้องกดซ้ำสองรอบ
     */
    public function actionReviewAndApprove($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $period = $this->findPeriod((int) $id);
        $unitId = (int) $period->unit_id;

        if (!RosterAccess::reviewerIsApprover($unitId)) {
            return ['status' => 'error', 'message' => 'คุณไม่ได้เป็นทั้งผู้ตรวจสอบและผู้อนุมัติของหน่วยนี้'];
        }
        if ($period->status !== Period::STATUS_SUBMITTED) {
            return ['status' => 'error', 'message' => 'รอบเวรนี้ไม่ได้อยู่ระหว่างรอตรวจสอบ'];
        }
        if (!$period->transitionTo(Period::STATUS_REVIEWED) || !$period->transitionTo(Period::STATUS_PUBLISHED)) {
            return ['status' => 'error', 'message' => 'ดำเนินการไม่สำเร็จ'];
        }
        return ['status' => 'success', 'message' => 'ตรวจสอบ อนุมัติ และประกาศแล้ว'];
    }

    /** เหตุผลที่ทำขั้นนี้ไม่ได้ — null = ทำได้ */
    private function transitionDenialReason(Period $period, string $to, int $unitId): ?string
    {
        switch ($to) {
            case Period::STATUS_SUBMITTED:
                if (!RosterAccess::canManageUnit($unitId)) {
                    return 'เฉพาะหัวหน้าหน่วยงานนี้เท่านั้นที่ส่งตรวจสอบได้';
                }
                if (!Item::find()->where(['period_id' => $period->id])->exists()) {
                    return 'ยังไม่ได้จัดเวร ส่งตรวจสอบไม่ได้';
                }
                return null;

            case Period::STATUS_REVIEWED:
                return RosterAccess::canReviewUnit($unitId)
                    ? null
                    : 'เฉพาะหัวหน้ากลุ่มงานที่หน่วยนี้สังกัดเท่านั้นที่ตรวจสอบได้';

            case Period::STATUS_PUBLISHED:
                return RosterAccess::canApprove()
                    ? null
                    : 'เฉพาะผู้อำนวยการเท่านั้นที่อนุมัติและประกาศได้';

            case Period::STATUS_CLOSED:
                return (RosterAccess::canApprove() || RosterAccess::canReviewUnit($unitId))
                    ? null
                    : 'เฉพาะผู้ตรวจสอบหรือผู้อำนวยการเท่านั้นที่ปิดรอบได้';

            case Period::STATUS_DRAFT:
                // ดึงกลับมาแก้ — ผู้มีสิทธิ์ขึ้นกับว่าตอนนี้ค้างอยู่ขั้นไหน
                if ($period->status === Period::STATUS_SUBMITTED) {
                    return (RosterAccess::canReviewUnit($unitId) || RosterAccess::canManageUnit($unitId))
                        ? null
                        : 'คุณไม่มีสิทธิ์ดึงรอบเวรนี้กลับมาแก้';
                }
                if ($period->status === Period::STATUS_REVIEWED) {
                    return (RosterAccess::canReviewUnit($unitId) || RosterAccess::canApprove())
                        ? null
                        : 'ตรวจสอบแล้ว ต้องให้ผู้ตรวจสอบหรือ ผอ. เปิดให้แก้';
                }
                // ประกาศแล้ว — ดึงกลับได้เฉพาะ ผอ. เพราะกระทบเวรที่ทีมรับทราบไปแล้ว
                return RosterAccess::canApprove()
                    ? null
                    : 'ประกาศแล้ว ต้องให้ผู้อำนวยการเปิดให้แก้ (หรือใช้การเปลี่ยนตัวเวรแทน)';
        }
        return 'ปลายทางไม่ถูกต้อง';
    }

    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $period = $this->findPeriod((int) $id);
        if (!RosterAccess::canManageUnit((int) $period->unit_id)) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์'];
        }
        if ($period->status !== Period::STATUS_DRAFT) {
            return ['status' => 'error', 'message' => 'ลบได้เฉพาะรอบที่ยังเป็นร่าง'];
        }
        $period->deleted_at = date('Y-m-d H:i:s');
        $period->deleted_by = Yii::$app->user->id;
        $period->save(false);
        return ['status' => 'success', 'container' => '#roster-period'];
    }

    /** ตอบรับ/ปฏิเสธคำขอหยุด-ขออยู่ ระหว่างจัดเวร */
    public function actionRespondRequest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = RosterRequest::findOne((int) $this->request->post('request_id'));
        if (!$request || !RosterAccess::canManageUnit((int) $request->unit_id)) {
            return ['status' => 'error', 'message' => 'ไม่พบคำขอ หรือไม่มีสิทธิ์'];
        }
        $status = (string) $this->request->post('status');
        if (!in_array($status, [RosterRequest::STATUS_ACCEPTED, RosterRequest::STATUS_REJECTED], true)) {
            return ['status' => 'error', 'message' => 'สถานะไม่ถูกต้อง'];
        }
        $request->status = $status;
        $request->responded_at = date('Y-m-d H:i:s');
        $request->responded_by = Yii::$app->user->id;
        $request->save(false);
        return ['status' => 'success', 'message' => 'บันทึกการพิจารณาแล้ว'];
    }

    /** ตารางเวร A4 แนวนอน สำหรับติดบอร์ด */
    public function actionPrint($id)
    {
        $period = $this->findPeriod((int) $id);
        $unitId = (int) $period->unit_id;
        $employees = $this->employeesOfUnit($unitId);
        $empIds = array_map(static fn($e) => (int) $e['id'], $employees);

        $this->layout = '@app/views/layouts/print';
        return $this->render('print', [
            'period' => $period,
            'employees' => $employees,
            'types' => ShiftType::activeList(),
            'unitShifts' => $period->sheetShifts(),
            'grid' => Item::gridForPeriod($period->id),
            'counts' => Item::countByDayShift($period->id),
            'holidays' => RosterContext::holidays($period->firstDate(), $period->lastDate()),
            'weekends' => RosterContext::weekends((int) $period->year_ce, (int) $period->month),
            'leaves' => RosterContext::leaves($empIds, $period->firstDate(), $period->lastDate()),
        ]);
    }

    /** ส่งออก Excel แยกสีตามผลัด — ดู RosterExporter */
    public function actionExport($id)
    {
        $period = $this->findPeriod((int) $id);
        $spreadsheet = RosterExporter::build($period, $this->employeesOfUnit((int) $period->unit_id));

        $dir = Yii::getAlias('@webroot/downloads');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = RosterExporter::filename($period);
        $filePath = $dir . '/' . $filename;
        (new Xlsx($spreadsheet))->save($filePath);
        if (!file_exists($filePath)) {
            throw new ServerErrorHttpException('สร้างไฟล์ไม่สำเร็จ');
        }
        return Yii::$app->response->sendFile($filePath, $filename, ['inline' => false]);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    /**
     * เจ้าหน้าที่ของหน่วย — เรียงคนที่ขึ้นเวร (work_shift='shift') ขึ้นก่อน
     * เพราะกริดมีไว้จัดเวรให้คนกลุ่มนี้เป็นหลัก
     */
    private function employeesOfUnit(int $unitId): array
    {
        return (new \yii\db\Query())
            ->select([
                'e.id', 'e.prefix', 'e.fname', 'e.lname', 'e.work_shift', 'e.employee_position_id',
                'position_name' => 'ep.title',
            ])
            ->from(['e' => Employees::tableName()])
            ->leftJoin(['ep' => 'employee_position'], 'ep.id = e.employee_position_id')
            ->where(['e.department' => $unitId, 'e.status' => 1])
            // เรียงคนขึ้นเวรก่อน แล้วจัดกลุ่มตามวิชาชีพ เพื่อให้หัวหน้าเห็นว่าใครเป็นพยาบาล
            // ใครเป็นผู้ช่วย โดยไม่ต้องจำ — หน่วยหนึ่งมีถึง 4 วิชาชีพและ 25 คน
            ->orderBy([
                new \yii\db\Expression("FIELD(e.work_shift,'shift') DESC"),
                'ep.sort' => SORT_ASC,
                'ep.title' => SORT_ASC,
                'e.fname' => SORT_ASC,
            ])
            ->all();
    }

    /** จำนวนคนที่จัดแล้วของวันนั้น แยกตามผลัด — ใช้อัปเดตตัวนับหลังคลิก */
    private function dayCounts(Period $period, int $day): array
    {
        $rows = (new \yii\db\Query())
            ->select(['unit_shift_id', 'c' => 'COUNT(*)'])
            ->from(Item::tableName())
            ->where(['period_id' => $period->id, 'work_date' => $period->dateOfDay($day)])
            ->andWhere(['<>', 'status', Item::STATUS_CANCELLED])
            ->groupBy('unit_shift_id')
            ->all();
        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['unit_shift_id']] = (int) $row['c'];
        }
        return $counts;
    }

    /** สรุป "จัดแล้ว x/y ช่อง" ของทั้งรอบ */
    private function summary(Period $period): array
    {
        $assigned = (int) Item::find()
            ->where(['period_id' => $period->id])
            ->andWhere(['<>', 'status', Item::STATUS_CANCELLED])
            ->count();
        // นับเฉพาะเวรของแผ่นนี้ และคิดอัตรากำลังตามประเภทวัน
        // ถ้าใช้ required_staff คูณจำนวนวันตรง ๆ หน่วยที่ลดคนวันหยุดจะขึ้นว่าจัดไม่ครบตลอด
        $holidays = RosterContext::holidays($period->firstDate(), $period->lastDate());
        $needed = 0;
        foreach ($period->sheetShifts() as $unitShift) {
            for ($d = 1, $days = $period->daysInMonth(); $d <= $days; $d++) {
                $needed += $unitShift->requiredFor(
                    isset($holidays[$d]),
                    (int) date('w', strtotime($period->dateOfDay($d)))
                );
            }
        }
        return ['assigned' => $assigned, 'needed' => $needed];
    }

    /**
     * จำนวนงานที่ "รอเราทำ" — รอบที่รอเราตรวจสอบ/อนุมัติ + ใบเปลี่ยนตัวที่รอเราอนุมัติ
     * ใช้เป็นตัวเลขบนเมนู จึงต้องนับเฉพาะสิ่งที่ผู้ใช้คนนี้ทำได้จริง
     */
    private function pendingCount(): int
    {
        $count = 0;
        $viewable = RosterAccess::viewableUnitIds();

        $waiting = Period::find()
            ->select(['unit_id', 'status'])
            ->where(['status' => [Period::STATUS_SUBMITTED, Period::STATUS_REVIEWED], 'deleted_at' => null]);
        if ($viewable !== null) {
            $waiting->andWhere(['unit_id' => $viewable ?: [0]]);
        }
        foreach ($waiting->asArray()->all() as $row) {
            $unitId = (int) $row['unit_id'];
            if ($row['status'] === Period::STATUS_SUBMITTED && RosterAccess::canReviewUnit($unitId)) {
                $count++;
            } elseif ($row['status'] === Period::STATUS_REVIEWED && RosterAccess::canApprove()) {
                $count++;
            }
        }

        $count += count(RosterSwapService::awaitingApproval(RosterAccess::manageableUnitIds()));
        return $count;
    }

    private function findPeriod(int $id): Period
    {
        $period = Period::findOne(['id' => $id, 'deleted_at' => null]);
        if (!$period) {
            throw new NotFoundHttpException('ไม่พบรอบเวร');
        }
        if (!RosterAccess::canViewUnit((int) $period->unit_id)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูตารางเวรของหน่วยงานนี้');
        }
        return $period;
    }
}

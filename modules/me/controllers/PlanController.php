<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\plan\models\PlanOrder;
use app\modules\plan\models\PlanOrderItem;
use app\modules\plan\models\PlanOrderRevision;
use app\modules\plan\components\PlanHelper;
use app\modules\plan\components\PersonnelPlanTaxonomy;
use app\modules\plan\services\PersonnelBudgetCalculator;
use app\modules\plan\services\PlanRevisionService;

/**
 * PlanController — หัวหน้าหน่วยงานจัดทำ/ติดตามแผนของหน่วยงานตนเอง (/me/plan)
 * เข้าถึงได้เฉพาะผู้ที่เป็นหัวหน้า (leader1) ของหน่วยงานอย่างน้อย 1 หน่วย
 */
class PlanController extends Controller
{
    /** @var \app\modules\hr\models\Employees */
    private $me;

    /** @var int[] id ของหน่วยงานที่ผู้ใช้ปัจจุบันเป็นหัวหน้า */
    private $ledOrgIds = [];

    /** จำนวนวันทำงานที่ใช้ตั้งต้นวงเงินทั้งปีของลูกจ้างรายวัน (ค่าเริ่มต้น ถ้า employee_type ไม่ได้ตั้งค่าไว้) */
    const DEFAULT_WORK_DAYS = PersonnelPlanTaxonomy::DEFAULT_WORK_DAYS;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'submit' => ['POST'],
                    'adjust' => ['POST'],
                    'pull-employees' => ['POST'],
                ],
            ],
        ]);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->me = UserHelper::GetEmployee();
        $orgs = $this->me ? $this->me->ledOrganizations() : [];
        $this->ledOrgIds = array_map(static fn($o) => (int) $o->id, $orgs);

        if (empty($this->ledOrgIds)) {
            throw new ForbiddenHttpException('เฉพาะหัวหน้าหน่วยงานเท่านั้นที่จัดทำแผนของหน่วยงานได้');
        }

        return true;
    }

    /** ประเภทคำขอ (bucket) => plan_category ที่นับเข้ากลุ่มนั้น (personnel = ทุกหมวด PER_*) */
    private function bucketCategories()
    {
        return [
            'parcel'    => ['INV_01', 'INV_02', 'INV_03', 'OPS_03'],
            'expenses'  => ['OPS_01', 'OPS_02', 'OPS_04', 'OPS_05', 'OPS_06'],
            // personnel = category ขึ้นต้น PER_
        ];
    }

    private function bucketOf($catCode)
    {
        if ($catCode !== null && strpos($catCode, 'PER') === 0) {
            return 'personnel';
        }
        foreach ($this->bucketCategories() as $bucket => $cats) {
            if (in_array($catCode, $cats, true)) {
                return $bucket;
            }
        }
        return 'other';
    }

    /** กลุ่มแผน (parcel/personnel/expenses) จาก item ที่เลือก — ไม่มีกลุ่ม department แยกอีกต่อไป */
    private function groupForItem($planItemId)
    {
        if (empty($planItemId)) {
            return 'expenses';
        }
        $cat = (new \yii\db\Query())->select('category_id')->from('categorise')
            ->where(['name' => 'plan_item', 'code' => $planItemId])->scalar();
        $bucket = $this->bucketOf($cat);
        return $bucket === 'other' ? 'expenses' : $bucket;
    }

    /** รายการแผนของหน่วยงาน + สรุปตามประเภท/หมวด + ค้นหา/กรอง */
    public function actionIndex()
    {
        $thaiYear   = (int) $this->request->get('thai_year', \app\modules\plan\components\PlanHelper::currentPlanYear());
        $status     = (string) $this->request->get('status', 'all');
        $q          = trim((string) $this->request->get('q', ''));
        $deptFilter = (int) $this->request->get('department_id', 0);
        $type       = (string) $this->request->get('type', 'all'); // parcel|personnel|expenses|all

        // แผนของหน่วยงานที่ตนเป็นหัวหน้า (ทุกประเภท ไม่มีกลุ่ม department แยก)
        $base = PlanOrder::find()
            ->where(['department_id' => $this->ledOrgIds])
            ->andWhere(['thai_year' => $thaiYear]);

        // สรุปตามประเภท (bucket) + หมวด (plan_category) ผ่านสาย item
        $sumRows = (new \yii\db\Query())
            ->select([
                'cat'    => 'c.code',
                'cat_title' => 'c.title',
                'status' => 'o.status',
                'cnt'    => 'COUNT(*)',
                'amt'    => 'COALESCE(SUM(o.order_price),0)',
            ])
            ->from(['o' => 'plan_order'])
            ->leftJoin(['i' => 'categorise'], "i.code = o.plan_item_id AND i.name = 'plan_item'")
            ->leftJoin(['c' => 'categorise'], "c.code = i.category_id AND c.name = 'plan_category'")
            ->where(['o.department_id' => $this->ledOrgIds, 'o.thai_year' => $thaiYear])
            ->groupBy(['c.code', 'c.title', 'o.status'])
            ->all();

        $byType = [
            'parcel'    => ['count' => 0, 'amount' => 0.0],
            'personnel' => ['count' => 0, 'amount' => 0.0],
            'expenses'  => ['count' => 0, 'amount' => 0.0],
        ];
        $byCat = []; // cat => [title, bucket, total_cnt, total_amt, appr_cnt, appr_amt]
        foreach ($sumRows as $r) {
            $bucket = $this->bucketOf($r['cat']);
            $cnt = (int) $r['cnt'];
            $amt = (float) $r['amt'];

            if (isset($byType[$bucket])) {
                $byType[$bucket]['count'] += $cnt;
                $byType[$bucket]['amount'] += $amt;
            }

            $catKey = $r['cat'] ?? '_none';
            if (!isset($byCat[$catKey])) {
                $byCat[$catKey] = [
                    'title' => $r['cat_title'] ?? 'ไม่ระบุหมวด',
                    'bucket' => $bucket,
                    'total_cnt' => 0, 'total_amt' => 0.0, 'appr_cnt' => 0, 'appr_amt' => 0.0,
                ];
            }
            $byCat[$catKey]['total_cnt'] += $cnt;
            $byCat[$catKey]['total_amt'] += $amt;
            if ($r['status'] === 'approve') {
                $byCat[$catKey]['appr_cnt'] += $cnt;
                $byCat[$catKey]['appr_amt'] += $amt;
            }
        }

        // สรุปตามสถานะ + ยอดรวมทั้งปี (สำหรับภาพรวมด้านบน)
        $byStatus = [
            'draft'   => ['c' => 0, 'a' => 0.0],
            'submit'  => ['c' => 0, 'a' => 0.0],
            'approve' => ['c' => 0, 'a' => 0.0],
            'reject'  => ['c' => 0, 'a' => 0.0],
        ];
        $grandCnt = 0;
        $grandAmt = 0.0;
        foreach ($sumRows as $r) {
            $c = (int) $r['cnt'];
            $a = (float) $r['amt'];
            if (isset($byStatus[$r['status']])) {
                $byStatus[$r['status']]['c'] += $c;
                $byStatus[$r['status']]['a'] += $a;
            }
            $grandCnt += $c;
            $grandAmt += $a;
        }

        // ใช้ตัวกรองกับรายการที่แสดง
        $query = clone $base;
        if ($status !== 'all') {
            $query->andWhere(['status' => $status]);
        }
        if ($deptFilter && in_array($deptFilter, $this->ledOrgIds, true)) {
            $query->andWhere(['department_id' => $deptFilter]);
        }
        if (in_array($type, ['parcel', 'personnel', 'expenses'], true)) {
            $itemQ = (new \yii\db\Query())
                ->select('i.code')->from(['i' => 'categorise'])
                ->leftJoin(['c' => 'categorise'], "c.code = i.category_id AND c.name = 'plan_category'")
                ->where(['i.name' => 'plan_item']);
            if ($type === 'personnel') {
                $itemQ->andWhere(['like', 'c.code', 'PER%', false]);
            } else {
                $itemQ->andWhere(['c.code' => $this->bucketCategories()[$type]]);
            }
            $query->andWhere(['plan_item_id' => $itemQ->column() ?: ['__none__']]);
        }
        if ($q !== '') {
            $itemCodes = (new \yii\db\Query())
                ->select('code')->from('categorise')
                ->where(['name' => 'plan_item'])->andWhere(['like', 'title', $q])
                ->column();
            $query->andWhere([
                'or',
                ['like', 'description', $q],
                ['in', 'plan_item_id', $itemCodes ?: ['__none__']],
            ]);
        }
        $models = $query->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])->all();

        return $this->render('index', [
            'me'         => $this->me,
            'models'     => $models,
            'thaiYear'   => $thaiYear,
            'years'      => $this->yearOptions(),
            'orgs'       => $this->me->ledOrganizations(),
            'byType'     => $byType,
            'byCat'      => $byCat,
            'byStatus'   => $byStatus,
            'grandCnt'   => $grandCnt,
            'grandAmt'   => $grandAmt,
            'status'     => $status,
            'q'          => $q,
            'deptFilter' => $deptFilter,
            'type'       => $type,
        ]);
    }

    /** สร้างแผนใหม่ */
    public function actionCreate()
    {
        if (!PlanHelper::canAdd()) {
            throw new ForbiddenHttpException('รอบทำแผนปิดรับข้อมูลแล้ว');
        }
        $model = new PlanOrder([
            'thai_year'     => \app\modules\plan\components\PlanHelper::currentPlanYear(),
            'department_id' => $this->ledOrgIds[0],
            'emp_id'        => (string) $this->me->id,
            'status'        => 'draft',
        ]);

        if ($model->load($this->request->post())) {
            $model->plan_group_id = $this->groupForItem($model->plan_item_id);
            if ($this->saveModel($model)) {
                Yii::$app->session->setFlash('success', 'บันทึกแผนเรียบร้อย');
                return $this->redirect(['index', 'thai_year' => $model->thai_year]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'orgs'  => $this->me->ledOrganizations(),
        ]);
    }

    /** จัดทำแผนพัสดุ (ฟอร์มเต็ม + ดึงเบิก) — ล็อกหน่วยงาน = หน่วยของหัวหน้า */
    public function actionCreateParcel()
    {
        if (!PlanHelper::canAdd()) {
            throw new ForbiddenHttpException('รอบทำแผนปิดรับข้อมูลแล้ว');
        }
        $model = new PlanOrder([
            'thai_year'     => \app\modules\plan\components\PlanHelper::currentPlanYear(),
            'plan_group_id' => 'parcel',
            'department_id' => $this->ledOrgIds[0],
            'emp_id'        => (string) $this->me->id,
            'status'        => 'draft',
        ]);
        $items = [];

        if ($this->request->isPost && $model->load($this->request->post())) {
            // บังคับหน่วยงาน/กลุ่ม/ผู้ขอ (กันแก้ข้ามสิทธิ์)
            if (!in_array((int) $model->department_id, $this->ledOrgIds, true)) {
                $model->department_id = $this->ledOrgIds[0];
            }
            $model->plan_group_id = 'parcel';
            $model->emp_id = (string) $this->me->id;

            if ($model->save(false)) {
                $this->saveParcelItems($model, (array) $this->request->post('items', []));
                Yii::$app->session->setFlash('success', 'บันทึกแผนพัสดุเรียบร้อย');
                return $this->redirect(['index', 'thai_year' => $model->thai_year]);
            }
        }

        $orgs = $this->me->ledOrganizations();
        return $this->render('create_parcel', [
            'model'        => $model,
            'items'        => $items,
            'lockDept'     => $this->ledOrgIds[0],
            'lockDeptName' => $orgs ? reset($orgs)->name : '',
        ]);
    }

    /** แก้ไขแผนพัสดุ (ฟอร์มเต็ม + รายการวัสดุเดิม) — เฉพาะร่าง/ไม่อนุมัติ */
    private function updateParcel(PlanOrder $model)
    {
        $origDept = (int) $model->department_id;
        $items = $model->getPlanItems()->orderBy(['id' => SORT_ASC])->all();

        if ($this->request->isPost && $model->load($this->request->post())) {
            // ล็อกหน่วยงาน/กลุ่มเดิม (กันแก้ข้ามสิทธิ์)
            if (!in_array((int) $model->department_id, $this->ledOrgIds, true)) {
                $model->department_id = $origDept;
            }
            $model->plan_group_id = 'parcel';

            if ($model->save(false)) {
                $this->saveParcelItems($model, (array) $this->request->post('items', []));
                Yii::$app->session->setFlash('success', 'แก้ไขแผนพัสดุเรียบร้อย');
                return $this->redirect(['index', 'thai_year' => $model->thai_year]);
            }
            $items = $model->getPlanItems()->orderBy(['id' => SORT_ASC])->all();
        }

        $deptName = '';
        foreach ($this->me->ledOrganizations() as $o) {
            if ((int) $o->id === (int) $model->department_id) {
                $deptName = $o->name;
                break;
            }
        }

        return $this->render('update_parcel', [
            'model'        => $model,
            'items'        => $items,
            'lockDept'     => (int) $model->department_id,
            'lockDeptName' => $deptName,
        ]);
    }

    /** บันทึกรายการวัสดุของแผน (แทนที่ชุดเดิมทั้งหมดด้วยที่ส่งมาจากฟอร์ม) */
    private function saveParcelItems(PlanOrder $model, array $postItems)
    {
        PlanOrderItem::deleteAll(['plan_order_id' => $model->id]);
        foreach ($postItems as $it) {
            PlanOrderItem::saveParcelRow($model->id, (array) $it);
        }
    }

    /**
     * จัดทำแผนบุคลากรทั้งหน่วยงานในครั้งเดียว (ดึงรายชื่อ -> กำหนดเงินรายคน -> 1 หน่วยงาน 1 แผน)
     * รายชื่อแต่ละคนเก็บเป็น plan_order_item ของแผนใบเดียว
     */
    public function actionCreatePersonnel()
    {
        if (!PlanHelper::canAdd()) {
            throw new ForbiddenHttpException('รอบทำแผนปิดรับข้อมูลแล้ว');
        }
        $model = new PlanOrder([
            'thai_year'     => \app\modules\plan\components\PlanHelper::currentPlanYear(),
            'plan_group_id' => 'personnel',
            'department_id' => $this->ledOrgIds[0],
            'emp_id'        => (string) $this->me->id,
            'status'        => 'draft',
        ]);
        $items = [];

        if ($this->request->isPost && $model->load($this->request->post())) {
            if (!in_array((int) $model->department_id, $this->ledOrgIds, true)) {
                $model->department_id = $this->ledOrgIds[0];
            }
            $model->plan_group_id = 'personnel';
            $model->emp_id = (string) $this->me->id;

            $postItems = (array) $this->request->post('items', []);
            if ($this->validatePersonnelPlan($model, $postItems) && $this->persistPersonnelPlan($model, $postItems)) {
                Yii::$app->session->setFlash('success', 'บันทึกแผนบุคลากรเรียบร้อย');
                return $this->redirect(['index', 'thai_year' => $model->thai_year]);
            }
            $items = $this->itemsFromPost($postItems);
        }

        $orgs = $this->me->ledOrganizations();
        return $this->render('create_personnel', [
            'model'        => $model,
            'items'        => $items,
            'lockDept'     => $this->ledOrgIds[0],
            'lockDeptName' => $orgs ? reset($orgs)->name : '',
            'departmentOptions' => $this->ledDepartmentOptions(),
        ]);
    }

    /** แก้ไขแผนบุคลากรแบบรายชื่อ (แผนที่มี plan_order_item) */
    private function updatePersonnel(PlanOrder $model)
    {
        $origDept = (int) $model->department_id;
        $items = $model->getPlanItems()->orderBy(['id' => SORT_ASC])->all();
        $planJson = is_array($model->data_json) ? $model->data_json : (json_decode((string) $model->data_json, true) ?: []);
        $baselineRevision = !empty($planJson['adjustment_cycle'])
            ? PlanOrderRevision::findOne([
                'plan_order_id' => $model->id,
                'cycle_no' => (int) $planJson['adjustment_cycle'],
                'version_type' => PlanRevisionService::BEFORE_ADJUST,
            ])
            : null;

        if ($this->request->isPost && $model->load($this->request->post())) {
            if (!in_array((int) $model->department_id, $this->ledOrgIds, true)) {
                $model->department_id = $origDept;
            }
            $model->plan_group_id = 'personnel';

            $postItems = (array) $this->request->post('items', []);
            if ($this->validatePersonnelPlan($model, $postItems) && $this->persistPersonnelPlan($model, $postItems)) {
                Yii::$app->session->setFlash('success', 'แก้ไขแผนบุคลากรเรียบร้อย');
                return $this->redirect(['index', 'thai_year' => $model->thai_year]);
            }
            $items = $this->itemsFromPost($postItems);
        }

        return $this->render('update_personnel', [
            'model'        => $model,
            'items'        => $items,
            'lockDept'     => (int) $model->department_id,
            'lockDeptName' => $this->deptName((int) $model->department_id),
            'departmentOptions' => $this->ledDepartmentOptions(),
            'baselineRevision' => $baselineRevision,
        ]);
    }

    /**
     * ดึงรายชื่อบุคลากรตามประเภทค่าใช้จ่ายที่เลือก
     */
    public function actionPullEmployees()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $dept  = (int) $this->request->post('department_id');
        $planItemId = trim((string) $this->request->post('plan_item_id'));
        $includeChildren = (int) $this->request->post('include_children');
        $allTypesInput = $this->request->post('all_employee_types', null);
        $requestedTypes = array_values(array_unique(array_filter(array_map(
            'intval',
            (array) $this->request->post('employee_type_ids', [])
        ))));

        if (!in_array($dept, $this->ledOrgIds, true)) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์ในหน่วยงานนี้'];
        }

        $allEmployeeTypes = $allTypesInput === null
            ? self::planItemAppliesToAllEmployees($planItemId)
            : (bool) $allTypesInput;
        $types = $requestedTypes ?: self::employeeTypesForPlanItem($planItemId);
        if (!$allEmployeeTypes && !$types) {
            return ['status' => 'error', 'message' => 'กรุณาเลือกประเภทบุคลากรอย่างน้อย 1 ประเภท หรือเลือกทุกประเภท'];
        }
        if (!$allEmployeeTypes) {
            $activeTypes = (new \yii\db\Query())
                ->select('id')->from('employee_type')->where(['active' => 1, 'id' => $types])->column();
            $types = array_map('intval', $activeTypes);
            if (!$types) {
                return ['status' => 'error', 'message' => 'ไม่พบประเภทบุคลากรที่ใช้งานอยู่ตามเงื่อนไข'];
            }
        }

        [$deptIds, $childCount] = $this->deptScope($dept, (bool) $includeChildren);

        $q = (new \yii\db\Query())
            ->select([
                'id'        => 'e.id',
                'name'      => "TRIM(CONCAT(COALESCE(e.prefix,''), e.fname, ' ', e.lname))",
                'position'  => "COALESCE(p.title, e.position_name, '')",
                'type_id'   => 'e.employee_type_id',
                'type_name' => "COALESCE(t.title, '')",
                'salary'    => 'e.salary',
            ])
            ->from(['e' => 'employees'])
            ->leftJoin(['p' => 'employee_position'], 'p.id = e.employee_position_id')
            ->leftJoin(['t' => 'employee_type'], 't.id = e.employee_type_id')
            ->where(['e.department' => $deptIds])
            ->andWhere(['e.status' => '1'])
            ->orderBy(['e.employee_type_id' => SORT_ASC, 'e.fname' => SORT_ASC]);
        if (!$allEmployeeTypes) {
            $q->andWhere(['e.employee_type_id' => $types]);
        }

        $rows = [];
        foreach ($q->all() as $r) {
            $typeId = (int) $r['type_id'];
            $annualBudget = PersonnelPlanTaxonomy::annualBudget($typeId, (float) $r['salary']);
            $rows[] = [
                'emp_id'    => (int) $r['id'],
                'name'      => $r['name'],
                'position'  => $r['position'],
                'type_id'   => $typeId,
                'type_name' => $r['type_name'],
                'annual_budget' => round($annualBudget, 2),
                'monthly_average' => round($annualBudget / 12, 2),
            ];
        }

        return ['status' => 'success', 'count' => count($rows), 'child_count' => $childCount, 'items' => $rows];
    }

    /** ประเภทบุคลากรที่ตรงกับประเภทค่าใช้จ่าย — ตั้งค่าที่ /plan/plan-item (ไม่ได้ตั้ง = ใช้ค่าเดิมตามรหัส) */
    public static function employeeTypesForPlanItem(string $planItemId): array
    {
        return PersonnelPlanTaxonomy::employeeTypeIds($planItemId);
    }

    /** ประเภทค่าใช้จ่ายที่จ่ายให้บุคลากรทุกประเภทในหน่วยงาน (เช่น ฉ.11) */
    public static function planItemAppliesToAllEmployees(string $planItemId): bool
    {
        return PersonnelPlanTaxonomy::appliesToAllEmployees($planItemId);
    }

    /** บันทึกรายชื่อบุคลากรของแผน (แทนที่ชุดเดิม) + ปรับยอดรวมตามรายการ */
    private function savePersonnelItems(PlanOrder $model, array $postItems): float
    {
        PlanOrderItem::deleteAll(['plan_order_id' => $model->id]);
        $total = 0.0;
        foreach ($postItems as $it) {
            if (trim((string) ($it['item_name'] ?? '')) === '') {
                continue;
            }
            $annualBudget = max(0, round((float) ($it['annual_budget'] ?? 0), 2));
            $allocation = PersonnelBudgetCalculator::allocate($annualBudget);
            $total += $allocation['total'];

            $pi = new PlanOrderItem();
            $pi->plan_order_id = $model->id;
            $pi->title       = (string) ($it['position'] ?? '');
            $pi->item_id     = (string) ($it['emp_id'] ?? '');
            $pi->item_name   = $it['item_name'];
            $pi->qty         = 12;
            $pi->unit_price  = $allocation['monthly_average'];
            $pi->total_price = $allocation['total'];
            $pi->data_json   = [
                'emp_id'          => $it['emp_id'] ?? null,
                'position'        => $it['position'] ?? '',
                'employee_type_id' => $it['type_id'] ?? null,
                'type_name'       => (string) ($it['type_name'] ?? ''),
                'annual_budget'   => $allocation['total'],
                'monthly_average' => $allocation['monthly_average'],
            ];
            if (!$pi->save(false)) {
                throw new \RuntimeException('บันทึกรายชื่อบุคลากรไม่สำเร็จ');
            }
        }
        return round($total, 2);
    }

    /** แปลง items ที่ post กลับมาเป็น object ชั่วคราว (ใช้ตอน render ซ้ำเมื่อฟอร์มไม่ผ่าน) */
    private function itemsFromPost(array $postItems)
    {
        $items = [];
        foreach ($postItems as $it) {
            if (trim((string) ($it['item_name'] ?? '')) === '') {
                continue;
            }
            $pi = new PlanOrderItem();
            $pi->item_name  = $it['item_name'];
            $pi->title      = (string) ($it['position'] ?? '');
            $pi->item_id    = (string) ($it['emp_id'] ?? '');
            $annualBudget = max(0, (float) ($it['annual_budget'] ?? 0));
            $pi->qty        = 12;
            $pi->unit_price = round($annualBudget / 12, 2);
            $pi->total_price = round($annualBudget, 2);
            $pi->data_json  = [
                'emp_id'          => $it['emp_id'] ?? null,
                'position'        => $it['position'] ?? '',
                'employee_type_id' => $it['type_id'] ?? null,
                'type_name'       => (string) ($it['type_name'] ?? ''),
                'annual_budget'   => round($annualBudget, 2),
                'monthly_average' => round($annualBudget / 12, 2),
            ];
            $items[] = $pi;
        }
        return $items;
    }

    private function validatePersonnelPlan(PlanOrder $model, array $postItems): bool
    {
        if (empty($model->plan_category_id)) {
            $model->addError('plan_category_id', 'กรุณาเลือกรายการคำขอบุคลากร');
        }
        if (empty($model->plan_item_id)) {
            $model->addError('plan_item_id', 'กรุณาเลือกประเภทค่าใช้จ่าย');
        }
        if ($model->plan_category_id && $model->plan_item_id) {
            $actualCategory = (new \yii\db\Query())
                ->select('category_id')->from('categorise')
                ->where(['name' => 'plan_item', 'code' => (string) $model->plan_item_id])
                ->scalar();
            if ((string) $actualCategory !== (string) $model->plan_category_id) {
                $model->addError('plan_item_id', 'ประเภทค่าใช้จ่ายไม่ตรงกับรายการคำขอบุคลากร');
            }
        }

        $validItems = [];
        $employeeIds = [];
        foreach ($postItems as $item) {
            if (trim((string) ($item['item_name'] ?? '')) === '') {
                continue;
            }
            if (!is_numeric($item['annual_budget'] ?? null) || (float) $item['annual_budget'] < 0) {
                $model->addError('plan_item_id', 'วงเงินประมาณการทั้งปีต้องเป็นศูนย์หรือมากกว่า');
                continue;
            }
            $employeeId = trim((string) ($item['emp_id'] ?? ''));
            if ($employeeId !== '' && isset($employeeIds[$employeeId])) {
                $model->addError('plan_item_id', 'พบรายชื่อบุคลากรซ้ำในแผน');
                continue;
            }
            if ($employeeId !== '') {
                $employeeIds[$employeeId] = true;
            }
            $validItems[] = $item;
        }
        if (!$validItems) {
            $model->addError('plan_item_id', 'กรุณาเพิ่มบุคลากรอย่างน้อย 1 ราย');
        }

        $duplicateQuery = PlanOrder::find()
            ->where([
                'thai_year' => (int) $model->thai_year,
                'department_id' => (int) $model->department_id,
                'plan_group_id' => 'personnel',
                'plan_item_id' => (string) $model->plan_item_id,
                'deleted_at' => null,
            ]);
        if (!$model->isNewRecord && $model->id) {
            $duplicateQuery->andWhere(['<>', 'id', $model->id]);
        }
        $duplicate = $duplicateQuery->exists();
        if ($duplicate) {
            $model->addError('plan_item_id', 'มีแผนของหน่วยงานและประเภทค่าใช้จ่ายนี้แล้ว กรุณาแก้ไขแผนเดิม');
        }
        return !$model->hasErrors();
    }

    private function persistPersonnelPlan(PlanOrder $model, array $postItems): bool
    {
        $wasNewRecord = $model->isNewRecord;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save(false)) {
                throw new \RuntimeException('บันทึกแผนไม่สำเร็จ');
            }
            $total = $this->savePersonnelItems($model, $postItems);
            $allocation = PersonnelBudgetCalculator::allocate($total);
            $model->order_price = $allocation['total'];
            foreach ($allocation['months'] as $month => $amount) {
                $model->{'month_' . $month} = $amount;
            }
            if (!$model->save(false)) {
                throw new \RuntimeException('บันทึกยอดรายเดือนไม่สำเร็จ');
            }
            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            if ($wasNewRecord) {
                $model->setIsNewRecord(true);
                $model->id = null;
            }
            Yii::error($e, __METHOD__);
            $model->addError('plan_item_id', 'บันทึกแผนไม่สำเร็จ กรุณาลองใหม่');
            return false;
        }
    }

    /** ชื่อหน่วยงานจากรายการที่ตนเป็นหัวหน้า */
    private function deptName($deptId)
    {
        foreach ($this->me->ledOrganizations() as $o) {
            if ((int) $o->id === (int) $deptId) {
                return $o->name;
            }
        }
        return '';
    }

    private function ledDepartmentOptions(): array
    {
        $options = [];
        foreach ($this->me->ledOrganizations() as $organization) {
            $options[(int) $organization->id] = (string) $organization->name;
        }
        return $options;
    }

    /** ขอบเขตหน่วยงาน (ตัวเอง + หน่วยย่อยในผัง ถ้าเลือก) => [deptIds, childCount] */
    private function deptScope($dept, $includeChildren)
    {
        $deptIds = [$dept];
        $childCount = 0;
        if ($includeChildren) {
            $node = (new \yii\db\Query())->select(['root', 'lft', 'rgt'])->from('tree')->where(['id' => $dept])->one();
            if ($node) {
                $children = (new \yii\db\Query())->select('id')->from('tree')
                    ->where(['root' => $node['root']])
                    ->andWhere(['>', 'lft', (int) $node['lft']])
                    ->andWhere(['<', 'rgt', (int) $node['rgt']])
                    ->column();
                $deptIds = array_merge($deptIds, $children);
                $childCount = count($children);
            }
        }
        return [array_values(array_unique(array_map('intval', $deptIds))), $childCount];
    }

    /** ดึงรายการจากการเบิกปีก่อน (จำกัดเฉพาะหน่วยงานที่ตนเป็นหัวหน้า) */
    public function actionPullConsumption()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $dept  = (int) $this->request->post('department_id');
        $atype = (string) $this->request->post('asset_type_id');
        $year  = (int) $this->request->post('thai_year');
        $includeChildren = (int) $this->request->post('include_children');

        if (!in_array($dept, $this->ledOrgIds, true)) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์ในหน่วยงานนี้'];
        }
        if ($atype === '' || !$year) {
            return ['status' => 'error', 'message' => 'กรุณาเลือกประเภทวัสดุ'];
        }
        $prevYear = $year - 1;

        $deptIds = [$dept];
        $childCount = 0;
        if ($includeChildren) {
            $node = (new \yii\db\Query())->select(['root', 'lft', 'rgt'])->from('tree')->where(['id' => $dept])->one();
            if ($node) {
                $children = (new \yii\db\Query())->select('id')->from('tree')
                    ->where(['root' => $node['root']])
                    ->andWhere(['>', 'lft', (int) $node['lft']])
                    ->andWhere(['<', 'rgt', (int) $node['rgt']])
                    ->column();
                $deptIds = array_merge($deptIds, $children);
                $childCount = count($children);
            }
        }
        $deptIn = implode(',', array_values(array_unique(array_map('intval', $deptIds))));

        $sql = "
            SELECT it.asset_item AS code, COALESCE(ci.title, it.asset_item) AS name,
                   SUM(it.qty) AS qty_year, ROUND(SUM(it.qty) / 12, 2) AS per_month,
                   CAST(SUBSTRING_INDEX(GROUP_CONCAT(it.unit_price ORDER BY o.movement_date DESC), ',', 1) AS DECIMAL(15,2)) AS last_price
            FROM stock_events o
            JOIN stock_events it ON it.category_id = o.id AND it.name = 'order_item'
            JOIN employees e ON e.id = o.emp_id
            LEFT JOIN categorise ci ON ci.code = it.asset_item AND ci.name = 'asset_item'
            WHERE o.name = 'order' AND o.transaction_type = 'OUT'
              AND o.thai_year = :y AND o.asset_type_id = :a AND e.department IN ($deptIn)
            GROUP BY it.asset_item, name HAVING qty_year > 0 ORDER BY qty_year DESC
        ";
        $rows = Yii::$app->db->createCommand($sql, [':y' => $prevYear, ':a' => $atype])->queryAll();

        return ['status' => 'success', 'prev_year' => $prevYear, 'count' => count($rows), 'child_count' => $childCount, 'items' => $rows];
    }

    /** แก้ไขแผน (เฉพาะสถานะร่าง/ไม่อนุมัติ) */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $editableStatuses = PlanHelper::canAdjust($model->thai_year) ? ['draft', 'reject', 'renew'] : ['draft', 'reject'];
        if (!in_array($model->status, $editableStatuses, true)) {
            throw new ForbiddenHttpException('แผนที่ส่งขออนุมัติหรืออนุมัติแล้ว แก้ไขไม่ได้');
        }
        $workflow = is_array($model->data_json) ? $model->data_json : (json_decode((string) $model->data_json, true) ?: []);
        $canEditAdjustment = ($workflow['workflow_cycle'] ?? '') === 'adjust' && PlanHelper::canAdjust($model->thai_year);
        if (!PlanHelper::canEdit($model->thai_year) && !$canEditAdjustment) {
            throw new ForbiddenHttpException('รอบทำแผนปิดการแก้ไขแล้ว (ติดต่อผู้ดูแลแผน)');
        }

        // แผนพัสดุใช้ฟอร์มเต็ม (มีรายการวัสดุ) คนละแบบกับแผนค่าใช้จ่าย
        if ($model->plan_group_id === 'parcel' || $this->groupForItem($model->plan_item_id) === 'parcel') {
            return $this->updateParcel($model);
        }
        // แผนบุคลากรทุกรายใช้ฟอร์มรายชื่อเดียวกับหน้าสร้าง
        // รวมถึงแผนเก่าที่ยังไม่มี plan_order_item เพื่อให้ดึงรายชื่อมาเติมได้
        if ($model->plan_group_id === 'personnel') {
            return $this->updatePersonnel($model);
        }

        if ($model->load($this->request->post())) {
            $model->plan_group_id = $this->groupForItem($model->plan_item_id);
            if ($this->saveModel($model)) {
                Yii::$app->session->setFlash('success', 'แก้ไขแผนเรียบร้อย');
                return $this->redirect(['index', 'thai_year' => $model->thai_year]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'orgs'  => $this->me->ledOrganizations(),
        ]);
    }

    /** ส่งขออนุมัติ (ร่าง/ไม่อนุมัติ -> รออนุมัติ) */
    public function actionSubmit($id)
    {
        $model = $this->findModel($id);
        $workflow = is_array($model->data_json) ? $model->data_json : (json_decode((string) $model->data_json, true) ?: []);
        $normalSubmit = in_array($model->status, ['draft', 'reject'], true) && PlanHelper::canAdd($model->thai_year);
        $adjustSubmit = in_array($model->status, ['renew', 'reject'], true)
            && ($workflow['workflow_cycle'] ?? '') === 'adjust'
            && PlanHelper::canAdjust($model->thai_year);
        if ($normalSubmit || $adjustSubmit) {
            $model->status = 'submit';
            $model->save(false);
            Yii::$app->session->setFlash('success', 'ส่งขออนุมัติแล้ว');
        }
        return $this->redirect(['index', 'thai_year' => $model->thai_year]);
    }

    /** เปิดแผนอนุมัติเดิมให้แก้ไขในรอบปรับแผน พร้อมเก็บตัวเลขเดิมครบ 12 เดือน */
    public function actionAdjust($id)
    {
        $model = $this->findModel($id);
        if ($model->status !== 'approve' || !PlanHelper::canAdjust($model->thai_year)) {
            throw new ForbiddenHttpException('ขณะนี้ไม่อยู่ในรอบปรับแผน หรือแผนยังไม่ได้อนุมัติ');
        }
        $cycle = PlanRevisionService::nextCycle($model);
        PlanRevisionService::capture($model, $cycle, PlanRevisionService::BEFORE_ADJUST);
        $json = is_array($model->data_json) ? $model->data_json : (json_decode((string) $model->data_json, true) ?: []);
        $json['adjustment_cycle'] = $cycle;
        $json['workflow_cycle'] = 'adjust';
        $model->data_json = $json;
        $model->status = 'renew';
        $model->save(false);
        Yii::$app->session->setFlash('success', 'เปิดแผนสำหรับปรับตัวเลขครบ 12 เดือนแล้ว');
        return $this->redirect(['update', 'id' => $model->id]);
    }

    /** ลบแผน (เฉพาะร่าง) */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->status === 'draft' && PlanHelper::canEdit($model->thai_year)) {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบแผนแล้ว');
        }
        return $this->redirect(['index', 'thai_year' => $model->thai_year]);
    }

    /**
     * บันทึก + ตรวจความถูกต้องขั้นต่ำ (ต้องเลือก item, หน่วยงานต้องอยู่ในสิทธิ์)
     * plan_type_id/plan_category_id ถูก derive จาก item ที่ PlanOrder::beforeSave()
     */
    private function saveModel(PlanOrder $model)
    {
        // กันแก้หน่วยงานข้ามสิทธิ์
        if (!in_array((int) $model->department_id, $this->ledOrgIds, true)) {
            $model->addError('department_id', 'ไม่มีสิทธิ์ในหน่วยงานนี้');
        }
        if (empty($model->plan_item_id)) {
            $model->addError('plan_item_id', 'กรุณาเลือกรายการ');
        }

        if ($model->hasErrors()) {
            return false;
        }

        // ยอดรวม = ผลรวมรายเดือน (ถ้าไม่ได้กรอกรายเดือน ใช้ order_price ตามที่กรอก)
        $sumMonths = 0;
        for ($i = 1; $i <= 12; $i++) {
            $sumMonths += (float) $model->{'month_' . $i};
        }
        if ($sumMonths > 0) {
            $model->order_price = $sumMonths;
        }

        return $model->save(false);
    }

    /** ตัวเลือกปีงบ (ปีถัดไป + ปีที่มีข้อมูลแล้ว) */
    private function yearOptions()
    {
        $next = \app\modules\plan\components\PlanHelper::currentPlanYear();
        $years = PlanOrder::find()
            ->select('thai_year')
            ->where(['department_id' => $this->ledOrgIds])
            ->distinct()
            ->column();
        $years[] = $next;
        $years[] = AppHelper::YearBudget();
        $years = array_values(array_unique(array_filter($years)));
        rsort($years);
        return $years;
    }

    /** โหลดแผน โดยจำกัดเฉพาะหน่วยงานที่ตนเป็นหัวหน้า */
    protected function findModel($id)
    {
        $model = PlanOrder::find()
            ->where(['id' => $id])
            ->andWhere(['department_id' => $this->ledOrgIds])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบแผนที่ต้องการ หรือไม่มีสิทธิ์เข้าถึง');
        }
        return $model;
    }
}

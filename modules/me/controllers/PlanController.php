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
use app\modules\plan\components\PlanHelper;

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

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'submit' => ['POST'],
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
                foreach ((array) $this->request->post('items', []) as $it) {
                    if (!empty($it['item_name'])) {
                        $pi = new PlanOrderItem();
                        $pi->plan_order_id = $model->id;
                        $pi->item_name  = $it['item_name'];
                        $pi->qty        = (int) ($it['qty'] ?? 0);
                        $pi->unit_price = (float) ($it['unit_price'] ?? 0);
                        $pi->save(false);
                    }
                }
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

        if (!in_array($model->status, ['draft', 'reject'], true)) {
            throw new ForbiddenHttpException('แผนที่ส่งขออนุมัติหรืออนุมัติแล้ว แก้ไขไม่ได้');
        }
        if (!PlanHelper::canEdit($model->thai_year)) {
            throw new ForbiddenHttpException('รอบทำแผนปิดการแก้ไขแล้ว (ติดต่อผู้ดูแลแผน)');
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
        if (in_array($model->status, ['draft', 'reject'], true) && PlanHelper::canAdd($model->thai_year)) {
            $model->status = 'submit';
            $model->save(false);
            Yii::$app->session->setFlash('success', 'ส่งขออนุมัติแล้ว');
        }
        return $this->redirect(['index', 'thai_year' => $model->thai_year]);
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

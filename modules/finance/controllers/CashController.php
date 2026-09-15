<?php

namespace app\modules\finance\controllers;

use app\components\AppHelper;
use app\modules\finance\models\FinanceCashCategory;
use app\modules\finance\models\FinanceCashTxn;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ระบบรับ-จ่ายเงินบำรุง (พอร์ต mophcash) — เฟส 1
 * ผังบัญชี 3 ระดับ + บันทึกรายการรับ/จ่าย + ทะเบียน (list)
 */
class CashController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['financeOperate']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save' => ['post'],
                    'delete' => ['post'],
                    'category-save' => ['post'],
                    'category-delete' => ['post'],
                    'seed-chart' => ['post'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        return $this->redirect(['income']);
    }

    public function actionIncome()
    {
        return $this->renderList(FinanceCashCategory::TYPE_IN, 'income');
    }

    public function actionExpense()
    {
        return $this->renderList(FinanceCashCategory::TYPE_OUT, 'expense');
    }

    /** ทะเบียนรายการรับ/จ่าย พร้อมตัวกรอง ปีงบ/วันที่/ค้นหา + แบ่งหน้า */
    private function renderList(string $type, string $active)
    {
        $req = Yii::$app->request;
        $fiscalYear = (int) $req->get('fiscal_year', FinanceCashTxn::currentFiscalYear());
        $date = trim((string) $req->get('date', ''));
        $q = trim((string) $req->get('q', ''));

        $query = FinanceCashTxn::find()
            ->with('category')
            ->where(['txn_type' => $type, 'fiscal_year' => $fiscalYear]);

        if ($date !== '') {
            $dbDate = AppHelper::normalizeDateToDb($date);
            if ($dbDate) {
                $query->andWhere(['doc_date' => $dbDate]);
            }
        }
        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'doc_no', $q],
                ['like', 'party_name', $q],
                ['like', 'note', $q],
            ]);
        }

        $sum = (clone $query)->sum('amount');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['doc_date' => SORT_DESC, 'id' => SORT_DESC]],
            'pagination' => ['pageSize' => 25],
        ]);

        return $this->render('list', [
            'type' => $type,
            'active' => $active,
            'dataProvider' => $dataProvider,
            'fiscalYear' => $fiscalYear,
            'date' => $date,
            'q' => $q,
            'sum' => (float) $sum,
            'tree' => FinanceCashCategory::treeArray($type),
        ]);
    }

    /** บันทึก/แก้ไขรายการผ่าน popup (AJAX) — id ว่าง = สร้างใหม่ */
    public function actionSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $id = (int) ($post['id'] ?? 0);
        $model = $id ? FinanceCashTxn::findOne($id) : new FinanceCashTxn();
        if (!$model) {
            return ['ok' => false, 'message' => 'ไม่พบรายการ'];
        }
        if ($id && $model->is_closed) {
            return ['ok' => false, 'message' => 'รายการนี้อยู่ในงวดที่ปิดบัญชีแล้ว แก้ไขไม่ได้'];
        }
        $model->load($post);
        // แปลงวันที่ไทย (วว/ดด/พ.ศ.) → ค.ศ. Y-m-d ก่อน validate
        $model->doc_date = AppHelper::normalizeDateToDb($post['FinanceCashTxn']['doc_date'] ?? null);
        if ($model->save()) {
            return ['ok' => true, 'message' => 'บันทึกรายการเรียบร้อย'];
        }
        return ['ok' => false, 'errors' => $model->getErrors()];
    }

    /** ดึงข้อมูลรายการเดิมมาเติมใน popup ตอนแก้ไข */
    public function actionGet($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findTxn($id);
        $chain = ['group' => null, 'category' => null, 'account' => null];
        $node = $model->category;
        $guard = 0;
        while ($node !== null && $guard++ < 5) {
            $chain[$node->level] = $node->id;
            $node = $node->parent;
        }
        return [
            'id' => $model->id,
            'txn_type' => $model->txn_type,
            'fiscal_year' => $model->fiscal_year,
            'category_id' => $model->category_id,
            'chain' => $chain,
            'doc_date' => $model->doc_date ? AppHelper::convertToThai($model->doc_date) : '',
            'doc_no' => $model->doc_no,
            'pay_method' => $model->pay_method,
            'amount' => $model->amount !== null ? (float) $model->amount : '',
            'party_name' => $model->party_name,
            'note' => $model->note,
        ];
    }

    public function actionDelete($id)
    {
        $model = $this->findTxn($id);
        if ($model->is_closed) {
            Yii::$app->session->setFlash('error', 'รายการนี้อยู่ในงวดที่ปิดบัญชีแล้ว ลบไม่ได้');
        } else {
            $type = $model->txn_type;
            $year = $model->fiscal_year;
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบรายการเรียบร้อย');
            return $this->redirect([$type === FinanceCashCategory::TYPE_OUT ? 'expense' : 'income', 'fiscal_year' => $year]);
        }
        return $this->redirect(['income']);
    }

    // ---- จัดการผังบัญชี (chart of accounts) --------------------------------

    public function actionCategory($type = FinanceCashCategory::TYPE_IN)
    {
        $type = $type === FinanceCashCategory::TYPE_OUT ? FinanceCashCategory::TYPE_OUT : FinanceCashCategory::TYPE_IN;
        return $this->render('category', [
            'type' => $type,
            'tree' => FinanceCashCategory::treeArray($type),
        ]);
    }

    public function actionCategorySave()
    {
        $post = Yii::$app->request->post();
        $id = (int) ($post['id'] ?? 0);
        $model = $id ? FinanceCashCategory::findOne($id) : new FinanceCashCategory();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบหมวด');
        }
        $model->txn_type = ($post['txn_type'] ?? FinanceCashCategory::TYPE_IN) === FinanceCashCategory::TYPE_OUT
            ? FinanceCashCategory::TYPE_OUT : FinanceCashCategory::TYPE_IN;
        $model->name = trim((string) ($post['name'] ?? ''));
        $model->description = trim((string) ($post['description'] ?? '')) ?: null;
        $model->parent_id = ((int) ($post['parent_id'] ?? 0)) ?: null;
        $model->sort_order = (int) ($post['sort_order'] ?? 0);
        $model->is_active = isset($post['is_active']) ? (int) $post['is_active'] : 1;
        // ระดับคิดจากความลึกของแม่: ไม่มีแม่=group, แม่เป็น group=category, ที่เหลือ=account
        $model->level = $model->levelFromParent();

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกผังบัญชีเรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['category', 'type' => $model->txn_type]);
    }

    public function actionCategoryDelete()
    {
        $id = (int) Yii::$app->request->post('id');
        $model = FinanceCashCategory::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบหมวด');
        }
        $type = $model->txn_type;
        if ($model->getChildren()->exists()) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะยังมีหมวดย่อยอยู่ภายใน');
        } elseif (FinanceCashTxn::find()->where(['category_id' => $id])->exists()) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะมีรายการบันทึกใช้หมวดนี้อยู่');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบหมวดเรียบร้อย');
        }
        return $this->redirect(['category', 'type' => $type]);
    }

    /** ป้อนผังบัญชีมาตรฐาน mophcash ครั้งเดียว (idempotent — ข้ามถ้ามีผังอยู่แล้ว) */
    public function actionSeedChart()
    {
        if (FinanceCashCategory::find()->exists()) {
            Yii::$app->session->setFlash('warning', 'มีผังบัญชีอยู่แล้ว ข้ามการ seed (กันซ้ำ)');
            return $this->redirect(['category']);
        }
        $count = 0;
        $insert = function (array $node, string $type, ?int $parentId, string $level, int &$count) use (&$insert) {
            $c = new FinanceCashCategory([
                'txn_type' => $type,
                'parent_id' => $parentId,
                'level' => $level,
                'name' => $node['name'],
                'description' => $node['desc'] ?? null,
                'sort_order' => $count,
            ]);
            $c->save(false);
            $count++;
            $childLevel = $level === FinanceCashCategory::LEVEL_GROUP
                ? FinanceCashCategory::LEVEL_CATEGORY : FinanceCashCategory::LEVEL_ACCOUNT;
            foreach ($node['children'] ?? [] as $child) {
                $insert($child, $type, $c->id, $childLevel, $count);
            }
        };

        foreach (self::standardChart() as $type => $groups) {
            foreach ($groups as $group) {
                $insert($group, $type, null, FinanceCashCategory::LEVEL_GROUP, $count);
            }
        }
        Yii::$app->session->setFlash('success', "ป้อนผังบัญชีมาตรฐานแล้ว $count รายการ");
        return $this->redirect(['category']);
    }

    private function findTxn($id): FinanceCashTxn
    {
        $model = FinanceCashTxn::findOne((int) $id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        return $model;
    }

    /**
     * ผังบัญชีมาตรฐานเงินบำรุง — คัดจาก tree "เลือกหัวข้อรายรับ/รายจ่าย" ของ mophcash โดยตรง
     * (เฉพาะหัวข้อที่มีปุ่ม [เลือก] = leaf ที่ใช้ลงรายการ) โครงสร้าง 2 ระดับ กลุ่ม → หัวข้อ
     * รายรับ 15 หัวข้อ (9+6) / รายจ่าย 23 หัวข้อ (12+6+3+2)
     */
    private static function standardChart(): array
    {
        $n = fn (string $name, array $children = []) => ['name' => $name, 'children' => $children];
        return [
            FinanceCashCategory::TYPE_IN => [
                $n('รายรับจากการดำเนินงาน', [
                    $n('รายรับค่ารักษาพยาบาลสำหรับโครงการสุขภาพถ้วนหน้า UC'),
                    $n('รายรับค่ารักษาพยาบาลสำหรับโครงการสุขภาพถ้วนหน้า UC งบลงทุน'),
                    $n('รายรับจากระบบปฏิบัติการฉุกเฉิน (EMS)'),
                    $n('รายรับค่ารักษาพยาบาลเบิกจ่ายตรงกรมบัญชีกลาง'),
                    $n('รายรับค่ารักษาพยาบาลผู้ป่วยเบิกต้นสังกัด'),
                    $n('รายรับค่ารักษาพยาบาลเบิกจาก อปท.'),
                    $n('รายรับค่ารักษาพยาบาลจากกองทุนประกันสังคม'),
                    $n('รายรับค่ารักษาพยาบาลแรงงานต่างด้าว'),
                    $n('รายรับค่ารักษาพยาบาลและการบริการอื่น'),
                ]),
                $n('รายรับอื่น', [
                    $n('รายรับเงินช่วยเหลือ'),
                    $n('รายรับเงินอุดหนุน'),
                    $n('รายรับจากการบริจาค'),
                    $n('รายรับดอกเบี้ยเงินฝากธนาคาร'),
                    $n('รายรับอื่น'),
                    $n('รายรับไม่ทราบแหล่งที่มา'),
                ]),
            ],
            FinanceCashCategory::TYPE_OUT => [
                $n('รายจ่ายบุคลากร', [
                    $n('ค่าจ้างลูกจ้างชั่วคราว / พนักงานกระทรวง'),
                    $n('ค่าล่วงเวลางานบริการ / งานสนับสนุน'),
                    $n('ค่าตอบแทนการปฏิบัติงานเวรผลัดบ่ายหรือผลัดดึกของเจ้าหน้าที่'),
                    $n('ค่าตอบแทนเงินเพิ่มพิเศษไม่ทำเวชปฏิบัติส่วนตัว หรือปฏิบัติงาน รพ.เอกชน'),
                    $n('ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.11)'),
                    $n('ค่าตอบแทนตามผลการปฏิบัติงาน (ฉ.12)'),
                    $n('เงินเพิ่ม (พ.ต.ส)'),
                    $n('ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานของเจ้าหน้าที่ (นอกเวลา) ฉ5'),
                    $n('ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานในคลินิกพิเศษเฉพาะทางนอกเวลาราชการ (SMC)'),
                    $n('ค่าตอบแทนอื่น'),
                    $n('เงินค่าใช้จ่ายบุคลากรอื่น'),
                    $n('ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.10)'),
                ]),
                $n('รายจ่ายจากการดำเนินงาน', [
                    $n('ค่ายา'),
                    $n('ค่าเวชภัณฑ์มิใช่ยา'),
                    $n('ค่าวัสดุ'),
                    $n('ค่าสาธารณูปโภค'),
                    $n('ค่าใช้สอย'),
                    $n('ค่าใช้จ่ายดำเนินงานอื่น'),
                ]),
                $n('รายจ่ายลงทุน', [
                    $n('ค่าครุภัณฑ์'),
                    $n('ค่าที่ดินและสิ่งก่อสร้าง'),
                    $n('ค่าครุภัณฑ์ต่ำกว่าเกณฑ์'),
                ]),
                $n('รายจ่ายอื่น', [
                    $n('รายจ่ายสนับสนุน รพ.สต. รพช. รพท. รพศ. สสอ. สสจ.'),
                    $n('รายจ่ายอื่นๆ'),
                ]),
            ],
        ];
    }
}

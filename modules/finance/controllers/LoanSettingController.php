<?php

namespace app\modules\finance\controllers;

use app\modules\finance\models\FinanceLoanAccount;
use app\modules\finance\models\FinanceLoanExpenseType;
use app\modules\finance\models\FinanceLoanItemKind;
use Yii;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ตั้งค่าระบบเงินยืม — ประเภทค่าใช้จ่าย / รายการในใบประมาณการ / บัญชีที่ยืม
 *
 * ไม่มีการลบ เพราะ FK จากใบยืมเป็น RESTRICT และใบเก่าต้องแสดงชื่อเดิมได้เสมอ
 * ของที่เลิกใช้ให้ปิดการใช้งาน จะหายจาก dropdown ของใบยืมใหม่เท่านั้น
 *
 * รหัส (code) ตั้งได้ตอนสร้างครั้งเดียว เพราะใช้อ้างอิงตอนนำเข้าไฟล์และในโค้ด
 * ส่วนเลขที่บัญชีแก้ได้ เพราะใบยืมผูกด้วย id ไม่ได้ผูกด้วยเลขบัญชี
 */
class LoanSettingController extends Controller
{
    /** แท็บ => [คลาสโมเดล, ชื่อแท็บ, ไอคอน] */
    private const TABS = [
        'type' => [FinanceLoanExpenseType::class, 'ประเภทค่าใช้จ่าย', 'bi-tags'],
        'kind' => [FinanceLoanItemKind::class, 'รายการในใบประมาณการ', 'bi-list-check'],
        'account' => [FinanceLoanAccount::class, 'บัญชีที่ยืม', 'bi-bank'],
    ];

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['financeAdmin']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['toggle' => ['POST']]],
        ]);
    }

    public function actionIndex($tab = 'type')
    {
        $tab = $this->normalizeTab($tab);
        $class = self::TABS[$tab][0];
        $models = $class::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        return $this->render('index', [
            'tab' => $tab,
            'tabs' => self::TABS,
            'models' => $models,
            'usage' => $this->usageCounts($tab),
        ]);
    }

    /**
     * จำนวนใบยืม (หรือบรรทัดประมาณการ) ที่อ้างถึงแต่ละรายการ
     * ให้ผู้ดูแลรู้ก่อนแก้ชื่อหรือปิดใช้ ว่ากระทบข้อมูลเดิมแค่ไหน
     *
     * @return array<int,int> id => จำนวน
     */
    private function usageCounts(string $tab): array
    {
        [$table, $column] = [
            'type' => ['{{%finance_loan}}', 'expense_type_id'],
            'kind' => ['{{%finance_loan_item}}', 'item_kind_id'],
            'account' => ['{{%finance_loan}}', 'account_id'],
        ][$tab];
        $rows = (new \yii\db\Query())
            ->select(['ref_id' => $column, 'cnt' => 'COUNT(*)'])
            ->from($table)
            ->where(['not', [$column => null]])
            ->groupBy($column)
            ->all();
        return array_map('intval', array_column($rows, 'cnt', 'ref_id'));
    }

    public function actionCreate($tab)
    {
        $tab = $this->normalizeTab($tab);
        $class = self::TABS[$tab][0];
        /** @var ActiveRecord $model */
        $model = new $class();
        $model->loadDefaultValues();
        // ต่อท้ายรายการเดิม ผู้ใช้ไม่ต้องไล่หาเลขลำดับเอง
        $model->sort_order = (int) $class::find()->max('sort_order') + 10;
        return $this->saveForm($tab, $model);
    }

    public function actionUpdate($tab, $id)
    {
        $tab = $this->normalizeTab($tab);
        return $this->saveForm($tab, $this->findModel($tab, $id));
    }

    public function actionToggle($tab, $id)
    {
        $tab = $this->normalizeTab($tab);
        $model = $this->findModel($tab, $id);
        $model->is_active = !$model->is_active;
        $model->save(false, ['is_active']);
        Yii::$app->session->setFlash('success', ($model->is_active ? 'เปิดใช้งาน ' : 'ปิดการใช้งาน ') . $this->labelOf($model));
        return $this->redirect(['index', 'tab' => $tab]);
    }

    private function saveForm(string $tab, ActiveRecord $model)
    {
        $isNew = $model->isNewRecord;
        $oldCode = $model->getOldAttribute('code');
        if ($model->load(Yii::$app->request->post())) {
            if (!$isNew && $model->hasAttribute('code')) {
                $model->code = $oldCode;
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', ($isNew ? 'เพิ่ม ' : 'บันทึก ') . $this->labelOf($model) . ' เรียบร้อย');
                return $this->redirect(['index', 'tab' => $tab]);
            }
        }
        return $this->render('form', [
            'tab' => $tab,
            'tabLabel' => self::TABS[$tab][1],
            'model' => $model,
        ]);
    }

    private function labelOf(ActiveRecord $model): string
    {
        return $model instanceof FinanceLoanAccount ? $model->displayName() : (string) $model->name;
    }

    private function normalizeTab($tab): string
    {
        return isset(self::TABS[$tab]) ? $tab : 'type';
    }

    private function findModel(string $tab, $id): ActiveRecord
    {
        $class = self::TABS[$tab][0];
        $model = $class::findOne((int) $id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบข้อมูลที่ต้องการ');
        }
        return $model;
    }
}

<?php

namespace app\modules\purchase\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\purchase\models\Bond;
use app\modules\purchase\models\Contract;
use app\modules\purchase\models\BondSearch;
use app\modules\purchase\components\BondCalculator;
use app\modules\purchase\components\BondWordExporter;

/**
 * ทะเบียนหลักประกัน — หลักประกันสัญญา/ซอง/ผลงาน และการคืน
 *
 * สิทธิ์: role 'purchase' เท่านั้น (ชุดเดียวกับงานสัญญา)
 * route ชุดนี้อยู่ใน allow list ของ AccessControl ระดับแอป และกันสิทธิ์เองที่ behaviors()
 */
class BondController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['purchase'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new BondSearch();
        if (!$this->request->get('BondSearch')) {
            $searchModel->thai_year = (int) AppHelper::YearBudget();
        }
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'counters' => $searchModel->counters(),
            'missing' => BondSearch::missingContracts($searchModel->thai_year ?: null),
        ]);
    }

    /**
     * บันทึกหลักประกันใบใหม่ — ส่ง contract_id มาด้วยเพื่อเติมค่าจากสัญญาให้อัตโนมัติ
     * ค่าที่เติมมาเป็นเพียงค่าตั้งต้นตามเกณฑ์ในทะเบียน ผู้ใช้แก้ได้ทุกช่อง
     */
    public function actionCreate($contract_id = null)
    {
        $model = new Bond([
            'thai_year' => (int) AppHelper::YearBudget(),
            'status' => Bond::STATUS_PENDING,
            'bond_type' => Bond::TYPE_CONTRACT,
            'bond_form' => Bond::FORM_BANK_GUARANTEE,
            'place_date' => date('Y-m-d'),
        ]);

        $emp = UserHelper::GetEmployee();
        if ($emp) {
            $model->emp_id = $emp->id;
            $model->department_id = $emp->department;
        }

        if ($contract_id && !$this->request->isPost) {
            $this->fillFromContract($model, (int) $contract_id);
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกหลักประกัน "' . $model->title . '" เรียบร้อย');

            if ($contract_id) {
                return $this->redirect(['/purchase/contract/view', 'id' => $contract_id]);
            }
            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกหลักประกัน "' . $model->title . '" เรียบร้อย');
            return $this->redirect(['index']);
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * บันทึกการคืนหรือการยึดหลักประกัน
     *
     * แยกออกมาเป็นหน้าของตัวเองเพราะเป็นการปิดเรื่องที่ต้องมีหลักฐานกำกับ คือวันที่คืน
     * และเลขที่หนังสือคืน โปรแกรมต้นแบบมีปุ่ม "ขอคืนหลักประกัน" ที่เปิดไฟล์ Word
     * อย่างเดียว ไม่บันทึกอะไรลงฐานและไม่เปลี่ยนสถานะ ทะเบียนจึงไม่มีทางรู้ว่าใบไหน
     * คืนไปแล้วเมื่อไรด้วยหนังสือฉบับใด
     */
    public function actionReturn($id)
    {
        $model = $this->findModel($id);

        if (in_array($model->status, [Bond::STATUS_RETURNED, Bond::STATUS_SEIZED], true)) {
            Yii::$app->session->setFlash('warning', 'หลักประกันใบนี้ปิดเรื่องไปแล้ว หากต้องแก้ไขให้ใช้หน้าแก้ไข');
            return $this->redirect(['index']);
        }

        if ($model->status === Bond::STATUS_EXEMPT) {
            Yii::$app->session->setFlash('warning', 'หลักประกันใบนี้บันทึกไว้ว่าได้รับการยกเว้น จึงไม่มีของให้คืน');
            return $this->redirect(['index']);
        }

        $model->scenario = Bond::SCENARIO_DEFAULT;
        $model->status = Bond::STATUS_RETURNED;
        $model->return_date = $model->return_date ?: date('Y-m-d');

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(
                'success',
                ($model->status === Bond::STATUS_SEIZED ? 'บันทึกการยึดหลักประกัน "' : 'บันทึกการคืนหลักประกัน "')
                    . $model->title . '" เรียบร้อย'
            );
            return $this->redirect(['index']);
        }

        return $this->render('return', ['model' => $model]);
    }

    /**
     * ลบแบบ soft delete — หลักประกันเป็นหลักฐานทางการเงินที่ต้องตรวจสอบย้อนหลังได้
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->deleted_by = Yii::$app->user->id;
        $model->save(false, ['deleted_at', 'deleted_by']);

        Yii::$app->session->setFlash('success', 'ลบหลักประกัน "' . $model->title . '" แล้ว');
        return $this->redirect(['index']);
    }

    /**
     * เกณฑ์ที่ใช้กับวงเงินก้อนหนึ่ง (JSON) — ฟอร์มเรียกใช้เพื่ออัปเดตกล่องคำแนะนำ
     *
     * ทั้งข้อความและตัวเลขถูกคิดที่นี่ที่เดียว ฝั่งหน้าจอมีหน้าที่แสดงผลอย่างเดียว
     * ไม่มีเงื่อนไขวงเงินซ้ำอยู่ใน JS — ถ้าปล่อยให้สองฝั่งคิดเอง สุดท้ายจะพูดไม่ตรงกัน
     * แบบที่เกิดกับโปรแกรมต้นแบบ
     */
    public function actionPolicy($amount = null, $contract_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $contract = $contract_id
            ? Contract::findOne(['id' => $contract_id, 'deleted_at' => null])
            : null;

        $base = ($amount !== null && $amount !== '')
            ? (float) $amount
            : ($contract ? (float) $contract->budget : 0.0);

        return [
            'base' => $base,
            'policy' => BondCalculator::policyFor($base, $contract ? $contract->contract_type : null),
            'contract' => $contract ? [
                'id' => $contract->id,
                'title' => $contract->title,
                'budget' => (float) $contract->budget,
                'vendor_name' => $contract->partyName(),
            ] : null,
        ];
    }

    /** ทะเบียนคุมหลักประกันทั้งปีเป็นไฟล์ Word */
    public function actionRegister($year = null)
    {
        $year = (int) ($year ?: AppHelper::YearBudget());
        $models = Bond::find()
            ->where(['deleted_at' => null, 'thai_year' => $year])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return BondWordExporter::sendRegister($models, $year);
    }

    /** เติมค่าจากสัญญาลงฟอร์มหลักประกันใบใหม่ ตามเกณฑ์ที่ตั้งไว้ในทะเบียน */
    private function fillFromContract(Bond $model, int $contractId): void
    {
        $contract = Contract::findOne(['id' => $contractId, 'deleted_at' => null]);
        if ($contract === null) {
            Yii::$app->session->setFlash('warning', 'ไม่พบสัญญาที่เลือก');
            return;
        }

        $policy = BondCalculator::policyFor((float) $contract->budget, $contract->contract_type);

        $model->source_type = Bond::SOURCE_CONTRACT;
        $model->source_id = $contract->id;
        $model->title = $contract->title;
        $model->vendor_id = $contract->vendor_id;
        $model->vendor_name = $contract->vendor_name;
        $model->thai_year = (int) $contract->thai_year;
        $model->base_amount = (float) $contract->budget;

        // เติมอัตราและยอดให้เฉพาะกรณีที่เกณฑ์บอกว่าต้องวาง ถ้าเกณฑ์ยกเว้นหรือยังไม่ได้
        // ตั้งเกณฑ์ไว้ ปล่อยว่างให้ผู้ใช้ตัดสินใจเอง ระบบต้องไม่เดายอดเงินให้
        if ($policy['required']) {
            $model->rate = $policy['rate'];
            $model->amount = $policy['amount'];
        }
    }

    protected function findModel($id)
    {
        $model = Bond::findOne(['id' => $id, 'deleted_at' => null]);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบหลักประกันที่ต้องการ');
        }
        return $model;
    }
}

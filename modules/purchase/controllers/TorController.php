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
use app\modules\purchase\models\Tor;
use app\modules\purchase\models\TorPrice;
use app\modules\purchase\models\TorSearch;
use app\modules\purchase\models\TorTemplate;

/**
 * งานเขียน TOR / ข้อกำหนดคุณลักษณะ
 *
 * สิทธิ์: role 'purchase' เท่านั้น (งานพัสดุเป็นผู้จัดทำ TOR)
 * route ชุดนี้อยู่ใน allow list ของ AccessControl ระดับแอป และกันสิทธิ์เองที่ behaviors() ด้านล่าง
 */
class TorController extends Controller
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
        $searchModel = new TorSearch();
        if (!$this->request->get('TorSearch')) {
            $searchModel->thai_year = (int) AppHelper::YearBudget();
        }
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    public function actionCreate()
    {
        $model = new Tor([
            'thai_year' => (int) AppHelper::YearBudget(),
            'tor_date' => date('Y-m-d'),
            'status' => Tor::STATUS_DRAFT,
            'mid_method' => 'ค่าเฉลี่ยของราคาที่สืบได้',
        ]);

        $emp = UserHelper::GetEmployee();
        if ($emp) {
            $model->emp_id = $emp->id;
            $model->department_id = $emp->department;
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($this->saveWithPrices($model)) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'prices' => [],
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($this->saveWithPrices($model)) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'prices' => $model->prices,
        ]);
    }

    /**
     * ลบแบบ soft delete — TOR เป็นเอกสารอ้างอิงราคากลาง ต้องตรวจสอบย้อนหลังได้
     * และเฟสถัดไปใบขอซื้อจะอ้าง tor_id ไว้ ถ้าลบจริงข้อมูลฝั่งใบขอซื้อจะชี้ไปที่ว่าง
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->deleted_by = Yii::$app->user->id;
        $model->save(false, ['deleted_at', 'deleted_by']);

        Yii::$app->session->setFlash('success', 'ลบ TOR "' . $model->title . '" แล้ว');
        return $this->redirect(['index']);
    }

    /**
     * บันทึกหัวเอกสาร + ใบสืบราคาในทรานแซกชันเดียว
     * ถ้าแถวสืบราคาบันทึกไม่สำเร็จต้อง rollback ทั้งหมด ไม่ปล่อยให้เหลือหัวเอกสารที่ไม่มีใบสืบราคา
     */
    private function saveWithPrices(Tor $model): bool
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save()) {
                $tx->rollBack();
                return false;
            }

            TorPrice::deleteAll(['tor_id' => $model->id]);

            $seq = 0;
            foreach ((array) $this->request->post('prices', []) as $row) {
                $name = trim((string) ($row['vendor_name'] ?? ''));
                $vendorId = trim((string) ($row['vendor_id'] ?? ''));
                $price = (float) ($row['price'] ?? 0);
                // แถวที่ไม่กรอกอะไรเลย ถือว่าผู้ใช้เว้นไว้ ไม่ต้องบันทึก
                if ($name === '' && $vendorId === '' && $price <= 0) {
                    continue;
                }
                $item = new TorPrice([
                    'tor_id' => $model->id,
                    'seq' => ++$seq,
                    'vendor_id' => $vendorId ?: null,
                    'vendor_name' => $name ?: null,
                    'detail' => (string) ($row['detail'] ?? ''),
                    'price' => $price,
                ]);
                if (!$item->save()) {
                    $tx->rollBack();
                    $model->addError('mid_price', 'บันทึกใบสืบราคาไม่สำเร็จ: ' . implode(' ', $item->getFirstErrors()));
                    return false;
                }
            }

            // ราคากลางคำนวณด้วย JS ตอนกรอก และผู้ใช้แก้ทับได้ แต่ถ้าไม่เคยแตะแท็บราคากลางเลย
            // ค่าจะยังเป็น 0 ทั้งที่มีใบสืบราคาแล้ว เอกสารที่พิมพ์ออกไปจะขึ้นราคากลาง 0.00
            // จึงเติมค่าที่คำนวณได้ให้เมื่อผู้ใช้ยังไม่ได้ระบุ (ถ้าระบุมาแล้วเคารพค่าที่ระบุ)
            if ((float) $model->mid_price <= 0) {
                $model->refresh();
                $computed = $model->calcMidPrice();
                if ($computed > 0) {
                    $model->mid_price = $computed;
                    $model->save(false, ['mid_price']);
                }
            }

            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึก TOR "' . $model->title . '" เรียบร้อย');
            return true;
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error('บันทึก TOR ไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
            $model->addError('title', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            return false;
        }
    }

    /** หน้าต่างเลือกแม่แบบ (โหลดเข้า modal) */
    public function actionTemplatePicker()
    {
        $query = TorTemplate::find()->where(['active' => 1]);

        $category = (string) $this->request->get('category', '');
        if ($category !== '') {
            $query->andWhere(['category' => $category]);
        }
        $q = trim((string) $this->request->get('q', ''));
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'title', $q], ['like', 'spec', $q]]);
        }

        $items = $query->orderBy(['sort_order' => SORT_ASC])->limit(200)->all();

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'เลือกแม่แบบคุณลักษณะ',
                'content' => $this->renderAjax('_template_picker', [
                    'items' => $items,
                    'categories' => TorTemplate::activeCategories(),
                    'category' => $category,
                    'q' => $q,
                ]),
            ];
        }

        return $this->render('_template_picker', [
            'items' => $items,
            'categories' => TorTemplate::activeCategories(),
            'category' => $category,
            'q' => $q,
        ]);
    }

    /**
     * ส่งเนื้อหาแม่แบบให้ JS เติมลงฟอร์ม
     * ส่ง ref_price ไปด้วยเพื่อ "แสดงเป็นข้อมูลประกอบ" เท่านั้น — ฝั่ง JS ต้องไม่นำไปเติมใบสืบราคา
     */
    public function actionTemplateData($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $t = TorTemplate::findOne(['id' => $id, 'active' => 1]);
        if (!$t) {
            return ['status' => 'error', 'message' => 'ไม่พบแม่แบบที่เลือก'];
        }

        return [
            'status' => 'success',
            'data' => [
                'title' => $t->title,
                'spec' => $t->spec,
                'standard' => $t->standard,
                'warranty' => $t->warranty,
                'unit_name' => $t->unit_name,
                'delivery_days' => $t->delivery_days,
                'ref_price' => $t->ref_price,
                'ref_price_text' => $t->ref_price ? number_format((float) $t->ref_price, 2) : '',
            ],
        ];
    }

    /** ส่งออกเอกสาร TOR เป็นไฟล์ Word */
    public function actionWord($id)
    {
        $model = $this->findModel($id);
        return \app\modules\purchase\components\TorWordExporter::send($model);
    }

    protected function findModel($id)
    {
        $model = Tor::findOne(['id' => $id, 'deleted_at' => null]);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบเอกสาร TOR ที่ต้องการ');
        }
        return $model;
    }
}

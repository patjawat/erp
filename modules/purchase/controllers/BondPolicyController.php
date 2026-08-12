<?php

namespace app\modules\purchase\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\modules\purchase\models\BondPolicy;

/**
 * ตั้งค่าเกณฑ์หลักประกันตามวงเงิน
 *
 * ต่างจากหน้าอัตราภาษีหัก ณ ที่จ่ายตรงที่เพิ่ม/ลบแถวได้ เพราะเกณฑ์เป็น "ช่วงวงเงิน"
 * ซึ่งไม่ใช่ชุดปิด หน่วยงานอาจต้องเพิ่มช่วงใหม่เมื่อมีหนังสือเวียนกำหนดวงเงินอื่น
 *
 * สิทธิ์: role 'purchase'
 */
class BondPolicyController extends Controller
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
        return $this->render('index', [
            'models' => BondPolicy::find()
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                ->all(),
        ]);
    }

    public function actionCreate()
    {
        $model = new BondPolicy([
            'proc_kind' => BondPolicy::KIND_ANY,
            'active' => 1,
            'required' => 0,
            'rate' => 0,
            'min_amount' => 0,
            // ต่อท้ายแถวสุดท้ายไว้ก่อน ผู้ใช้เลื่อนลำดับเองได้ถ้าต้องการให้จับคู่ก่อนแถวอื่น
            'sort_order' => (int) BondPolicy::find()->max('sort_order') + 10,
        ]);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มเกณฑ์ "' . $model->title . '" แล้ว');
            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกเกณฑ์ "' . $model->title . '" แล้ว');
            return $this->redirect(['index']);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $title = $model->title;
        $model->delete();

        Yii::$app->session->setFlash('success', 'ลบเกณฑ์ "' . $title . '" แล้ว');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        $model = BondPolicy::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบเกณฑ์ที่ต้องการ');
        }
        return $model;
    }
}

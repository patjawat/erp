<?php

namespace app\modules\purchase\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\modules\purchase\models\WhtRate;

/**
 * ตั้งค่าอัตราภาษีหัก ณ ที่จ่ายที่ใช้กับงานสัญญา
 *
 * ตั้งใจให้เป็นทะเบียนแก้ไขอย่างเดียว ไม่มีเพิ่ม/ลบ เพราะชุดค่าถูกกำหนดโดย
 * ประเภทสัญญา × ประเภทผู้รับเงิน ซึ่งเป็นชุดปิด การเพิ่มแถวใหม่ที่ไม่ตรงกับ
 * ประเภทสัญญาในระบบจะไม่มีอะไรเรียกใช้
 *
 * สิทธิ์: role 'purchase'
 */
class WhtRateController extends Controller
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
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'models' => WhtRate::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = WhtRate::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบอัตราภาษีที่ต้องการ');
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกอัตราภาษี "' . $model->title . '" แล้ว');
            return $this->redirect(['index']);
        }

        return $this->render('update', ['model' => $model]);
    }
}

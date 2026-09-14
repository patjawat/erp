<?php

namespace app\modules\km\controllers;

use app\modules\km\models\KmCategory;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * จัดการหมวดหมู่กิจกรรม KM (ตารางง่าย + ฟอร์มในแถว)
 */
class CategoryController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['save' => ['POST'], 'delete' => ['POST']],
            ],
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'categories' => KmCategory::find()->orderBy(['sort' => SORT_ASC, 'name' => SORT_ASC])->all(),
        ]);
    }

    public function actionSave()
    {
        $id = (int) Yii::$app->request->post('id');
        $model = $id ? KmCategory::findOne($id) : new KmCategory();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบหมวดที่ต้องการ');
        }

        $model->name = trim((string) Yii::$app->request->post('name'));
        $model->icon = trim((string) Yii::$app->request->post('icon')) ?: null;
        $model->color = trim((string) Yii::$app->request->post('color')) ?: null;
        $model->sort = (int) Yii::$app->request->post('sort');
        $model->is_active = Yii::$app->request->post('is_active') ? 1 : 0;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', $id ? 'แก้ไขหมวดแล้ว' : 'เพิ่มหมวดแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['index']);
    }

    public function actionDelete($id)
    {
        $model = KmCategory::findOne((int) $id);
        if ($model) {
            // กิจกรรมที่อ้างหมวดนี้จะถูกตั้ง category_id = NULL อัตโนมัติ (FK SET NULL)
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบหมวดแล้ว');
        }
        return $this->redirect(['index']);
    }
}

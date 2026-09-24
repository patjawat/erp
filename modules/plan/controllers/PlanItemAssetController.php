<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use app\models\Categorise;
use app\modules\plan\components\PlanItemAssetMap;

/**
 * ตั้งค่า "แผนงาน ↔ ประเภทพัสดุ" สำหรับงานจัดซื้อผูกแผน — ผู้มีสิทธิ์ทำแผน (role plan / admin)
 */
class PlanItemAssetController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->user->can('plan') && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('เฉพาะผู้มีสิทธิ์ทำแผนหรือผู้ดูแลระบบเท่านั้น');
        }
        return true;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['save' => ['POST'], 'suggest' => ['POST']],
            ],
        ]);
    }

    /** เลือกหมวดทีละหมวด (กันหน้าหนักจาก Select2 หลายร้อยตัว) */
    public function actionIndex($cat = null)
    {
        $categories = Categorise::find()->where(['name' => 'plan_category'])->orderBy(['code' => SORT_ASC])->all();
        if ($cat === null) {
            $cat = 'OPS_03';
        }
        $items = Categorise::find()
            ->where(['name' => 'plan_item', 'category_id' => $cat])
            ->orderBy(['code' => SORT_ASC])
            ->all();

        // จำนวนแผนงานที่กำหนดแล้วต่อหมวด (แสดงบนตัวเลือกหมวด)
        $mapped = PlanItemAssetMap::all();
        $itemCat = \yii\helpers\ArrayHelper::map(
            Categorise::find()->select(['code', 'category_id'])->where(['name' => 'plan_item'])->asArray()->all(),
            'code', 'category_id'
        );
        $countByCat = [];
        foreach (array_keys($mapped) as $code) {
            $c = $itemCat[$code] ?? null;
            if ($c !== null) {
                $countByCat[$c] = ($countByCat[$c] ?? 0) + 1;
            }
        }

        return $this->render('index', [
            'categories' => $categories,
            'cat' => $cat,
            'items' => $items,
            'mapped' => $mapped,
            'countByCat' => $countByCat,
            'typeOptions' => PlanItemAssetMap::assetTypeOptions(),
        ]);
    }

    public function actionSave($cat)
    {
        $posted = (array) $this->request->post('types', []);
        $codes = (array) $this->request->post('codes', []);
        foreach ($codes as $code) {
            PlanItemAssetMap::save($code, (array) ($posted[$code] ?? []));
        }
        Yii::$app->session->setFlash('success', 'บันทึกการจับคู่ประเภทพัสดุแล้ว');
        return $this->redirect(['index', 'cat' => $cat]);
    }

    public function actionSuggest($cat = null)
    {
        $n = PlanItemAssetMap::applySuggestions();
        Yii::$app->session->setFlash('success', 'เติมค่าแนะนำ ' . $n . ' แผนงาน (เฉพาะแผนงานที่ยังไม่ได้กำหนด)');
        return $this->redirect(['index', 'cat' => $cat]);
    }
}

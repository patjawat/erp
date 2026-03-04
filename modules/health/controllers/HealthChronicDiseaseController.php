<?php

namespace app\modules\health\controllers;

use Yii;
use yii\bootstrap5\Html;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\modules\health\models\HealthChronicDisease;
use app\modules\health\models\HealthOption;

/**
 * CRUD สำหรับตั้งค่าโรคประจำตัว (categorise.name = chronic_disease)
 */
class HealthChronicDiseaseController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => ['delete' => ['POST'], 'seed' => ['POST']],
            ],
        ]);
    }

    public function actionIndex()
    {
        $models = HealthOption::find()
            ->where(['name' => HealthOption::CATEGORY_CHRONIC_DISEASE])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return $this->render('index', ['models' => $models]);
    }

    public function actionCreate()
    {
        $model = new HealthOption();
        $model->name   = HealthOption::CATEGORY_CHRONIC_DISEASE;
        $model->active = 1;

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->load($this->request->post());
            $model->name = HealthOption::CATEGORY_CHRONIC_DISEASE;
            if ($model->save()) {
                return ['forceReload' => '#pjax-container', 'status' => 'success', 'message' => 'เพิ่มรายการเรียบร้อยแล้ว'];
            }
            $errors = array_map(fn($e) => implode(', ', $e), $model->errors);
            return ['status' => 'error', 'message' => implode(' | ', $errors)];
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title'   => 'เพิ่มโรคประจำตัว',
                'size'    => 'modal-md',
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer'  => Html::button('<i class="fas fa-save me-1"></i> บันทึก', ['class' => 'btn btn-primary form-submit', 'data' => ['id' => 'health-option-form']])
                           . Html::button('ปิด', ['class' => 'btn btn-secondary', 'data-bs-dismiss' => 'modal']),
            ];
        }

        return $this->redirect(['index']);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->load($this->request->post());
            $model->name = HealthOption::CATEGORY_CHRONIC_DISEASE;
            if ($model->save()) {
                return ['forceReload' => '#pjax-container', 'status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว'];
            }
            $errors = array_map(fn($e) => implode(', ', $e), $model->errors);
            return ['status' => 'error', 'message' => implode(' | ', $errors)];
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title'   => 'แก้ไขโรคประจำตัว: ' . Html::encode($model->title),
                'size'    => 'modal-md',
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer'  => Html::button('<i class="fas fa-save me-1"></i> บันทึก', ['class' => 'btn btn-primary form-submit', 'data' => ['id' => 'health-option-form']])
                           . Html::button('ปิด', ['class' => 'btn btn-secondary', 'data-bs-dismiss' => 'modal']),
            ];
        }

        return $this->redirect(['index']);
    }

    public function actionDelete($id)
    {
        try {
            $this->findModel($id)->delete();
            Yii::$app->session->setFlash('success', 'ลบรายการเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('warning', 'ไม่สามารถลบข้อมูลได้');
        }
        return $this->redirect(['index']);
    }

    public function actionSeed()
    {
        $count = HealthOption::find()
            ->where(['name' => HealthOption::CATEGORY_CHRONIC_DISEASE])
            ->count();

        if ($count > 0) {
            Yii::$app->session->setFlash('warning', 'มีข้อมูลอยู่แล้ว ไม่สามารถนำเข้าซ้ำได้');
            return $this->redirect(['index']);
        }

        $inserted = 0;
        foreach (HealthChronicDisease::defaultList() as $code => $title) {
            $model = new HealthOption();
            $model->name   = HealthOption::CATEGORY_CHRONIC_DISEASE;
            $model->code   = $code;
            $model->title  = $title;
            $model->active = 1;
            if ($model->save()) {
                $inserted++;
            }
        }

        Yii::$app->session->setFlash('success', "นำเข้าข้อมูลพื้นฐานเรียบร้อย {$inserted} รายการ");
        return $this->redirect(['index']);
    }

    protected function findModel($id): HealthOption
    {
        $model = HealthOption::findOne([
            'id'   => $id,
            'name' => HealthOption::CATEGORY_CHRONIC_DISEASE,
        ]);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

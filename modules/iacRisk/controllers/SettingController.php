<?php

namespace app\modules\iacRisk\controllers;

use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\iacRisk\models\FiscalYear;
use app\modules\iacRisk\models\Hospital;
use app\modules\iacRisk\services\AccessService;
use app\modules\iacRisk\services\FiscalYearService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class SettingController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['initialize' => ['POST'], 'open' => ['POST'], 'close' => ['POST']]],
        ]);
    }

    public function beforeAction($action): bool
    {
        if (!(new AccessService())->canManageSettings()) throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ตั้งค่า IAC&Risk');
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $hospitals = Hospital::find()->orderBy(['is_current' => SORT_DESC, 'name' => SORT_ASC])->all();
        $years = FiscalYear::find()->with(['hospital', 'periods'])->orderBy(['fiscal_year' => SORT_DESC, 'hospital_id' => SORT_ASC])->all();
        return $this->render('index', ['hospitals' => $hospitals, 'years' => $years]);
    }

    public function actionInitialize()
    {
        if (Hospital::find()->exists()) {
            Yii::$app->session->setFlash('info', 'มีข้อมูลโรงพยาบาลในระบบแล้ว');
            return $this->redirect(['index']);
        }
        $info = SiteHelper::getInfo();
        $hospital = new Hospital([
            'code' => trim((string) ($info['hoscode'] ?? $info['hospital_code'] ?? $info['hospcode'] ?? 'LOCAL')) ?: 'LOCAL',
            'name' => trim((string) ($info['company_name'] ?? 'โรงพยาบาล')) ?: 'โรงพยาบาล',
            'province' => trim((string) ($info['province'] ?? '')) ?: null,
            'active' => 1, 'is_current' => 1,
        ]);
        if (!$hospital->save()) {
            Yii::$app->session->setFlash('error', implode(' ', $hospital->getFirstErrors()));
        } else {
            Yii::$app->session->setFlash('success', 'เชื่อมข้อมูลโรงพยาบาลปัจจุบันแล้ว');
        }
        return $this->redirect(['index']);
    }

    public function actionCreateYear()
    {
        $model = new FiscalYear(['status' => FiscalYear::STATUS_DRAFT]);
        $model->fiscal_year = (int) AppHelper::YearBudget();
        $model->hospital_id = (int) Hospital::find()->where(['is_current' => 1, 'active' => 1])->select('id')->scalar();
        $model->applyDefaultDates();
        if ($model->load(Yii::$app->request->post())) {
            $model->applyDefaultDates();
            try {
                (new FiscalYearService())->createWithPeriods($model);
                Yii::$app->session->setFlash('success', 'สร้างปีงบประมาณและรอบรายงานแล้ว');
                return $this->redirect(['index']);
            } catch (\Throwable $e) { $model->addError('fiscal_year', $e->getMessage()); }
        }
        return $this->render('create-year', ['model' => $model, 'hospitalOptions' => Hospital::find()->where(['active' => 1])->select(['name', 'id'])->indexBy('id')->column()]);
    }

    public function actionOpen(int $id)
    {
        $model = $this->findYear($id);
        try { (new FiscalYearService())->open($model); Yii::$app->session->setFlash('success', 'เปิดใช้งานปีงบประมาณแล้ว'); }
        catch (\Throwable $e) { Yii::$app->session->setFlash('error', $e->getMessage()); }
        return $this->redirect(['index']);
    }

    public function actionClose(int $id)
    {
        $model = $this->findYear($id);
        try { (new FiscalYearService())->close($model); Yii::$app->session->setFlash('success', 'ปิดปีงบประมาณแล้ว'); }
        catch (\Throwable $e) { Yii::$app->session->setFlash('error', $e->getMessage()); }
        return $this->redirect(['index']);
    }

    private function findYear(int $id): FiscalYear
    {
        $model = FiscalYear::findOne($id);
        if (!$model) throw new NotFoundHttpException('ไม่พบปีงบประมาณ');
        return $model;
    }
}

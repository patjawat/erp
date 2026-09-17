<?php

namespace app\modules\laundry\controllers;

use app\modules\laundry\services\ProcurementGapService;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class ProcurementController extends Controller
{
    public function behaviors(): array
    {
        return ['access' => ['class' => AccessControl::class, 'rules' => [
            ['allow' => true, 'actions' => ['index'], 'roles' => ['laundry.view']],
            ['allow' => true, 'actions' => ['index'], 'roles' => ['laundry.approve']],
        ]]];
    }

    public function actionIndex()
    {
        $year = filter_var(Yii::$app->request->get('year', date('Y')), FILTER_VALIDATE_INT);
        if ($year === false || $year < 2000 || $year > 2200) {
            throw new BadRequestHttpException('ปีสอบยอดไม่ถูกต้อง');
        }
        $rows = (new ProcurementGapService())->report($year);
        return $this->render('index', compact('year', 'rows'));
    }
}

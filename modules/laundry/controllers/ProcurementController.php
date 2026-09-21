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
        // รับปีเป็น พ.ศ. แล้วแปลงเป็น ค.ศ. (count_year เก็บเป็น ค.ศ.)
        $year = filter_var(Yii::$app->request->get('year', (int) date('Y') + 543), FILTER_VALIDATE_INT);
        $gy = ($year ?: 0) - 543;
        if ($year === false || $gy < 2000 || $gy > 2200) {
            throw new BadRequestHttpException('ปีสอบยอดไม่ถูกต้อง');
        }
        $rows = (new ProcurementGapService())->report($gy);
        return $this->render('index', compact('year', 'rows'));
    }
}

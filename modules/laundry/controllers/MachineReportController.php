<?php

namespace app\modules\laundry\controllers;

use app\modules\laundry\services\MachineReport;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class MachineReportController extends Controller
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
        // รับปีเป็น พ.ศ. (มาตรฐาน ERP) แล้วแปลงเป็น ค.ศ. ภายใน
        $year = filter_var(Yii::$app->request->get('year', (int) date('Y') + 543), FILTER_VALIDATE_INT);
        $gy = ($year ?: 0) - 543;
        if ($year === false || $gy < 2000 || $gy > 2200) {
            throw new BadRequestHttpException('ปีรายงานไม่ถูกต้อง');
        }
        $rows = (new MachineReport())->yearly($gy);
        return $this->render('index', compact('year', 'rows'));
    }
}

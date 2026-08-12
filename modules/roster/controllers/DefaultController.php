<?php

namespace app\modules\roster\controllers;

use app\modules\roster\helpers\RosterAccess;
use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        if (!RosterAccess::canEnter()) {
            return $this->redirect(['no-access']);
        }
        return $this->redirect(['/roster/period/index']);
    }

    /**
     * อธิบายว่าทำไมเข้าไม่ได้ แทนที่จะโยน 403 เปล่าๆ
     *
     * สิทธิ์ตารางเวรมาจากการเป็นหัวหน้าหน่วยงานในผังองค์กร ไม่ใช่จาก RBAC role
     * ผู้ใช้ที่เจอหน้านี้จึงไม่มีทางเดาถูกว่าต้องไปแก้ที่ไหน ถ้าไม่บอกให้ชัด
     */
    public function actionNoAccess()
    {
        $employee = RosterAccess::currentEmployee();
        return $this->render('no-access', [
            'employee' => $employee,
            'hasProfile' => $employee !== null,
        ]);
    }
}

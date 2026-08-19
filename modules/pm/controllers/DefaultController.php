<?php

namespace app\modules\pm\controllers;

use Yii;
use app\modules\pm\models\Projects;
use app\modules\settings\models\OrgUnit;
use app\modules\plan\components\PlanHelper;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * Default controller for the `pm` module — หน้าภาพรวม
 */
class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $year = (int) Yii::$app->request->get('thai_year', PlanHelper::currentPlanYear());

        $base = Projects::find()->where(['deleted_at' => null]);

        $byStatus = [];
        foreach (array_keys(Projects::statusList()) as $st) {
            $byStatus[$st] = (int) (clone $base)->andWhere(['status' => $st, 'thai_year' => $year])->count();
        }

        $total = (int) (clone $base)->andWhere(['thai_year' => $year])->count();
        $budgetSum = (float) (clone $base)->andWhere(['thai_year' => $year])->sum('budget_total');

        // งบประมาณ + จำนวนโครงการ แยกตามสถานะ
        $budgetByStatus = [];
        foreach (array_keys(Projects::statusList()) as $st) {
            $budgetByStatus[$st] = (float) (clone $base)->andWhere(['status' => $st, 'thai_year' => $year])->sum('budget_total');
        }

        // งบประมาณ + จำนวนโครงการ แยกตามหน่วยงาน
        // สรุปตามทะเบียนหน่วยงาน (org_unit) ไม่ใช่ผังบุคลากร มิฉะนั้นโครงการของทีมประสาน
        // ซึ่งไม่มีตัวตนในผัง จะไปกองรวมกันที่ "ไม่ระบุหน่วยงาน" ทั้งหมด
        $deptRows = (clone $base)
            ->select([
                'org_unit_id',
                'cnt' => 'COUNT(*)',
                'budget' => 'COALESCE(SUM(budget_total),0)',
            ])
            ->andWhere(['thai_year' => $year])
            ->groupBy('org_unit_id')
            ->orderBy(['budget' => SORT_DESC])
            ->asArray()
            ->all();

        // ไม่กรองด้วยปี เพราะ id ของทะเบียนไม่ซ้ำข้ามปีอยู่แล้ว
        // ถ้ากรอง โครงการที่อ้างหน่วยของปีอื่น (เช่นปีที่ยังไม่ได้ตั้งทะเบียน) จะกลายเป็น "ไม่ระบุหน่วยงาน"
        $unitNames = ArrayHelper::map(
            OrgUnit::find()->select(['id', 'name'])->asArray()->all(),
            'id',
            'name'
        );
        $byDept = [];
        foreach ($deptRows as $r) {
            $byDept[] = [
                'name' => $unitNames[$r['org_unit_id']] ?? 'ไม่ระบุหน่วยงาน',
                'count' => (int) $r['cnt'],
                'budget' => (float) $r['budget'],
            ];
        }

        $recent = (clone $base)->andWhere(['thai_year' => $year])->orderBy(['id' => SORT_DESC])->limit(8)->all();

        $years = Projects::find()
            ->select('thai_year')
            ->where(['deleted_at' => null])
            ->andWhere(['not', ['thai_year' => null]])
            ->distinct()
            ->orderBy(['thai_year' => SORT_DESC])
            ->column();

        return $this->render('index', [
            'year' => $year,
            'years' => $years,
            'byStatus' => $byStatus,
            'budgetByStatus' => $budgetByStatus,
            'byDept' => $byDept,
            'total' => $total,
            'budgetSum' => $budgetSum,
            'recent' => $recent,
        ]);
    }
}

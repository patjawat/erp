<?php

namespace app\modules\hr\controllers;

use Yii;
use app\modules\hr\models\Organization;
use app\modules\hr\models\EmployeesSearch;

class WorkTypeController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new EmployeesSearch([
            'branch' => 'MAIN'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $notStatusParam = $this->request->get('not-status');

        if (isset($searchModel->user_register) && $searchModel->user_register == 0) {
            $dataProvider->query->andWhere(['user_id' => 0]);
        }
        if (isset($searchModel->user_register) && $searchModel->user_register == 1) {
            $dataProvider->query->andWhere(['!=','user_id',0]);
        }

        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],
            ['like', 'fname', $searchModel->q],
            ['like', 'lname', $searchModel->q],
        ]);

        $dataProvider->query->andWhere(['NOT', ['id' => 1]]);

        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามีกลุ่มย่อย
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.name = :name;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':name', $org1->name)
                ->queryAll();
            $arrDepartment = [];
            foreach ($querys as $tree) {
                $arrDepartment[] = $tree['id'];
            }

            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            } else {
                $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }
        // จบการค้นหา

        if (!$searchModel->status && $searchModel->all_status == 0) {
            $dataProvider->query->andWhere(['status' => 1]);
        }

        if (!$searchModel->status && $searchModel->all_status == 0) {
            if ($notStatusParam) {
                $dataProvider->query->andWhere(['is', 'status', new \yii\db\Expression('null')]);
            } else {
                // $dataProvider->query->andWhere(['NOT', ['status' => [5, 7, 8]]]);
            }
        }

        $dataProvider->query->andWhere(new \yii\db\Expression("CONCAT(fname,' ', lname) LIKE :term", [':term' => '%' . $searchModel->fullname . '%']));

        // ค้นหาตามอายุ
        if ($searchModel->range1 && !$searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) = ' . $searchModel->range1));
        }
        // ค้นหาระหว่างช่วงอายุ
        if ($searchModel->range1 && $searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) BETWEEN ' . $searchModel->range1 . ' AND ' . $searchModel->range2));
        }

        $dataProvider->query->orderBy(['id' => SORT_DESC]);
        $dataProvider->pagination->pageSize = 16;

  

        $sql = 'SELECT count(id) as total  FROM `employees` WHERE `status` IS NULL';
        $notStatus = Yii::$app->db->createCommand($sql)->queryScalar();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'notStatus' => $notStatus

        ]);
    }

}

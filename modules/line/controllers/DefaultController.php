<?php

namespace app\modules\line\controllers;


use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\modules\hr\models\EmployeesSearch;

/**
 * Default controller for the `line` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */

     public function actionMenu()
     {
        return $this->render('menu');
     }
  
     public function actionLogout()
     {
         \Yii::$app->user->logout();
 
         return true;
     }

     
     public function actionIndex()
     {
         $searchModel = new EmployeesSearch();
         $dataProvider = $searchModel->search($this->request->queryParams);
         $dataProvider->query->andWhere(['NOT', ['id' => 1]]);
         if (!$searchModel->status) {
             $dataProvider->query->andWhere(['status' => 1]);
         }
 
         
         //แบ่งชายหญิงจามช่วงอายุ
         $dataProviderGender = $searchModel->search($this->request->queryParams);
         // $dataProviderGender->query->select(["CONCAT(5 * FLOOR(((YEAR(NOW()) - YEAR(birthday))/5)), ' - ', 5 * FLOOR(((YEAR(NOW()) - YEAR(birthday))/5)) + 4) AS `_age_generation`,
         //         SUM(IF(gender = 'ชาย',1,0)* -1) AS _male, SUM(IF(gender = 'หญิง',1,0)) AS _female, SUM(IF(gender = 'ชาย',1,0)* -1) * 100 /
         //         (select count(id) FROM employees WHERE status not in(5,8,7)) as _male_percen, SUM(IF(gender = 'หญิง',1,0)) * 100 / (select count(id)
         //         FROM employees WHERE status not in(5,8,7)) as _female_percen, (select count(id)
         //         FROM employees WHERE status not in(5,8,7)) as cnt", ]);
 
         $dataProviderGender->query->select(["CONCAT(5 * FLOOR(((YEAR(NOW()) - YEAR(birthday))/5)), ' - ', 5 * FLOOR(((YEAR(NOW()) - YEAR(birthday))/5)) + 4) AS `_age_generation`,
                 SUM(IF(gender = 'ชาย',1,0)* -1) AS _male, SUM(IF(gender = 'หญิง',1,0)) AS _female, SUM(IF(gender = 'ชาย',1,0)* -1) * 100 /
                 (select count(id) FROM employees WHERE status = 1 ) as _male_percen, SUM(IF(gender = 'หญิง',1,0)) * 100 / (select count(id)
                 FROM employees WHERE status = 1) as _female_percen, (select count(id)
                 FROM employees WHERE status= 1) as cnt"]);
         $dataProviderGender->query->andWhere(['not', ['birthday' => null, 'id' => 1]]);
         $dataProviderGender->query->andWhere(new \yii\db\Expression('FLOOR(((YEAR(NOW()) - YEAR(birthday)))) < 60'));
         $dataProviderGender->query->groupBy([new \yii\db\Expression('1')]);
         $dataProviderGender->query->orderBy([new \yii\db\Expression('5 * FLOOR(((YEAR(NOW()) - YEAR(birthday))/5)) DESC')]);
         if (!$searchModel->status) {
             $dataProvider->query->andWhere(['status' => 1]);
         }
         //แบ่งชายหญิงจามช่วงอายุ จบ
 
         //นับจำนวนชาย
         $dataProviderGenderM = $searchModel->search($this->request->queryParams);
         if (!$searchModel->status) {
             $dataProviderGenderM->query->andWhere(['status' => 1]);
 
         }
         $dataProviderGenderM->query->select(['COUNT(employees.id) AS cnt']);
         $dataProviderGenderM->query->andWhere(['NOT', ['employees.id' => 1]]);
         $dataProviderGenderM->query->andWhere(['gender' => 'ชาย']);
         $dataProviderGenderM->query->orderBy([
             'code' => SORT_DESC,
         ]);
        
         //นับจำนวนหญิง
         $dataProviderGenderW = $searchModel->search($this->request->queryParams);
         $dataProviderGenderW->query->select(['COUNT(employees.id) AS cnt']);
         $dataProviderGenderW->query->andWhere(['NOT', ['employees.id' => 1]]);
         $dataProviderGenderW->query->andWhere(['gender' => 'หญิง']);
         $dataProviderGenderW->query->orderBy([
             'code' => SORT_DESC,
         ]);
         if (!$searchModel->status) {
             $dataProviderGenderW->query->andWhere(['status' => 1]);
 
         }
 
         //ประเภทการจ้าง
         $dataProviderPositionType = $searchModel->search($this->request->queryParams);
         $dataProviderPositionType->query->leftJoin('categorise pt', 'pt.code=employees.position_type');
         $dataProviderPositionType->query->select(['COUNT(employees.id) AS cnt,IFNULL(pt.title, "ไม่ระบุ") as title']);
         $dataProviderPositionType->query->groupBy(['position_type']);
         $dataProviderPositionType->query->andWhere(['NOT', ['employees.id' => 1]]);
         $dataProviderPositionType->query->orderBy([
             'code' => SORT_ASC,
         ]);
         if (!$searchModel->status) {
             $dataProviderPositionType->query->andWhere(['status' => 1]);
 
         }
         //ประเภทการจ้าง จบ
 
         //ระดับตำแหน่งทางราชการ
         $dataProviderPositionLevel = $searchModel->search($this->request->queryParams);
         $dataProviderPositionLevel->query->select(['COUNT(id) AS cnt,employees.*']);
         $dataProviderPositionLevel->query->groupBy(['position_level']);
         $dataProviderPositionLevel->query->andWhere(['NOT', ['id' => 1]]);
         $dataProviderPositionLevel->query->orderBy([
             'COUNT(id)' => SORT_DESC,
         ]);
         if (!$searchModel->status) {
             $dataProviderPositionLevel->query->andWhere(['status' => 1]);
         }
 
         //ตำแหน่ง
         $dataProviderPositionName = $searchModel->search($this->request->queryParams);
         //  $dataProviderPositionName->query->join('positionName c');
         $dataProviderPositionName->query->leftJoin('categorise c', 'c.code=employees.position_name');
         $dataProviderPositionName->query->select(['c.title as title,COUNT(employees.id) AS cnt']);
         $dataProviderPositionName->query->groupBy(['c.code']);
         $dataProviderPositionName->query->andWhere(['NOT', ['employees.id' => 1]]);
         $dataProviderPositionName->query->andWhere(['c.name' => 'position_name']);
         $dataProviderPositionName->query->orderBy([
             'COUNT(employees.id)' => SORT_DESC,
         ]);
         if (!$searchModel->status) {
             $dataProviderPositionName->query->andWhere(['status' => 1]);
         }
 
 //ระดับตำแหน่งทางราชการ จบ
 
         //workgroup
         $dataProviderWorkGroup = $searchModel->search($this->request->queryParams);
         $dataProviderWorkGroup->query->select(['w.title as _groupname,c.code as _dep_code,c.title as dename,count(employees.id) as cnt,
         (SELECT count(e1.id) FROM employees e1 WHERE e1.department = employees.department AND e1.position_type = 1) as _position1,
         (SELECT count(e1.id) FROM employees e1 WHERE e1.department = employees.department AND e1.position_type = 2) as _position2,
         (SELECT count(e1.id) FROM employees e1 WHERE e1.department = employees.department AND e1.position_type = 3) as _position3,
         (SELECT count(e1.id) FROM employees e1 WHERE e1.department = employees.department AND e1.position_type = 4) as _position4,
         (SELECT count(e1.id) FROM employees e1 WHERE e1.department = employees.department AND e1.position_type = 5) as _position5,
         (SELECT count(e1.id) FROM employees e1 WHERE e1.department = employees.department AND e1.position_type = 6) as _position6,
         (SELECT count(e1.id) FROM employees e1 WHERE e1.department = employees.department AND e1.position_type = 7) as _position7,employees.*']);
         $dataProviderWorkGroup->query->leftJoin('categorise c', 'c.code=employees.department');
         $dataProviderWorkGroup->query->leftJoin('categorise w', 'w.code=c.category_id');
         $dataProviderWorkGroup->query->where(['w.name' => 'workgroup']);
         $dataProviderPositionLevel->query->andWhere(['NOT', ['employees.id' => 1]]);
         $dataProviderWorkGroup->query->groupBy(['w.code']);
         // $dataProviderPositionLevel->query->orderBy([
         //     'COUNT(employees.id)' => SORT_DESC,
         // ]);
         if (!$searchModel->status) {
             $dataProviderWorkGroup->query->andWhere(['status' => 1]);
         }
 //ระดับตำแหน่งทางราชการ จบ
 
         //Generation
         $dataProviderGenB = $searchModel->search($this->request->queryParams);
         $dataProviderGenB->query->select(['COUNT(id)']);
         $dataProviderGenB->query->andWhere(['between', 'YEAR(birthday)', '1946', '1964']);
         $dataProviderGenB->query->andWhere(['not', ['birthday' => null, 'id' => 1]]);
         if (!$searchModel->status) {
             $dataProviderGenB->query->andWhere(['status' => 1]);
         }
 
         $dataProviderGenX = $searchModel->search($this->request->queryParams);
         $dataProviderGenX->query->select(['COUNT(id)']);
         $dataProviderGenX->query->andWhere(['between', 'YEAR(birthday)', '1965', '1981']);
         $dataProviderGenX->query->andWhere(['not', ['birthday' => null, 'id' => 1]]);
         if (!$searchModel->status) {
             $dataProviderGenX->query->andWhere(['status' => 1]);
         }
 
         $dataProviderGenY = $searchModel->search($this->request->queryParams);
         $dataProviderGenY->query->select(['COUNT(id)']);
         $dataProviderGenY->query->andWhere(['between', 'YEAR(birthday)', '1982', '2000']);
         $dataProviderGenY->query->andWhere(['not', ['birthday' => null, 'id' => 1]]);
         if (!$searchModel->status) {
             $dataProviderGenY->query->andWhere(['status' => 1]);
         }
 
         $dataProviderGenZ = $searchModel->search($this->request->queryParams);
         $dataProviderGenZ->query->select(['COUNT(id)']);
         $dataProviderGenZ->query->andWhere(['between', 'YEAR(birthday)', '2001', '2024']);
         $dataProviderGenZ->query->andWhere(['not', ['birthday' => null, 'id' => 1]]);
         if (!$searchModel->status) {
             $dataProviderGenZ->query->andWhere(['status' => 1]);
         }
 
         $dataProviderGenA = $searchModel->search($this->request->queryParams);
         $dataProviderGenA->query->select(['COUNT(id)']);
         $dataProviderGenA->query->andWhere(['between', 'YEAR(birthday)', '2014', '2023']);
         $dataProviderGenA->query->andWhere(['not', ['birthday' => null, 'id' => 1]]);
         if (!$searchModel->status) {
             $dataProviderGenA->query->andWhere(['status' => 1]);
         }
 
         //Generation จบ
 
         if ($this->request->isAjax) {
             Yii::$app->response->format = Response::FORMAT_JSON;
             return [
                 'positionType' => [
                     'categories' => '',
                     'data' => [1, 2, 3],
                 ],
             ];
         } else {
 
             return $this->render('index', [
                 'searchModel' => $searchModel,
                 'dataProvider' => $dataProvider,
                 'dataProviderGender' => $dataProviderGender,
                 'dataProviderGenderM' => $dataProviderGenderM,
                 'dataProviderGenderW' => $dataProviderGenderW,
                 'dataProviderPositionType' => $dataProviderPositionType,
                 'dataProviderPositionLevel' => $dataProviderPositionLevel,
                 'dataProviderWorkGroup' => $dataProviderWorkGroup,
                 'dataProviderPositionName' => $dataProviderPositionName,
                 'dataProviderGenB' => $dataProviderGenB,
                 'dataProviderGenX' => $dataProviderGenX,
                 'dataProviderGenY' => $dataProviderGenY,
                 'dataProviderGenZ' => $dataProviderGenZ,
                 'dataProviderGenA' => $dataProviderGenA,
             ]);
         }
     }
}

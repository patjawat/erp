<?php

namespace app\modules\hr\controllers;

use app\modules\hr\models\EmployeesSearch;
use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\EmployeeType;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\hr\models\TeamGroup;
use app\models\Categorise;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Query;
use yii\imagine\Image;
use Imagine\Image\Box;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;


/**
 * Default controller for the `hr` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */

    
    public function actionIndex()
    {
        $searchModel = new EmployeesSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['NOT', ['id' => 1]]);
        $dataProvider->query->andWhere(['branch' => 'MAIN']);
        if (!$searchModel->status) {
            $dataProvider->query->andWhere(['status' => 1]);
        }

        
        //แบ่งชายหญิงจามช่วงอายุ
        $dataProviderGender = $searchModel->search($this->request->queryParams);
   
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
            $dataProviderGender->query->andWhere(['status' => 1]);
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

        //workgroup: จำนวนคนแยกตามกลุ่มงานและประเภทการจ้าง (position_type 1-7)
        $dataProviderWorkGroup = $searchModel->search($this->request->queryParams);
        $dataProviderWorkGroup->query->select([
            'w.title as _groupname',
            'w.code as _wcode',
            'count(employees.id) as cnt',
            '(SELECT COUNT(e1.id) FROM employees e1 INNER JOIN categorise c1 ON c1.code = e1.department INNER JOIN categorise w1 ON w1.code = c1.category_id AND w1.name = \'workgroup\' WHERE w1.code = w.code AND e1.position_type = 1' . (!$searchModel->status ? ' AND e1.status = 1' : '') . ') as _position1',
            '(SELECT COUNT(e1.id) FROM employees e1 INNER JOIN categorise c1 ON c1.code = e1.department INNER JOIN categorise w1 ON w1.code = c1.category_id AND w1.name = \'workgroup\' WHERE w1.code = w.code AND e1.position_type = 2' . (!$searchModel->status ? ' AND e1.status = 1' : '') . ') as _position2',
            '(SELECT COUNT(e1.id) FROM employees e1 INNER JOIN categorise c1 ON c1.code = e1.department INNER JOIN categorise w1 ON w1.code = c1.category_id AND w1.name = \'workgroup\' WHERE w1.code = w.code AND e1.position_type = 3' . (!$searchModel->status ? ' AND e1.status = 1' : '') . ') as _position3',
            '(SELECT COUNT(e1.id) FROM employees e1 INNER JOIN categorise c1 ON c1.code = e1.department INNER JOIN categorise w1 ON w1.code = c1.category_id AND w1.name = \'workgroup\' WHERE w1.code = w.code AND e1.position_type = 4' . (!$searchModel->status ? ' AND e1.status = 1' : '') . ') as _position4',
            '(SELECT COUNT(e1.id) FROM employees e1 INNER JOIN categorise c1 ON c1.code = e1.department INNER JOIN categorise w1 ON w1.code = c1.category_id AND w1.name = \'workgroup\' WHERE w1.code = w.code AND e1.position_type = 5' . (!$searchModel->status ? ' AND e1.status = 1' : '') . ') as _position5',
            '(SELECT COUNT(e1.id) FROM employees e1 INNER JOIN categorise c1 ON c1.code = e1.department INNER JOIN categorise w1 ON w1.code = c1.category_id AND w1.name = \'workgroup\' WHERE w1.code = w.code AND e1.position_type = 6' . (!$searchModel->status ? ' AND e1.status = 1' : '') . ') as _position6',
            '(SELECT COUNT(e1.id) FROM employees e1 INNER JOIN categorise c1 ON c1.code = e1.department INNER JOIN categorise w1 ON w1.code = c1.category_id AND w1.name = \'workgroup\' WHERE w1.code = w.code AND e1.position_type = 7' . (!$searchModel->status ? ' AND e1.status = 1' : '') . ') as _position7',
        ]);
        $dataProviderWorkGroup->query->leftJoin('categorise c', 'c.code=employees.department');
        $dataProviderWorkGroup->query->leftJoin('categorise w', 'w.code=c.category_id');
        $dataProviderWorkGroup->query->where(['w.name' => 'workgroup']);
        $dataProviderWorkGroup->query->andWhere(['NOT', ['employees.id' => 1]]);
        $dataProviderWorkGroup->query->groupBy(['w.code', 'w.title']);
        $dataProviderWorkGroup->query->asArray();
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

        // ชื่อประเภทการจ้าง (สำหรับกราฟกลุ่มงาน)
        $positionTypeTitles = Categorise::find()
            ->where(['name' => 'position_type'])
            ->orderBy(['code' => SORT_ASC])
            ->all();
        $positionTypeLabels = [];
        foreach ($positionTypeTitles as $pt) {
            $positionTypeLabels[] = $pt->title ?: 'ไม่ระบุ';
        }
        $totalCount = (int) $dataProvider->getTotalCount();

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
                'totalCount' => $totalCount,
                'positionTypeLabels' => $positionTypeLabels,
            ]);
        }
    }

    //ตั้งค่าบุคลากร
    public function actionSettings()
    {
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ตั้งค่า',
                'content' => $this->renderAjax('settings'),
            ];
        } else {
            return $this->render('settings');
        }
    }

    //ดึง dashbroad จาก looker studion
    public function actionLooker()
    {
        return $this->render('looker');
    }

    /**
     * Dashboard มุมมองผู้บริหาร
     * ใช้เฉพาะ branch = 'MAIN' และ status = '1' (ปฏิบัติราชการ)
     * รองรับการกรองจากคลิก Chart: gender, department, employee_type_id, employee_position_id, workgroup, gen
     */
    public function actionDashboard()
    {
        $req = $this->request->get();
        $filterGender = isset($req['gender']) && in_array($req['gender'], ['ชาย', 'หญิง'], true) ? $req['gender'] : null;
        $filterDepartment = isset($req['department']) ? $req['department'] : null;
        $filterPositionType = $this->normalizeDashboardEmployeeTypeFilter($req['employee_type_id'] ?? $req['position_type'] ?? null);
        $filterWorkgroup = isset($req['workgroup']) ? $req['workgroup'] : null;
        $filterGen = isset($req['gen']) ? $req['gen'] : null;
        $filterPositionName = $this->normalizeDashboardEmployeePositionFilter($req['employee_position_id'] ?? $req['position_name'] ?? null);
        $filterServiceBand = isset($req['service_band']) && $req['service_band'] !== '' ? $req['service_band'] : null;
        $genYearRanges = [
            'Gen B' => [1946, 1964],
            'Gen X' => [1965, 1981],
            'Gen Y' => [1982, 2000],
            'Gen Z' => [2001, 2024],
        ];

        $baseQuery = function () use ($filterGender, $filterDepartment, $filterPositionType, $filterWorkgroup, $filterGen, $filterPositionName, $filterServiceBand, $genYearRanges) {
            $q = Employees::find()
                ->andWhere(['branch' => 'MAIN'])
                ->andWhere(['status' => '1'])
                ->andWhere(['not', ['id' => 1]]);
            if ($filterGender !== null) {
                $q->andWhere(['gender' => $filterGender]);
            }
            if ($filterDepartment !== null && $filterDepartment !== '') {
                $q->andWhere(['department' => $filterDepartment]);
            }
            if ($filterPositionType !== null && $filterPositionType !== '') {
                $q->andWhere(['employee_type_id' => $filterPositionType]);
            }
            if ($filterPositionName !== null && $filterPositionName !== '') {
                $q->andWhere(['employee_position_id' => $filterPositionName]);
            }
            if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
                $deptCodes = Categorise::find()
                    ->select('code')
                    ->where(['category_id' => $filterWorkgroup, 'name' => 'department'])
                    ->column();
                if (!empty($deptCodes)) {
                    $q->andWhere(['in', 'department', $deptCodes]);
                }
            }
            if ($filterGen !== null && isset($genYearRanges[$filterGen])) {
                $years = $genYearRanges[$filterGen];
                $q->andWhere(['not', ['birthday' => null]])
                    ->andWhere(['between', 'YEAR(birthday)', (string) $years[0], (string) $years[1]]);
            }
            if ($filterServiceBand !== null && $filterServiceBand !== '') {
                $q->andWhere(self::serviceBandCondition($filterServiceBand));
            }
            return $q;
        };

        $totalCount = (int) (clone $baseQuery())->count();

        // เพศ
        $countMale = (int) (clone $baseQuery())->andWhere(['gender' => 'ชาย'])->count();
        $countFemale = (int) (clone $baseQuery())->andWhere(['gender' => 'หญิง'])->count();

        // ประเภทพนักงาน
        $positionTypes = EmployeeType::find()
            ->where(['active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
        $positionTypeLabels = [];
        $positionTypeCodes = [];
        foreach ($positionTypes as $pt) {
            $positionTypeLabels[] = $pt->title ?: 'ไม่ระบุ';
            $positionTypeCodes[] = (int) $pt->id;
        }
        $positionTypeCounts = [];
        foreach ($positionTypeCodes as $typeId) {
            $positionTypeCounts[] = (int) (clone $baseQuery())->andWhere(['employee_type_id' => $typeId])->count();
        }

        // ตำแหน่ง
        $positionNameQuery = (new Query())
            ->select(['p.id as id', 'p.title as title', 'COUNT(e.id) as cnt'])
            ->from(['e' => 'employees'])
            ->leftJoin(['p' => EmployeePosition::tableName()], 'p.id = e.employee_position_id')
            ->where(['e.branch' => 'MAIN', 'e.status' => '1'])
            ->andWhere(['not', ['e.id' => 1]])
            ->andWhere(['not', ['e.employee_position_id' => null]]);
        if ($filterGender !== null) $positionNameQuery->andWhere(['e.gender' => $filterGender]);
        if ($filterDepartment !== null && $filterDepartment !== '') $positionNameQuery->andWhere(['e.department' => $filterDepartment]);
        if ($filterPositionType !== null && $filterPositionType !== '') $positionNameQuery->andWhere(['e.employee_type_id' => $filterPositionType]);
        if ($filterPositionName !== null && $filterPositionName !== '') $positionNameQuery->andWhere(['e.employee_position_id' => $filterPositionName]);
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $wgDeptCodes = Categorise::find()->select('code')->where(['category_id' => $filterWorkgroup, 'name' => 'department'])->column();
            if (!empty($wgDeptCodes)) $positionNameQuery->andWhere(['in', 'e.department', $wgDeptCodes]);
        }
        if ($filterGen !== null && isset($genYearRanges[$filterGen])) {
            $yr = $genYearRanges[$filterGen];
            $positionNameQuery->andWhere(['not', ['e.birthday' => null]])->andWhere(['between', 'YEAR(e.birthday)', (string)$yr[0], (string)$yr[1]]);
        }
        if ($filterServiceBand !== null && $filterServiceBand !== '') {
            $positionNameQuery->andWhere(new \yii\db\Expression(self::serviceBandWhereSql('e', $filterServiceBand)));
        }
        $positionNameRows = $positionNameQuery->groupBy(['p.id', 'p.title'])->orderBy(['cnt' => SORT_DESC])->all();
        $positionNameMaxShow = 12; // แสดง Top N แล้วรวมที่เหลือเป็น "อื่นๆ" เพื่อให้กราฟอ่านง่าย
        if (count($positionNameRows) > $positionNameMaxShow) {
            $top = array_slice($positionNameRows, 0, $positionNameMaxShow);
            $rest = array_slice($positionNameRows, $positionNameMaxShow);
            $restSum = array_sum(array_column($rest, 'cnt'));
            $positionNameRows = $top;
            if ($restSum > 0) {
                $positionNameRows[] = ['id' => null, 'title' => 'อื่นๆ', 'cnt' => $restSum];
            }
        }
        $positionNameCategories = array_map(function ($t) { return $t !== null && $t !== '' ? $t : 'ไม่ระบุ'; }, array_column($positionNameRows, 'title'));
        $positionNameCodes = array_column($positionNameRows, 'id');
        $positionNameValues = array_map('intval', array_column($positionNameRows, 'cnt'));

        // กลุ่มงาน × ประเภทพนักงาน
        $workgroupRows = [];
        $wgWhere = "e.branch = 'MAIN' AND e.status = '1' AND e.id <> 1";
        $wgParams = [];
        if ($filterGender !== null) { $wgWhere .= " AND e.gender = :gender"; $wgParams[':gender'] = $filterGender; }
        if ($filterDepartment !== null && $filterDepartment !== '') { $wgWhere .= " AND e.department = :department"; $wgParams[':department'] = $filterDepartment; }
        if ($filterPositionType !== null && $filterPositionType !== '') { $wgWhere .= " AND e.employee_type_id = :employee_type_id"; $wgParams[':employee_type_id'] = $filterPositionType; }
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $wgDeptCodes = Categorise::find()->select('code')->where(['category_id' => $filterWorkgroup, 'name' => 'department'])->column();
            if (!empty($wgDeptCodes)) {
                $wgWhere .= " AND e.department IN (" . implode(',', array_map('intval', $wgDeptCodes)) . ")";
            }
        }
        if ($filterGen !== null && isset($genYearRanges[$filterGen])) {
            $yr = $genYearRanges[$filterGen];
            $wgWhere .= " AND e.birthday IS NOT NULL AND YEAR(e.birthday) BETWEEN :gen0 AND :gen1";
            $wgParams[':gen0'] = $yr[0]; $wgParams[':gen1'] = $yr[1];
        }
        if ($filterPositionName !== null && $filterPositionName !== '') { $wgWhere .= " AND e.employee_position_id = :employee_position_id"; $wgParams[':employee_position_id'] = $filterPositionName; }
        if ($filterServiceBand !== null && $filterServiceBand !== '') { $wgWhere .= " AND (" . self::serviceBandWhereSql('e', $filterServiceBand) . ")"; }
        $wgCounts = Yii::$app->db->createCommand(
            "SELECT w.code AS wcode, w.title AS wtitle, e.employee_type_id AS pt_id, COUNT(e.id) AS cnt
             FROM employees e
             INNER JOIN categorise c ON c.code = e.department
             INNER JOIN categorise w ON w.code = c.category_id AND w.name = 'workgroup'
             WHERE {$wgWhere}
             GROUP BY w.code, w.title, e.employee_type_id"
        )->bindValues($wgParams)->queryAll();
        $byWg = [];
        foreach ($wgCounts as $r) {
            $k = $r['wcode'];
            if (!isset($byWg[$k])) {
                $byWg[$k] = ['name' => $r['wtitle'] ?: 'ไม่ระบุ', 'code' => $r['wcode'], 'data' => array_fill(0, count($positionTypeCodes), 0)];
            }
            $idx = array_search((int) $r['pt_id'], $positionTypeCodes, true);
            if ($idx !== false) {
                $byWg[$k]['data'][$idx] = (int) $r['cnt'];
            }
        }
        $workgroupRows = array_values($byWg);

        // ประชากรตามช่วงอายุ (ชาย/หญิง) สำหรับกราฟ
        $ageWhere = "branch = 'MAIN' AND status = '1' AND id <> 1 AND birthday IS NOT NULL AND FLOOR((YEAR(NOW()) - YEAR(birthday))) < 60";
        $ageParams = [];
        if ($filterGender !== null) { $ageWhere .= " AND gender = :gender"; $ageParams[':gender'] = $filterGender; }
        if ($filterDepartment !== null && $filterDepartment !== '') { $ageWhere .= " AND department = :department"; $ageParams[':department'] = $filterDepartment; }
        if ($filterPositionType !== null && $filterPositionType !== '') { $ageWhere .= " AND employee_type_id = :employee_type_id"; $ageParams[':employee_type_id'] = $filterPositionType; }
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $ageWgDepts = Categorise::find()->select('code')->where(['category_id' => $filterWorkgroup, 'name' => 'department'])->column();
            if (!empty($ageWgDepts)) { $ageWhere .= " AND department IN (" . implode(',', array_map('intval', $ageWgDepts)) . ")"; }
        }
        if ($filterGen !== null && isset($genYearRanges[$filterGen])) {
            $yr = $genYearRanges[$filterGen];
            $ageWhere .= " AND YEAR(birthday) BETWEEN :gen0 AND :gen1";
            $ageParams[':gen0'] = $yr[0]; $ageParams[':gen1'] = $yr[1];
        }
        if ($filterPositionName !== null && $filterPositionName !== '') { $ageWhere .= " AND employee_position_id = :employee_position_id"; $ageParams[':employee_position_id'] = $filterPositionName; }
        if ($filterServiceBand !== null && $filterServiceBand !== '') { $ageWhere .= " AND (" . self::serviceBandWhereSql('', $filterServiceBand) . ")"; }
        $ageRows = Yii::$app->db->createCommand(
            "SELECT CONCAT(5 * FLOOR((YEAR(NOW()) - YEAR(birthday))/5), ' - ', 5 * FLOOR((YEAR(NOW()) - YEAR(birthday))/5) + 4) AS age_band,
                    SUM(IF(gender = 'ชาย', 1, 0) * -1) AS _male,
                    SUM(IF(gender = 'หญิง', 1, 0)) AS _female
             FROM employees
             WHERE {$ageWhere}
             GROUP BY 1
             ORDER BY 1 DESC"
        )->bindValues($ageParams)->queryAll();
        $ageCategories = array_column($ageRows, 'age_band');
        $ageMale = array_map(function ($r) { return (float) $r['_male']; }, $ageRows);
        $ageFemale = array_map(function ($r) { return (float) $r['_female']; }, $ageRows);

        // ช่วงวัย (Generation)
        $genCounts = [];
        foreach ($genYearRanges as $label => $years) {
            $genCounts[$label] = (int) (clone $baseQuery())
                ->andWhere(['not', ['birthday' => null]])
                ->andWhere(['between', 'YEAR(birthday)', (string) $years[0], (string) $years[1]])
                ->count();
        }

        // บรรจุใหม่ปีนี้ (status 1, join_date ในปีปัจจุบัน)
        $currentYear = (string) date('Y');
        $newHiresThisYear = (int) (clone $baseQuery())
            ->andWhere(['not', ['join_date' => null]])
            ->andWhere(['YEAR(join_date)' => $currentYear])
            ->count();

        // ลาออก/สิ้นสุดปีนี้ (มี end_date และปีของ end_date = ปีปัจจุบัน)
        $leftThisYear = (int) Employees::find()
            ->andWhere(['branch' => 'MAIN'])
            ->andWhere(['not', ['id' => 1]])
            ->andWhere(['not', ['end_date' => null]])
            ->andWhere(['YEAR(end_date)' => $currentYear])
            ->count();

        // แผนก (department) – จำนวนคนต่อแผนก; ชื่อแผนกจากตาราง tree (Organization)
        $departmentQuery = (new Query())
            ->select(['org.id as code', 'org.name as title', 'COUNT(e.id) as cnt'])
            ->from(['e' => 'employees'])
            ->leftJoin(['org' => Organization::tableName()], 'org.id = e.department')
            ->where(['e.branch' => 'MAIN', 'e.status' => '1'])
            ->andWhere(['not', ['e.id' => 1]]);
        if ($filterGender !== null) $departmentQuery->andWhere(['e.gender' => $filterGender]);
        if ($filterDepartment !== null && $filterDepartment !== '') $departmentQuery->andWhere(['e.department' => $filterDepartment]);
        if ($filterPositionType !== null && $filterPositionType !== '') $departmentQuery->andWhere(['e.employee_type_id' => $filterPositionType]);
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $dwg = Categorise::find()->select('code')->where(['category_id' => $filterWorkgroup, 'name' => 'department'])->column();
            if (!empty($dwg)) $departmentQuery->andWhere(['in', 'e.department', $dwg]);
        }
        if ($filterGen !== null && isset($genYearRanges[$filterGen])) {
            $yr = $genYearRanges[$filterGen];
            $departmentQuery->andWhere(['not', ['e.birthday' => null]])->andWhere(['between', 'YEAR(e.birthday)', (string)$yr[0], (string)$yr[1]]);
        }
        if ($filterPositionName !== null && $filterPositionName !== '') $departmentQuery->andWhere(['e.employee_position_id' => $filterPositionName]);
        if ($filterServiceBand !== null && $filterServiceBand !== '') {
            $departmentQuery->andWhere(new \yii\db\Expression(self::serviceBandWhereSql('e', $filterServiceBand)));
        }
        $departmentRows = $departmentQuery->groupBy(['org.id', 'org.name'])->orderBy(['cnt' => SORT_DESC])->all();
        $departmentLabels = array_map(function ($t) { return $t !== null && $t !== '' ? $t : 'ไม่ระบุ'; }, array_column($departmentRows, 'title'));
        $departmentCodes = array_map(function ($v) { return $v === null ? '' : (string) $v; }, array_column($departmentRows, 'code'));
        $departmentLabelMap = array_combine($departmentCodes, $departmentLabels) ?: [];
        $departmentValues = array_map('intval', array_column($departmentRows, 'cnt'));

        // อายุงาน (ปี) – ช่วงอายุงาน
        $sbWhere = "branch = 'MAIN' AND status = '1' AND id <> 1";
        $sbParams = [];
        if ($filterGender !== null) { $sbWhere .= " AND gender = :gender"; $sbParams[':gender'] = $filterGender; }
        if ($filterDepartment !== null && $filterDepartment !== '') { $sbWhere .= " AND department = :department"; $sbParams[':department'] = $filterDepartment; }
        if ($filterPositionType !== null && $filterPositionType !== '') { $sbWhere .= " AND employee_type_id = :employee_type_id"; $sbParams[':employee_type_id'] = $filterPositionType; }
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $sbWg = Categorise::find()->select('code')->where(['category_id' => $filterWorkgroup, 'name' => 'department'])->column();
            if (!empty($sbWg)) { $sbWhere .= " AND department IN (" . implode(',', array_map('intval', $sbWg)) . ")"; }
        }
        if ($filterGen !== null && isset($genYearRanges[$filterGen])) {
            $yr = $genYearRanges[$filterGen];
            $sbWhere .= " AND birthday IS NOT NULL AND YEAR(birthday) BETWEEN :gen0 AND :gen1";
            $sbParams[':gen0'] = $yr[0]; $sbParams[':gen1'] = $yr[1];
        }
        if ($filterPositionName !== null && $filterPositionName !== '') { $sbWhere .= " AND employee_position_id = :employee_position_id"; $sbParams[':employee_position_id'] = $filterPositionName; }
        if ($filterServiceBand !== null && $filterServiceBand !== '') { $sbWhere .= " AND (" . self::serviceBandWhereSql('', $filterServiceBand) . ")"; }
        $serviceBandRows = Yii::$app->db->createCommand(
            "SELECT
                CASE
                    WHEN join_date IS NULL THEN 'ไม่ระบุ'
                    WHEN TIMESTAMPDIFF(YEAR, join_date, CURDATE()) < 1 THEN 'น้อยกว่า 1 ปี'
                    WHEN TIMESTAMPDIFF(YEAR, join_date, CURDATE()) < 5 THEN '1 - 5 ปี'
                    WHEN TIMESTAMPDIFF(YEAR, join_date, CURDATE()) < 10 THEN '5 - 10 ปี'
                    WHEN TIMESTAMPDIFF(YEAR, join_date, CURDATE()) < 20 THEN '10 - 20 ปี'
                    ELSE '20 ปีขึ้นไป'
                END AS band,
                COUNT(id) AS cnt
             FROM employees
             WHERE {$sbWhere}
             GROUP BY 1"
        )->bindValues($sbParams)->queryAll();
        $serviceBandOrder = ['น้อยกว่า 1 ปี', '1 - 5 ปี', '5 - 10 ปี', '10 - 20 ปี', '20 ปีขึ้นไป', 'ไม่ระบุ'];
        $byBand = [];
        foreach ($serviceBandRows as $r) {
            $byBand[$r['band']] = (int) $r['cnt'];
        }
        $serviceBandLabels = [];
        $serviceBandValues = [];
        foreach ($serviceBandOrder as $band) {
            if (isset($byBand[$band])) {
                $serviceBandLabels[] = $band;
                $serviceBandValues[] = $byBand[$band];
            }
        }
        foreach (array_keys($byBand) as $band) {
            if (!in_array($band, $serviceBandOrder, true)) {
                $serviceBandLabels[] = $band;
                $serviceBandValues[] = $byBand[$band];
            }
        }

        // อายุงานเฉลี่ย (ปี) – เฉพาะคนที่มี join_date
        $avgWhere = "branch = 'MAIN' AND status = '1' AND id <> 1 AND join_date IS NOT NULL";
        $avgParams = [];
        if ($filterGender !== null) { $avgWhere .= " AND gender = :gender"; $avgParams[':gender'] = $filterGender; }
        if ($filterDepartment !== null && $filterDepartment !== '') { $avgWhere .= " AND department = :department"; $avgParams[':department'] = $filterDepartment; }
        if ($filterPositionType !== null && $filterPositionType !== '') { $avgWhere .= " AND employee_type_id = :employee_type_id"; $avgParams[':employee_type_id'] = $filterPositionType; }
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $avgWg = Categorise::find()->select('code')->where(['category_id' => $filterWorkgroup, 'name' => 'department'])->column();
            if (!empty($avgWg)) { $avgWhere .= " AND department IN (" . implode(',', array_map('intval', $avgWg)) . ")"; }
        }
        if ($filterGen !== null && isset($genYearRanges[$filterGen])) {
            $yr = $genYearRanges[$filterGen];
            $avgWhere .= " AND birthday IS NOT NULL AND YEAR(birthday) BETWEEN :gen0 AND :gen1";
            $avgParams[':gen0'] = $yr[0]; $avgParams[':gen1'] = $yr[1];
        }
        if ($filterPositionName !== null && $filterPositionName !== '') { $avgWhere .= " AND employee_position_id = :employee_position_id"; $avgParams[':employee_position_id'] = $filterPositionName; }
        if ($filterServiceBand !== null && $filterServiceBand !== '') { $avgWhere .= " AND (" . self::serviceBandWhereSql('', $filterServiceBand) . ")"; }
        $avgYearsService = Yii::$app->db->createCommand(
            "SELECT ROUND(AVG(TIMESTAMPDIFF(YEAR, join_date, CURDATE())), 1) AS avg_years FROM employees WHERE {$avgWhere}"
        )->bindValues($avgParams)->queryScalar();
        $avgYearsService = $avgYearsService !== null ? round((float) $avgYearsService, 1) : null;
        $organizationDiagramCount = (int) Organization::find()->where(['tb_name' => 'diagram'])->count('id');
        $teamGroupCount = (int) TeamGroup::find()->count('id');
        $dashboardTooltipPeople = self::buildDashboardTooltipPeople($baseQuery(), $genYearRanges, $serviceBandLabels, $ageCategories, $departmentLabelMap);

        return $this->render('dashboard', [
            'totalCount' => $totalCount,
            'countMale' => $countMale,
            'countFemale' => $countFemale,
            'organizationDiagramCount' => $organizationDiagramCount,
            'teamGroupCount' => $teamGroupCount,
            'dashboardTooltipPeople' => $dashboardTooltipPeople,
            'positionTypeLabels' => $positionTypeLabels,
            'positionTypeCounts' => $positionTypeCounts,
            'positionNameCategories' => $positionNameCategories,
            'positionNameValues' => $positionNameValues,
            'workgroupRows' => $workgroupRows,
            'ageCategories' => $ageCategories,
            'ageMale' => $ageMale,
            'ageFemale' => $ageFemale,
            'genCounts' => $genCounts,
            'numWorkgroups' => count($workgroupRows),
            'numPositionTypes' => count($positionTypeLabels),
            'newHiresThisYear' => $newHiresThisYear,
            'leftThisYear' => $leftThisYear,
            'departmentLabels' => $departmentLabels,
            'departmentValues' => $departmentValues,
            'serviceBandLabels' => $serviceBandLabels,
            'serviceBandValues' => $serviceBandValues,
            'avgYearsService' => $avgYearsService,
            'filterGender' => $filterGender,
            'filterDepartment' => $filterDepartment,
            'filterPositionType' => $filterPositionType,
            'filterWorkgroup' => $filterWorkgroup,
            'filterGen' => $filterGen,
            'positionTypeCodes' => $positionTypeCodes,
            'departmentCodes' => $departmentCodes ?? [],
            'dashboardUrl' => Url::to(['/hr/default/dashboard']),
            'filterPositionName' => $filterPositionName,
            'filterServiceBand' => $filterServiceBand,
            'positionNameCodes' => $positionNameCodes ?? [],
        ]);
    }

    private function normalizeDashboardEmployeeTypeFilter($value): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            $typeId = (int) $value;
            return EmployeeType::findOne($typeId) ? $typeId : null;
        }

        $needle = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
        foreach (EmployeeType::find()->all() as $type) {
            $title = trim((string) $type->title);
            $typeTitle = function_exists('mb_strtolower') ? mb_strtolower($title, 'UTF-8') : strtolower($title);
            if ($typeTitle !== '' && $typeTitle === $needle) {
                return (int) $type->id;
            }

            foreach ((array) $type->legacyCodes() as $legacyCode) {
                if (strcasecmp(trim((string) $legacyCode), $value) === 0) {
                    return (int) $type->id;
                }
            }
        }

        return null;
    }

    private function normalizeDashboardEmployeePositionFilter($value): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            $positionId = (int) $value;
            return EmployeePosition::findOne($positionId) ? $positionId : null;
        }

        $needle = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
        foreach (EmployeePosition::find()->all() as $position) {
            $title = trim((string) $position->title);
            $positionTitle = function_exists('mb_strtolower') ? mb_strtolower($title, 'UTF-8') : strtolower($title);
            if ($positionTitle !== '' && $positionTitle === $needle) {
                return (int) $position->id;
            }

            $legacyCode = trim((string) ($position->legacy_code ?? ''));
            if ($legacyCode !== '' && strcasecmp($legacyCode, $value) === 0) {
                return (int) $position->id;
            }
        }

        return null;
    }

    /**
     * เงื่อนไขสำหรับ filter ช่วงอายุงาน (ใช้กับ ActiveQuery)
     */
    private static function serviceBandCondition($label)
    {
        $expr = new \yii\db\Expression('TIMESTAMPDIFF(YEAR, join_date, CURDATE())');
        switch ($label) {
            case 'ไม่ระบุ':
                return ['join_date' => null];
            case 'น้อยกว่า 1 ปี':
                return ['and', ['not', ['join_date' => null]], ['<', $expr, 1]];
            case '1 - 5 ปี':
                return ['and', ['not', ['join_date' => null]], ['>=', $expr, 1], ['<', $expr, 5]];
            case '5 - 10 ปี':
                return ['and', ['not', ['join_date' => null]], ['>=', $expr, 5], ['<', $expr, 10]];
            case '10 - 20 ปี':
                return ['and', ['not', ['join_date' => null]], ['>=', $expr, 10], ['<', $expr, 20]];
            case '20 ปีขึ้นไป':
                return ['and', ['not', ['join_date' => null]], ['>=', $expr, 20]];
            default:
                return [];
        }
    }

    /**
     * เงื่อนไข SQL สำหรับ filter ช่วงอายุงาน (ใช้กับ raw SQL หรือ Query + Expression)
     * @param string $alias alias ตาราง employees เช่น 'e' หรือ '' สำหรับไม่มี alias
     */
    private static function buildDashboardTooltipPeople($query, array $genYearRanges, array $serviceBandLabels, array $ageCategories, array $departmentLabelMap = []): array
    {
        $maxPeoplePerBucket = 300;
        $fallbackAvatar = Url::to('@web/img/profiles/avatar-01.jpg');
        $result = [
            'gender' => [],
            'generation' => [],
            'positionType' => [],
            'workgroup' => [],
            'workgroupPositionType' => [],
            'age' => [],
            'ageGender' => [],
            'positionName' => [],
            'serviceBand' => [],
            'department' => [],
        ];

        $departmentToWorkgroups = [];
        foreach (Categorise::find()->select(['category_id', 'code'])->where(['name' => 'department'])->asArray()->all() as $row) {
            $departmentCode = (string)($row['code'] ?? '');
            $workgroupCode = (string)($row['category_id'] ?? '');
            if ($departmentCode !== '' && $workgroupCode !== '') {
                $departmentToWorkgroups[$departmentCode][] = $workgroupCode;
            }
        }

        $peopleRows = $query
            ->select([
                'id',
                'prefix',
                'fname',
                'lname',
                'avatar',
                'ref',
                'gender',
                'birthday',
                'age_bucket' => new \yii\db\Expression("
                    CASE
                        WHEN birthday IS NULL THEN NULL
                        WHEN YEAR(CURDATE()) - YEAR(birthday) < 20 THEN '20+'
                        WHEN YEAR(CURDATE()) - YEAR(birthday) >= 60 THEN '60+'
                        ELSE CONCAT(
                            5 * FLOOR((YEAR(CURDATE()) - YEAR(birthday)) / 5),
                            ' - ',
                            5 * FLOOR((YEAR(CURDATE()) - YEAR(birthday)) / 5) + 4
                        )
                    END
                "),
                'join_date',
                'department',
                'employee_type_id',
                'employee_position_id',
            ])
            ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])
            ->asArray()
            ->all();

        $pushPerson = static function (string $group, $key, array $person) use (&$result, $maxPeoplePerBucket): void {
            $key = (string)$key;
            if ($key === '') {
                return;
            }
            if (!isset($result[$group][$key])) {
                $result[$group][$key] = [];
            }
            if (count($result[$group][$key]) < $maxPeoplePerBucket) {
                $result[$group][$key][] = $person;
            }
        };

        $avatarRefs = array_values(array_unique(array_filter(array_map(static function ($row) {
            return trim((string)($row['ref'] ?? ''));
        }, $peopleRows), static function ($ref) {
            return $ref !== '';
        })));

        $avatarUploads = [];
        if (!empty($avatarRefs)) {
            $avatarUploads = Uploads::find()
                ->select(['id', 'ref'])
                ->where(['ref' => $avatarRefs, 'name' => 'avatar'])
                ->indexBy('ref')
                ->asArray()
                ->all();
        }

        $positionIds = array_values(array_unique(array_filter(array_map(static function ($row) {
            return trim((string)($row['employee_position_id'] ?? ''));
        }, $peopleRows), static function ($id) {
            return $id !== '';
        })));

        $positionTitles = [];
        if (!empty($positionIds)) {
            $positionTitles = EmployeePosition::find()
                ->select(['id', 'title'])
                ->where(['id' => $positionIds])
                ->indexBy('id')
                ->asArray()
                ->all();
        }

        $resolveAvatarUrl = static function (array $row) use ($fallbackAvatar, $avatarUploads) {
            $ref = trim((string)($row['ref'] ?? ''));
            $uploadId = $ref !== '' ? ($avatarUploads[$ref]['id'] ?? null) : null;
            if ($uploadId) {
                return Url::to(['/filemanager/uploads/get-image', 'id' => $uploadId]);
            }

            $avatar = trim((string)($row['avatar'] ?? ''));
            if ($avatar === '') {
                return $fallbackAvatar;
            }

            if (preg_match('/^(?:https?:)?\/\//i', $avatar) || strpos($avatar, '/') === 0 || strpos($avatar, '@') === 0) {
                return Url::to($avatar);
            }

            if (strpos($avatar, '/') !== false) {
                return Url::to('@web/' . ltrim($avatar, '/'));
            }

            return Url::to('@web/avatar/' . rawurlencode($avatar));
        };

        foreach ($peopleRows as $row) {
            $name = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
                $row['prefix'] ?? '',
                $row['fname'] ?? '',
                $row['lname'] ?? '',
            ], static function ($value) {
                return trim((string)$value) !== '';
            }))));
            $avatarUrl = $resolveAvatarUrl($row);
            $departmentCode = (string)($row['department'] ?? '');
            $departmentLabel = $departmentLabelMap[$departmentCode] ?? null;
            $person = [
                'id' => (int)($row['id'] ?? 0),
                'name' => $name !== '' ? $name : 'ไม่ระบุชื่อ',
                'avatar' => $avatarUrl,
                'profileUrl' => Url::to(['/hr/employees/view', 'id' => (int)($row['id'] ?? 0)]),
                'position' => $positionTitles[(string)($row['employee_position_id'] ?? '')]['title'] ?? 'ไม่ระบุตำแหน่ง',
                'department' => $departmentLabel !== null && $departmentLabel !== '' ? $departmentLabel : ($departmentCode !== '' ? 'แผนก/ฝ่าย #' . $departmentCode : 'ไม่ระบุแผนก/ฝ่าย'),
            ];

            $gender = preg_replace('/\s+/u', '', trim((string)($row['gender'] ?? '')));
            $department = (string)($row['department'] ?? '');
            $employeeTypeId = (string)($row['employee_type_id'] ?? '');
            $employeePositionId = (string)($row['employee_position_id'] ?? '');
            $generation = self::dashboardGenerationBucket($row['birthday'] ?? null, $genYearRanges);
            $age = trim((string)($row['age_bucket'] ?? ''));
            if ($age === '') {
                $age = self::dashboardAgeBucket($row['birthday'] ?? null, $ageCategories);
            }
            $serviceBand = self::dashboardServiceBandBucket($row['join_date'] ?? null, $serviceBandLabels);

            $pushPerson('gender', $gender, $person);
            $pushPerson('generation', $generation, $person);
            $pushPerson('positionType', $employeeTypeId, $person);
            $pushPerson('age', $age, $person);
            $pushPerson('ageGender', $age !== null && $gender !== '' ? $age . '|' . $gender : '', $person);
            $pushPerson('positionName', $employeePositionId, $person);
            $pushPerson('serviceBand', $serviceBand, $person);
            $pushPerson('department', $department, $person);

            foreach (($departmentToWorkgroups[$department] ?? []) as $workgroupCode) {
                $pushPerson('workgroup', $workgroupCode, $person);
                $pushPerson('workgroupPositionType', $employeeTypeId !== '' ? $workgroupCode . '|' . $employeeTypeId : '', $person);
            }
        }

        return $result;
    }

    private static function dashboardGenerationBucket($birthday, array $genYearRanges): ?string
    {
        $year = self::dashboardDateYear($birthday);
        if ($year === null) {
            return null;
        }
        foreach ($genYearRanges as $label => $range) {
            if (isset($range[0], $range[1]) && $year >= (int)$range[0] && $year <= (int)$range[1]) {
                return (string)$label;
            }
        }
        return null;
    }

    private static function dashboardAgeBucket($birthday, array $ageCategories): ?string
    {
        $age = self::dashboardYearDiff($birthday);
        if ($age === null) {
            return null;
        }
        return self::dashboardNumericBucketLabel($age, $ageCategories, $age >= 60 ? '60+' : null);
    }

    private static function dashboardServiceBandBucket($joinDate, array $serviceBandLabels): ?string
    {
        $years = self::dashboardYearDiff($joinDate);
        if ($years === null) {
            return null;
        }
        return self::dashboardNumericBucketLabel($years, $serviceBandLabels, $years >= 20 ? '20+' : null, false);
    }

    private static function dashboardNumericBucketLabel(int $value, array $labels, ?string $fallback = null, bool $upperInclusive = true): ?string
    {
        foreach ($labels as $index => $label) {
            $label = (string)$label;
            if ($label === '') {
                continue;
            }
            preg_match_all('/\d+/', $label, $matches);
            $numbers = array_map('intval', $matches[0] ?? []);
            if (strpos($label, '+') !== false && isset($numbers[0]) && $value >= $numbers[0]) {
                return $label;
            }
            if (strpos($label, '<') !== false && isset($numbers[0]) && $value < $numbers[0]) {
                return $label;
            }
            if (isset($numbers[0], $numbers[1]) && $value >= $numbers[0] && ($upperInclusive ? $value <= $numbers[1] : $value < $numbers[1])) {
                return $label;
            }
            if (!$upperInclusive && $index === 0 && isset($numbers[1]) && $value < $numbers[1]) {
                return $label;
            }
            if (count($numbers) === 1 && $value === $numbers[0]) {
                return $label;
            }
        }

        return $fallback;
    }

    private static function dashboardDateYear($date): ?int
    {
        $date = trim((string)$date);
        if ($date === '' || $date === '0000-00-00') {
            return null;
        }
        return (int)substr($date, 0, 4) ?: null;
    }

    private static function dashboardYearDiff($date): ?int
    {
        $date = trim((string)$date);
        if ($date === '' || $date === '0000-00-00') {
            return null;
        }
        try {
            return (int)(new \DateTimeImmutable($date))->diff(new \DateTimeImmutable('today'))->y;
        } catch (\Exception $e) {
            return null;
        }
    }

    private static function serviceBandWhereSql($alias, $label)
    {
        $p = $alias !== '' ? $alias . '.' : '';
        switch ($label) {
            case 'ไม่ระบุ':
                return $p . 'join_date IS NULL';
            case 'น้อยกว่า 1 ปี':
                return $p . 'join_date IS NOT NULL AND TIMESTAMPDIFF(YEAR, ' . $p . 'join_date, CURDATE()) < 1';
            case '1 - 5 ปี':
                return $p . 'join_date IS NOT NULL AND TIMESTAMPDIFF(YEAR, ' . $p . 'join_date, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, ' . $p . 'join_date, CURDATE()) < 5';
            case '5 - 10 ปี':
                return $p . 'join_date IS NOT NULL AND TIMESTAMPDIFF(YEAR, ' . $p . 'join_date, CURDATE()) >= 5 AND TIMESTAMPDIFF(YEAR, ' . $p . 'join_date, CURDATE()) < 10';
            case '10 - 20 ปี':
                return $p . 'join_date IS NOT NULL AND TIMESTAMPDIFF(YEAR, ' . $p . 'join_date, CURDATE()) >= 10 AND TIMESTAMPDIFF(YEAR, ' . $p . 'join_date, CURDATE()) < 20';
            case '20 ปีขึ้นไป':
                return $p . 'join_date IS NOT NULL AND TIMESTAMPDIFF(YEAR, ' . $p . 'join_date, CURDATE()) >= 20';
            default:
                return '1=1';
        }
    }
}

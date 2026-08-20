<?php

namespace app\modules\hr\controllers;

use app\modules\hr\models\EmployeesSearch;
use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\EmployeeType;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\hr\models\TeamGroup;
use app\models\Categorise;
use app\components\AppHelper;
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
     * รหัสสถานะ (categorise name='emp_status') ที่ถือว่าพ้นจากหน่วยงานแล้ว
     * ใช้กับการ์ด "ลาออก/สิ้นสุดปีนี้" บน dashboard
     * ไม่รวมสถานะที่ยังเป็นบุคลากรอยู่ เช่น ไปราชการ ฝึกอบรม ลาศึกษา (11,12,14-18,20,21,32)
     * และไม่รวม 19 = ยกเลิกคำสั่งบรรจุ
     */
    private const EXIT_STATUS_CODES = [
        '2',  // ลาออก
        '3', '4', '5', '6', // เกษียณอายุราชการ (ทุกแบบ)
        '7', '8', // ถึงแก่กรรม
        '9', '10', // ปลดออก
        '13', // ย้าย
        '22', '23', '34', // ลาออกรับบำนาญ/บำเหน็จ
        '24', // เลิกจ้าง
        '25', // ไล่ออก
        '26', // หมดสัญญาจ้าง
        '27', '28', '29', '30', // ให้ออก
        '31', // ให้โอน
        '33', '35', '36', '37', // บำนาญ/บำเหน็จถึงแก่กรรม
    ];

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
     * จำนวนปีงบประมาณย้อนหลังที่ให้เลือกดูบน dashboard
     */
    private const DASHBOARD_BUDGET_YEARS_BACK = 4;

    /**
     * ตาราง derived ของบุคลากร ที่คำนวณ "วันบรรจุ" และ "วันพ้นจากหน่วยงาน" ไว้แล้ว
     * ใช้แทนตาราง employees ทุกจุดบน dashboard เพื่อให้ดูข้อมูล ณ วันใดก็ได้
     *
     * hire_date = COALESCE(join_date, วันที่เริ่มของประวัติตำแหน่งแรก)
     * exit_date = วันพ้นจากหน่วยงาน เฉพาะคนที่สถานะปัจจุบันแปลว่าออกแล้ว
     *             = COALESCE(end_date, วันที่เริ่มของประวัติตำแหน่งล่าสุดที่สถานะตรงกัน)
     *             ถ้าออกแล้วแต่ไม่มีวันที่ในระบบ ใช้ '1900-01-01' เพื่อให้ไม่ถูกนับเป็น
     *             ผู้ปฏิบัติงาน ณ วันใด ๆ (ข้อมูลเก่าที่ไม่เคยบันทึกประวัติไว้)
     */
    private static function workforceSnapshotSql(): string
    {
        $exitIn = "'" . implode("','", self::EXIT_STATUS_CODES) . "'";

        return "(SELECT emp.*,
                        COALESCE(emp.join_date, hp.first_start) AS hire_date,
                        CASE WHEN emp.status IN ({$exitIn})
                             THEN COALESCE(emp.end_date, lp.ds, '1900-01-01')
                        END AS exit_date
                 FROM employees emp
                 LEFT JOIN (
                    SELECT emp_id, MIN(STR_TO_DATE(JSON_UNQUOTE(data_json->'$.date_start'), '%Y-%m-%d')) AS first_start
                    FROM employee_detail WHERE name = 'position' GROUP BY emp_id
                 ) hp ON hp.emp_id = emp.id
                 LEFT JOIN (
                    SELECT emp_id, st, ds FROM (
                        SELECT emp_id,
                               JSON_UNQUOTE(data_json->'$.status') AS st,
                               STR_TO_DATE(JSON_UNQUOTE(data_json->'$.date_start'), '%Y-%m-%d') AS ds,
                               ROW_NUMBER() OVER (
                                   PARTITION BY emp_id
                                   ORDER BY STR_TO_DATE(JSON_UNQUOTE(data_json->'$.date_start'), '%Y-%m-%d') DESC, id DESC
                               ) AS rn
                        FROM employee_detail WHERE name = 'position'
                    ) ranked WHERE rn = 1
                 ) lp ON lp.emp_id = emp.id AND lp.st = emp.status)";
    }

    /**
     * แผนที่ "แผนก/หน่วยงาน -> กลุ่มงาน" จากผังองค์กร (tree, tb_name = 'diagram')
     *
     * employees.department ชี้ไปที่ node ของผังองค์กรได้ทุกระดับ
     * (lvl 0 = กลุ่ม, lvl 1 = กลุ่มงาน, lvl 2 = งาน) จึงไต่ nested set ขึ้นไปหา
     * node ระดับกลุ่มงาน ถ้าไม่มีก็ใช้ node ตัวเองเป็นกลุ่มงาน
     *
     * เดิม dashboard ใช้ตาราง categorise (name = 'workgroup'/'department')
     * ซึ่งไม่มีข้อมูลแล้ว ทำให้ชาร์ตกลุ่มงานว่างและตัวกรองกลุ่มงานใช้ไม่ได้
     *
     * @return array [deptId => ['id' => wgId, 'name' => wgName, 'sort' => lft]]
     */
    private static function dashboardWorkgroupMap(): array
    {
        $rows = Yii::$app->db->createCommand(
            "SELECT d.id AS dept_id,
                    COALESCE(wg.id, d.id) AS wg_id,
                    COALESCE(wg.name, d.name) AS wg_name,
                    COALESCE(wg.root, d.root) AS wg_root,
                    COALESCE(wg.lft, d.lft) AS wg_lft
             FROM tree d
             LEFT JOIN tree wg
                    ON wg.tb_name = 'diagram' AND wg.root = d.root AND wg.lvl = 1
                   AND wg.lft <= d.lft AND wg.rgt >= d.rgt
             WHERE d.tb_name = 'diagram'"
        )->queryAll();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['dept_id']] = [
                'id' => (string) $row['wg_id'],
                'name' => (string) ($row['wg_name'] !== '' ? $row['wg_name'] : 'ไม่ระบุ'),
                'sort' => (int) $row['wg_root'] * 1000 + (int) $row['wg_lft'],
            ];
        }

        return $map;
    }

    /**
     * เงื่อนไข "เป็นบุคลากรที่ปฏิบัติงานอยู่ ณ วันที่ :as_of"
     * ใช้แทนเงื่อนไข status = '1' เดิม เพื่อให้ย้อนดูปีงบประมาณก่อนหน้าได้
     */
    private static function inServiceWhereSql(string $alias = 'e'): string
    {
        $p = $alias !== '' ? $alias . '.' : '';

        return "({$p}hire_date IS NULL OR {$p}hire_date <= :as_of)"
            . " AND ({$p}exit_date IS NULL OR {$p}exit_date > :as_of)";
    }

    /**
     * Dashboard มุมมองผู้บริหาร
     * ดูข้อมูลได้ตามปีงบประมาณที่เลือก (budget_year) โดยยึด "ณ วันสิ้นปีงบประมาณ"
     * สำหรับปีที่ผ่านมาแล้ว และ "ณ วันนี้" สำหรับปีงบประมาณปัจจุบัน
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

        // ---------------------------------------------------------------
        // ปีงบประมาณที่กำลังดู และ "วันที่อ้างอิง" (as of) ของทุกการ์ด/ชาร์ต
        //  - ปีงบประมาณปัจจุบัน  => ดูสถานะ ณ วันนี้
        //  - ปีงบประมาณที่ผ่านมา => ดูสถานะ ณ วันสิ้นปีงบประมาณนั้น (30 ก.ย.)
        // ตัวเลข "บรรจุใหม่/ลาออก" ใช้ทั้งช่วงปีงบประมาณ ไม่ใช่ ณ วันเดียว
        // ---------------------------------------------------------------
        $currentBudgetYear = (int) AppHelper::YearBudget();
        $budgetYearOptions = range($currentBudgetYear, $currentBudgetYear - self::DASHBOARD_BUDGET_YEARS_BACK);
        $budgetYear = isset($req['budget_year']) && ctype_digit((string) $req['budget_year'])
            ? (int) $req['budget_year']
            : $currentBudgetYear;
        if (!in_array($budgetYear, $budgetYearOptions, true)) {
            $budgetYear = $currentBudgetYear;
        }
        $budgetRange = AppHelper::BudgetYearRange($budgetYear);
        $isCurrentBudgetYear = $budgetYear === $currentBudgetYear;
        $asOfDate = $isCurrentBudgetYear ? date('Y-m-d') : $budgetRange['end'];
        if ($asOfDate > $budgetRange['end']) {
            $asOfDate = $budgetRange['end'];
        }
        $snapshotSql = self::workforceSnapshotSql();
        $snapshotFrom = new \yii\db\Expression($snapshotSql);

        // แผนที่แผนก -> กลุ่มงาน จากผังองค์กร ใช้ทั้งชาร์ตกลุ่มงานและตัวกรองกลุ่มงาน
        $departmentWorkgroupMap = self::dashboardWorkgroupMap();
        $workgroupDepartmentIds = function ($workgroupCode) use ($departmentWorkgroupMap) {
            $ids = [];
            foreach ($departmentWorkgroupMap as $deptId => $workgroup) {
                if ((string) $workgroup['id'] === (string) $workgroupCode) {
                    $ids[] = (int) $deptId;
                }
            }
            return $ids;
        };
        // เงื่อนไข SQL "อยู่ในกลุ่มงานที่เลือก" (ถ้าไม่พบกลุ่มงาน = ไม่มีใครเข้าเงื่อนไข)
        $workgroupDeptWhereSql = function ($alias) use ($filterWorkgroup, $workgroupDepartmentIds) {
            $p = $alias !== '' ? $alias . '.' : '';
            $ids = $workgroupDepartmentIds($filterWorkgroup);
            return empty($ids)
                ? '1 = 0'
                : $p . 'department IN (' . implode(',', $ids) . ')';
        };

        $baseQuery = function () use ($filterGender, $filterDepartment, $filterPositionType, $filterWorkgroup, $filterGen, $filterPositionName, $filterServiceBand, $genYearRanges, $snapshotFrom, $asOfDate, $workgroupDepartmentIds) {
            $q = Employees::find()
                ->from(['e' => $snapshotFrom])
                ->andWhere(['branch' => 'MAIN'])
                ->andWhere(new \yii\db\Expression(self::inServiceWhereSql('e')))
                ->addParams([':as_of' => $asOfDate])
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
                $q->andWhere(['in', 'department', $workgroupDepartmentIds($filterWorkgroup)]);
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
            ->from(['e' => $snapshotFrom])
            ->leftJoin(['p' => EmployeePosition::tableName()], 'p.id = e.employee_position_id')
            ->where(['e.branch' => 'MAIN'])
            ->andWhere(new \yii\db\Expression(self::inServiceWhereSql('e')))
            ->addParams([':as_of' => $asOfDate])
            ->andWhere(['not', ['e.id' => 1]])
            ->andWhere(['not', ['e.employee_position_id' => null]]);
        if ($filterGender !== null) $positionNameQuery->andWhere(['e.gender' => $filterGender]);
        if ($filterDepartment !== null && $filterDepartment !== '') $positionNameQuery->andWhere(['e.department' => $filterDepartment]);
        if ($filterPositionType !== null && $filterPositionType !== '') $positionNameQuery->andWhere(['e.employee_type_id' => $filterPositionType]);
        if ($filterPositionName !== null && $filterPositionName !== '') $positionNameQuery->andWhere(['e.employee_position_id' => $filterPositionName]);
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $positionNameQuery->andWhere(new \yii\db\Expression($workgroupDeptWhereSql('e')));
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
        $wgWhere = "e.branch = 'MAIN' AND e.id <> 1 AND " . self::inServiceWhereSql('e');
        $wgParams = [':as_of' => $asOfDate];
        if ($filterGender !== null) { $wgWhere .= " AND e.gender = :gender"; $wgParams[':gender'] = $filterGender; }
        if ($filterDepartment !== null && $filterDepartment !== '') { $wgWhere .= " AND e.department = :department"; $wgParams[':department'] = $filterDepartment; }
        if ($filterPositionType !== null && $filterPositionType !== '') { $wgWhere .= " AND e.employee_type_id = :employee_type_id"; $wgParams[':employee_type_id'] = $filterPositionType; }
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $wgWhere .= ' AND ' . $workgroupDeptWhereSql('e');
        }
        if ($filterGen !== null && isset($genYearRanges[$filterGen])) {
            $yr = $genYearRanges[$filterGen];
            $wgWhere .= " AND e.birthday IS NOT NULL AND YEAR(e.birthday) BETWEEN :gen0 AND :gen1";
            $wgParams[':gen0'] = $yr[0]; $wgParams[':gen1'] = $yr[1];
        }
        if ($filterPositionName !== null && $filterPositionName !== '') { $wgWhere .= " AND e.employee_position_id = :employee_position_id"; $wgParams[':employee_position_id'] = $filterPositionName; }
        if ($filterServiceBand !== null && $filterServiceBand !== '') { $wgWhere .= " AND (" . self::serviceBandWhereSql('e', $filterServiceBand) . ")"; }
        // นับรายแผนกก่อน แล้วยุบขึ้นเป็นกลุ่มงานตามผังองค์กรใน PHP
        $wgCounts = Yii::$app->db->createCommand(
            "SELECT e.department AS dept_id, e.employee_type_id AS pt_id, COUNT(e.id) AS cnt
             FROM {$snapshotSql} e
             WHERE {$wgWhere}
             GROUP BY e.department, e.employee_type_id"
        )->bindValues($wgParams)->queryAll();
        $byWg = [];
        foreach ($wgCounts as $r) {
            $workgroup = $departmentWorkgroupMap[(string) $r['dept_id']] ?? null;
            if ($workgroup === null) {
                continue; // แผนกที่ไม่มีใน ผังองค์กร (ข้อมูลค้าง) ไม่นำมาแสดงในชาร์ตกลุ่มงาน
            }
            $k = $workgroup['id'];
            if (!isset($byWg[$k])) {
                $byWg[$k] = [
                    'name' => $workgroup['name'],
                    'code' => $workgroup['id'],
                    'sort' => $workgroup['sort'],
                    'data' => array_fill(0, count($positionTypeCodes), 0),
                ];
            }
            $idx = array_search((int) $r['pt_id'], $positionTypeCodes, true);
            if ($idx !== false) {
                $byWg[$k]['data'][$idx] += (int) $r['cnt'];
            }
        }
        uasort($byWg, function ($a, $b) {
            return array_sum($b['data']) <=> array_sum($a['data']) ?: ($a['sort'] <=> $b['sort']);
        });
        $workgroupRows = array_values($byWg);

        // ประชากรตามช่วงอายุ (ชาย/หญิง) สำหรับกราฟ
        $ageWhere = "branch = 'MAIN' AND id <> 1 AND " . self::inServiceWhereSql('')
            . " AND birthday IS NOT NULL AND FLOOR((YEAR(:as_of) - YEAR(birthday))) < 60";
        $ageParams = [':as_of' => $asOfDate];
        if ($filterGender !== null) { $ageWhere .= " AND gender = :gender"; $ageParams[':gender'] = $filterGender; }
        if ($filterDepartment !== null && $filterDepartment !== '') { $ageWhere .= " AND department = :department"; $ageParams[':department'] = $filterDepartment; }
        if ($filterPositionType !== null && $filterPositionType !== '') { $ageWhere .= " AND employee_type_id = :employee_type_id"; $ageParams[':employee_type_id'] = $filterPositionType; }
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $ageWhere .= ' AND ' . $workgroupDeptWhereSql('');
        }
        if ($filterGen !== null && isset($genYearRanges[$filterGen])) {
            $yr = $genYearRanges[$filterGen];
            $ageWhere .= " AND YEAR(birthday) BETWEEN :gen0 AND :gen1";
            $ageParams[':gen0'] = $yr[0]; $ageParams[':gen1'] = $yr[1];
        }
        if ($filterPositionName !== null && $filterPositionName !== '') { $ageWhere .= " AND employee_position_id = :employee_position_id"; $ageParams[':employee_position_id'] = $filterPositionName; }
        if ($filterServiceBand !== null && $filterServiceBand !== '') { $ageWhere .= " AND (" . self::serviceBandWhereSql('', $filterServiceBand) . ")"; }
        $ageRows = Yii::$app->db->createCommand(
            "SELECT CONCAT(5 * FLOOR((YEAR(:as_of) - YEAR(birthday))/5), ' - ', 5 * FLOOR((YEAR(:as_of) - YEAR(birthday))/5) + 4) AS age_band,
                    SUM(IF(gender = 'ชาย', 1, 0) * -1) AS _male,
                    SUM(IF(gender = 'หญิง', 1, 0)) AS _female
             FROM {$snapshotSql} snap
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

        // ---------------------------------------------------------------
        // การเคลื่อนไหวกำลังคนตลอดปีงบประมาณที่เลือก (บรรจุใหม่ / พ้นจากหน่วยงาน)
        //
        // employees.join_date และ employees.end_date เชื่อถือไม่ได้:
        //  - คนบรรจุใหม่ถูกบันทึกผ่านประวัติตำแหน่ง (employee_detail name='position')
        //    ทำให้ join_date ว่าง จึงนับ "บรรจุใหม่" ไม่ได้เลย
        //  - end_date ไม่เคยถูกบันทึกใช้งานจริง (ว่างทั้งตาราง) การนับจาก end_date
        //    จึงได้ 0 เสมอ ทั้งที่มีคนลาออก/เกษียณ/ย้ายจริงในระบบ
        // จึงใช้ hire_date / exit_date ที่คำนวณไว้ใน self::workforceSnapshotSql()
        // ---------------------------------------------------------------

        // ตัวกรองจากชาร์ต (ไม่รวม branch / id / เงื่อนไขวันที่) เพื่อใช้ร่วมกับ query การเคลื่อนไหว
        $movementFilterSql = function ($alias) use ($filterGender, $filterDepartment, $filterPositionType, $filterWorkgroup, $filterGen, $filterPositionName, $filterServiceBand, $genYearRanges, $workgroupDeptWhereSql) {
            $p = $alias !== '' ? $alias . '.' : '';
            $sql = '';
            $params = [];
            if ($filterGender !== null) { $sql .= " AND {$p}gender = :mv_gender"; $params[':mv_gender'] = $filterGender; }
            if ($filterDepartment !== null && $filterDepartment !== '') { $sql .= " AND {$p}department = :mv_department"; $params[':mv_department'] = $filterDepartment; }
            if ($filterPositionType !== null && $filterPositionType !== '') { $sql .= " AND {$p}employee_type_id = :mv_employee_type_id"; $params[':mv_employee_type_id'] = $filterPositionType; }
            if ($filterPositionName !== null && $filterPositionName !== '') { $sql .= " AND {$p}employee_position_id = :mv_employee_position_id"; $params[':mv_employee_position_id'] = $filterPositionName; }
            if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
                $sql .= ' AND ' . $workgroupDeptWhereSql($alias);
            }
            if ($filterGen !== null && isset($genYearRanges[$filterGen])) {
                $yr = $genYearRanges[$filterGen];
                $sql .= " AND {$p}birthday IS NOT NULL AND YEAR({$p}birthday) BETWEEN :mv_gen0 AND :mv_gen1";
                $params[':mv_gen0'] = $yr[0]; $params[':mv_gen1'] = $yr[1];
            }
            if ($filterServiceBand !== null && $filterServiceBand !== '') {
                $sql .= ' AND (' . self::serviceBandWhereSql($alias, $filterServiceBand) . ')';
            }
            return [$sql, $params];
        };
        [$mvWhere, $mvParams] = $movementFilterSql('e');
        $mvParams[':mv_start'] = $budgetRange['start'];
        $mvParams[':mv_end'] = $budgetRange['end'];
        // ผูก :as_of เฉพาะเมื่อมีตัวกรองช่วงอายุงาน (SQL จะมี token นี้ก็ต่อเมื่อกรอง)
        if (strpos($mvWhere, ':as_of') !== false) {
            $mvParams[':as_of'] = $asOfDate;
        }

        // บรรจุใหม่ – นับทุกคนที่วันบรรจุอยู่ในปีงบประมาณที่เลือก
        // (รวมคนที่บรรจุปีนี้แล้วออกไปแล้ว เพราะเป็นการบรรจุที่เกิดขึ้นจริงในปีนั้น
        //  แต่ตัดคำสั่งที่ถูกยกเลิกออก)
        $newHiresThisYear = (int) Yii::$app->db->createCommand(
            "SELECT COUNT(*)
             FROM {$snapshotSql} e
             WHERE e.branch = 'MAIN' AND e.id <> 1
               AND (e.status IS NULL OR e.status NOT IN ('CANCEL', '19'))
               AND e.hire_date BETWEEN :mv_start AND :mv_end
               {$mvWhere}"
        )->bindValues($mvParams)->queryScalar();

        // ลาออก/สิ้นสุด – แยกตามเหตุผลเพื่อให้ตรวจสอบตัวเลขได้
        $leftThisYearRows = Yii::$app->db->createCommand(
            "SELECT e.status AS status, COALESCE(cat.title, 'ไม่ระบุ') AS reason, COUNT(*) AS cnt
             FROM {$snapshotSql} e
             LEFT JOIN categorise cat ON cat.name = 'emp_status' AND cat.code = e.status
             WHERE e.branch = 'MAIN' AND e.id <> 1
               AND e.exit_date BETWEEN :mv_start AND :mv_end
               {$mvWhere}
             GROUP BY e.status, cat.title
             ORDER BY cnt DESC"
        )->bindValues($mvParams)->queryAll();
        $leftThisYear = 0;
        $leftThisYearBreakdown = [];
        foreach ($leftThisYearRows as $row) {
            $leftThisYear += (int) $row['cnt'];
            $leftThisYearBreakdown[] = ['reason' => $row['reason'], 'count' => (int) $row['cnt']];
        }

        // แผนก (department) – จำนวนคนต่อแผนก; ชื่อแผนกจากตาราง tree (Organization)
        $departmentQuery = (new Query())
            ->select(['org.id as code', 'org.name as title', 'COUNT(e.id) as cnt'])
            ->from(['e' => $snapshotFrom])
            ->leftJoin(['org' => Organization::tableName()], 'org.id = e.department')
            ->where(['e.branch' => 'MAIN'])
            ->andWhere(new \yii\db\Expression(self::inServiceWhereSql('e')))
            ->addParams([':as_of' => $asOfDate])
            ->andWhere(['not', ['e.id' => 1]]);
        if ($filterGender !== null) $departmentQuery->andWhere(['e.gender' => $filterGender]);
        if ($filterDepartment !== null && $filterDepartment !== '') $departmentQuery->andWhere(['e.department' => $filterDepartment]);
        if ($filterPositionType !== null && $filterPositionType !== '') $departmentQuery->andWhere(['e.employee_type_id' => $filterPositionType]);
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $departmentQuery->andWhere(new \yii\db\Expression($workgroupDeptWhereSql('e')));
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
        $sbWhere = "branch = 'MAIN' AND id <> 1 AND " . self::inServiceWhereSql('');
        $sbParams = [':as_of' => $asOfDate];
        if ($filterGender !== null) { $sbWhere .= " AND gender = :gender"; $sbParams[':gender'] = $filterGender; }
        if ($filterDepartment !== null && $filterDepartment !== '') { $sbWhere .= " AND department = :department"; $sbParams[':department'] = $filterDepartment; }
        if ($filterPositionType !== null && $filterPositionType !== '') { $sbWhere .= " AND employee_type_id = :employee_type_id"; $sbParams[':employee_type_id'] = $filterPositionType; }
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $sbWhere .= ' AND ' . $workgroupDeptWhereSql('');
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
                    WHEN hire_date IS NULL THEN 'ไม่ระบุ'
                    WHEN TIMESTAMPDIFF(YEAR, hire_date, :as_of) < 1 THEN 'น้อยกว่า 1 ปี'
                    WHEN TIMESTAMPDIFF(YEAR, hire_date, :as_of) < 5 THEN '1 - 5 ปี'
                    WHEN TIMESTAMPDIFF(YEAR, hire_date, :as_of) < 10 THEN '5 - 10 ปี'
                    WHEN TIMESTAMPDIFF(YEAR, hire_date, :as_of) < 20 THEN '10 - 20 ปี'
                    ELSE '20 ปีขึ้นไป'
                END AS band,
                COUNT(id) AS cnt
             FROM {$snapshotSql} snap
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

        // อายุงานเฉลี่ย (ปี) – เฉพาะคนที่ทราบวันบรรจุ
        $avgWhere = "branch = 'MAIN' AND id <> 1 AND " . self::inServiceWhereSql('') . " AND hire_date IS NOT NULL";
        $avgParams = [':as_of' => $asOfDate];
        if ($filterGender !== null) { $avgWhere .= " AND gender = :gender"; $avgParams[':gender'] = $filterGender; }
        if ($filterDepartment !== null && $filterDepartment !== '') { $avgWhere .= " AND department = :department"; $avgParams[':department'] = $filterDepartment; }
        if ($filterPositionType !== null && $filterPositionType !== '') { $avgWhere .= " AND employee_type_id = :employee_type_id"; $avgParams[':employee_type_id'] = $filterPositionType; }
        if ($filterWorkgroup !== null && $filterWorkgroup !== '') {
            $avgWhere .= ' AND ' . $workgroupDeptWhereSql('');
        }
        if ($filterGen !== null && isset($genYearRanges[$filterGen])) {
            $yr = $genYearRanges[$filterGen];
            $avgWhere .= " AND birthday IS NOT NULL AND YEAR(birthday) BETWEEN :gen0 AND :gen1";
            $avgParams[':gen0'] = $yr[0]; $avgParams[':gen1'] = $yr[1];
        }
        if ($filterPositionName !== null && $filterPositionName !== '') { $avgWhere .= " AND employee_position_id = :employee_position_id"; $avgParams[':employee_position_id'] = $filterPositionName; }
        if ($filterServiceBand !== null && $filterServiceBand !== '') { $avgWhere .= " AND (" . self::serviceBandWhereSql('', $filterServiceBand) . ")"; }
        $avgYearsService = Yii::$app->db->createCommand(
            "SELECT ROUND(AVG(TIMESTAMPDIFF(YEAR, hire_date, :as_of)), 1) AS avg_years FROM {$snapshotSql} snap WHERE {$avgWhere}"
        )->bindValues($avgParams)->queryScalar();
        $avgYearsService = $avgYearsService !== null ? round((float) $avgYearsService, 1) : null;
        $organizationDiagramCount = (int) Organization::find()->where(['tb_name' => 'diagram'])->count('id');
        $teamGroupCount = (int) TeamGroup::find()->count('id');
        $dashboardTooltipPeople = self::buildDashboardTooltipPeople($baseQuery(), $genYearRanges, $serviceBandLabels, $ageCategories, $departmentLabelMap, $asOfDate);

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
            'leftThisYearBreakdown' => $leftThisYearBreakdown,
            'movementBudgetYear' => $budgetYear,
            'movementRangeStart' => $budgetRange['start'],
            'movementRangeEnd' => $budgetRange['end'],
            'movementPeriodText' => \app\components\ThaiDateHelper::formatThaiDateRange($budgetRange['start'], $budgetRange['end']),
            'budgetYear' => $budgetYear,
            'currentBudgetYear' => $currentBudgetYear,
            'budgetYearOptions' => $budgetYearOptions,
            'isCurrentBudgetYear' => $isCurrentBudgetYear,
            'asOfDate' => $asOfDate,
            'asOfDateText' => \app\components\ThaiDateHelper::formatThaiDate($asOfDate),
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
        $expr = new \yii\db\Expression('TIMESTAMPDIFF(YEAR, hire_date, :as_of)');
        switch ($label) {
            case 'ไม่ระบุ':
                return ['hire_date' => null];
            case 'น้อยกว่า 1 ปี':
                return ['and', ['not', ['hire_date' => null]], ['<', $expr, 1]];
            case '1 - 5 ปี':
                return ['and', ['not', ['hire_date' => null]], ['>=', $expr, 1], ['<', $expr, 5]];
            case '5 - 10 ปี':
                return ['and', ['not', ['hire_date' => null]], ['>=', $expr, 5], ['<', $expr, 10]];
            case '10 - 20 ปี':
                return ['and', ['not', ['hire_date' => null]], ['>=', $expr, 10], ['<', $expr, 20]];
            case '20 ปีขึ้นไป':
                return ['and', ['not', ['hire_date' => null]], ['>=', $expr, 20]];
            default:
                return [];
        }
    }

    /**
     * เงื่อนไข SQL สำหรับ filter ช่วงอายุงาน (ใช้กับ raw SQL หรือ Query + Expression)
     * @param string $alias alias ตาราง employees เช่น 'e' หรือ '' สำหรับไม่มี alias
     */
    private static function buildDashboardTooltipPeople($query, array $genYearRanges, array $serviceBandLabels, array $ageCategories, array $departmentLabelMap = [], ?string $asOfDate = null): array
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
        foreach (self::dashboardWorkgroupMap() as $departmentCode => $workgroup) {
            $departmentCode = (string) $departmentCode;
            $workgroupCode = (string) $workgroup['id'];
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
                        WHEN YEAR(:as_of) - YEAR(birthday) < 20 THEN '20+'
                        WHEN YEAR(:as_of) - YEAR(birthday) >= 60 THEN '60+'
                        ELSE CONCAT(
                            5 * FLOOR((YEAR(:as_of) - YEAR(birthday)) / 5),
                            ' - ',
                            5 * FLOOR((YEAR(:as_of) - YEAR(birthday)) / 5) + 4
                        )
                    END
                "),
                'join_date' => 'hire_date',
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
                $age = self::dashboardAgeBucket($row['birthday'] ?? null, $ageCategories, $asOfDate);
            }
            $serviceBand = self::dashboardServiceBandBucket($row['join_date'] ?? null, $serviceBandLabels, $asOfDate);

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

    private static function dashboardAgeBucket($birthday, array $ageCategories, ?string $asOfDate = null): ?string
    {
        $age = self::dashboardYearDiff($birthday, $asOfDate);
        if ($age === null) {
            return null;
        }
        return self::dashboardNumericBucketLabel($age, $ageCategories, $age >= 60 ? '60+' : null);
    }

    /**
     * ช่วงอายุงาน – ต้องใช้เกณฑ์เดียวกับ SQL ที่สร้างชาร์ต ไม่งั้น tooltip รายชื่อ
     * จะไม่ตรงกับแท่งในชาร์ต (เดิมเดาจากตัวเลขในป้ายกำกับ ทำให้ช่วงแรกและช่วงสุดท้ายหลุด)
     */
    private static function dashboardServiceBandBucket($joinDate, array $serviceBandLabels, ?string $asOfDate = null): ?string
    {
        $years = self::dashboardYearDiff($joinDate, $asOfDate);
        if ($years === null) {
            return 'ไม่ระบุ';
        }
        if ($years < 1) {
            return 'น้อยกว่า 1 ปี';
        }
        if ($years < 5) {
            return '1 - 5 ปี';
        }
        if ($years < 10) {
            return '5 - 10 ปี';
        }
        if ($years < 20) {
            return '10 - 20 ปี';
        }
        return '20 ปีขึ้นไป';
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

    private static function dashboardYearDiff($date, ?string $asOfDate = null): ?int
    {
        $date = trim((string)$date);
        if ($date === '' || $date === '0000-00-00') {
            return null;
        }
        try {
            $asOf = new \DateTimeImmutable($asOfDate !== null && $asOfDate !== '' ? $asOfDate : 'today');
            return (int)(new \DateTimeImmutable($date))->diff($asOf)->y;
        } catch (\Exception $e) {
            return null;
        }
    }

    private static function serviceBandWhereSql($alias, $label)
    {
        $p = $alias !== '' ? $alias . '.' : '';
        $years = 'TIMESTAMPDIFF(YEAR, ' . $p . 'hire_date, :as_of)';
        switch ($label) {
            case 'ไม่ระบุ':
                return $p . 'hire_date IS NULL';
            case 'น้อยกว่า 1 ปี':
                return $p . 'hire_date IS NOT NULL AND ' . $years . ' < 1';
            case '1 - 5 ปี':
                return $p . 'hire_date IS NOT NULL AND ' . $years . ' >= 1 AND ' . $years . ' < 5';
            case '5 - 10 ปี':
                return $p . 'hire_date IS NOT NULL AND ' . $years . ' >= 5 AND ' . $years . ' < 10';
            case '10 - 20 ปี':
                return $p . 'hire_date IS NOT NULL AND ' . $years . ' >= 10 AND ' . $years . ' < 20';
            case '20 ปีขึ้นไป':
                return $p . 'hire_date IS NOT NULL AND ' . $years . ' >= 20';
            default:
                return '1=1';
        }
    }
}

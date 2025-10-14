<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\LogHelper;
use app\models\UploadCsvForm;
use app\components\UserHelper;

use app\modules\hr\models\Leave;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// Microsoft Excel
use PhpOffice\PhpSpreadsheet\Style\Border;
use app\modules\inventory\models\Warehouse;
use app\modules\hr\models\LeaveEntitlements;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use app\modules\hr\models\LeaveEntitlementsSearch;

/**
 * LeaveEntitlementsController implements the CRUD actions for LeaveEntitlements model.
 */
class LeaveEntitlementsController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all LeaveEntitlements models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LeaveEntitlementsSearch([
            'thai_year' => AppHelper::YearBudget()
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
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

        // $dataProvider->query->orderBy(['emp_id' => SORT_ASC]); // เรียงจากน้อยไปมาก

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single LeaveEntitlements model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new LeaveEntitlements model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new LeaveEntitlements([
            'thai_year' => $this->request->get('thai_year') ?? AppHelper::YearBudget()
        ]);
        $model->scenario = 'no-thai-year';
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $check = LeaveEntitlements::find()
                    ->where(['emp_id' => $model->emp_id])
                    ->andWhere(['leave_type_id' => $model->leave_type_id])
                    ->andWhere(['thai_year' => $model->thai_year])
                    ->one();
                if ($check) {
                    return [
                        'status' => 'error',
                        'message' => 'มีการบันทึกข้อมูลไว้แล้ว'
                    ];
                }
                $json = [
                    'max_days' => $model->calLeaveMaxDays(),
                ];
                $model->data_json = ArrayHelper::merge($model->data_json, $json);
                if ($model->save()) {

                    $data = [
                        'title' => 'กำหนดสิทธิลาพักผ่อน',
                        'data' => $model
                    ];
                    LogHelper::log('leaev_entitlements', $data);

                    return [
                        'status' => 'success',
                        'message' => 'บันทึกข้อมูลสำเร็จ',
                        'container' => '#leave'
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionLeaveSummaryByEmp()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $emp_id = $this->request->get('emp_id');
        $thai_year = $this->request->get('thai_year');
        $model = LeaveEntitlements::find()->where(['emp_id' => $emp_id, 'thai_year' => ($thai_year - 1)])->one();
        return $model->leaveSummaryDays();
    }

    public function actionCreateAll()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            $thaiYear = $this->request->post('thai_year');
            $check = LeaveEntitlements::find()->andWhere(['thai_year' => $thaiYear])->count();

            if ($check > 0) {
                return [
                    'status' => false,
                    'message' => 'ปีงบประมาณ ' . $thaiYear . ' ได้กำหนดวันลาเรียบร้อยแล้ว'
                ];
            }

            $data = [];
            foreach (Employees::find()->where(['status' => 1])->andWhere(['<>', 'id', 1])->all() as $emp) {
                $leaveBefore = LeaveEntitlements::find()->where(['emp_id' => $emp->id, 'thai_year' => ($thaiYear - 1)])->one();

                $newModel = new LeaveEntitlements();
                $newModel->emp_id = $emp->id;
                $newModel->leave_type_id = 'LT4';
                $newModel->position_type_id = $emp->position_type;
                $newModel->year_of_service = isset($emp->workYear()['year']) ? $emp->workYear()['year'] : 0;
                $newModel->month_of_service = isset($emp->workYear()['month']) ? $emp->workYear()['month'] : 0;
                $newModel->days =  isset($leaveBefore) ? $leaveBefore->leaveSummaryDays()['leave_forward_days'] : 0;
                $newModel->thai_year = $thaiYear;
                $newModel->data_json = [
                    'before_days' => isset($leaveBefore->days) ? $leaveBefore->days : 0,
                    'before_leave_use' => isset($leaveBefore) ?  $leaveBefore->leaveSummaryDays()['leave_use'] : 0,
                    'before_leave_balance' => isset($leaveBefore) ? $leaveBefore->leaveSummaryDays()['leave_balance'] : 0,
                    'leave_max_days' => $newModel->calLeaveMaxDays()['leave_max_days'],
                    'accumulation' => $newModel->calLeaveMaxDays()['accumulation'],
                ];
                $newModel->save(false);
            }
            return [
                'status' => 'success',
                'container' => '#leave'
            ];
        }
    }

    // กำหนดสิทธทั้งหมด
    public function actionCreateAllOld()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            $thaiYear = $this->request->post('thai_year');

            $check = LeaveEntitlements::find()->andWhere(['thai_year' => $thaiYear])->count();

            if ($check > 0) {
                return [
                    'status' => false,
                    'message' => 'ปีงบประมาณ ' . $thaiYear . ' ได้กำหนดวันลาเรียบร้อยแล้ว'
                ];
            }

            $sql = "SELECT x4.*, (x4.days - x4.days_use) AS days_balance,
                        (
                            CASE 
                                WHEN x4.accumulation = 0 THEN 10
                                WHEN (x4.days - x4.days_use + 10) > x4.max_days AND x4.accumulation = 1 THEN x4.max_days
                                ELSE (x4.days - x4.days_use + 10)
                            END
                        ) AS froward_days
                    FROM (
                        SELECT 
                            x3.*,
                            COALESCE(
                                (SELECT days 
                                FROM leave_entitlements 
                                WHERE emp_id = x3.emp_id 
                                AND leave_type_id = x3.leave_type_id 
                                AND thai_year = :thai_year), 
                                0
                            ) AS days,
                            COALESCE(
                                (SELECT SUM(total_days) 
                                FROM `leave` 
                                WHERE emp_id = x3.emp_id 
                                AND leave_type_id = x3.leave_type_id 
                                AND thai_year = :thai_year), 
                                0
                            ) AS days_use
                        FROM (
                            SELECT 
                                x2.*,
                                COALESCE((
                                    SELECT max_days 
                                    FROM `leave_policies` 
                                    WHERE position_type_id = x2.position_type 
                                    AND year_of_service <= x2.years_of_service 
                                    ORDER BY year_of_service DESC 
                                    LIMIT 1
                                ),0) AS max_days
                            FROM (
                                SELECT 
                                    x1.*
                                FROM (
                                    SELECT 
                                        e.id AS emp_id,
                                        CONCAT(e.fname, ' ', e.lname) AS fullname,
                                        lt.title AS leave_type_name,
                                        l.leave_type_id,
                                        e.position_type,
                                        pt.title AS position_type_name,
                                        COALESCE(lp.accumulation,0) as accumulation,
                                        TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) AS years_of_service
                                    FROM employees e
                                    LEFT JOIN leave_policies lp 
                                        ON lp.position_type_id = e.position_type
                                    LEFT JOIN `leave` l 
                                        ON e.id = l.emp_id 
                                    AND l.leave_type_id = 'LT4'
                                    JOIN categorise lt 
                                        ON l.leave_type_id = lt.code 
                                    AND lt.name = 'leave_type'
                                    JOIN categorise pt 
                                        ON e.position_type = pt.code 
                                    AND pt.name = 'position_type'
                                    AND e.status = 1
                                    GROUP BY e.id
                                    ORDER BY e.id ASC
                                ) AS x1
                            ) AS x2
                        ) AS x3
                    ) AS x4";
            $querys = Yii::$app->db->createCommand($sql)
                ->bindValue('thai_year', ($thaiYear - 1))
                ->queryAll();
            $data = [];
            foreach ($querys as $item) {

                $last_day = LeaveEntitlements::find()->where(['emp_id' => $item['emp_id'], 'thai_year' => ($thaiYear - 1)])->one();
                $newModel = new LeaveEntitlements(
                    [
                        'emp_id' => $item['emp_id'],
                        'leave_type_id' => $item['leave_type_id'],
                        'position_type_id' => $item['position_type'],
                        'year_of_service' => (int)$item['years_of_service'],
                        'month_of_service' => 0,
                        'days' => $item['froward_days'],
                        'thai_year' => $thaiYear,
                        'data_json' => [
                            'before_days' => $last_day ? ($last_day->getSummary()['leave_total'] ?? 0) : 0,
                            // 'before_days' => $item['days'],
                            'before_leave_use' => $item['days_use'],
                            'before_leave_balance' => $item['days_balance'],
                            'leave_max_days' => $item['max_days'],
                            'accumulation' => $item['accumulation'],
                        ]
                    ]
                );
                if ($newModel->save(false)) {
                    $data[] = [
                        'emp_id' => $item['emp_id'],
                        'fullname' => $item['fullname'],
                    ];
                }
            }
            return [
                'status' => 'success',
                'container' => '#leave'
            ];




            return $this->request->post();
        } else {
            return 'No';
        }
    }

    /**
     * Updates an existing LeaveEntitlements model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                try {

                $me = UserHelper::GetEmployee();
                $data = [
                    "fullname" => $me->fullname,
                    'title' => 'แก้ไขสิทธิลาพักผ่อน',
                    'data' => $model
                ];
                LogHelper::log('leaev_entitlements', $data);
                                    
                } catch (\Throwable $th) {

                }
                return [
                    'status' => 'success',
                    'message' => 'บันทึกข้อมูลสำเร็จ',
                    'container' => '#leave'
                ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing LeaveEntitlements model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $this->findModel($id)->delete();

        return [
            'status' => 'success',
            'message' => 'ลบข้อมูลสำเร็จ',
            'container' => '#leave'
        ];
    }

    public function actionUpdateMaxDays()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        try {

            $thaiYear = $this->request->get('thai_year');

            $max_days = LeaveEntitlements::find()
                ->where(['thai_year' => $thaiYear])
                ->all();

            foreach ($max_days as $item) {
                $leavePolicy = \app\modules\hr\models\LeavePolicies::find()
                    ->where(['position_type_id' => $item->position_type_id])
                    ->andWhere(['<=', 'year_of_service', $item->year_of_service])
                    ->orderBy(['year_of_service' => SORT_DESC])
                    ->one();
                if ($leavePolicy) {
                    $item->data_json = ArrayHelper::merge($item->data_json, ['max_days' => $leavePolicy->max_days]);
                    $item->save(false);
                }
            }

            return [
                'status' => 'success',
                'max_days' => $max_days
            ];
            //code...
        } catch (\Throwable $th) {
            return [
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาด',
                'msg' => $th->getMessage()
            ];
        }
    }

    public function actionExport()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new LeaveEntitlementsSearch([
            'thai_year' => AppHelper::YearBudget()
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
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

        $dataProvider->pagination = false;
        $data = [];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('สรุปรายการ');  // ตั้งชื่อแผ่นงานที่สอง
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'รายงานกำหนดวันลาประจำปีงบประมาณ ' . $searchModel->thai_year);
        $sheet->setCellValue('A2', 'ชื่อ-นามสกุล');
        $sheet->setCellValue('A2', 'ชื่อ-นามสกุล');
        $sheet->setCellValue('B2', 'อายุงาน');
        $sheet->setCellValue('C2', 'ประเภท');
        $sheet->setCellValue('D2', 'ยอดยกมา');
        $sheet->setCellValue('E2', 'สิทธิพักผ่อนประจำปี');
        $sheet->setCellValue('F2', 'สะสมวันลาสูงสุด');
        $sheet->setCellValue('G2', 'รวมสิทธิที่ลาได้');
        $sheet->setCellValue('H2', 'ใช้ไปแล้ว');
        $sheet->setCellValue('I2', 'วันลาคงเหลือ');

        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);

        $StartRowSheet = 3;
        foreach ($dataProvider->getModels() as $item) {
            $numRow = $StartRowSheet++;
            $sheet->setCellValue('A' . $numRow, $item->employee->fullname);
            $sheet->setCellValue('B' . $numRow, $item->employee?->workYear()['ym']);
            $sheet->setCellValue('C' . $numRow, $item->employee?->positionType?->title ?? '-');
            $sheet->setCellValue('D' . $numRow, $item->data_json['before_leave_balance'] ?? '-');
            $sheet->setCellValue('E' . $numRow, 10);
            $sheet->setCellValue('F' . $numRow, isset($item->data_json['leave_max_days']) ? $item->data_json['leave_max_days'] : 0);
            $sheet->setCellValue('G' . $numRow, $item->days);
            $sheet->setCellValue('H' . $numRow, $item->leaveSummaryDays()['leave_use']);
            $sheet->setCellValue('I' . $numRow, $item->leaveSummaryDays()['leave_balance']);
        }
        $setHeader = 'A1:Z3000';
        $sheet->getStyle($setHeader)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
        $sheet->getStyle($setHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($setHeader)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($setHeader)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($setHeader)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($setHeader)->getFill()->getStartColor()->setRGB('8DB4E2');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true)->setItalic(false);
        $sheet->setAutoFilter("A2:I2" . ($StartRowSheet));

        $writer = new Xlsx($spreadsheet);
        $filePath = Yii::getAlias('@webroot') . '/downloads/myStock.xlsx';
        $writer->save($filePath);  // สร้าง excel


        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
    }


    public function actionFormImport()
    {
        $model = new LeaveEntitlements();

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_import', ['model' => $model])
            ];
        } else {
            return $this->render('_form_import', ['model' => $model]);
        }
    }

    public function actionPreview()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new UploadCsvForm();
        $model->csvFile = UploadedFile::getInstanceByName('csvFile');

        if ($model && $model->validate()) {
            // บันทึกไฟล์ชั่วคราว
            $filePath = Yii::getAlias('@runtime') . '/import_' . time() . '.' . $model->csvFile->extension;
            $model->csvFile->saveAs($filePath);

            // อ่าน CSV แถวแรก 10 แถว
            $previewData = [];
            if (($handle = fopen($filePath, "r")) !== false) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $emp = Employees::findOne(['cid' => $data[0]]);
                    // เพิ่มสถานะว่าพบหรือไม่
                    $previewData[] = [
                        'data' => $data,
                        'emp' => $emp,
                        'exists' => $emp ? true : false
                    ];
                    $row++;
                    // if ($row >= 10) break;
                }
                fclose($handle);
            }

            return [
                'status' => 'success',
                'preview' => $previewData,
                'filePath' => $filePath,
            ];
        }

        return [
            'status' => 'error',
            'errors' => $model->getErrors(),
        ];
    }

    /**
     * POST: นำเข้าข้อมูลจริง
     */
    public function actionImportCsv()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $filePath = Yii::$app->request->post('filePath');
        $thaiYear = Yii::$app->request->post('thai_year');

        if (!$filePath || !file_exists($filePath)) {
            return ['status' => 'error', 'message' => 'ไม่พบไฟล์'];
        }

        $imported = 0;
        $errorData = [];
        if (($handle = fopen($filePath, "r")) !== false) {
            $row = 0;
            $error = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $row++;
                if ($row == 1) continue; // ข้าม header
                $emp = Employees::findOne(['cid' => $data[0]]);
                $checkDuplicate = LeaveEntitlements::find()->where(['emp_id' => $emp->id, 'thai_year' => $thaiYear])->one();

                if ($emp) {
                    $workYear = $emp->workYear();
                    $model = new LeaveEntitlements;
                    $model->emp_id = $emp->id;
                    $model->position_type_id = $emp->position_type;
                    $model->leave_type_id = 'LT4';
                    $model->thai_year = $thaiYear;
                    $model->days = $data[9] ?? 0;

                    $model->year_of_service = $data[3] === '' ? ($workYear['year'] ?? 0) : $data[3];
                    $model->month_of_service = $data[4] === '' ? ($workYear['month'] ?? 0) : $data[4];

                    $model->data_json = [
                        'before_leave_balance' => $data[5] ?? 0,
                        'leave_days' => $data[6] ?? 0,
                        'accumulation' => $data[7] ?? 0,
                        'leave_max_days' => $data[8] ?? 0,
                    ];

                    if ($model->save()) {
                        $imported++;
                    } else {
                        $errorData[] = [
                            'status' => false,
                            'fullname' => $data[1] . ' ' . $data[2],
                            'reason' => json_encode($model->getErrors())
                        ];
                    }
                } else {
                    $errorData[] = [
                        'status' => false,
                        'fullname' => $data[1] . ' ' . $data[2],
                        'reason' => 'ไม่พบพนักงานในระบบ'
                    ];
                }
            }
            fclose($handle);
            if ($error >= 1) {
                return [
                    'status' => 'error',
                    'message' => "เกิดข้อผิดพลาด",
                    'errorData' => $errorData
                ];
            }
            return [
                'status' => 'success',
                'container' => '#leave',
                'message' => "นำเข้าข้อมูลเรียบร้อย {$imported} แถว",
            ];
        }
    }



    /**
     * Finds the LeaveEntitlements model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return LeaveEntitlements the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LeaveEntitlements::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

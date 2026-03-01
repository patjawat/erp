<?php

namespace app\modules\development\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\FileHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\components\ThaiDateHelper;
use yii\web\NotFoundHttpException;
use app\modules\development\models\Development;
use app\modules\development\models\DevelopmentSearch;
use app\modules\development\models\DevelopmentDetail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Default controller for the `development` module.
 */
class DefaultController extends Controller
{
    /**
     * จุดเข้าโมดูล — ไปหน้า dashboard
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        return $this->redirect(['/development/default/dashboard']);
    }

    /**
     * Dashboard ภาพรวมอบรม/ประชุม/ดูงาน
     * @return string
     */
    public function actionDashboard()
    {
        $thaiYear = (int) (Yii::$app->request->get('thai_year') ?: AppHelper::YearBudget());
        $summary = Development::getDashboardSummary($thaiYear);
        $yearlySummary = Development::getYearlyDevelopmentSummary($thaiYear);
        $activityType = Development::getActivityTypeSummary($thaiYear);
        $monthlyTrend = Development::getMonthlyTrendSummary($thaiYear);
        $budgetByType = Development::getBudgetByTypeSummary($thaiYear);
        $participationByDept = Development::getParticipationByDepartmentSummary($thaiYear);
        $listSummaryMonth = Development::listSummaryMonth($thaiYear);
        $yearlyCompare = Development::getYearlyCountComparison(6);
        $statusSummary = Development::getStatusSummary($thaiYear);

        $searchModel = new DevelopmentSearch(['thai_year' => $thaiYear]);
        $dataProvider = $searchModel->search([]);
        $dataProvider->query->andWhere([Development::tableName() . '.thai_year' => $thaiYear]);
        $dataProvider->query->joinWith(['developmentType', 'emp', 'statusCategorise']);
        $dataProvider->query->orderBy(['development.id' => SORT_DESC]);
        $dataProvider->pagination = ['pageSize' => 10, 'pageParam' => 'development-page'];

        return $this->render('dashboard', [
            'thaiYear' => $thaiYear,
            'summary' => $summary,
            'yearlySummary' => $yearlySummary,
            'activityType' => $activityType,
            'monthlyTrend' => $monthlyTrend,
            'budgetByType' => $budgetByType,
            'participationByDept' => $participationByDept,
            'listSummaryMonth' => $listSummaryMonth,
            'yearlyCompare' => $yearlyCompare,
            'statusSummary' => $statusSummary,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * รายการกิจกรรมทั้งหมด (ภายในโมดูล — ไม่เชื่อมระบบเก่า)
     * @return string
     */
    public function actionList()
    {
        $thaiYear = (int) (Yii::$app->request->get('thai_year') ?: AppHelper::YearBudget());
        $searchModel = new DevelopmentSearch(['thai_year' => $thaiYear]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere([Development::tableName() . '.thai_year' => $thaiYear]);
        $dataProvider->query->joinWith(['developmentType', 'emp', 'statusCategorise']);
        $dataProvider->query->orderBy(['development.id' => SORT_DESC]);
        $dataProvider->pagination = ['pageSize' => 20, 'pageParam' => 'development-page'];

        return $this->render('list', [
            'thaiYear' => $thaiYear,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Export รายการอบรม/ประชุม/ดูงาน เป็น Excel ตามปีงบประมาณ
     * @return \yii\web\Response
     */
    public function actionExportExcel()
    {
        $thaiYear = (int) (Yii::$app->request->get('thai_year') ?: AppHelper::YearBudget());
        $searchModel = new DevelopmentSearch(['thai_year' => $thaiYear]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere([Development::tableName() . '.thai_year' => $thaiYear]);
        $dataProvider->query->joinWith(['developmentType', 'createdByEmp']);
        $dataProvider->query->orderBy([Development::tableName() . '.date_start' => SORT_DESC, Development::tableName() . '.id' => SORT_DESC]);
        $dataProvider->pagination = false;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $reportTitle = 'ทะเบียนอบรม/ประชุม/ดูงาน ปีงบประมาณ ' . $thaiYear;
        $sheet->setCellValue('A1', $reportTitle);
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'name' => 'TH Sarabun New',
                'size' => 16,
                'bold' => true,
            ],
        ]);

        $headerConfig = [
            'A' => ['title' => 'ลำดับ', 'width' => 6],
            'B' => ['title' => 'เลขบัตรประชาชน', 'width' => 14],
            'C' => ['title' => 'จำนวนวัน', 'width' => 10],
            'D' => ['title' => 'ชื่อ-นามสกุล', 'width' => 28],
            'E' => ['title' => 'หน่วยงานผู้ขอ', 'width' => 28],
            'F' => ['title' => 'หน่วยงานที่จัด', 'width' => 28],
            'G' => ['title' => 'สถานที่จัด', 'width' => 28],
            'H' => ['title' => 'ตั้งแต่วันที่', 'width' => 14],
            'I' => ['title' => 'ถึงวันที่', 'width' => 14],
            'J' => ['title' => 'ประเภทการพัฒนา', 'width' => 32],
            'K' => ['title' => 'หัวข้อการไป', 'width' => 45],
            'L' => ['title' => 'สถานะ', 'width' => 12],
        ];

        $headerStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => ['name' => 'TH Sarabun New', 'size' => 12, 'bold' => true],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => Color::COLOR_BLACK],
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
        ];

        foreach ($headerConfig as $col => $config) {
            $sheet->setCellValue($col . '2', $config['title']);
            $sheet->getColumnDimension($col)->setWidth($config['width']);
        }
        $sheet->getStyle('A2:L2')->applyFromArray($headerStyle);

        $row = 3;
        foreach ($dataProvider->getModels() as $index => $item) {
            $days = $this->getTotalDays($item->date_start, $item->date_end);
            $emp = $item->createdByEmp ?? $item->emp;
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $emp ? ($emp->cid ?? '-') : '-');
            $sheet->setCellValue('C' . $row, $days);
            $sheet->setCellValue('D' . $row, $emp ? $emp->fullname() : ($item->emp_id ?? '-'));
            $sheet->setCellValue('E' . $row, $emp && method_exists($emp, 'departmentName') ? $emp->departmentName() : '-');
            $sheet->setCellValue('F' . $row, isset($item->data_json['location_org']) ? $item->data_json['location_org'] : 'ไม่ระบุ');
            $sheet->setCellValue('G' . $row, isset($item->data_json['location']) ? $item->data_json['location'] : 'ไม่ระบุ');
            $sheet->setCellValue('H' . $row, $item->date_start ? AppHelper::convertToThai($item->date_start) : '-');
            $sheet->setCellValue('I' . $row, $item->date_end ? AppHelper::convertToThai($item->date_end) : '-');
            $sheet->setCellValue('J' . $row, $item->developmentType ? $item->developmentType->title : (isset($item->data_json['development_type_name']) ? $item->data_json['development_type_name'] : 'ไม่ระบุ'));
            $sheet->setCellValue('K' . $row, $item->topic ?? '-');
            $sheet->setCellValue('L' . $row, $item->status ?? '-');
            $row++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 3) {
            $dataRange = 'A3:L' . $lastRow;
            $sheet->getStyle($dataRange)->applyFromArray([
                'font' => ['name' => 'TH Sarabun New', 'size' => 11],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => Color::COLOR_BLACK],
                    ],
                ],
            ]);
            foreach (['A', 'C', 'H', 'I', 'L'] as $col) {
                $sheet->getStyle($col . '3:' . $col . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        $runtimePath = Yii::getAlias('@runtime/export');
        if (!is_dir($runtimePath)) {
            FileHelper::createDirectory($runtimePath, 0775, true);
        }
        $fileName = 'export-development-' . $thaiYear . '-' . date('Ymd-His') . '.xlsx';
        $filePath = $runtimePath . '/' . $fileName;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath, $fileName)
                ->on(\yii\web\Response::EVENT_AFTER_SEND, function ($event) use ($filePath) {
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                });
        }
        throw new \yii\web\NotFoundHttpException('ไม่พบไฟล์ที่ต้องการดาวน์โหลด');
    }

    /**
     * ดูรายละเอียดกิจกรรม (ระบบใหม่ ไม่เชื่อม HR)
     * @param int $id รหัส development
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findDevelopment($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * สร้างรายการใหม่ (ใช้ฟอร์มเดียวกับ update)
     * @return mixed
     */
    public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        $model = new Development([
            'thai_year' => (int) AppHelper::YearBudget(),
            'emp_id' => $me ? $me->id : null,
            'status' => 'Pending',
        ]);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->emp_id = $me ? $me->id : $model->emp_id;
                $model->status = $model->status ?: 'Pending';
                try {
                    $model->date_start = $model->date_start ? AppHelper::convertToGregorian($model->date_start) : null;
                    $model->date_end = $model->date_end ? AppHelper::convertToGregorian($model->date_end) : null;
                    $model->vehicle_date_start = $model->vehicle_date_start ? AppHelper::convertToGregorian($model->vehicle_date_start) : null;
                    $model->vehicle_date_end = $model->vehicle_date_end ? AppHelper::convertToGregorian($model->vehicle_date_end) : null;
                } catch (\Throwable $e) {
                    // ignore
                }
                if ($model->save()) {
                    if (is_array($model->data_json) && !empty($model->data_json['location'])) {
                        AppHelper::checkLocation($model->data_json['location']);
                    }
                    if (is_array($model->data_json) && !empty($model->data_json['location_org'])) {
                        AppHelper::checkLocation($model->data_json['location_org']);
                    }
                    // เพิ่มผู้ขอเป็นสมาชิกผู้ร่วมเดินทางคนแรก
                    $addMember = new DevelopmentDetail([
                        'development_id' => $model->id,
                        'name' => 'member',
                        'emp_id' => $model->emp_id,
                    ]);
                    $addMember->save(false);
                    if (method_exists($model, 'createApprove')) {
                        $model->createApprove();
                    }
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                        return [
                            'status' => 'success',
                            'redirect' => \yii\helpers\Url::to(['/development/default/view', 'id' => $model->id]),
                        ];
                    }
                    return $this->redirect(['/development/default/view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'title' => Yii::$app->request->get('title', 'บันทึกข้อความขอไปราชการ'),
                'content' => $this->renderAjax('create', ['model' => $model]),
            ];
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * แก้ไขรายการ (ภายในโมดูล development)
     * @param int $id รหัส development
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findDevelopment($id);

        try {
            $model->date_start = AppHelper::convertToThai($model->date_start);
            $model->date_end = AppHelper::convertToThai($model->date_end);
            $model->vehicle_date_start = AppHelper::convertToThai($model->vehicle_date_start);
            $model->vehicle_date_end = AppHelper::convertToThai($model->vehicle_date_end);
        } catch (\Throwable $e) {
            // ignore
        }

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                try {
                    $model->date_start = $model->date_start ? AppHelper::convertToGregorian($model->date_start) : null;
                    $model->date_end = $model->date_end ? AppHelper::convertToGregorian($model->date_end) : null;
                    $model->vehicle_date_start = $model->vehicle_date_start ? AppHelper::convertToGregorian($model->vehicle_date_start) : null;
                    $model->vehicle_date_end = $model->vehicle_date_end ? AppHelper::convertToGregorian($model->vehicle_date_end) : null;
                } catch (\Throwable $e) {
                    // ignore
                }
                if ($model->save()) {
                    if (is_array($model->data_json) && !empty($model->data_json['location'])) {
                        AppHelper::checkLocation($model->data_json['location']);
                    }
                    if (is_array($model->data_json) && !empty($model->data_json['location_org'])) {
                        AppHelper::checkLocation($model->data_json['location_org']);
                    }
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                        return [
                            'status' => 'success',
                            'redirect' => \yii\helpers\Url::to(['/development/default/view', 'id' => $model->id]),
                        ];
                    }
                    return $this->redirect(['/development/default/view', 'id' => $model->id]);
                }
            }
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'title' => Yii::$app->request->get('title', 'แก้ไข อบรม/ประชุม/ดูงาน'),
                'content' => $this->renderAjax('update', ['model' => $model]),
            ];
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * แก้ไขสมาชิกผู้ร่วมเดินทาง (development_detail)
     * @param int $id development_detail id
     */
    public function actionUpdateDetail($id)
    {
        $model = DevelopmentDetail::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการที่ต้องการ');
        }

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save(false)) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return ['status' => 'success', 'redirect' => \yii\helpers\Url::to(['/development/default/view', 'id' => $model->development_id])];
                }
                return $this->redirect(['/development/default/view', 'id' => $model->development_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'title' => Yii::$app->request->get('title', 'แก้ไขผู้ร่วมเดินทาง'),
                'content' => $this->renderAjax('_form_member', ['model' => $model, 'modal' => true]),
            ];
        }
        return $this->render('_form_member', ['model' => $model]);
    }

    /**
     * ลบสมาชิกผู้ร่วมเดินทาง
     * @param int $id development_detail id
     */
    public function actionDeleteMember($id)
    {
        $model = DevelopmentDetail::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการที่ต้องการ');
        }
        $developmentId = $model->development_id;
        $model->delete();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['status' => 'success', 'redirect' => \yii\helpers\Url::to(['/development/default/view', 'id' => $developmentId])];
        }
        return $this->redirect(['/development/default/view', 'id' => $developmentId]);
    }

    /**
     * พิมพ์ใบขอไปราชการ (แบบฟอร์ม HTML สำหรับพิมพ์หรือบันทึกเป็น PDF)
     * @param int $id รหัส development
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPrintOfficial($id)
    {
        $model = Development::find()
            ->andWhere([Development::tableName() . '.id' => $id])
            ->joinWith(['developmentType', 'createdByEmp', 'assignedTo', 'vehicleType'])
            ->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการที่ต้องการ');
        }
        $info = SiteHelper::getInfo();
        $this->layout = '@app/views/layouts/print';
        return $this->render('print-official', [
            'model' => $model,
            'info' => $info,
        ]);
    }

    /**
     * โหลด Development ตาม id (สำหรับ view, print)
     * @param int $id
     * @return Development
     * @throws NotFoundHttpException
     */
    protected function findDevelopment($id)
    {
        $model = Development::find()
            ->andWhere([Development::tableName() . '.id' => $id])
            ->joinWith(['developmentType', 'emp', 'createdByEmp', 'assignedTo', 'vehicleType', 'statusCategorise'])
            ->one();
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบรายการที่ต้องการ');
    }

    /**
     * คำนวณจำนวนวันระหว่าง date_start ถึง date_end
     */
    protected function getTotalDays($startDate, $endDate)
    {
        if (!$startDate || !$endDate) {
            return 0;
        }
        $s = new \DateTime($startDate);
        $e = new \DateTime($endDate);
        return $s->diff($e)->days + 1;
    }
}
